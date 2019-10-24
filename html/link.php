<?php declare(strict_types=1);
  // this file does the redirection and increases the link counter by one
  require_once('functions.php');
  $dbConn = initialize();  // does the session start and opens connection to the data base. Returns the dbConn variable
  
  function linkAndUpdateDb(object $dbConn, int $idSafe, int $userid): bool {
    if (!(($idSafe > 0) and ($userid > 0))) {
      return false;
    }
    // important to verify the userid as well. Query should always return just one
    if (!($result = $dbConn->query('SELECT `link` FROM `links` WHERE `userid` = "'.$userid.'" AND `id` = "'.$idSafe.'"'))) { 
      return false;
    }
    if (!($result->num_rows == 1)) {
      return false;
    }
    $row = $result->fetch_row();
    if (!($dbConn->query('UPDATE `links` SET cntTot = cntTot + 1 WHERE `userid` = "'.$userid.'" AND `id` = "'.$idSafe.'"'))) {
      return false;
    }    
    header('Location: '.$row[0]);
    return true;  
  }
    
  $idSafe = safeIntFromExt('GET', 'id', 11); 
  $userid = getUserid();
  
  if (!(linkAndUpdateDb($dbConn, $idSafe, $userid))) {
    printErrorAndDie('Error in file link.php','Something related to the data base went wrong... well, that doesn\'t help that much, does it?<br>But you might still want to inform me (sali@widmedia.ch) or just try again later.');
  }
?>
