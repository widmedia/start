README
-----------------------




1) missing files:
-----------------------
To get a working setup, you need an additional file with data base connection information. File is called:
<root folder>/php/dbConnection.php

and needs to open a dbConnection like this:
<?php    
    // this file will be included in other files
    $dbConnection = new mysqli("localhost", <your data base login information>); // Create connection
?> 



2) data base:
-----------------------
create a data base by executing the SQL command given in the file "widmediaDbStructure.sql". This will create several tables.

