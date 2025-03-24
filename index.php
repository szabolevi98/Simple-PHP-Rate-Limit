<?php
    require("./src/rate-limit.php");
    $rateLimit = new RateLimit();
    $rateLimit->checkLimited();
    echo "OK";
