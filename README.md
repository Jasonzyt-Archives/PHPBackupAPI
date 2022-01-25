# BackupAPI

## Requirement
- PHP v7.4+
- Web server(Such as Apache, Nginx)

## Config
> [`config.php`](config.php): 
> Config file

Before using, please edit this file.  
There are some default values, you can change them.  
But you must change `$accessKey`, it is a key to access the API.

## APIs
### Create
> [`create.php`](create.php): 
> Create a new backup
- Request: `GET/POST`
- Arguments
  - `key`: Access key
  - `info`: The basic information of the backup(JSON)
    - `totalFiles`: The number of the files you will upload  
      If this is `0` or not set, you must request [`Finish`](#Finish) after uploading
    - `others`: Other things you want to store on the server
- Response `JSON`
  - `id`: The ID of the new backup
  - `timeStamp`: The creation time of the new backup
  - `success`: If create successfully
  - `reason`: If `success` is `false`, this will save the reason of the error

**Example**: 
```
Request: 
create.php?key=2333&info={"totalFiles":10,"others":{"name":"test"}}
Response:
{"success":true,"id":"2022012413_25cf29","timeStamp":1643029329}
```

### Upload

### Finish

### Query

### Delete

### Download

### DownloadZip

### Info