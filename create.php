<?php

include "internal.php";

// Set header
header('Content-type: application/json');
// Check permission
if (!checkPermission("create")) {
    exit(errorJson("Permission denied"));
}
// Process backup information
$backup = null;
$totalFiles = $_REQUEST["totalFiles"] ?: 0;
$others = json_decode($_REQUEST["others"]);
$backup = Backup::create($totalFiles, $others);
exit(json_encode([
    "success" => true,
    "id" => $backup->id,
    "timeStamp" => $backup->timeStamp
]));
