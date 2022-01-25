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
$backup = getBackup($id);
if ($backup == null || !$backup->isUploading) {
    exit(errorJson("Uploading backup '$id' is not found! Maybe it is already uploaded or not created yet or it has been deleted(because of timeout?)"));
}
$backup->totalFiles = $backup->uploadedFiles;
$backup->isUploading = false;
$backup->save();
exit(json_encode([
    "success" => true,
    "id" => $id,
    "size" => $backup->size,
    "uploadedFiles" => $backup->totalFiles
]));
