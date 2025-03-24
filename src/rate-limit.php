<?php
/**
 * @package     Rate Limit
 * @version     v1.1.0
 * @author      Szabó Levente
 * @link        https://github.com/szabolevi98
 */
class RateLimit {

    /**
     * Max requests
     *
     * @var int
     */
    private int $maxRequests;
    /**
     * Interval in seconds
     *
     * @var int
     */
    private int $interval;
    /**
     * Path to store data
     *
     * @var string
     */
    private string $cachePath;
    /**
     * Chance to clean old files (1-100)
     *
     * @var int
     */
    private int $cleanupChance;
    /**
     * Clean older files then (in seconds)
     *
     * @var int
     */
    private int $cleanupThreshold;
    /**
     * Message to display alongside with http 429
     *
     * @var string
     */
    private string $message;

    /**
     * @param int $maxRequests
     * @param int $interval
     * @param string $cachePath
     * @param int $cleanupChance
     * @param int $cleanupThreshold
     * @param string $message
     */
    public function __construct(
        int $maxRequests = 50,
        int $interval = 300,
        string $cachePath = "./data",
        int $cleanupChance = 3,
        int $cleanupThreshold = 60*60*24*7,
        string $message = "Too many requests!"
    ) {
        $this->maxRequests = $maxRequests;
        $this->interval = $interval;
        $this->cachePath = $cachePath;
        $this->cleanupChance = $cleanupChance;
        $this->cleanupThreshold = $cleanupThreshold;
        $this->message = $message;
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * @return void
     */
    public function checkLimited(): void
    {
        if (rand(1, 100) <= $this->cleanupChance) {
            $this->cleanupOldFiles();
        }

        $ipHash = hash('sha256', $this->getUserIP());
        $filePath = $this->cachePath . "/" . $ipHash . ".json";
        $currentTime = time();
        $requests = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];

        $requests = array_filter($requests, function ($timestamp) use ($currentTime) {
            return $timestamp > ($currentTime - $this->interval);
        });

        if (count($requests) < $this->maxRequests) {
            $requests[] = $currentTime;
            file_put_contents($filePath, json_encode(array_values($requests)), LOCK_EX);
        } else {
            http_response_code(429);
            exit($this->message);
        }
    }

    /**
     * @return void
     */
    private function cleanupOldFiles(): void
    {
        foreach (glob($this->cachePath . "/*.json") as $file) {
            if (filemtime($file) < (time() - $this->cleanupThreshold)) {
                unlink($file);
            }
        }
    }

    /**
     * @return string
     */
    private function getUserIP(): string
    {
        if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            return (string)$_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return (string)$_SERVER['HTTP_CLIENT_IP'];
        }
        return (string)$_SERVER['REMOTE_ADDR'];
    }
}
