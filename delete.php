<?php

include "internal.php";

// Set header
header('Content-type: application/json');
// Check permission
if (!checkPermission("delete")) {
    exit(errorJson("Permission denied"));
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