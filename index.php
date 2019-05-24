<?php
  require_once('functions.php');
  $dbConnection = initialize();
  
  // Form processing
  $doSafe     = makeSafeInt($_GET['do'], 1);       // this is an integer (range 1 to 2)
  $useridSafe = makeSafeInt($_GET['userid'], 11);  
  $useridCookieSafe = makeSafeInt($_COOKIE['userIdCookie'], 11);
  
  // TODO: security measures
  if ($doSafe == 0) { // the $_GET-do parameter has higher priority than the rest
    if ($useridSafe) { // the $_GET-userid has higher priority than the cookie userid
      if (verifyCredentials($useridSafe, $dbConnection)) { 
        if (makeSafeInt($_GET['setCookie'], 1)) {
          $expire = 60 * 60 * 24 * 7 * 4; // valid for 4 weeks
          setcookie('userIdCookie', $useridSafe, time() + $expire);      
        }    
        redirectRelative('main.php');    
      }      
    } elseif ($useridCookieSafe) {    
      if (verifyCredentials($useridCookieSafe, $dbConnection)) {    
        redirectRelative('main.php');    
      }
    } // else, present the userid selection page
  }
  
  // deletes both the cookie and the session 
  function logOut () {        
    sessionAndCookieDelete();    
    echo '
    <h3 class="section-heading">Logged out</h3>
    <div class="row">
      <div class="four columns">&nbsp;</div>
      <div class="eight columns">go back to <a href="index.php">the start page</a></div>
    </div>';
  }  
  
  function printEntryPoint($dbConnection) {
    // TODO: this will change...
    echo '
    <h3 class="section-heading">Existing users</h3>    
    <div class="row" style="font-weight: bold; font-size: larger;"><div class="one columns">id</div><div class="one columns">email</div><div class="three columns">last login</div><div class="three columns">single login</div><div class="four columns">login with a cookie</div></div>';
      
    $sqlString = 'SELECT * FROM `user` WHERE 1 ORDER BY `id` LIMIT 20';   // should be only 10 for now
    if ($result = $dbConnection->query($sqlString)) {
      while ($row = $result->fetch_assoc()) {
        echo '
        <div class="row">
          <div class="one columns">'.$row['id'].'</div>
          <div class="one columns">'.substr($row['email'], 0, 4).'...</div>
          <div class="three columns">'.$row['lastLogin'].'</div>
          <div class="three columns"><a href="index.php?userid='.$row['id'].'">single login</a></div>
          <div class="four columns"><a href="index.php?userid='.$row['id'].'&setCookie=1">login and set a cookie</a></div>
        </div>';
      } // while row
    } // sql did work
    printHr();    
    echo '<h3 class="section-heading">Non-existing user</h3>
    <div class="row">
      <div class="one columns">3</div>
      <div class="one columns">-</div>
      <div class="three columns">-</div>
      <div class="three columns"><a href="index.php?userid=3">single login</a></div>
      <div class="four columns"><a href="index.php?userid=3&setCookie=1">login and set a cookie</a></div>
    </div>';
    printHr();    
    echo '<h3 class="section-heading">New user</h3>
    <div class="row">
      <div class="twelve columns" style="text-align: left;"><a href="index.php?do=2">add a user</a> (limit of 10 users)</div>        
    </div>';    
  } // function  
?>

<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs -->
  <meta charset="utf-8">
  <title>Start - user selection</title>
  <meta name="description" content="a modifiable page containing various links, intended to be used as a personal start page">
  <meta name="author" content="Daniel Widmer">

  <!-- Mobile Specific Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FONT -->
  <link rel="stylesheet" href="css/font.css" type="text/css">

  <!-- CSS -->
  <link rel="stylesheet" href="css/normalize.css" type="text/css">
  <link rel="stylesheet" href="css/skeleton.css" type="text/css">
  <link rel="stylesheet" href="css/custom.css" type="text/css">

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon-96x96.png">

</head>
<body>
  <div class="section categories noBottom">
    <div class="container">
    <?php           
      // possible actions: 
      // 0/non-existing: normal case
      // 1=> logout
      // 2=> add new user
      
      if ($doSafe == 0) { // valid use case. Entry point of this site
        printEntryPoint($dbConnection);        
      } elseif ($doSafe > 0) {        
        switch ($doSafe) {
        case 1:
          logOut();
          break;
        case 2:
          // TODO:
          // form with some basic information: email.
          // - some selection: with or without pw? ... stuff like that

          // Adding 1 user, 3 categories, 4 links
          // add a new user but only if number of users is less than 10 (TODO: developer limitation for now)
          if ($result = $dbConnection->query('SELECT count(*) AS `total` FROM `user`')) {
              $row = $result->fetch_assoc();
              $rowCnt = $row['total'];              
              if($rowCnt < 10) {
                $sqlInsertUser = 'INSERT INTO `user` (`id`, `email`, `lastLogin`) VALUES (NULL, "new@widmedia.ch", CURRENT_TIMESTAMP)';
                if ($result = $dbConnection->query($sqlInsertUser)) { 
                  $newUserId = $dbConnection->insert_id;
                  $dbConnection->query('INSERT INTO `categories` (`id`, `userid`, `category`, `text`) VALUES (NULL, "'.$newUserId.'", "1", "News")');
                  $dbConnection->query('INSERT INTO `categories` (`id`, `userid`, `category`, `text`) VALUES (NULL, "'.$newUserId.'", "2", "Work")');
                  $result = $dbConnection->query('INSERT INTO `categories` (`id`, `userid`, `category`, `text`) VALUES (NULL, "'.$newUserId.'", "3", "Div")');
                  if ($result) { // assuming that if the last one did work, the other two did work as well
                    $dbConnection->query('INSERT INTO `links` (`id`, `userid`, `category`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$newUserId.'", "1", "NZZ", "https://www.nzz.ch", "0")');
                    $dbConnection->query('INSERT INTO `links` (`id`, `userid`, `category`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$newUserId.'", "2", "leo", "https://dict.leo.org", "0")');
                    $dbConnection->query('INSERT INTO `links` (`id`, `userid`, `category`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$newUserId.'", "2", "gmail", "https://mail.google.com", "0")');
                    $result = $dbConnection->query('INSERT INTO `links` (`id`, `userid`, `category`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$newUserId.'", "3", "WhatsApp", "https://web.whatsapp.com", "0")');
                    if ($result) { // assuming that if the last one did work, the other three did work as well
                      printConfirmation('Did add a new user', 'Userid: '.$newUserId.'. Login with this user: <a href="index.php?userid='.$newUserId.'">login</a>', 'six', 'six');
                    } else { $dispErrorMsg = 25; } // links insert
                  } else { $dispErrorMsg = 24; } // categories insert
                } else { $dispErrorMsg = 23; } // user insert
              } else { $dispErrorMsg = 22; } // have less than 10
          } else { $dispErrorMsg = 21; } // query to do the counting did not work          
          break;  
        default: 
          $dispErrorMsg = 1;
        } // switch  
        if ($dispErrorMsg > 0) {
          printConfirmation('Error', '"Something" at step '.$dispErrorMsg.' went wrong when processing user input data (very helpful error message, I know...). Might try again?', 'nine', 'three');
        }
      } // action == integer          
    ?> 
    </div> <!-- /container -->
    <div class="section noBottom">
      <div class="container">
        <div class="row">
          <div class="twelve columns"><hr /></div>
        </div>      
      </div>
    </div>
  </div> <!-- /section categories -->
</body>
</html>