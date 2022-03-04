<?php

include "internal.php";

if (checkPermission("download")) {
    header('Content-type: application/json');
    exit(errorJson("Permission denied"));
}
$id = $_REQUEST["id"];
if ($id == null) {
    header('Content-type: application/json');
    exit(errorJson("Missing argument 'id'"));
}
$file = $_REQUEST["file"];
if ($file == null) {
    header('Content-type: application/json');
    exit(errorJson("Missing argument 'file', you can request query.php?id=xxx to get the file name"));
}
$path = getBackupPath() . $id . "/" . $file;
if (file_exists($path)) {
    header('Content-type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
} else {
    header('Content-type: application/json');
    exit(errorJson("File '$file' not found"));
}