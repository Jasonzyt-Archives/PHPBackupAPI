<?php

include "internal.php";

function addDirectoryToArchive($zip, $path) {
    if (is_dir($path)) {
        $files = glob($path . "/*");
        foreach ($files as $file) {
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
    if (isAllowDownloadWithoutAccessKey() && !checkKey($_REQUEST["key"])) {
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
    if ($zip->open("temp/$id.zip", ZipArchive::CREATE)) {
        addDirectoryToArchive($zip, $path);
        $zip->close();
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=Backup-$id.zip");
        header("Content-Length: " . filesize("temp/$id.zip"));
        readfile("temp/$id.zip");
        unlink("temp/$id.zip");
    } else {
        header('Content-type: application/json');
        exit(errorJson("Failed to create zip file"));
    }

}