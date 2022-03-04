<?php
// 此配置已弃用, 请使用$permissions
// Deprecated. Please use $permissions
//$accessKey = "key";
// 上传的备份的保存目录
// The directory to store the backup
$backupPath = "Backups/";
// 上传超时(两次上传请求间隔时间最大值,秒,正整数)
// Upload timeout (the maximum time between two upload requests, seconds, positive integer)
$timeLimit = 60;
// 是否在上传超时后删除备份文件
// Whether to delete the backup file after the upload timeout
$deleteAfterTimeLimitExceeded = true;
// 最大备份大小(字节,0表示不限)
// The maximum backup size (bytes, 0 means no limit)
$maxSize = 0;
// 单个备份文件最大大小(字节,0表示不限)
// The maximum size of a single backup file (bytes, 0 means no limit)
$maxFileSize = 0;
// 时区, 请参考此文档https://www.php.net/manual/zh/timezones.php
// Time zone, please refer to this document https://www.php.net/manual/zh/timezones.php
$timeZone = "Asia/Shanghai";
// 此配置已弃用, 请使用$permissions
// Deprecated. Please use $permissions
//$allowDownloadWithoutAccessKey = false;
// 此配置已弃用, 请使用$permissions
// Deprecated. Please use $permissions
//$allowDownloadZip = true;
// 权限
// Permissions
$permissions = [
    "" => [ // if no key provided in request header
        "info" => true,
    ],
    "key" => [
        "create" => true,
        "delete" => true,
        "download" => true,
        "downloadZip" => true,
        "finish" => true,
        "info" => true,
        "query" => true,
        "upload" => true,
    ]
];
