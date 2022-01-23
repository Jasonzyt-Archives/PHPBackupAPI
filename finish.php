<?php

include "internal.php";

header('Content-type: application/json');
// Check Key
if (!checkKey($_REQUEST["key"])) {
    exit(errorJson("The key is wrong!"));
}
// Process argument
$id = $_REQUEST["id"];
if ($id == null) {
    exit(errorJson("Missing argument 'id'"));
}
$backup = getBackup($id);
if ($backup == null || !$backup->isUploading) {
    exit(errorJson("Couldn't find a uploading backup with ID '$id'"));
}
$backup->totalFiles = $backup->uploadedFiles;
$backup->isUploading = false;
$backup->save();
exit(json_encode([
    "success" => true,
    "id" => $id,
    "size" => $backup->size,
    "totalFiles" => $backup->totalFiles
]));
