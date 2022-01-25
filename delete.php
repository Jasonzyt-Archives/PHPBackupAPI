<?php

include "internal.php";

// Set header
header('Content-type: application/json');
// Check key
if (!checkKey($_REQUEST["key"] ?? null)) {
    exit(errorJson("The key is wrong!"));
}
// Process argument
$id = $_REQUEST["id"];
if ($id == null) {
    exit(errorJson("Missing argument 'id'"));
}
// Get backup
$backup = getBackup($id);
if ($backup == null || !$backup->isUploading) {
    exit(errorJson("Backup '$id' is not found!"));
}
$backup->delete();
exit(json_encode([
    "success" => true,
    "id" => $id
]));