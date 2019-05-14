 <?php
  // this file does the redirection and increases the link counter by one
  require_once('functions.php');
  $dbConnection = initialize();  // does the session start and opens connection to the data base. Returns the dbConnection variable
  
  // id as 'get' parameter
  $idUnsafe = filter_var(substr($_GET['id'], 0, 11), FILTER_SANITIZE_NUMBER_INT); // this is an integer (max 11 characters)
  $idSafe = 0;
  
  
  if (filter_var($idUnsafe, FILTER_VALIDATE_INT)) { 
    $idSafe = $idUnsafe; // because it's an integer, it is safe for both sql and html§
    $userid = getUserid();
    
    // important to verify the userid as well
    $sqlSelectString = 'SELECT link FROM `links` WHERE userid = '.$userid.' AND id = '.$idSafe; // should always return just one
    $sqlUpdateString = 'UPDATE `links` SET cntTot = cntTot + 1 WHERE userid = '.$userid.' AND id = '.$idSafe;

    if ($result = $dbConnection->query($sqlSelectString)) {
      if ($result->num_rows == 1) {
        $row = $result->fetch_row();
        if ($dbConnection->query($sqlUpdateString)) {
          // everything went as expected, no errors
          $result->close(); // free result set
          header('Location: '.$row[0]);
          exit();
        } // sql update      
      } // one row result
    } // sql select
  } // valid integer
   
  // should never reach this code...
  die('<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Errorpage</title><link rel="stylesheet" href="css/skeleton.css" type="text/css"></head>
  <body><br>Something related to the data base went wrong (in file '.htmlentities($_SERVER['PHP_SELF']).')... well, that doesn\'t help that much, does it?<br>But you might still want to inform me (sali@widmedia.ch) or just try again later</body></html>');
