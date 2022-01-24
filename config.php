<?php
// 请确保此处Key与客户端填写的一致
$accessKey = "key";
// 上传的备份的保存目录
$backupPath = "Backups/";
// 上传超时(两次上传请求间隔时间最大值,秒,正整数)
$timeLimit = 60;
// 是否在上传超时后删除备份文件
$deleteAfterTimeLimitExceeded = true;
// 最大备份大小(字节,0表示不限)
$maxSize = 0;
// 单个备份文件最大大小(字节,0表示不限)
$maxFileSize = 0;
// 时区, 请参考此文档https://www.php.net/manual/zh/timezones.php
$timeZone = "Asia/Shanghai";
// 访问下载API是否需要AccessKey
$allowDownloadWithoutAccessKey = false;
// 是否允许下载zip(需要安装PHP插件)
$allowDownloadZip = true;
