# Simple PHP Rate Limiting

### Usage:
Simply require it in your main php file:
`require_once("rate-limiting/rate-limiting.php");`

### Settings:
`$maxRequests = "50";`  
`$interval = "300";`

This means that a user can make 50 requests every 300 seconds before getting **429 Too many requests**.
