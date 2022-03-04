<?php

include "internal.php";

function addDirectoryToArchive($zip, $path, $base) {
    if (is_dir($path)) {
        $files = glob($path . "/*");
        foreach ($files as $filePath) {
            $file = substr($filePath, $base);
            if (isIgnoredFile($file)) {
                continue;
            }
            if (is_dir($filePath)) {
                $zip->addEmptyDir($file);
                addDirectoryToArchive($zip, $filePath, $base);
            } else {
                $zip->addFile($filePath, $file);
            }
        }
    }
}

if (!checkPermission("downloadZip")) {
    header('Content-type: application/json');
    exit(errorJson("The key is wrong"));
}
$id = $_REQUEST["id"];
if ($id == null) {
    header('Content-type: application/json');
    exit(errorJson("Missing argument 'id'"));
}
$backup = getBackup($id);
if ($backup == null || $backup->isUploading) {
    header('Content-type: application/json');
    exit(errorJson("Backup is not found or it is uploading"));
}
$path = getBackupPath() . $id;
$zip = new ZipArchive();
$tempFile = tempnam(sys_get_temp_dir(), $id);
unlink($tempFile);
if ($zip->open($tempFile, ZipArchive::CREATE) === true) {
    $timeZone = getTimeZone();
    date_default_timezone_set($timeZone);
    $dateStr = date("Y-m-d H:i:s", $backup->timeStamp);
    $othersStr = $backup->others == null ? "null" : json_encode($backup->others);
    $zip->setArchiveComment(
        <<<EOT
This is a backup made by PHPBackupAPI
GitHub: https://github.com/Jasonzyt/PHPBackupAPI

[Backup information]
- ID: $id
- Time: $dateStr($timeZone)
- Files count: $backup->totalFiles
- Source size: $backup->size
- Others: $othersStr
EOT
    );
    if ($backup->totalFiles > 0) {
        addDirectoryToArchive($zip, $path, strlen($path) + 1);
    } else {
        $zip->addEmptyDir(".");
    }
    $zip->close();
    header("Content-Type: application/zip");
    header("Content-Disposition: attachment; filename=Backup-$id.zip");
    header("Content-Length: " . filesize($tempFile));
    readfile($tempFile);
    unlink($tempFile);
} else {
    header('Content-type: application/json');
    exit(errorJson("Failed to create zip file"));
}