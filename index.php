<?php
  require_once('functions.php');
  $dbConnection = initialize();
  
  $doSafe           = makeSafeInt($_GET['do'], 1);
  $useridGetSafe    = makeSafeInt($_GET['userid'], 11);
  $useridCookieSafe = makeSafeInt($_COOKIE['userIdCookie'], 11);
  $randCookieSafe   = makeSafeHex($_COOKIE['randCookie'], 64);
  
  // this page has several entry points
  // a: unsecured
  // a1: first visit, direct visit (people typing widmedia.ch/start)
  // a2: log out function. do=1. linked from within the secure site, doing the logout (could be moved to some other page?)
  // a3: add new user function. do=2 (process form: do=3). linked from the insecure site
  // a4: confirm email. do=5
  // b: secured
  // b1: link with userid (do=0)
  // b2: direct visit (do=0), cookie is set
  // b3: login form done, do=4, (email/pw as POST). setCookie is checked or not

  if ($doSafe == 0) { // the $_GET-do parameter has higher priority than the rest
    if ($useridGetSafe > 0) { // login like index.php?userid=2 the $_GET-userid has higher priority than the cookie userid
      if (verifyCredentials($dbConnection, 3, $useridGetSafe, '', '')) {
        redirectRelative('main.php');
      }
    } elseif ($useridCookieSafe > 0) {
      if (verifyCredentials($dbConnection, 2, $useridCookieSafe, '', $randCookieSafe)) {
        redirectRelative('main.php');
      }
    }
  }
  
  // deletes both the cookie and the session
  function logOut () {
    sessionAndCookieDelete();
    redirectRelative('index.php?msg=7');
  }
 
  // function to do the login. Several options are available to log in
  function verifyCredentials ($dbConnection, $authMethod, $userid, $passwordUnsafe, $randCookieInput) {
    $loginOk = false;
    $_SESSION['userid'] = 0; // clear it just to make sure
    $dispErrorMsg = 0;
    
    if ($result = $dbConnection->query('SELECT `hasPw`, `pwHash`, `randCookie` FROM `user` WHERE `id` = "'.$userid.'"')) { // make sure a pw is enabled in the login-db
      if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hasPw = $row['hasPw'];
        $pwHash = $row['pwHash'];
        $randCookie = $row['randCookie'];

        if ($authMethod == 1) { // with a pw
          if ($hasPw == 1) {
            if (password_verify($passwordUnsafe, $pwHash)) {                 
              $loginOk = true;
            } else { $dispErrorMsg = 11; } // password was verified
          } else { $dispErrorMsg = 10; } // hasPw == 1
        } elseif ($authMethod == 2) { // with a Cookie
          if ($randCookie) { // new user has a zero
            if ($randCookie == $randCookieInput) {                
              $loginOk = true;            
            } else { $dispErrorMsg = 21; } // 64hex value is correct
          } else { $dispErrorMsg = 20; }  // there is no zero in the data base
        } elseif ($authMethod == 3) { // id only. the most unsafe one          
          if ($hasPw == 0) {
            $loginOk = true;     
          } else { $dispErrorMsg = 30; } // hasPw == 0
        } // authMethod
      } else { $dispErrorMsg = 2; } // numRows == 1
    } else { $dispErrorMsg = 1; } // select query did work

    printError($dispErrorMsg); // NB: this message will be displayed at the very start of a page. Not really nice but that's fine

    if ($loginOk) {
      if ($result = $dbConnection->query('UPDATE `user` SET `lastLogin` = CURRENT_TIMESTAMP WHERE `id` = "'.$userid.'"')) {
        $_SESSION['userid'] = $userid;
        return true;
      } // update query did work
    }  // loginOk 
    
    return false; 
  } // function
  
  
  // inserts some example values into `links` and `categories` tables
  function newUserLinks ($dbConnection, $newUserid) {
    $result0 = $dbConnection->query('INSERT INTO `categories` (`userid`, `category`, `text`) VALUES ("'.$newUserid.'", "1", "News")');
    $result1 = $dbConnection->query('INSERT INTO `categories` (`userid`, `category`, `text`) VALUES ("'.$newUserid.'", "2", "Work")');
    $result2 = $dbConnection->query('INSERT INTO `categories` (`userid`, `category`, `text`) VALUES ("'.$newUserid.'", "3", "Div")');
    
    $result3 = $dbConnection->query('INSERT INTO `links` (`userid`, `category`, `text`, `link`, `cntTot`) VALUES ("'.$newUserid.'", "1", "NZZ", "https://www.nzz.ch", "0")');
    $result4 = $dbConnection->query('INSERT INTO `links` (`userid`, `category`, `text`, `link`, `cntTot`) VALUES ("'.$newUserid.'", "2", "leo", "https://dict.leo.org", "0")');
    $result5 = $dbConnection->query('INSERT INTO `links` (`userid`, `category`, `text`, `link`, `cntTot`) VALUES ("'.$newUserid.'", "2", "gmail", "https://mail.google.com", "0")');
    $result6 = $dbConnection->query('INSERT INTO `links` (`userid`, `category`, `text`, `link`, `cntTot`) VALUES ("'.$newUserid.'", "3", "WhatsApp", "https://web.whatsapp.com", "0")');

    if ($result0 and $result1 and $result2 and $result3 and $result4 and $result5 and $result6) { 
      return true;
    } else {
      return false;
    }    
  }
  
  // sets the value in the `user` table as well as the `links` table
  function newUserLoginAndLinks ($dbConnection, $newUserid, $hasPw, $pw) {       
    // password_hash("testUserPassword", PASSWORD_DEFAULT) returns '$2y$10$3qc.gl4eDPpXDqM7hDssquu4ThnJ9rbH7wrEkdTZd0Cg0NAjAzm.2';
    if ($hasPw == 1) {
      $pwHash = password_hash($pw, PASSWORD_DEFAULT); // $pw is potentially unsafe. Shouldn't be an issue as I store the hash
    } else {
      $pwHash = '';
    }
  
    // NB: set a cookie for some random big number. Not the password itself and not the pwHash!
    // NB: will use this number on every cookie for this user, to login on several devices. One cannot guess other users random number                  
    $hexStr64 = bin2hex(random_bytes(32)); // some random value, used for cookie 
    $result = $dbConnection->query('UPDATE `user` SET `hasPw` = "'.$hasPw.'", `pwHash` = "'.$pwHash.'", `randCookie` = "'.$hexStr64.'" WHERE `id` = "'.$newUserid.'"');
    if ($result) { 
      return newUserLinks($dbConnection, $newUserid); // Adding 1 user, 3 categories, 4 links
    } else {
      return false;
    }    
  } 

  // returns the userid which matches to the email given. Returns 0 if something went wrong
  function mail2userid ($dbConnection, $emailSafe) {
    $userid = 0;
    if ($result = $dbConnection->query('SELECT `id` FROM `user` WHERE `email` = "'.mysqli_real_escape_string($dbConnection, $emailSafe).'"')) {
      if ($result->num_rows == 1) {
        $row = $result->fetch_row();
        $userid = $row[0];        
      }
    }
    return $userid;
  }  
  
  // sends an email to the new user with a special link and updates the database with that email confirmation link
  function newUserEmailConfirmation($dbConnection, $newUserid, $hasPw, $emailSqlSafe) {
    $hexStr64 = bin2hex(random_bytes(32)); // this is stored in the database    
    $emailBody = '
    Hello,
    
    Thank you for opening an account on widmedia.ch/start.
    You need to confirm your email address within 24 hours to fully use your account. Please click on the link below to do so:
    https://widmedia.ch/start/index.php?do=5&userid='.$newUserid.'&ver='.$hexStr64.'    
    
    ';
    if ($hasPw == 1) {
      $emailBody = $emailBody.'
      You did select password protection for your account. Please use the form on https://widmedia.ch/start/index.php#login to log in.
      ';
    } else {
      $emailBody = $emailBody.'
      You did not select password protection. This means you (and, btw. everybody else) may login with this link:
      https://widmedia.ch/start/index.php?userid='.$newUserid.'
      Please store this link for future use as a bookmark or maybe your browser starting page.
      ';
    }
    $emailBody = $emailBody.'
    Have fun and best regards,
    Daniel from widmedia
    
    -- 
    Contact (English or German): sali@widmedia.ch
    ';
    
    if ($result = $dbConnection->query('UPDATE `user` SET `verCode` = "'.$hexStr64.'" WHERE `id` = "'.$newUserid.'"')) {   
      if (mail($emailSqlSafe, 'Your new account on widmedia.ch/start', $emailBody)) {
        return true;
      } // mail send
    } // update query
    
    // should not reach this point
    return false;    
  }
  
  
  // there is a similar function (printUserEdit) in editUser.php. However, differs too heavy to merge those two  
  function printNewUserForm() {
    echo '<h3 class="section-heading">New account</h3>
    <form action="index.php?do=3" method="post">
    <div class="row twelve columns" style="text-align: left;"><input type="checkbox" id="pwCheckBox" name="hasPw" value="1" checked onclick="pwToggle();"> password protection for this account <div id="noPwWarning" class="noPwWarning" style="display: none;">Please be aware: when not using a password, everybody can log into this account and edit information or delete the account itself</div></div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row">
      <div class="two columns">email: </div>
      <div class="ten columns"><input name="email" type="email" maxlength="127" value="" required size="20"></div>
    </div>    
    <div class="row" id="pwRow">
      <div class="two columns">password: </div>
      <div class="ten columns"><input name="password" type="password" maxlength="63" value="" size="20"></div>
    </div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns"><input name="create" type="submit" value="create account"></div>
    </form>
    ';   
  } // function
  
  function printTitle() {
    echo '<h2 class="section-heading">widmedia.ch/start</h2>
    <div class="row" style="font-weight: bold; font-size: larger; text-align: left"><div class="twelve columns">a simple customizable start page, a personal link collection</div></div>';
    printHr();
    echo '
    <div class="row">
      <div class="eight columns"><img src="images/teaser.gif" width="400" height="182" style="border: 1px black solid;" class="u-max-full-width"></div>
      <div class="four columns textBox"><br /><ul>
        <li>your personal list of links</li>
        <li>sorted by occurence</li>
        <li>links open on new tab</li>
        <li>edit and add your own links</li>
        <li>easy login</li>
      </ul></div>
    </div>';
    printHr();
    echo '<div class="row twelve columns">Try it <img src="images/arrow_right_green.png" class="logoImg"> <a href="index.php?userid=2" class="button button-primary">log in as the test user</a></div>';
    printHr();
  } // function
  
  function printEntryPoint($dbConnection) {
    printTitle();
    echo '
    <h3 class="section-heading"><span id="login">Log in</span></h3>
    <form action="index.php?do=4" method="post">
    <div class="row">
      <div class="three columns">email: </div>
      <div class="nine columns"><input name="email" type="email" maxlength="127" value="" required size="20"></div>
    </div>
    <div class="row">
      <div class="three columns">password: </div>
      <div class="nine columns"><input name="password" type="password" maxlength="63" value="" required size="20"></div>
    </div>
    <div class="row twelve columns" style="font-size: smaller;"><input type="checkbox" name="setCookie" value="1" checked>save log in information for 4 weeks</div>
    <div class="row twelve columns"><input name="login" type="submit" value="log in"></div>
    </form>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row" style="font-size: smaller;">
      <div class="six columns"><a href="index.php?do=2" class="button button-primary"><img src="images/plus_green.png" class="logoImg"> open a new account</a></div>
      <div class="six columns"><a href="index.php?do=9" class="button button-primary"><img src="images/question_green.png" class="logoImg"> (TODO) forgot my password</a></div>
    </div>
    <div class="row twelve columns">&nbsp;</div>';
  } // function  
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>widmedia.ch/start</title>
  <meta name="description" content="a modifiable page containing various links, intended to be used as a personal start page">
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
  // possible actions: 
  // 0/non-existing: normal case
  // 1=> logout
  // 2=> add new user
  // 3=> process adding new user
  // 4=> process login form (email/pw/setCookie)
  // 5=> do the email verification
  
  $msgSafe = makeSafeInt($_GET['msg'], 1);
  if ($msgSafe > 0) {
    echo '<body onLoad="overlayMsgFade();">'; 
    printMessage($msgSafe); 
  } else {
    echo '<body>';
  }
  
  echo '<div class="section categories noBottom"><div class="container">';
  
  if ($doSafe == 0) { // valid use case. Entry point of this site
    printEntryPoint($dbConnection);        
  } elseif ($doSafe > 0) {
    $emailUnsafe    = filter_var(substr($_POST['email'], 0, 127), FILTER_SANITIZE_EMAIL);    // email string, max length 127
    $passwordUnsafe = filter_var(substr($_POST['password'], 0, 63), FILTER_SANITIZE_STRING); // generic string, max length 63
    $setCookieSafe  = makeSafeInt($_POST['setCookie'], 1);
    
    
    if ($doSafe == 1) { // log out
      logOut();
    } elseif ($doSafe == 2) { // present the new user form
      printTitle();
      printNewUserForm();
    } elseif ($doSafe == 3) { // process the new user form data, add a new user
      // step 1: user data need to make sense: email-addr valid
      $hasPw = makeSafeInt($_POST['hasPw'], 1);
     
      if (filter_var($emailUnsafe, FILTER_VALIDATE_EMAIL)) { // have a valid email 
        // check whether email already exists
        $emailSqlSafe = mysqli_real_escape_string($dbConnection, $emailUnsafe);
        if ($result = $dbConnection->query('SELECT `verified` FROM `user` WHERE `email` LIKE "'.$emailSqlSafe.'" LIMIT 1')) {
          if ($result->num_rows == 0) {
            if (($hasPw == 0) or (($hasPw == 1) and (strlen($passwordUnsafe) > 3))) {                    
              if ($result = $dbConnection->query('INSERT INTO `user` (`email`, `lastLogin`) VALUES ("'.$emailSqlSafe.'", CURRENT_TIMESTAMP)')) { 
                $newUserid = $dbConnection->insert_id;
                if (newUserLoginAndLinks($dbConnection, $newUserid, $hasPw, $passwordUnsafe)) {                      
                  if(newUserEmailConfirmation($dbConnection, $newUserid, $hasPw, $emailSqlSafe)) {
                    if ($hasPw == 1) {
                      $loginText = '<a href="index.php">go to login page</a>';
                    } else {
                      $loginText = '<a href="index.php?userid='.$newUserid.'">login</a>';
                    }
                    printConfirm('Your account has been created', 'Congratulations and thanks. Your account is now ready. Please '.$loginText);                        
                  } else { $dispErrorMsg = 37; } // newUserEmail
                } else { $dispErrorMsg = 36; } // links, categories insert
              } else { $dispErrorMsg = 35; } // user insert                        
            } else { $dispErrorMsg = 34; echo 'you selected a password but the password is too short (at least 4 characters)'; } // if password, then length ok TODO: error message
          } else { $dispErrorMsg = 33; echo 'An account with this email address is already existing'; } // email does not exist. TODO: error message
        } else { $dispErrorMsg = 32; } // query worked
      } else { $dispErrorMsg = 31; } // have a valid email 
    } elseif ($doSafe == 4) { // process the login data, maybe set a cookie
      if (filter_var($emailUnsafe, FILTER_VALIDATE_EMAIL)) { // have a valid email
        $userid = mail2userid($dbConnection, $emailUnsafe);
        if ($userid > 0) { // now, can do the check of the hash value
          if (verifyCredentials($dbConnection, 1, $userid, $passwordUnsafe, '')) {                
            if ($setCookieSafe == 1) {
              $expire = time() + (3600 * 24 * 7 * 4); // valid for 4 weeks
              setcookie('userIdCookie', $userid, $expire); 
              if ($result = $dbConnection->query('SELECT `randCookie` FROM `user` WHERE `id` = "'.$userid.'"' )) { // this is just a random number which has been set at user creation
                $row = $result->fetch_row();
                setcookie('randCookie', $row[0], $expire);
              } else { $dispErrorMsg = 43; } // select query
            } // setCookie is selected
            redirectRelative('main.php');
          } else { $dispErrorMsg = 42; } // verification ok
        } else { $dispErrorMsg = 41; } // email found in db
      } else { $dispErrorMsg = 40; } // valid email          
    } elseif ($doSafe == 5) { // confirm the email address 
      if ($useridGetSafe > 2) {
        // NB: I'm not even looking at the date (mentioned a 24 hour limit in the email). That's fine. I'll just delete accounts which have not been verified after some days...
        $verGet = makeSafeHex($_GET['ver'], 64);
        $verSqlSafe = mysqli_real_escape_string($dbConnection, $verGet);
        if ($result = $dbConnection->query('SELECT `hasPw` FROM `user` WHERE `id` = "'.$useridGetSafe.'" AND `verCode` = "'.$verSqlSafe.'"')) {
          if ($result->num_rows == 1) {
            $row = $result->fetch_row();
            $hasPw = $row[0];
            if ($result = $dbConnection->query('UPDATE `user` SET `verified` = "1" WHERE `id` = "'.$useridGetSafe.'"')) {   
              $loginLink = 'https://widmedia.ch/start/index.php';
              if ($hasPw == 1) {
                $loginLink = $loginLink.'#login';  
              } else {
                $loginLink = $loginLink.'?userid='.$useridGetSafe;  
              }
              printConfirm('Verified', 'Thank you. Your email address has been verified and your account is now fully functional. Please <a href="'.$loginLink.'">log in</a>.');
            } else { $dispErrorMsg = 53; } // update query
          } else { $dispErrorMsg = 52; } // 1 result
        } else { $dispErrorMsg = 51; } // select query
      } else { $dispErrorMsg = 50; } // valid userid         
    } else {
      $dispErrorMsg = 1;
    } // switch
    printError($dispErrorMsg);
  } // action == integer          
  echo '</div> <!-- /container -->';  
  printFooter(); 
?>     
  </div> <!-- /section categories -->
</body>
</html>