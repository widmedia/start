<?php declare(strict_types=1);
  require_once('../functions.php');
  $dbConn = initialize();
  
  
  // prints all the users (limit 100) in the database, sorted by id
  function printUserTable ($dbConn): void {
    $currentTime = time();
    
    echo '<table><tr><th>id</th><th>email</th><th>login</th><th>Pw/verified</th><th>verDate</th><th>select</th></tr>';
    if ($result = $dbConn->query('SELECT `id`, `email`, `lastLogin`, `hasPw`, `verified`, `verDate` FROM `user` WHERE 1 ORDER BY `lastLogin` ASC LIMIT 100')) {
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
  
  function printValueTable ($result, int $numEntries): void {
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
  function reminderMail($dbConn, int $editUserId, int $reason): bool { 
    if ($reason == 1) { 
      $emailBodyDe = "(English below)\n\nSalü,\n\nDein Account auf widmedia.ch/start ist seit einiger Zeit inaktiv (kein Login während mindestens einem Monat).\n\n- Falls du deinen Account behalten möchtest, log dich bitte innerhalb von 24 Stunden wieder ein (Logininfos wurden dir bei der Accounteröffnung zugeschickt).\n";
      $emailBodyEn = "Hello,\n\nYour account on widmedia.ch/start has been inactive for quite some time (no login for at least one month).\n\n- If you like to keep your account, please login within the next 24 hours (login information have been sent at account opening).\n";
      $confirm = 'inactivity';
    } elseif ($reason == 2) {
      $emailBodyDe = "(English below)\n\nSalü,\n\nDeine Emailadresse auf widmedia.ch/start wurde noch nicht verifiziert.\n\n- Falls du deinen Account behalten möchtest, bestätige bitte deine Emailadresse innerhalb der nächsten 24 Stunden (Link zur Emailverifizierung wurde dir bei der Accounteröffnung zugeschickt).\n";
      $emailBodyEn = "Hello,\n\nYour email address on widmedia.ch/start has not yet been verified.\n\n- If you like to keep your account, please verify the email within the next 24 hours (email verification information has been sent at account opening).\n";
      $confirm = 'verification';
    }
    
    $emailBodyDe = $emailBodyDe . "- Falls du den Account nicht mehr benötigst, musst du nichts weiter tun, er wird gelöscht und du bekommst keine weiteren Nachrichten.\n\nViel Spass und Gruss,\nDaniel von widmedia\n\n--\nKontakt (Deutsch oder Englisch): sali@widmedia.ch\n\n";
    $emailBodyEn = $emailBodyEn . "- If you don't need the account anymore, you don't need to do anything, it will be deleted and you will not receive any more messages.\n\nHave fun and best regards,\nDaniel from widmedia\n\n--\nContact (English or German): sali@widmedia.ch\n";
    
    $emailBody = $emailBodyDe.$emailBodyEn;
    
    if ($result = $dbConn->query('SELECT `email` FROM `user` WHERE `id` = "'.$editUserId.'"')) {
      $row = $result->fetch_assoc();       
      if (mail($row['email'], 'widmedia.ch/start: dein Account wird nächstens gelöscht / your account will be deleted soon', $emailBody)) {
        
        printConfirm($dbConn, 'Email to '.htmlentities($row['email']).' sent', 'The '.$confirm.' email has been sent successfully.');
        return true;
      } // mail send
    }    
    // should not reach this point
    return false;    
  }
  
  // reads the user db, checks how many users have been active in this month and updates the statistics database (userStat)
  // userStat structure: id (int 11) / year (format 2019) int4 / month (format 07) int 2 / numUser (int 11)
  function doUserStatistics($dbConn): void {
    // find current year and month
    $currentTime = time();
    $year = date('Y', $currentTime);
    $month = date('m', $currentTime);
    
    if ($result = $dbConn->query('SELECT `numUser` FROM `userStat` WHERE `year` = "'.$year.'" AND `month` = "'.$month.'" LIMIT 1')) {
      if ($result->num_rows == 1) { // stats for this month have already been done
        $row = $result->fetch_assoc();
        echo '<div class="row twelve columns textBox">stats for '.$year.'-'.$month.' are already existing, '.$row['numUser'].' have been active this month</div>';        
      } else { // need to do the statistics
        $activeUsers = 0;
        $inactiveUsers = 0;
        if($result = $dbConn->query('SELECT `lastLogin` FROM `user` WHERE 1 ORDER BY `lastLogin` ASC')) {
          while ($row = $result->fetch_assoc()) {            
            $sinceLast_h = ($currentTime - strtotime($row['lastLogin'])) / 3600; // difference, in hours
            if ($sinceLast_h < 24*31) { 
              $activeUsers++;
            } else {
              $inactiveUsers++;
            }
          } // while
          printConfirm($dbConn, 'stats for '.$year.'-'.$month, $activeUsers.' have been active this month, '.$inactiveUsers.' users are inactive.');
          $result = $dbConn->query('INSERT INTO `userStat` (`year`, `month`, `numUser`) VALUES ("'.$year.'", "'.$month.'", "'.$activeUsers.'")');
        } // query
      } // have one result
    } // query    
  }

  echo '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin page</title>
  <meta name="author" content="Daniel Widmer">
  <meta name="robots" content="noindex, nofollow">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/font.css" type="text/css">
  <link rel="stylesheet" href="../css/normalize.css" type="text/css">
  <link rel="stylesheet" href="../css/skeleton.css" type="text/css">';
  printInlineCss($dbConn, true);  
  echo '  
  <style>
    html { background: url("../images/bg_ice_1920x1080.jpg") no-repeat center center fixed; }    
  </style>  
  <link rel="icon" type="image/png" sizes="96x96" href="../images/favicon.png">
  </head>
  <body>';

  
  $userid = getUserid();
  if ($userid != 1) { // admin has the userid 1...
    die('sorry, only the admin may visit this site</body></html>');    
  }
  
  $num = 0;
  if($result = $dbConn->query('SELECT `lastLogin` FROM `user` WHERE 1')) {
    $num = $result->num_rows;
  }
  
  echo '<div class="section categories noBottom"><div class="container">'; 
  echo '<h3 class="section-heading"><span class="bgCol">'.$num.' Accounts</span></h3>';
  echo '<div class="row twelve columns" style="background-color: rgba(0, 113, 255, 0.3);">';
  printUserTable($dbConn);
  echo '</div>';
  
  echo '<div class="row twelve columns">&nbsp;</div><div class="row twelve columns">';
  doUserStatistics($dbConn);
  echo '</div>';  
  
  $doSafe = safeIntFromExt('GET', 'do', 1); // this is an integer (range 1 to 3)
  $editUserId = safeIntFromExt('GET','editUserId', 11); // this is an integer    
  
  if ($doSafe == 1) { // display all the infos related to to current user
    if ($editUserId) { // have a valid userid      
      $result_categories = $dbConn->query('SELECT * FROM `categories` WHERE `userid` = "'.$editUserId.'"');
      $result_links = $dbConn->query('SELECT * FROM `links` WHERE `userid` = "'.$editUserId.'"');
      $result_user = $dbConn->query('SELECT * FROM `user` WHERE `id` = "'.$editUserId.'"');
      
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
      } else { error($dbConn, 170100); } // select queries did work
    } else { error($dbConn, 170101); } // have a valid userid
  } elseif ($doSafe == 2) { // send an email
    if (! reminderMail($dbConn, $editUserId, 1)) { error($dbConn, 170200); }
  } elseif ($doSafe == 3) { // send an email
    if (! reminderMail($dbConn, $editUserId, 2)) { error($dbConn, 170300); }  
  } elseif ($doSafe == 4) { // delete the user
    if (deleteUser($dbConn, $editUserId)) {
      printConfirm($dbConn, 'Deleted the account', 'Deleted userid: '.$editUserId.' <br/><br/>');
    } else {
      error($dbConn, 170400);
    }
  }     
?>
  </div> <!-- /container -->
  <div class="section noBottom">
    <div class="container">
      <div class="row twelve columns"><hr /></div>
      <div class="row twelve columns"><a class="button differentColor" href="../links.php"><img src="../images/icon/home.png" class="logoImg"> back to links</a></div>
    </div>
  </div>                
  </div> <!-- /section categories -->
</body>
</html>
