<?php
/* Simple PHP Rate Limiting */
$maxRequests = "50";
$interval = "300";
$dbFilePath = "rate-limiting/database.json";
$maxFileSize = 10 * 1024 * 1024;

if (!(file_exists($dbFilePath))) {
    fopen($dbFilePath, "w") or die("Unable to open file $dbFilePath!");;
}

$fileSize = filesize($dbFilePath);
if ($fileSize !== false && $fileSize > $maxFileSize) { // It should never reach the limit. But if ever, we're deleting the db to avoid long processing time.
    file_put_contents($dbFilePath, '');
} else {
    $dbFile = file_get_contents($dbFilePath, LOCK_EX);
    $oldDb = json_decode($dbFile, true);
    $newDb = [];
    foreach ($oldDb as $oldDbValue) {
        if (strtotime($oldDbValue["time"]) > (time() - $interval)) { // Only keep the requests in the $interval so the db gets cleaned
            $newDb[] = $oldDbValue;
        }
    }
    $ip = getUserIP();
    $newDb[] = ["ip" => $ip, "time" => date("Y-m-d H:i:s")];
    file_put_contents($dbFilePath, json_encode($newDb));
    $count = 0;
    foreach ($newDb as $record) {
        if ($ip == $record["ip"]) {
            $count++;
            if ($count >= $maxRequests) {
                http_response_code(429);
                die("Too Many Requests!");
            }
        }
    }
}

function getUserIP() {
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) { //Get real visitor IP behind CloudFlare network
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')>0) {
            $addr = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($addr[0]);
        } else {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    else if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    else {
        return $_SERVER['REMOTE_ADDR'];
    }
}
