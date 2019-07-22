<?php
  // this file does the redirection and increases the link counter by one
  require_once('functions.php');
  $dbConnection = initialize();  // does the session start and opens connection to the data base. Returns the dbConnection variable
  
  $idSafe = makeSafeInt($_GET['id'], 11); 
  $userid = getUserid();
  
  if (($idSafe > 0) and ($userid > 0)) {  
    // important to verify the userid as well. Query should always return just one
    if ($result = $dbConnection->query('SELECT `link` FROM `links` WHERE `userid` = "'.$userid.'" AND `id` = "'.$idSafe.'"')) { 
      if ($result->num_rows == 1) {
        $row = $result->fetch_row();
        if ($dbConnection->query('UPDATE `links` SET cntTot = cntTot + 1 WHERE `userid` = "'.$userid.'" AND `id` = "'.$idSafe.'"')) {
          // everything went as expected, no errors          
          header('Location: '.$row[0]);
          exit();
        } // sql update      
      } // one row result
    } // sql select
  } // valid integer
  
  // should never reach this code...
  printErrorAndDie('Error in file link.php','Something related to the data base went wrong... well, that doesn\'t help that much, does it?<br>But you might still want to inform me (sali@widmedia.ch) or just try again later.');
?>
