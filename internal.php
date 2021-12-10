<?php

include "config.php";
///////////////////////////////////// CONST /////////////////////////////////////
$uploading = "uploading.json";
$banExtensions = ["php", "phar"];

///////////////////////////////////// UTILS /////////////////////////////////////
function checkKey($k): bool
{
    global $accessKey;
    return $k == $accessKey;
}
function getBackupPath(): string
{
    global $backupPath;
    return $backupPath;
}

function generateBackupID(): string
{
    $bytes = random_bytes(20);
    return bin2hex($bytes);
}

function getBackupFromUploading($id): ?Backup {
    global $uploading;
    $content = file_get_contents($uploading);
    $json = json_decode($content);
    if ($json[$id] != null) {
        return Backup::fromJson($json[$id]);
    }
    return null;
}

function errorJson($str): string
{
    return json_encode([
        "success" => false,
        "reason" => $str
    ]);
}

class Backup {

    public var string $id = "";
    public var string $path = "";
    public var int $timeStamp = 0;
    public var string $others = "";
    // Upload
    public var int $totalFiles = 0;
    public var int $progress = 0;
    //public var array $fileNames = [];

    public function __construct($id, $time, $others) {
        $this->id = $id;
        $this->timeStamp = $time;
        $this->others = $others;
    }

    public function isUploadDone(): bool
    {
        return $this->totalFiles == $this->progress;
    }

    public function writeToUploading()
    {
        global $uploading;
        $content = file_get_contents($uploading);
        $json = json_decode($content);
        $json[$this->id] = $this;
        $content = json_encode($json);
        file_put_contents("uploading.json", $content);
    }

    public static function fromJson($obj) {
        $backup = new Backup($obj["id"], $obj["timeStamp"], $obj["others"]);
        $backup->totalFiles = $obj["totalFiles"];
        $backup->progress = $obj["progress"];
        return $backup;
    }
}
