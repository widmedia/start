<?php
  require_once('functions.php');
  $dbConnection = initialize();
  
  // prints a message when logged in as a test user
  function printMsgTestUser ($userid) {
    if ($userid == 2) { 
      echo '<div class="overlayMessage" style="background-color: rgba(255, 47, 25, 0.5); z-index: 3;">This is the (somewhat limited) test account</div>'; 
    }
  } 

  // prints a message when the email of this account has not been verified
  function printMsgAccountVerify ($dbConnection, $userid) {
    $verified = false;
    if ($result = $dbConnection->query('SELECT `verified` FROM `user` WHERE `id` = "'.$userid.'"')) {
      $row = $result->fetch_row();
      if ($row[0] == 1) {
        $verified = true;
      } // verified
    } // select query
    
    if (!$verified) {
      echo '<div class="overlayMessage" style="background-color: rgba(255, 47, 25, 0.5); z-index: 4;">Your email address has not yet been verified. Please do so within 24 hours, otherwise this account will be deleted.</div>';
    }
  } // function
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Startpage</title>
  <meta name="description" content="a modifiable page containing various links, your personal start page">
  <meta name="author" content="Daniel Widmer">

  <!-- Mobile Specific Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS -->
  <link rel="stylesheet" href="css/font.css" type="text/css">
  <link rel="stylesheet" href="css/normalize.css" type="text/css">
  <link rel="stylesheet" href="css/skeleton.css" type="text/css">
  <link rel="stylesheet" href="css/custom.css" type="text/css">
 
  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon-96x96.png">
  
  <script type="text/javascript" src="js/scripts.js"></script>  
</head>  
<?php
  $msgSafe = makeSafeInt($_GET['msg'], 1);
  if ($msgSafe > 0) {
    echo '<body onLoad="overlayMsgFade();">'; 
    printMessage($msgSafe); 
  } else {
    echo '<body>';
  }
  $userid = getUserid();
  
  printMsgTestUser($userid);      
  printMsgAccountVerify($dbConnection, $userid);

  echo '<div class="section categories noBottom"><div class="container">';
  
  echo '<h3 class="section-heading">'.getCategory($dbConnection, $userid, 1).'</h3><div class="row">';
  printLinks($dbConnection, false, $userid, 1);
  
  echo '</div><h3 class="section-heading">'.getCategory($dbConnection, $userid, 2).'</h3><div class="row">';
  printLinks($dbConnection, false, $userid, 2);
  
  echo '</div><h3 class="section-heading">'.getCategory($dbConnection, $userid, 3).'</h3><div class="row">';
  printLinks($dbConnection, false, $userid, 3);
  echo '</div>
  </div> <!-- /container -->';
  printFooter();
?>                
  </div> <!-- /section categories -->
</body>
</html>
