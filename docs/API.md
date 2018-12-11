# API of DatabaseHub

POST to `/api/CONTROLLER/METHOD`.

Request body should be a json string. All APIs except login should send field `token` in body, which would be ignored below. 

Response as json object with two fields, `code` and `data`. Code is `OK` or `FAIL`, `data` is various. 

## Constants

### Application Types

* READ
* MODIFY
* EXECUTE
* DDL


### Application Status

* APPLIED
* DENIED
* CANCELLED
* APPROVED
* EXECUTING
* DONE
* ERROR

### Application Parallelable
    
* YES
* NO

### Database Type

* MYSQL

### Database Status

* NORMAL
* DISABLED

### USER DATABASE PERMISSION

* READ
* MODIFY
* DDL
* EXECUTE
* QUICK_QUERY
* KILL

### Quick Query Type

* SYNC
* ASYNC

### User Type

* ADMIN
* USER

### User Status

* NORMAL
* DISABLED
* FROZEN

### User Organization

* FREE
* (any others defined by login plugins)

## LoginController

### login

Login and create session, then fetch token.

REQ:

* username
* password

## ApplicationController

### create

Create an application.

REQ:

* application

Here `application` is an object contains fields:

* title
* description
* database_id
* sql
* type

### update

Update an application, which should be `DENIED`, `CANCELLED` or `ERROR`.

REQ:

* application_id
* application

Here `application` is an object contains fields:

* title
* description
* database_id
* sql
* type

### cancel

Cancel an `APPLIED` application applied by self.

REQ: 

* application_id

### deny

Deny an `APPLIED` application, if permitted.

REQ:

* application_id
* reason

### approve

Approve an `APPLIED` application, if permitted.

REQ:

* application_id

### search

Search applications with conditions.

REQ:

* title
* database_id
* type (array)
* apply_user
* status (array)
* page_size
* page (since 1)


### myApprovals

List permitted applications for approving.

REQ:

* page_size
* page (since 1)

### detail

Fetch detail of an application.

REQ:

* application_id

### downloadExportedContentAsCSV

Download the result of a `READ` application. 

REQ:

* application_id

## DatabaseManageController

### add

Add a new database.

REQ:

* database_info

The `database_info` is a json object contains fields:

* database_name
* host
* port
* status
* engine (only MYSQL supported)

### edit

Edit a new database.

REQ:

* database_id
* database_info

The `database_info` is a json object contains fields:

* database_name
* host
* port
* status
* engine (only MYSQL supported)

### remove

Remove a database.

REQ:

* database_id

### commonList

Fetch the normal database list (for common options).

No special REQ, disabled would not be shown

### advanceList

Fetch the complete database list (for management).

No special REQ, disabled would also be shown

### editAccount

Update an account pair for database.

REQ:

* database_id
* username
* password

### removeAccount

Remove one account of database.

REQ:

* database_id
* account_id

### setDefaultAccount

Set one account as default of database.

REQ:

* database_id
* account_id

### databaseAccountList

Fetch the accounts of database.

REQ:

* database_id


## PermissionManageController

### getUserPermission

Fetch the permissions of user with databases.

REQ:

* user_id
* database_id_list (an array of database_id)

### updateUserPermission

Update the permissions of user with databases.

REQ:

* user_id
* database_id
* permissions (array of permissions)

## QuickQueryController

### permittedDatabases

For quick query page, show the permitted databases.

No special REQ

### syncExecute

Run quick query sql as sync task.

REQ:

* database_id
* sql

## KillerController

### permittedDatabases

List the permitted databases.

No special REQ.

### showProcessList

Run `show full processlist` and fetch result. Kill would rely on the `ID` and `USER` (case might not determined).

REQ:

* database_id

### kill

Kill one thread by tid, with certain account by username.

REQ:

* database_id
* username
* tid
