<?php

include "config.php";
///////////////////////////////////// CONST /////////////////////////////////////
$bakInfoFile = "_backup_info.json";
$uploadingInfoFile = "_uploading_info.json";
$bannedFiles = ["*.inc", "*.phtml", "*.module", "*.php?", "*.hphp", "*.ctp", "*.php", "*.phar",
    "_bak_info.json", "_uploading_info.json"];

function getBackupInfoFileName(): string {
    global $bakInfoFile;
    return $bakInfoFile;
}

function getUploadingInfoFileName(): string {
    global $uploadingInfoFile;
    return $uploadingInfoFile;
}

function getBannedFiles(): array {
    global $bannedFiles;
    return $bannedFiles;
}

function getAccessKey(): string {
    global $accessKey;
    return $accessKey;
}

function getBackupPath(): string {
    global $backupPath;
    return $backupPath;
}

function getTimeLimit(): int {
    global $timeLimit;
    return $timeLimit;
}

function getMaxSize(): int {
    global $maxSize;
    return $maxSize;
}

function isAllowDownloadWithoutAccessKey(): bool {
    global $allowDownloadWithoutAccessKey;
    return $allowDownloadWithoutAccessKey;
}

function isAllowDownloadZip(): bool {
    global $allowDownloadZip;
    return $allowDownloadZip;
}

///////////////////////////////////// VARS //////////////////////////////////////
$backups   = loadBackupList();
$uploading = loadUploadingList();

function getBackups(): array {
    global $backups;
    return $backups;
}

function getUploadingBackups(): array {
    global $uploading;
    return $uploading;
}

///////////////////////////////////// UTILS /////////////////////////////////////

function checkKey($k): bool {
    return $k == getAccessKey();
}

function generateBackupID(): string {
    $time = strftime("%Y%m%d%H");
    $bytes = random_bytes(20);
    return $time . '_' . bin2hex($bytes);
}

function getUploadingBackup($id): ?UploadingBackup {
    foreach (getUploadingBackups() as $b) {
        if ($b->id == $id) {
            return $b;
        }
    }
    return null;
}

function loadBackupList(): array {
    $result = array();
    $folders = glob(getBackupPath() . "*", GLOB_ONLYDIR);
    foreach ($folders as $folder) {
        $infoFile = $folder . "/" . getBackupInfoFileName();
        if (file_exists($infoFile)) {
            $content = file_get_contents($infoFile);
            $json = json_decode($content);
            $result[] = Backup::fromJson($json);
        }
    }
    return $result;
}

function loadUploadingList(): array {
    $result = array();
    $folders = glob(getBackupPath() . "*", GLOB_ONLYDIR);
    foreach ($folders as $folder) {
        $infoFile = $folder . "/" . getUploadingInfoFileName();
        if (file_exists($infoFile)) {
            $content = file_get_contents($infoFile);
            $json = json_decode($content);
            $result[] = UploadingBackup::fromJson($json);
        }
    }
    return $result;
}

function deleteDirectory($path) {
    if (is_dir($path)) {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                deleteDirectory($file);
            } else {
                unlink($file);
            }
        }
        rmdir($path);
    }
}

function getSizeOfDirectory($path) {
    $size = 0;
    if (is_dir($path)) {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                $size += getSizeOfDirectory($file);
            } else {
                $size += filesize($file);
            }
        }
    }
    return $size;
}

function errorJson($str): string {
    return json_encode([
        "success" => false,
        "reason" => $str
    ]);
}

class Backup {

    public var string $id = "";
    public var int $timeStamp = 0;
    public var ?object $others = null;
    public var int $totalFiles = 0;

    public function __construct($id, $time, $others) {
        $this->id = $id;
        $this->timeStamp = $time;
        $this->others = $others;
    }

    public function delete() {
        $path = getBackupPath() . $this->id;
        deleteDirectory($path);
    }

    public function size() {
        $path = getBackupPath() . $this->id;
        return getSizeOfDirectory($path);
    }

    public function write() {
        global $backupList;
        $content = file_get_contents($backupList);
        $json = json_decode($content);
        $json[$this->id] = $this;
        $content = json_encode($json);
        file_put_contents($backupList, $content);
    }

    public static function fromJson($obj): Backup {
        $backup = new Backup($obj["id"], $obj["timeStamp"], $obj["others"]);
        $backup->totalFiles = $obj["totalFiles"];
        return $backup;
    }

}

class UploadingBackup extends Backup {
    public var int $progress = 0;

    public function __construct($id, $time, $others) {
        parent::__construct($id, $time, $others);
    }

    public function checkTime(): bool {
        global $timeLimit;
        $time = time();
        return $time - $this->timeStamp < $timeLimit;
    }

    public function delete() {
        $path = getBackupPath() . $this->id;
        deleteDirectory($path);
    }

    public function write() {
        $json = json_encode($this);

    }

    public static function fromJson($obj): UploadingBackup {
        $backup = new UploadingBackup($obj["id"], $obj["timeStamp"], $obj["others"]);
        $backup->totalFiles = $obj["totalFiles"];
        $backup->progress = $obj["progress"];
        return $backup;
    }

}
