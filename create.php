<?php

include "internal.php";

// Set header
header('Content-type: application/json');
// Check key
if (!checkKey($_REQUEST["key"] ?? null)) {
    exit(errorJson("The key is wrong!"));
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
