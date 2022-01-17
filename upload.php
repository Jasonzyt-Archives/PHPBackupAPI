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
$backup = getUploadingBackup($id);
if ($backup == null) {
    exit(errorJson("Couldn't find a uploading backup with ID '$id'"));
}
if (time() - $backup->timeStamp > getTimeLimit()) {
    $backup->delete();
    exit(errorJson("Uploading time limit exceeded! This backup has been deleted."));
}
// Process file
$file = $_FILES["file"];
if ($file == null) {
    exit(errorJson("Missing argument 'file'"));
}
$fn = $file["name"];
foreach (getBannedFiles() as $bfn) {
    if (fnmatch($bfn, $fn)) {
        exit(errorJson("File name '$fn' is banned"));
    }
}
$dir = $_REQUEST["dir"];
if ($file["error"] == 0) {
    $tmp = $file["tmp_name"];
    $target = getBackupPath() . $id . "/" . $fn;
    move_uploaded_file($tmp, $target);
    exit(json_encode([
        "success" => true,
        "id" => $id,
        "progress" => ++$backup->progress
    ]));
} else {
    exit(errorJson("Failed to upload. Error code: " . $file["error"]));
}