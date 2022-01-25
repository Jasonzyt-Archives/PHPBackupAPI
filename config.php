<?php
// 请确保此处Key与客户端填写的一致
// Please make sure the key is the same as the one in the client
$accessKey = "key";
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
// 访问下载API是否需要AccessKey
// Whether to access the download API requires AccessKey
$allowDownloadWithoutAccessKey = false;
// 是否允许下载zip
// Whether to allow download zip
$allowDownloadZip = true;
