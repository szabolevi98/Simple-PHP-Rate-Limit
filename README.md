# Simple PHP Rate Limit

Usage as in `index.php`:

    require("./src/rate-limit.php");
    $rateLimit = new RateLimit();
    $rateLimit->checkLimited();

You can set these settings in the constructor:

    int $maxRequests = 50,
    int $interval = 300,
    string $cachePath = "./data",
    int $cleanupChance = 5,
    int $cleanupThreshold = 60*60*24*7,
    string $message = "Too many requests!"

This means that a user can make 50 requests (`$maxRequests`) every 300 seconds (`$interval`) before getting http 429 "Too many requests" (`$message`).  
The path where the system stores data is the data folder (`$cachePath`) and it will clean up older files then 7 days (`$cleanupThreshold`) with a chance of 5% (`$cleanupChance`).
