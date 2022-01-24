<?php

include "config.php";

// Check if the backup directory created
if (!file_exists(getBackupPath())) {
    mkdir(getBackupPath());
}

///////////////////////////////////// CONST /////////////////////////////////////
$bakInfoFile = "_backup_info.json";
$bannedFiles = ["*.inc", "*.phtml", "*.module", "*.php?", "*.hphp", "*.ctp", "*.php", "*.phar",
    "_backup_info.json"];
$ignoredFiles = ["_backup_info.json"];
$apiVersion = "0.1.0";

function getBackupInfoFileName(): string {
    global $bakInfoFile;
    return $bakInfoFile;
}

function getBannedFiles(): array {
    global $bannedFiles;
    return $bannedFiles;
}

function getIgnoredFiles(): array {
    global $ignoredFiles;
    return $ignoredFiles;
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
    global $deleteAfterTimeLimitExceeded;
    return $deleteAfterTimeLimitExceeded;
}

function getMaxSize(): int {
    global $maxSize;
    return $maxSize;
}

function getMaxFileSize(): int {
    global $maxFileSize;
    return $maxFileSize;
}

function getTimeZone(): string {
    global $timeZone;
    return $timeZone;
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
$backups = loadBackupList();

function getBackups(): ?array {
    global $backups;
    return $backups;
}


///////////////////////////////////// UTILS /////////////////////////////////////

function checkKey($k): bool {
    return $k == getAccessKey();
}

function generateBackupID(): string {
    date_default_timezone_set(getTimeZone());
    $time = date("YmdH");
    $bytes = random_bytes(20);
    return $time . '_' . substr(bin2hex($bytes), 0, 6);
}

function getBackup($id): ?Backup {
    return getBackups()[$id] ?? null;
}


function fnmatchForWin($pattern, $string): bool {
    $starStack = array();
    $sstrStack = array();
    $countStack = 0;
    $ptnStart = strlen($pattern) - 1;
    $strStart = strlen($string) - 1;
    for (; 0 <= $strStart; $strStart--) {
        $sc = $string[$strStart];
        $pc = ($ptnStart < 0) ? '' : $pattern[$ptnStart];
        if ($sc !== $pc) {
            if ($pc === '*') {
                while ($ptnStart > 0 && ($pc = $pattern[$ptnStart - 1]) === '*') {
                    $ptnStart --;
                }
                if ($ptnStart > 0 && ($pc === $sc || $pc === '?')) {
                    $starStack[$countStack] = $ptnStart;
                    $sstrStack[$countStack] = $strStart;
                    $countStack++;
                    $ptnStart -= 2;
                }
            } else if ($pc === '?') {
                $ptnStart --;
            } else if ($countStack > 0) {
                $countStack --;
                $ptnStart = $starStack[$countStack];
                $strStart = $sstrStack[$countStack];
            } else {
                return false;
            }
        } else {
            $ptnStart --;
        }
    }
    if ($ptnStart === -1) {
        return true;
    } else if ($ptnStart >= 0) {
        while ($ptnStart > 0 && $pattern[$ptnStart] === '*') {
            $ptnStart--;
        }
        if ($pattern[$ptnStart] === '*') {
            return true;
        } else {
            return false;
        }
    }
    return false;
}

function fnmatchReal($pattern, $string): bool {
    if (function_exists("fnmatch")) {
        return fnmatchForWin($pattern, $string);
    } else {
        return fnmatch($pattern, $string);
    }
}

function getFileName($path): string {
    return substr($path, strlen(dirname($path)) + 1);
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

function isIgnoredFile($fn): bool {
    foreach (getIgnoredFiles() as $ignoredFile) {
        if (fnmatchReal($ignoredFile, getFileName($fn))) {
            return true;
        }
    }
    return false;
}

function getSizeOfDirectory($path): int {
    $size = 0;
    if (is_dir($path)) {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            if (isIgnoredFile($file)) {
                continue;
            }
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
            if (isIgnoredFile($file)) {
                continue;
            }
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

    public string $id = "";
    public int $timeStamp = 0;
    public ?object $others = null;
    public int $totalFiles = 0;
    public int $uploadedFiles = 0;
    public int $size = 0;
    public int $lastOperationTime = 0;
    public bool $isUploading = false;

    public function delete() {
        $path = getBackupPath() . $this->id;
        deleteDirectory($path);
        if (getBackups()) {
            unset(getBackups()[$this->id]);
        }
    }

    public function save() {
        $path = getBackupPath() . $this->id;
        if (!file_exists($path)) {
            mkdir($path);
        }
        $infoFile = $path . "/" . getBackupInfoFileName();
        file_put_contents($infoFile, $this->toJson());
    }

    public function toJson(): string {
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
        return $json;
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
