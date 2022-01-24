<?php

include "internal.php";

function addDirectoryToArchive($zip, $path) {
    if (is_dir($path)) {
        $files = glob($path . "/*");
        foreach ($files as $file) {
            if (isIgnoredFile($file)) {
                continue;
            }
            if (is_dir($file)) {
                $zip->addEmptyDir(basename($file));
                addDirectoryToArchive($zip, $file);
            } else {
                $zip->addFile($file, basename($file));
            }
        }
    }
}

if (isAllowDownloadZip()) {
    if (isAllowDownloadWithoutAccessKey() && !checkKey($_REQUEST["key"] ?? null)) {
        header('Content-type: application/json');
        exit(errorJson("The key is wrong"));
    }
    $id = $_REQUEST["id"];
    if ($id == null) {
        header('Content-type: application/json');
        exit(errorJson("Missing argument 'id'"));
    }
    $path = getBackupPath() . $id;
    $zip = new ZipArchive();
    $tempFile = tempnam(sys_get_temp_dir(), $id);
    if ($zip->open($tempFile, ZipArchive::CREATE)) {
        addDirectoryToArchive($zip, $path);
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

}