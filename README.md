# BackupAPI

## Requirement
- PHP v7.4+
- Web Server(Such as Apache, Nginx)

## Config
> [config.php](config.php): 
> Config file
- accessKey: A key to confirm whether the client has access
- backupPath: The path that you want to use to save uploaded(backup) files

## APIs
### Create
> [create.php](create.php): 
> Create a new backup
- Request: GET/POST
- Arguments
  - key: Access key
  - info: The basic information of the backup(JSON)
    - totalFiles: The number of the files you will upload
    - others: Other things you want to store on the server
- Response
  - id: The ID of the new backup
  - timeStamp: The creation time of the new backup
  - success: If create successfully
  - reason: If `success` is `false`, this will save the reason of the erorr

### Upload

