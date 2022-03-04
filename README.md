# BackupAPI

## Requirement
- PHP v7.4+
- Web server(Such as Apache, Nginx)

## Authorization
If not specified, the request header should contain `Authorization` to verify the access key.  
See also [`Permissions`](#Permissions)  
e.g.
```http request
/info.php HTTP/1.1
Accept: application/json
Authorization: myAccessKey
```

## Config
> [`config.php`](config.php): 
> Config file

Before using, please edit this file.  
There are some default values, you can change them.  
But you must change `$permission["{keyName}"]`, it is a key to access the API.

### Permissions
`$permissions` is an array, you can add or remove permissions.  
The key in it is the access key, and the value is an array to specify the access permission.  
e.g.
```php
$permissions = [
    "" => [ // If no key provided in request header
        "info" => true, // The request with access key "" can access Info API
    ],
    "myAccessKey" => [
        "create" => true, // The request with access key "myAccessKey" can access Create API
        // But it can't access Info API though "info" is true at Line 3
    ]
];
```

## APIs
### Create
> [`create.php`](create.php): 
> Create a new backup
- Request: `GET/POST`
- Arguments
  - `[totalFiles: int]`: The number of the files you will upload  
    If this is `0` or not set, you must request [`Finish`](#Finish) after uploading
  - `[others: string(JSON object)]`: Other things you want to store on the server
- Response `JSON`
  - `<id: string>`: The ID of the new backup. You must use this ID to [`Upload`](#Upload) & [`Finish`](#Finish)
  - `<timeStamp: int>`: The creation time of the new backup
  - `<success: bool>`: Whether the request is successful
  - `[reason: string]`: If `success` is `false`, this will save the reason of the error

**Example**: 
```
Request[GET]:
/create.php?totalFiles=10&others={}
Response:
{"success":true,"id":"2022012421_42604b","timeStamp":1643029329}
```

### Upload
> [`upload.php`](upload.php): 
> Upload a file to the backup
- Request: `POST`
- Arguments
  - `<id: string>`: The ID of the backup
  - `<file: object>`: The file you want to upload
- Response `JSON`
  - `<id: string>`: The ID of the backup
  - `<uploadedFiles: int>`: The number of the uploaded files
  - `[totalFiles: int]`: The number of the files you will upload
  - `[done: bool]`:
  - `<success: bool>`: Whether the request is successful
  - `[reason: bool]`: If `success` is `false`, this will save the reason of the error

**Example**:
```
Request[POST]:
/upload.php
{{"id", "2022012421_42604b"}, {"file", "content", "test.txt", "text/plain"}}
Response[1]:
{"success":true,"id":"2022012421_42604b","uploadedFiles":1,"totalFiles":10,"done":false}
Response[2]:
{"success":true,"id":"2022012421_42604b","uploadedFiles":2}
```

### Finish
> [`finish.php`](finish.php): 
> Finish uploading a backup
- Request: `GET/POST`
- Arguments
  - `<id: string>`: The ID of the backup
- Response `JSON`
  - `<id: string>`: The ID of the backup
  - `<size: int>`: The size of the backup(bytes)
  - `<uploadedFiles: int>`: The number of the uploaded files
  - `<success: bool>`: Whether the request is successful
  - `[reason: string]`: If `success` is `false`, this will save the reason of the error
  
**Example**: 
```
Request[GET]:
/finish.php?id=2022012421_42604b
Response:
{"success":true,"id":"2022012421_42604b","size":114514,"uploadedFiles":10}
```

**Note**:
[`$timeLimit`](config.php#L7) [`$deleteAfterTimeLimitExceeded`](config.php#L10)

### Query
> [`query.php`](query.php): 
> Query the information of a backup
- Request: `GET/POST`
- Arguments
  - `[id: string]`: The ID of the backup
- Response `JSON`
  - `[backup: object]`: The backup information
  - `[list: array]`: The list of all the backups
  - `[count: int]`: The number of the backups
  - `<success: bool>`: Whether the request is successful
  - `[reason: string]`: If `success` is `false`, this will save the reason of the error

**Example**: 
```
Request[GET][1]:
/query.php
Response[1]:
{"success":true,"count":1,"list":[{"id":"2022012421_42604b","timeStamp":1643030721,"others":null,"totalFiles":3,"isUploading":false}]}

Request[GET][2]:
/query.php?id=2022012421_42604b
Response[2]:
{"success":true,"backup":{"id":"2022012421_42604b","timeStamp":1643030721,"others":null,"totalFiles":3,"isUploading":false,"files":["qwd.log","wow/test.txt","wow/woc/114514.txt"]}}
```

### Delete
> [`delete.php`](delete.php): 
> Delete a backup
- Request: `GET/POST`
- Arguments
  - `<id: string>`: The ID of the backup
- Response `JSON`
  - `<id: string>`: The ID of the backup 
  - `<success: bool>`: Whether the request is successful
  - `[reason: string]`: If `success` is `false`, this will save the reason of the error

**Example**: 
```
Request[GET]:
/delete.php?id=2022012421_42604b
Response:
{"success":true,"id":"2022012421_42604b"}
```

### Download
> [`download.php`](download.php): 
> Download a file of the backup
- Request: `GET/POST`
- Arguments
  - `<id: string>`: The ID of the backup
  - `<file: string>`: The name of the file
- Response[1] `octet-stream`: The file
- Response[2] `JSON`:
  - `<success: bool>`: Always `false`
  - `<reason: string>`:  The reason of the error

**Example**: 
```
Request[GET]:
/download.php?id=2022012421_42604b&file=qwd.log
Response:
{FILE CONTENT}
```

**Note**:  
You can get the filename by [`Query`](#Query)

### DownloadZip
> [`downloadZip.php`](downloadZip.php): 
> Download the backup as a zip file
- Request: `GET/POST`
- Arguments
  - `<id: string>`: The ID of the backup
- Response[1] `zip`: The zip file
- Response[2] `JSON`:
  - `<success: bool>`: Always `false`
  - `<reason: string>`:  The reason of the error

**Example**: 
```
Request[GET]:
/downloadZip.php?id=2022012421_42604b
Response:
{FILE CONTENT}
```

### Info
> [`info.php`](info.php): 
> Get the information of APIs
- Request: `GET/POST`
- Arguments: None
- Response `JSON`:
  - `<success: bool>`: Always `true`
  - `<apiVersion: string>`: The API version

**Example**: 
```
Request[GET]:
/info.php
Response:
{"success":true,"apiVersion":"0.1.0"}
```
