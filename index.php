<?php
    require("./src/rate_limit.php");
    $rateLimit = new RateLimit();
    $rateLimit->checkLimited();
    echo "OK";
