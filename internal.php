<?php

include "config.php";
///////////////////////////////////// CONST /////////////////////////////////////
$bakInfoFile = "_backup_info.json";
$bannedFiles = ["*.inc", "*.phtml", "*.module", "*.php?", "*.hphp", "*.ctp", "*.php", "*.phar",
    "_bak_info.json"];

function getBackupInfoFileName(): string {
    global $bakInfoFile;
    return $bakInfoFile;
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

function isDeleteAfterLimitExceeded(): bool {
    global $deleteAfterLimitExceeded;
    return $deleteAfterLimitExceeded;
}

function getMaxSize(): int {
    global $maxSize;
    return $maxSize;
}

function getMaxFileSize(): int {
    global $maxFileSize;
    return $maxFileSize;
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

function getBackups(): array {
    global $backups;
    return $backups;
}


///////////////////////////////////// UTILS /////////////////////////////////////

function checkKey($k): bool {
    return $k == getAccessKey();
}

function generateBackupID(): string {
    $time = strftime("%Y%m%d%H");
    $bytes = random_bytes(20);
    return $time . '_' . substr(bin2hex($bytes), 0, 6);
}

function getBackup($id): ?Backup {
    return getBackups()[$id] ?? null;
}

function loadBackupList(): array {
    $result = array();
    $folders = glob(getBackupPath() . "*", GLOB_ONLYDIR);
    foreach ($folders as $folder) {
        $infoFile = $folder . "/" . getBackupInfoFileName();
        if (file_exists($infoFile)) {
            $content = file_get_contents($infoFile);
            $json = json_decode($content);
            $backup = Backup::fromJson($json);
            if ($backup->isUploading && time() - $backup->lastOperationTime > getTimeLimit()) {
                if (isDeleteAfterLimitExceeded()) {
                    $backup->delete();
                    continue;
                }
                else {
                    $backup->isUploading = false;
                    $backup->totalFiles = $backup->uploadedFiles;
                    $backup->save();
                }
            }
            $backup->size = getSizeOfDirectory($folder);
            $result[$backup->id] = $backup;
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

function getTotalFilesOfDirectory($path): int {
    $total = 0;
    if (is_dir($path)) {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                $total += getTotalFilesOfDirectory($file);
            } else {
                $total += 1;
            }
        }
    }
    return $total;
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
    public var int $uploadedFiles = 0;
    public var int $size = 0;
    public var int $lastOperationTime = 0;
    public var bool $isUploading = false;

    public function delete() {
        $path = getBackupPath() . $this->id;
        deleteDirectory($path);
        unset(getBackups()[$this->id]);
    }

    public function save() {
        $path = getBackupPath() . $this->id;
        if (!file_exists($path)) {
            mkdir($path);
        }
        $infoFile = $path . "/" . getBackupInfoFileName();
        $json = null;
        if ($this->isUploading) {
            $json = json_encode(array(
                "id" => $this->id,
                "timeStamp" => $this->timeStamp,
                "others" => $this->others,
                "totalFiles" => $this->totalFiles,
                "uploadedFiles" => $this->uploadedFiles,
                "lastOperationTime" => $this->lastOperationTime,
                "isUploading" => $this->isUploading
            ));
        } else {
            $json = json_encode(array(
                "id" => $this->id,
                "timeStamp" => $this->timeStamp,
                "others" => $this->others,
                "totalFiles" => $this->totalFiles,
                "isUploading" => $this->isUploading
            ));
        }
        file_put_contents($infoFile, $json);
    }

    public static function fromJson($obj): Backup {
        $backup = new Backup();
        $backup->id = $obj->id;
        $backup->timeStamp = $obj->timeStamp;
        $backup->others = $obj->others;
        $backup->totalFiles = $obj->totalFiles;
        $backup->isUploading = $obj->isUploading;
        if ($backup->isUploading) {
            $backup->uploadedFiles = $obj->uploadedFiles;
            $backup->lastOperationTime = $obj->lastOperationTime;
        }
        return $backup;
    }

    public static function create($totalFiles = 0, $others = null): Backup {
        $backup = new Backup();
        $backup->id = generateBackupID();
        $backup->timeStamp = time();
        $backup->others = $others;
        $backup->totalFiles = $totalFiles;
        $backup->lastOperationTime = $backup->timeStamp;
        $backup->isUploading = true;
        mkdir(getBackupPath() . $backup->id);
        $backup->save();
        return $backup;
    }
}
