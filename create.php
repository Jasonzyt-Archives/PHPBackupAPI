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
    $totalFiles = $info->totalFiles ?? 0;
    $backup = Backup::create($totalFiles, $info->others ?? null);
    exit(json_encode([
        "success" => true,
        "id" => $backup->id,
        "timeStamp" => $backup->timeStamp
    ]));
}
else {
    exit(errorJson("Missing argument 'info'"));
}

