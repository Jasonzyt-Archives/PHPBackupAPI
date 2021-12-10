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
$backup = getBackupFromUploading($id);
if ($backup == null) {
    exit(errorJson("Couldn't find a uploading backup with ID '$id'"));
}
// Process file
$file = $_FILES["file"];
if ($file == null) {
    exit(errorJson("Missing argument 'file'"));
}
$arr = explode('.', $file["name"]);
$ext = end($arr);
global $banExtensions;
if (in_array($ext, $banExtensions)) {
    exit(errorJson("File extension '$ext' is banned"));
}
