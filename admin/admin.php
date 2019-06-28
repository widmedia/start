<?php
  require_once('../functions.php');
  $dbConnection = initialize();
  
  
  // prints all the users (limit 100) in the database, sorted by id
  function printUserTable ($dbConnection) {
    $currentTime = time();
    
    echo '<table><tr><th>id</th><th>email</th><th>login</th><th>Pw/verified</th><th>verDate</th><th>select</th></tr>';
    if ($result = $dbConnection->query('SELECT `id`, `email`, `lastLogin`, `hasPw`, `verified`, `verDate` FROM `user` WHERE 1 ORDER BY `lastLogin` ASC LIMIT 100')) {
      while ($row = $result->fetch_assoc()) {
        $selectTemplate = '<a href="admin.php?do=1&editUserId='.$row['id'].'" class="button differentColor">select</a>';
        
        $select = '-';
        $lastLogin = strtotime($row['lastLogin']);
        $sinceLast_h = ($currentTime - $lastLogin) / 3600; // difference, in hours
        if ($sinceLast_h < 24) { $diffText = '&lt; 1 day'; }
        elseif ($sinceLast_h < 24*7) { $diffText = '&lt; 1 week'; }
        elseif ($sinceLast_h < 24*31) { $diffText = '&lt; 1 month'; }
        else { 
          $diffText = '<span style="font-weight:600; color:red">&gt; 1 month</span>'; 
          $select = $selectTemplate;
        }
        
        if ($row['verified'] == 0) { $select = $selectTemplate; };
        
        $email = $row['email'];
        if (strlen($email) > 15) { $email = substr($email,0,12).'...'; }
        
        $verDate = date('d.m.Y', strtotime($row['verDate']));
        echo '<tr><td>'.$row['id'].'</td><td>'.$email.'</td><td>'.$diffText.'</td><td>'.$row['hasPw'].' / '.$row['verified'].'</td><td>'.$verDate.'</td><td>'.$select.'</td></tr>';
      } // while
    } // query ok
    echo '</table>';
  } // function
  
  function printValueTable ($result, $numEntries) {
    echo '<div class="row twelve columns"><table>';
    while ($row = $result->fetch_row()) {
      echo '<tr>';
      for ($i = 0; $i < $numEntries; $i++) {
        echo '<td>'.$row[$i].'</td>';
      }
      echo '</tr>';
    } 
    echo '</table></div>';    
  }
  
  
  // sends an email to the user
  function reminderMail($dbConnection, $editUserId, $reason) { 
    if ($reason == 1) {
      $emailBody = "Hello,\n\nYour account on widmedia.ch/start has been inactive for quite some time (no login for at least one month).\n\n- If you like to keep your account, please login within the next 24 hours (login information have been sent at account opening).\n";
      $confirm = 'inactivity';
    } elseif ($reason == 2) {
      $emailBody = "Hello,\n\nYour email address on widmedia.ch/start has not yet been verified.\n\n- If you like to keep your account, please verify the email within the next 24 hours (email verification information has been sent at account opening).\n";
      $confirm = 'verification';
    }
    $emailBody = $emailBody . "- If you don't need the account anymore, you don't need to do anything, it will be deleted and you will not receive any more messages.\n\nHave fun and best regards,\nDaniel from widmedia\n\n--\nContact (English or German): sali@widmedia.ch\n";
    
    if ($result = $dbConnection->query('SELECT `email` FROM `user` WHERE `id` = "'.$editUserId.'"')) {
      $row = $result->fetch_assoc();       
      if (mail($row['email'], 'widmedia.ch/start: your account will be deleted soon', $emailBody)) {
        
        printConfirm('Email to '.htmlentities($row['email']).' sent', 'The '.$confirm.' email has been sent successfully.');
        return true;
      } // mail send
    }    
    // should not reach this point
    return false;    
  }

  echo '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin page</title>
  <meta name="author" content="Daniel Widmer">
  <meta name="robots" content="noindex, nofollow">

  <!-- Mobile Specific Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- CSS -->
  <link rel="stylesheet" href="../css/font.css" type="text/css">
  <link rel="stylesheet" href="../css/normalize.css" type="text/css">
  <link rel="stylesheet" href="../css/skeleton.css" type="text/css">

  <!-- Favicon -->  
  <link rel="icon" type="image/png" sizes="96x96" href="../images/favicon-96x96.png">
  </head>
  <body>';

  
  $userid = getUserid();
  if ($userid != 1) { // admin has the userid 1...
    die('sorry, only the admin may visit this site</body></html>');    
  }
       
  echo '<div class="section categories noBottom"><div class="container">';
  
  echo '<h3 class="section-heading">Accounts</h3>';
  echo '<div class="row twelve columns" style="background-color: rgba(0, 113, 255, 0.3);">';
  printUserTable($dbConnection);
  echo '</div>'; // row
  
  $doSafe = makeSafeInt($_GET['do'], 1); // this is an integer (range 1 to 3)
  $editUserId = makeSafeInt($_GET['editUserId'], 11); // this is an integer
  $dispErrorMsg = 0;
  
  
  if ($doSafe == 1) { // display all the infos related to to current user
    if ($editUserId) { // have a valid userid      
      $result_categories = $dbConnection->query('SELECT * FROM `categories` WHERE `userid` = "'.$editUserId.'"');
      $result_links = $dbConnection->query('SELECT * FROM `links` WHERE `userid` = "'.$editUserId.'"');
      $result_user = $dbConnection->query('SELECT * FROM `user` WHERE `id` = "'.$editUserId.'"');
      
      if ($result_categories and $result_links and $result_user) {        
        echo '<div class="row twelve columns">&nbsp;</div><h3 class="section-heading">User details userid '.$editUserId.'</h3>';
        // `categories`: `id`/`userid`/`category`/`text`/
        // `links`: `id`/`userid`/`category`/`text`/`link`/`cntTot`
        // `user`: `id`/`email`/`lastLogin`/`hasPw`/`pwHash`/`randCookie`/`verified`/`verCode`/`verDate`      
        printValueTable($result_user, 9);
        printValueTable($result_categories, 4);
        printValueTable($result_links, 6);
        
        echo '
        <div class="row">
          <div class="four columns"><a href="admin.php?do=2&editUserId='.$editUserId.'" class="button differentColor">send inactivity email</a></div>
          <div class="four columns"><a href="admin.php?do=3&editUserId='.$editUserId.'" class="button differentColor">send address verification email</a></div>
          <div class="four columns"><a href="admin.php?do=4&editUserId='.$editUserId.'" class="button differentColor">delete this user</a></div>
        </div>';
      } else { $dispErrorMsg = 11; } // select queries did work
    } else { $dispErrorMsg = 10; } // have a valid userid
  } elseif ($doSafe == 2) { // send an email
    if (! reminderMail($dbConnection, $editUserId, 1)) { $dispErrorMsg = 20; }
  } elseif ($doSafe == 3) { // send an email
    if (! reminderMail($dbConnection, $editUserId, 2)) { $dispErrorMsg = 30; }  
  } elseif ($doSafe == 4) { // delete the user
    if (deleteUser($dbConnection, $editUserId)) {
      printConfirm('Deleted the account', 'Deleted userid: '.$editUserId.' <br/><br/>');
    } else {
      $dispErrorMsg = 40;
    }
  } 
  printError($dispErrorMsg);
  
?>
  </div> <!-- /container -->
  <div class="section noBottom">
    <div class="container">
      <div class="row twelve columns"><hr /></div>
      <div class="row twelve columns"><a class="button differentColor" href="../main.php"><img src="../images/home_green.png" class="logoImg"> back to main</a></div>
    </div>
  </div>                
  </div> <!-- /section categories -->
</body>
</html>
