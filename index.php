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
  // a2: log out function. do=1. linked from within the secure site, doing the logout
  // a3: add new user function. do=2 (process form: do=3). linked from the insecure site
  // a4: confirm email. do=5
  // b: secured
  // b1: link with userid (do=0)
  // b2: direct visit (do=0), cookie is set
  // b3: login form done, do=4, (email/pw as POST). setCookie is checked or not

  if ($doSafe == 0) { // the $_GET-do parameter has higher priority than the rest
    if ($useridGetSafe > 0) { // login like index.php?userid=2 the $_GET-userid has higher priority than the cookie userid
      if (verifyCredentials($dbConnection, 3, $useridGetSafe, '', '')) {
        redirectRelative('links.php');
      }
    } elseif ($useridCookieSafe > 0) {
      if (verifyCredentials($dbConnection, 2, $useridCookieSafe, '', $randCookieSafe)) {
        redirectRelative('links.php');
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
            } else {
              $dispErrorMsg = 21;
            } // 64hex value is correct
          } else { $dispErrorMsg = 20; }  // there is no zero in the data base
        } elseif ($authMethod == 3) { // id only. the most unsafe one
          if ($hasPw == 0) {
            $loginOk = true;
          } else { $dispErrorMsg = 30; } // hasPw == 0
        } // authMethod
      } else { $dispErrorMsg = 2; } // numRows == 1
    } else { $dispErrorMsg = 1; } // select query did work

    printError($dbConnection, $dispErrorMsg); // NB: this message will be displayed at the very start of a page. Not really nice but that's fine

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
    $result4 = $dbConnection->query('INSERT INTO `links` (`userid`, `category`, `text`, `link`, `cntTot`) VALUES ("'.$newUserid.'", "2", "Leo", "https://dict.leo.org", "0")');
    $result5 = $dbConnection->query('INSERT INTO `links` (`userid`, `category`, `text`, `link`, `cntTot`) VALUES ("'.$newUserid.'", "2", "Gmail", "https://mail.google.com", "0")');
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
    $emailBody = "Sali,\n\n".getLanguage($dbConnection,95)."\n\n".getLanguage($dbConnection,96)."\nhttps://widmedia.ch/start/index.php?do=5&userid=".$newUserid."&ver=".$hexStr64."\n";
    if ($hasPw == 1) {
      $emailBody = $emailBody.getLanguage($dbConnection,97)."\nhttps://widmedia.ch/start/index.php#login ".getLanguage($dbConnection,98)."\n";
    } else {
      $emailBody = $emailBody.getLanguage($dbConnection,99)."\nhttps://widmedia.ch/start/index.php?userid=".$newUserid."\n".getLanguage($dbConnection,100)."\n";
    }
    $emailBody = $emailBody."\n\n".getLanguage($dbConnection,101)."\nDaniel ".getLanguage($dbConnection,102)." widmedia\n\n--\n".getLanguage($dbConnection,5).": sali@widmedia.ch\n";
    
    if ($result = $dbConnection->query('UPDATE `user` SET `verCode` = "'.$hexStr64.'" WHERE `id` = "'.$newUserid.'"')) {   
      if (mail($emailSqlSafe, getLanguage($dbConnection,103), $emailBody)) {
        return true;
      } // mail send
    } // update query
    
    // should not reach this point
    return false;    
  }
  
  // prints some graph with the user statistics 
  function printUserStat($dbConnection) {
    $currentTime = time();
    $year = date('Y', $currentTime); // TODO: provide option to select another year
    
    $result = $dbConnection->query('SELECT `month`, `numUser` FROM `userStat` WHERE `year` = "'.$year.'" ORDER BY `month`');
    
    $userStatPerMonth = array(0,0,0,0,0,0,0,0,0,0,0,0); // twelve zeros
    $months = array('Jan','Feb',getLanguage($dbConnection,58),'Apr',getLanguage($dbConnection,59),'Jun','Jul','Aug','Sep',getLanguage($dbConnection,60),'Nov',getLanguage($dbConnection,61));
    $maxVal = 0;
    while ($row = $result->fetch_assoc()) {
      $userStatPerMonth[($row['month']-1)] = $row['numUser']; // array index is from 0 to 11
      if ($row['numUser'] > $maxVal) { 
        $maxVal = $row['numUser'];
      } 
    } // while
    
    // print a table with the twelve months
    echo '<div class="row twelve columns">&nbsp;</div><div class="row twelve columns">&nbsp;</div>';
    printHr();
    echo '<h3 class="section-heading">'.getLanguage($dbConnection,56).$year.'</h3><div class="row">';
    for ($i = 0; $i < 12; $i++) {       
      $height = round($userStatPerMonth[$i] / $maxVal * 100)+1; // maxVal corresponds to 100 px min-height
      echo '<div class="one columns" style="vertical-align: bottom;"><span style="font-weight: 600;">'.$months[$i].'</span><br><span class="userStatBar" style="min-height: '.$height.'px;">'.$userStatPerMonth[$i].'</span></div>';
    }
    echo '</div>
    <div class="row twelve columns">'.getLanguage($dbConnection,57).'</div>';
  }
    
  
  // there is a similar function (printUserEdit) in editUser.php. However, differs too heavy to merge those two  
  function printNewUserForm($dbConnection) {
    echo '<h3 class="section-heading"><span id="newUser">'.getLanguage($dbConnection,32).'</span></h3>
    <form action="index.php?do=3" method="post">
    <div class="row twelve columns" style="text-align: left;"><input type="checkbox" id="pwCheckBox" name="hasPw" value="1" checked onclick="pwToggle();"> '.getLanguage($dbConnection,47).' <div id="noPwWarning" class="noPwWarning" style="display: none;">'.getLanguage($dbConnection,48).'</div></div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row">
      <div class="three columns">'.getLanguage($dbConnection,62).': </div>
      <div class="nine columns"><input name="email" type="email" maxlength="127" value="" required size="20"></div>
    </div>    
    <div class="row" id="pwRow">
      <div class="three columns">'.getLanguage($dbConnection,63).': </div>
      <div class="nine columns"><input name="password" type="password" maxlength="63" value="" size="20"></div>
    </div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns"><input name="create" type="submit" value="'.getLanguage($dbConnection,64).'"></div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns">&nbsp;</div>
    </form>
    ';   
  } // function
  
  function printTitle($dbConnection) {    
    echo '<h2 class="section-heading">widmedia.ch/start</h2>
    <div class="row twelve columns" style="font-weight: bold; font-size: larger; text-align: left">'.getLanguage($dbConnection,65).'</div>';
    printHr();
    echo '
    <div class="row">
      <div class="eight columns">
        <div class="slideshow-container">
          <div class="mySlides fade u-max-full-width"><img src="images/teaser_01.png" alt="your startpage with all the links" class="imgBorder" style="width:100%; vertical-align:middle;"><div class="captionText">'.getLanguage($dbConnection,74).'</div></div>
          <div class="mySlides fade u-max-full-width"><img src="images/teaser_02.png" alt="click your external link" class="imgBorder" style="width:100%; vertical-align:middle;"><div class="captionText">'.getLanguage($dbConnection,75).'</div></div>
          <div class="mySlides fade u-max-full-width"><img src="images/teaser_03.png" alt="the link opens in a new tab" class="imgBorder" style="width:100%; vertical-align:middle;"><div class="captionText">'.getLanguage($dbConnection,76).'</div></div>
          <div class="mySlides fade u-max-full-width"><img src="images/teaser_04.png" alt="edit your links, add a new one" class="imgBorder" style="width:100%; vertical-align:middle;"><div class="captionText">'.getLanguage($dbConnection,77).'</div></div>
          <div class="mySlides fade u-max-full-width"><img src="images/teaser_05.png" alt="there it is, your new link" class="imgBorder" style="width:100%; vertical-align:middle;"><div class="captionText">'.getLanguage($dbConnection,78).'</div></div>
          <div class="mySlides fade u-max-full-width"><img src="images/teaser_06.png" alt="your new start page" class="imgBorder" style="width:100%; vertical-align:middle;"><div class="captionText">'.getLanguage($dbConnection,79).'</div></div>
        </div>
        <br>
        <div style="text-align:center">
          <span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span>
        </div>
        <script type="text/javascript">
          var slideIndex = 0;
          showSlides();

          function showSlides() {
            var i;
            var slides = document.getElementsByClassName("mySlides");
            var dots = document.getElementsByClassName("dot");
            for (i = 0; i < slides.length; i++) {
              slides[i].style.display = "none";  
            }
            slideIndex++;
            if (slideIndex > slides.length) {slideIndex = 1}    
            for (i = 0; i < dots.length; i++) {
              dots[i].className = dots[i].className.replace(" active", "");
            }
            slides[slideIndex-1].style.display = "block";  
            dots[slideIndex-1].className += " active";
            setTimeout(showSlides, 4000); 
          }
        </script>
        </div>
      <div class="four columns textBox"><br><ul>
        <li>'.getLanguage($dbConnection,66).'</li>
        <li>'.getLanguage($dbConnection,67).'</li>
        <li>'.getLanguage($dbConnection,68).'</li>
        <li>'.getLanguage($dbConnection,69).'</li>
        <li>'.getLanguage($dbConnection,70).'</li>
        <li>'.getLanguage($dbConnection,71).': <a href="index.php?userid=2">'.getLanguage($dbConnection,72).'</a> ... '.getLanguage($dbConnection,73).' <a href="index.php?do=2#newUser">'.getLanguage($dbConnection,64).'</a></li>        
      </ul></div>
    </div>';
    printHr();
    echo '<div class="row twelve columns">'.getLanguage($dbConnection,80).' <img src="images/icon_arrow_right.png" alt="pointing to the open free account form" class="logoImg"> <a href="index.php?do=2#newUser" class="button"><img src="images/icon_plus.png" alt="open your own free account" class="logoImg"> '.getLanguage($dbConnection,81).'</a></div>';
    printHr();
    echo '<div class="row twelve columns">'.getLanguage($dbConnection,82).' <img src="images/icon_arrow_right.png" alt="pointing to the test user login" class="logoImg"> <a href="index.php?userid=2" class="button">'.getLanguage($dbConnection,83).'</a></div>';
    printHr();
  } // function
  
  function printEntryPoint($dbConnection) {    
    echo '
    <h3 class="section-heading"><span id="login">Log in</span></h3>
    <form action="index.php?do=4" method="post">
    <div class="row">
      <div class="three columns">Email: </div>
      <div class="nine columns"><input name="email" type="email" maxlength="127" value="" required size="20"></div>
    </div>
    <div class="row">
      <div class="three columns">'.getLanguage($dbConnection,84).': </div>
      <div class="nine columns"><input name="password" type="password" maxlength="63" value="" required size="20"></div>
    </div>
    <div class="row twelve columns" style="font-size: smaller;"><input type="checkbox" name="setCookie" value="1" checked>'.getLanguage($dbConnection,85).'</div>
    <div class="row twelve columns"><input name="login" type="submit" value="log in"></div>
    </form>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row">
      <div class="six columns"><a href="index.php?do=2#newUser" class="button"><img src="images/icon_plus.png" alt="open your own account" class="logoImg"> '.getLanguage($dbConnection,81).'</a></div>
      <div class="six columns"><a href="index.php?do=9" class="button"><img src="images/icon_question.png" alt="get an email with your new password" class="logoImg"> '.getLanguage($dbConnection,87).'</a></div>
    </div>
    <div class="row twelve columns">&nbsp;</div>';    
  } // function  
  
  // possible actions: 
  // 0/non-existing: normal case
  // 1=> logout
  // 2=> add new user
  // 3=> process adding new user
  // 4=> process login form (email/pw/setCookie)
  // 5=> do the email verification
  
  $emailUnsafe    = filter_var(substr($_POST['email'], 0, 127), FILTER_SANITIZE_EMAIL);    // email string, max length 127
  $passwordUnsafe = filter_var(substr($_POST['password'], 0, 63), FILTER_SANITIZE_STRING); // generic string, max length 63
  $setCookieSafe  = makeSafeInt($_POST['setCookie'], 1);
  $hasPw          = makeSafeInt($_POST['hasPw'], 1);
  
  if ($doSafe == 0) { // valid use case. Entry point of this site
    printStartOfHtml($dbConnection);
    printTitle($dbConnection);
    printEntryPoint($dbConnection);
    printUserStat($dbConnection);
  } elseif ($doSafe == 1) { // log out
    logOut();
  } elseif ($doSafe == 2) { // present the new user form
    printStartOfHtml($dbConnection);
    printTitle($dbConnection);
    printNewUserForm($dbConnection);
    printUserStat($dbConnection);
  } elseif ($doSafe == 3) { // process the new user form data, add a new user
    printStartOfHtml($dbConnection);
    // step 1: user data need to make sense: email-addr valid           
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
                    $loginText = getLanguage($dbConnection,88).' <a href="index.php#login">https://widmedia.ch/start/index.php#login</a>';
                  } else {
                    $loginText = 'login <a href="index.php?userid='.$newUserid.'">https://widmedia.ch/start/index.php?userid='.$newUserid.'</a>';
                  }                    
                  printConfirm(getLanguage($dbConnection,89), getLanguage($dbConnection,90).$loginText.'
                  <br><br>'.getLanguage($dbConnection,86));
                } else { $dispErrorMsg = 37; } // newUserEmail
              } else { $dispErrorMsg = 36; } // links, categories insert
            } else { $dispErrorMsg = 35; } // user insert                        
          } else { $dispErrorMsg = 34; printConfirm('Error',getLanguage($dbConnection,91)); } // if password, then length ok
        } else { $dispErrorMsg = 33; printConfirm('Error',getLanguage($dbConnection,92)); } // email does not exist
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
          redirectRelative('links.php');
        } else { $dispErrorMsg = 42; } // verification ok
      } else { $dispErrorMsg = 41; } // email found in db
    } else { $dispErrorMsg = 40; } // valid email          
  } elseif ($doSafe == 5) { // confirm the email address
    printStartOfHtml($dbConnection);
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
            printConfirm(getLanguage($dbConnection,93), getLanguage($dbConnection,94).' <a href="'.$loginLink.'">log in</a>.');
          } else { $dispErrorMsg = 53; } // update query
        } else { $dispErrorMsg = 52; } // 1 result
      } else { $dispErrorMsg = 51; } // select query
    } else { $dispErrorMsg = 50; } // valid userid
  } elseif ($doSafe == 6) {  // print the normal startpage, do not forward to links.php
    printStartOfHtml($dbConnection);
    printTitle($dbConnection);
    printEntryPoint($dbConnection);
    printUserStat($dbConnection);
  } else {
    $dispErrorMsg = 1;
  } // switch
  printError($dbConnection, $dispErrorMsg);
  
  echo '</div> <!-- /container -->';  
  printFooter($dbConnection); 
?>     
  </div> <!-- /section categories -->
</body>
</html>