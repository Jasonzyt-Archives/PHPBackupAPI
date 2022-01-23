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
// Process file
$file = $_FILES["file"];
if ($file == null) {
    exit(errorJson("Missing argument 'file'"));
}
if ($backup->size + $file["size"] > getMaxSize()) {
    exit(errorJson("Backup size limit exceeded! Max backup size: " . getMaxSize() . " MB"));
}
$fn = $file["name"];
foreach (getBannedFiles() as $bfn) {
    if (fnmatch($bfn, $fn)) {
        exit(errorJson("File name '$fn' is banned"));
    }
}
$dir = $_REQUEST["dir"];
if ($file["error"] == 0) {
    if ($file["size"] > getMaxFileSize()) {
        exit(errorJson("File size limit exceeded! Max file size: " . getMaxFileSize() . " MB"));
    }
    $tmp = $file["tmp_name"];
    $target = getBackupPath() . $id . "/" . $fn;
    move_uploaded_file($tmp, $target);
    $backup->size += $file["size"];
    ++$backup->uploadedFiles;
    if ($backup->totalFiles == 0) {
        exit(json_encode([
            "success" => true,
            "id" => $id,
            "uploadedFiles" => $backup->uploadedFiles
        ]));
    }
    if ($backup->totalFiles == $backup->uploadedFiles) {
        $backup->isUploading = false;
        $backup->save();
    }
    exit(json_encode([
        "success" => true,
        "id" => $id,
        "uploadedFiles" => $backup->uploadedFiles,
        "totalFiles" => $backup->totalFiles,
        "done" => $backup->totalFiles == $backup->uploadedFiles
    ]));
} else {
    exit(errorJson("Failed to upload. Error code: " . $file["error"]));
}