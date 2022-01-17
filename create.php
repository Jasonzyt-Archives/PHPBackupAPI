<?php

include "internal.php";
header('Content-type: application/json');
// Check Key
if (!checkKey($_REQUEST["key"])) {
    exit(errorJson("The key is wrong!"));
}
// Process backup information
$backup = null;
if ($_REQUEST["info"] != null) {
    $info = json_decode($_REQUEST["info"]);
    if ($info == null) {
        exit(errorJson("Invalid argument 'info'"));
    }
    if ($info["totalFiles"] == null) {
        exit(errorJson("Missing 'totalFiles' in argument 'info'"));
    }
    $id = generateBackupID();
    $backup = new UploadingBackup($id, time(), $info["others"]);
    $backup->totalFiles = $info["totalFiles"];
    $backup->write();
    exit(json_encode([
        "success" => true,
        "id" => $id,
        "timeStamp" => $backup->timeStamp
    ]));
}
else {
    exit(errorJson("Missing argument 'info'"));
}

