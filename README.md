# start
the php/html code of a configurable 'those-are-my-links' start page

An example implementation can be seen on https://widmedia.ch/start


## README

### 1) missing files
To get a working setup, you need an additional file with data base connection information. File is called:
```
"root folder"/php/dbConn.php
```
and needs to set the variable $dbConn (like this):
```php
    $dbConn = new mysqli("localhost", <your data base login information>);
```  


### 2) data base
create a data base by executing the SQL command given in the file "widmediaDb.sql". This will create several tables
the admin of the site must have userid = 1, the test user must have userid = 2 (those two users are treated differently)


### 3) Site structure
following files do NOT require a logged-in user:
* index.php
* about.php

following files do require a logged-in user:
* links.php
* link.php
* edit.php
* admin.php (logged-in user must have userid 1)
* functions.php: pure function definition file

### 4) Test & verification
Selenium test scripts (written in python) may be found in the verify-folder. 
Still in an early stage and most probably will change its file location later on.
