# DatabaseHub
Review and execute on databases

## Dependencies

PHP

* PHP 7 and later
* PHP Extension: mbstring, mysql (pdo and mysqli), json
* Library:
    - sinri/ark (2 and later)
    - sinri/ark-pdo
    - sinri/ark-mysqli
    - sinri/ark-queue
    - sinri/ark-curl 
    - sinri/ark-core
    - phpmyadmin/sql-parser
    - greenlion/php-sql-parser (sinri modification)
    - jdorn/sql-formatter
    
    
Once Used:

phpmyadmin/sql-parser (sinri modification)    

```json
    {
      "type": "git",
      "url": "https://github.com/sinri/sql-parser.git"
    }
```
    
JavaScript

* axios@0.18.0
* js-cookie@2.2.0
* vue@2.5.17
* vue-router@3.0.2
* iview@3.1.5
* codemirror@5.42.2
* vue-codemirror@4.0.0
* markdown-it@8.4.2
* SinriQF@1.0

## User Management

Users are controlled by the table `user` and a standard of Login Plugin is provided to implement the user authentication.

Class `LoginPluginStandalone` is prepared for the common situation, and it is the default configuration. 
However, you need to extend this project to meet your requirements.
This project does not contain a user interface to manage users,
as it was thought to be controlled by a central user manage system.
If you do not want to extend, and users are limited, you can also use CLI script to resolve.

```bash
php runner.php command/InitCommand CreateUser [USERNAME] [PASSWORD] [USER or ADMIN]
```

## Deployment

Prepare the main database, use SQL file `DatabaseDesignInitialize.sql` to initialize.
Make a config file `config/config.php` referring to the sample file before you start your project.
Config the web server, follow the instruction of framework [Ark](https://github.com/sinri/Ark).
Start the task queue daemon with `RunDHQueue.php`. You might need to run it with `nohup`.
To stop the queue, use `StopDHQueue.php`. If needed, you can use `kill -9` to do it fiercely.

## About

This project is maintained by [Sinri](https://github.com/sinri), [Vinci](https://github.com/RoamIn) and [Caroltc](https://github.com/caroltc).

DatabaseHub is licensed under the GNU General Public License v3.0.

Lord, have mercy on us. 2019 Jan 19th.