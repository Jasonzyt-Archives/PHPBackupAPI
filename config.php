<?php
// 请确保此处Key与客户端填写的一致
$accessKey = "key";
// 上传的备份的保存目录
$backupPath = "Backups/";
// 上传超时(两次请求间隔时间最大值,秒,正整数)
$timeLimit = 60;
// 最大备份大小(0表示无限)
$maxSize = 0;
// 访问下载API是否需要AccessKey
$allowDownloadWithoutAccessKey = false;
// 允许下载zip(需要安装PHP插件)
$allowDownloadZip = true;
