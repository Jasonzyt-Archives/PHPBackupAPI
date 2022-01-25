<?php

include "internal.php";

// Set header
header("Content-Type: application/json");
// Check key
if (!checkKey($_REQUEST["key"] ?? null)) {
    exit(errorJson("The key is wrong!"));
}
// Process argument
$id = $_REQUEST["id"] ?? null;
if ($id == null) {
    $arr = array();
    foreach (getBackups() as $backup) {
        $arr[] = json_decode($backup->toJson());
    }
    exit(json_encode([
        "success" => true,
        "count" => count($arr),
        "list" => $arr
    ]));
}
// Get backup
$backup = getBackup($id);
if ($backup == null) {
    exit(errorJson("Backup '$id' is not found!"));
}
$path = getBackupPath() . $backup->id;
exit(json_encode([
    "success" => true,
    "backup" => array_merge(json_decode($backup->toJson(), true),
        ["files" => getFilesOfDirectory($path, strlen($path) + 1)])
], JSON_UNESCAPED_SLASHES));
