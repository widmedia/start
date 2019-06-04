<?php
  require_once('functions.php');
  $dbConnection = initialize();
  
  // Form processing
  $doSafe     = makeSafeInt($_GET['do'], 1);       // this is an integer (range 1 to 2)
  $useridSafe = makeSafeInt($_GET['userid'], 11);  
  $useridCookieSafe = makeSafeInt($_COOKIE['userIdCookie'], 11);
  $keySafe = makeSafeKey($_GET['key']);
  
  // TODO: security measures 
  
  // this page has several entry points
  // a: unsecured
  // a1: first visit, direct visit (people typing widmedia.ch/start)
  // a2: log out function. do=1. linked from within the secure site, doing the logout
  // a3: add new user function. do=2. linked from the insecure site. TODO
  // b: secured
  // b1: link with userid and key: userid=7&key=ABCDEF1234...
  // b2: direct visit (do=0), cookie is set
  // b3: login form done, do=4, (email/pw as POST). setCookie is checked or not
  
  
  // NB: the difference between user and login, why not have only one?
  // - I don't want sensitive info (email) in the login db. And user info will grow, custom colors and stuff...
  
  if ($doSafe == 0) { // the $_GET-do parameter has higher priority than the rest
    if ($useridSafe) { // the $_GET-userid has higher priority than the cookie userid
      if (verifyCredentials($dbConnection, true, $useridSafe, 'non-existing')) { 
        if (makeSafeInt($_GET['setCookie'], 1)) {
          $expire = 60 * 60 * 24 * 7 * 4; // valid for 4 weeks
          setcookie('userIdCookie', $useridSafe, time() + $expire);      
        }    
        redirectRelative('main.php');    
      }      
    } elseif ($useridCookieSafe) {    
      if (verifyCredentials($dbConnection, true, $useridCookieSafe, 'non-existing')) {    
        redirectRelative('main.php');    
      }
    } // else, present the userid selection page
  } 
  
  // deletes both the cookie and the session 
  function logOut () {        
    sessionAndCookieDelete();
    redirectRelative('index.php'); // maybe TODO: display some 'logout successful' overlay on the index page?
  }

  // TODO:  
  // - remove legacy stuff
  // - without a password, verify the key
  function verifyCredentials ($dbConnection, $legacyMode, $userid, $passwordUnsafe) {
    if ($legacyMode) { // TODO: will be removed
      $internalUserid = makeSafeInt($userid, 11); // might be unnecessary because it's safe already
      if ($result = $dbConnection->query('SELECT `lastLogin` FROM `user` WHERE `id` = "'.$internalUserid.'"')) {
        if ($result->num_rows == 1) { // we are sure the id exists and there is only one
          if ($result = $dbConnection->query('UPDATE `user` SET `lastLogin` = CURRENT_TIMESTAMP WHERE `id` = "'.$internalUserid.'"')) {
            $_SESSION['userid'] = $internalUserid;
            return true;
          } // update query did work      
        } // id does exist
      } // select query did work
    } else {      
      if ($result = $dbConnection->query('SELECT `pwHash` FROM `login` WHERE `userid` = "'.$userid.'"')) {
        if ($result->num_rows == 1) {
          $row = $result->fetch_row();
          if (password_verify($passwordUnsafe, $row[0])) { 
            if ($result = $dbConnection->query('SELECT `lastLogin` FROM `user` WHERE `id` = "'.$userid.'"')) {
              if ($result->num_rows == 1) { // we are sure the id exists and there is only one
                if ($result = $dbConnection->query('UPDATE `user` SET `lastLogin` = CURRENT_TIMESTAMP WHERE `id` = "'.$userid.'"')) {
                  $_SESSION['userid'] = $userid;
                  return true;
                } // update query did work      
              } // id does exist
            } // select query did work            
          } // password was verified
        } // pwHash did get one result
      } // pwHash query ok      
    } // legacy mode
    return false;
  }
    
  // inserts standard values into `links` and `categories` tables
  function newUserLinks ($newUserid, $dbConnection) {
    $result0 = $dbConnection->query('INSERT INTO `categories` (`id`, `userid`, `category`, `text`) VALUES (NULL, "'.$newUserid.'", "1", "News")');
    $result1 = $dbConnection->query('INSERT INTO `categories` (`id`, `userid`, `category`, `text`) VALUES (NULL, "'.$newUserid.'", "2", "Work")');
    $result2 = $dbConnection->query('INSERT INTO `categories` (`id`, `userid`, `category`, `text`) VALUES (NULL, "'.$newUserid.'", "3", "Div")');
    
    $result3 = $dbConnection->query('INSERT INTO `links` (`id`, `userid`, `category`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$newUserid.'", "1", "NZZ", "https://www.nzz.ch", "0")');
    $result4 = $dbConnection->query('INSERT INTO `links` (`id`, `userid`, `category`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$newUserid.'", "2", "leo", "https://dict.leo.org", "0")');
    $result5 = $dbConnection->query('INSERT INTO `links` (`id`, `userid`, `category`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$newUserid.'", "2", "gmail", "https://mail.google.com", "0")');
    $result6 = $dbConnection->query('INSERT INTO `links` (`id`, `userid`, `category`, `text`, `link`, `cntTot`) VALUES (NULL, "'.$newUserid.'", "3", "WhatsApp", "https://web.whatsapp.com", "0")');
                  
    if ($result0 and $result1 and $result2 and $result3 and $result4 and $result5 and $result6) { 
      return true;
    } else {
      return false;
    }    
  }
  
  // TODO: this might be merged with a similar function (printUserEdit) in editUser.php
  function printNewUserForm() {
    // TODO: design not yet very nice
    echo '<h3 class="section-heading">New account</h3>
    <form action="index.php?do=3" method="post">
    <div class="row"><div class="two columns">email: </div><div class="ten columns"><input name="email" type="email" maxlength="127" value="" required size="20"></div></div>
    <div class="row"><div class="two columns"><input type="checkbox" name="hasPw" value="1" checked onclick="pwFieldToggle();"></div><div class="ten columns" style="text-align: left;">password protection for this account</div></div>
    <div class="row"><div class="two columns">password: </div><div class="ten columns"><input id="passwordTextField" name="password" type="password" maxlength="63" value="" size="20"></div></div>
    <div class="row"><div class="two columns"><input type="checkbox" name="hasKey" value="1" onclick="magicKeyToggle();"></div><div class="ten columns" style="text-align: left;">"magic key" login for easier access</div></div>
    <div class="row"><div class="twelve columns">login like: <span id="magicKeyExample" style="text-decoration: line-through;">https://widmedia.ch/start/index.php?userid=2&key=9233B77ADC7F47F5A7F2EC5F</span></div></div>    
    <div class="row"><div class="twelve columns"><input name="create" type="submit" value="create account"></div></div>
    </form>
    ';   
  } // function
  
  function printTitle() {
    // TODO: need to advertise the /start functionality. With some kind of animation...
    echo '<h2 class="section-heading">widmedia.ch/start</h2>
    <div class="row" style="font-weight: bold; font-size: larger; text-align: left"><div class="twelve columns">a simple customizable start page, a personal link collection</div></div>
    <div class="row"><div class="twelve columns"><p>&nbsp;</p></div></div>
    <div class="row"><div class="twelve columns">Try it out: <a href="index.php?userid=2&key=9233B77ADC7F47F5A7F2EC5F" class="button button-primary">log in as the test user</a></div>
    </div>';
    printHr();    
  } // function
  
  function printEntryPoint($dbConnection) {
    // TODO: this will change...    
    
    // TODO
    // I have a table `login`: id / userid / hasPw / hasKey / pwHash / magicKey. Use it for login-related stuff
    // --> TODO: new user requires items there as well, delete an existing user has to remove it once more
    // selection between
    // a: login as test user, try stuff (test user does have both a pw and a key)
    // b: TODO: prio_1: open a new account --> more or less the editUser page
    // c: login as existing --> more or less the page below
    
    printTitle();
    
    echo '
    <h3 class="section-heading">Log in</h3>
    <form action="index.php?do=4" method="post">
    <div class="row"><div class="three columns">email: </div><div class="nine columns"><input name="email" type="email" maxlength="127" value="" required size="20"></div></div>
    <div class="row"><div class="three columns">password: </div><div class="nine columns"><input name="password" type="password" maxlength="63" value="" required size="20"></div></div>
    <div class="row" style="font-size: smaller;"><div class="twelve columns"><input type="checkbox" name="setCookie" value="1" checked>save log in information for 4 weeks</div></div>    
    <div class="row"><div class="twelve columns"><input name="login" type="submit" value="log in"></div></div>
    </form>
    <div class="row"><div class="twelve columns">&nbsp;</div></div>
    <div class="row" style="font-size: smaller;">
      <div class="six columns"><a href="index.php?do=2">open a new account</a></div>
      <div class="six columns"><a href="index.php?do=9">forgot my password</a></div>
    </div>
    ';
    printHr();
    
    echo '
    <div class="row"><div class="twelve columns"><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p></div></div>
    <h3 class="section-heading">...old stuff...</h3>    
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
  <title>widmedia.ch/start</title>
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
  
  <script>
  function pwFieldToggle() {
    document.getElementById('passwordTextField').disabled = !(document.getElementById('passwordTextField').disabled);
  }
  function magicKeyToggle() {       
    if (document.getElementById('magicKeyExample').style.textDecoration == "line-through") {
      document.getElementById('magicKeyExample').style.textDecoration = "none";
    } else {
      document.getElementById('magicKeyExample').style.textDecoration = "line-through";
    }
  }  
</script>

</head>
<body>
  <div class="section categories noBottom">
    <div class="container">
    <?php           
      // possible actions: 
      // 0/non-existing: normal case
      // 1=> logout
      // 2=> add new user
      // 3=> TODO
      // 4=> process email/pw form
      
      if ($doSafe == 0) { // valid use case. Entry point of this site
        printEntryPoint($dbConnection);        
      } elseif ($doSafe > 0) {
        $emailUnsafe    = filter_var(substr($_POST['email'], 0, 127), FILTER_SANITIZE_EMAIL);    // email string, max length 127
        $passwordUnsafe = filter_var(substr($_POST['password'], 0, 63), FILTER_SANITIZE_STRING); // generic string, max length 63
        $setCookieSafe  = makeSafeInt($_POST['setCookie'],1);
        
        
        switch ($doSafe) {
        case 1:
          logOut();
          break;
        case 2:
          printTitle();
          printNewUserForm();
          break;
        case 3:
          // Adding 1 user, 3 categories, 4 links
          // add a new user but only if number of users is less than 10 (TODO: developer limitation for now)
          if (false) { // TODO: need to process the form data
            // password_hash("testUserPassword", PASSWORD_DEFAULT);
            // returns '$2y$10$3qc.gl4eDPpXDqM7hDssquu4ThnJ9rbH7wrEkdTZd0Cg0NAjAzm.2';
            if ($result = $dbConnection->query('SELECT count(*) AS `total` FROM `user`')) {
                $row = $result->fetch_assoc();
                $rowCnt = $row['total'];              
                if($rowCnt < 10) {
                  $sqlInsertUser = 'INSERT INTO `user` (`id`, `email`, `lastLogin`) VALUES (NULL, "new@widmedia.ch", CURRENT_TIMESTAMP)';
                  if ($result = $dbConnection->query($sqlInsertUser)) { 
                    $newUserid = $dbConnection->insert_id;
                    if(newUserLinks($newUserid, $dbConnection)) {
                      printConfirmation('Did add a new user', 'Userid: '.$newUserid.'. Login with this user: <a href="index.php?userid='.$newUserid.'">login</a>', 'six', 'six');
                    } else { $dispErrorMsg = 24; } // links, categories insert
                  } else { $dispErrorMsg = 23; } // user insert
                } else { $dispErrorMsg = 22; } // have less than 10
            } else { $dispErrorMsg = 21; } // query to do the counting did not work          
          }
          break;        
        case 4:
          if (filter_var($emailUnsafe, FILTER_VALIDATE_EMAIL)) { // have a valid email
            $userid = mail2userid($emailUnsafe, $dbConnection);
            if ($userid) { // now, can do the check of the hash value
              if (verifyCredentials($dbConnection, false, $userid, $passwordUnsafe)) {                
                if ($setCookieSafe == 1) {
                  $expire = time() + (3600 * 24 * 7 * 4); // valid for 4 weeks
                  setcookie('userIdCookie', $userid, $expire); 
                  // NB: set a cookie for some random big number. Not the password itself and not the pwHash! Cannot use the magicKey either because this one is used on $_GET
                  // TODO: not sure if I can use this to login on several devices ...
                  $hexStr64 = bin2hex(random_bytes(32)); 
                  setcookie('randCookie', $hexStr64, $expire);
                  if (!($result = $dbConnection->query('UPDATE `login` SET `randCookie` = "'.$hexStr64.'" WHERE `id` = "'.$userid.'"'))) {   
                    die('setting the cookie did not work'); // TODO: not a very meaningful message
                  } // updating the random string did work ok
                } // setCookie is selected
                redirectRelative('main.php');
              } else { $dispErrorMsg = 43; } // verification ok
            } else { $dispErrorMsg = 42; } // email found in db
          } else { $dispErrorMsg = 41; } // valid email          
          break;          
        default: 
          $dispErrorMsg = 1;
        } // switch  
        if ($dispErrorMsg > 0) {
          printConfirmation('Error', '"Something" at step '.$dispErrorMsg.' went wrong when processing user input data (very helpful error message, I know...). Might try again?', 'nine', 'three');
        }
      } // action == integer          
      echo '</div> <!-- /container -->';  
      printFooter(); 
    ?>     
  </div> <!-- /section categories -->
</body>
</html>