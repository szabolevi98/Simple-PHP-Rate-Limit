# Simple PHP Rate Limit

Usage as in `index.php`:

    require("./src/rate_limit.php");
    $rateLimit = new RateLimit();
    $rateLimit->checkLimited();

You can set these settings in the constructor:

    int $maxRequests = 75,
    int $interval = 300,
    string $cachePath = "./data",
    int $cleanupChance = 3,
    ?int $cleanupThreshold = 3600, // if empty then it will be $interval +30 
    string $message = "Too many requests!"

This means that a user can make 75 requests (`$maxRequests`) every 300 seconds (`$interval`) before getting http 429 "Too many requests" (`$message`).  
The path where the system stores data is the "./data" folder (`$cachePath`) and it will clean up older files then 7 days (`$cleanupThreshold`) with a chance of 3% (`$cleanupChance`).
