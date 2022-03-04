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
$infoStr = $_REQUEST["info"] ?? "{}";
$info = json_decode($infoStr) ?? array();
$totalFiles = $info->totalFiles ?? 0;
$backup = Backup::create($totalFiles, $info->others ?? null);
exit(json_encode([
    "success" => true,
    "id" => $backup->id,
    "timeStamp" => $backup->timeStamp
]));
