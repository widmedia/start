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
create a data base by executing the SQL command given in the file "widmediaDb.sql". This will create several tables
the admin of the site must have userid = 1, the test user must have userid = 2 (those two users are treated differently)


Notes: 
-----------------------
following files do NOT require a logged-in user:
- index.php
- about.php

following files do require a logged-in user:
- links.php
- link.php
- editUser.php
- editLink.php
- admin/admin.php (logged-in user must have userid 1)

icons are from iconsdb.com
icon_db: https://www.iconsdb.com/custom-color/data-configuration-icon.html
icon_arrow_right: https://www.iconsdb.com/custom-color/arrow-32-icon.html
icon_edit: https://www.iconsdb.com/custom-color/edit-icon.html
icon_home: https://www.iconsdb.com/custom-color/home-2-icon.html
icon_info: https://www.iconsdb.com/custom-color/info-2-icon.html
icon_logout: https://www.iconsdb.com/custom-color/account-logout-icon.html
icon_plus: https://www.iconsdb.com/custom-color/plus-5-icon.html
icon_question: https://www.iconsdb.com/custom-color/question-mark-4-icon.html
icon_zero: https://www.iconsdb.com/custom-color/0-icon.html
- yellow color is faff3b
- red color is 9c2448


color of the background image is more or less #84888d
triade (adobe color wheel): 
- reddish     = 255/ 47/ 25
- blueish     =   0/113/255