<?php

include "internal.php";

// Set header
header("Content-Type: application/json");

exit(json_encode([
    "success" => true,
    "apiVersion" => getAPIVersion()
]));
