<?php declare(strict_types=1);
  require_once('functions.php');
  $dbConn = initialize();  
  // this page has several entry points
  // a: unsecured
  // a1: first visit, direct visit (people typing widmedia.ch/start)
  // a2: log out function. do=1. linked from within the secure site, doing the logout
  // a3: add new user function. do=2 (process form: do=3). linked from the insecure site
  // a4: confirm email. do=5
  // b: secured
  // b1: link with testuserid (do=0)
  // b2: direct visit (do=0), cookie is set
  // b3: login form done, do=4, (email/pw as POST). setCookie is checked or not
  
  // page number for errorCode: 11
  // possible actions: 
  //  0/non-existing: normal case
  //  1 - logout
  //  2 - add new user
  //  3 - process adding new user
  //  4 - process login form (email/pw/setCookie)
  //  5 - do the email verification
  //  6 - print standard index, without forwarding to links.php
  //  7 - forgot password
  //  8 - process forgot password
  //  9 - process the pwRecovery link from the email
  // 10 - process the new password
  
  // function list:
  // 20 - function logOut ()
  // 21 - function verifyCredentials ($dbConn, $authMethod, $userid, $passwordUnsafe, $randCookieInput)
  // 22 - function newUserLinks ($dbConn, $newUserid)
  // 23 - function newUserLoginAndLinks ($dbConn, $newUserid, $pw)
  // 24 - function mail2userid ($dbConn, $emailSafe)
  // 25 - function newUserEmailConfirmation ($dbConn, $newUserid, $emailSqlSafe)
  // 26 - function printUserStat ($dbConn)
  // 27 - function printNewUserForm ($dbConn)
  // 28 - function printTitle($dbConn)
  // 29 - function printLogin($dbConn, $forgotPw)
  // 30 - function checkPwForgot($dbConn, $useridGetSafe, $verSqlSafe)

  

  // deletes both the cookie and the session
  function logOut (): void {
    sessionAndCookieDelete();
    redirectRelative('index.php?msg=7');
  }
 
  // function to do the login. Several options are available to log in
  function verifyCredentials (object $dbConn, int $authMethod, int $userid, $passwordUnsafe, $randCookieInput) : bool {
    $_SESSION['userid'] = 0; // clear it just to make sure    
    
    if (!($result = $dbConn->query('SELECT `pwHash`, `randCookie` FROM `user` WHERE `id` = "'.$userid.'"'))) {
      return error($dbConn, 112004);
    }
    if (!($result->num_rows == 1)) {
      return error($dbConn, 112003);
    }

    $row = $result->fetch_assoc();        
    $pwHash = $row['pwHash'];
    $randCookie = $row['randCookie'];

    if ($authMethod == 1) { // with a pw
      if (!(($userid == 2) or (password_verify($passwordUnsafe, $pwHash)))) {
        printConfirm($dbConn, 'Error',getLanguage($dbConn,137,'wrong password').'<a href="index.php#login">login</a>');
        return false;        
      } 
    } elseif ($authMethod == 2) { // with a Cookie
      if (!(($randCookie) and ($randCookie == $randCookieInput))) { // there is no zero in the data base and 64hex value is correct
        return error($dbConn, 112001);
      }
    } else { return false; } // should not happen, I myself set the authMethod
    
    if (!($dbConn->query('UPDATE `user` SET `lastLogin` = CURRENT_TIMESTAMP WHERE `id` = "'.$userid.'"'))) {
      return error($dbConn, 112005);
    }
    $_SESSION['userid'] = $userid;
    return true;    
  } // function
  
  
  // inserts some example values into `links` and `categories` tables
  function newUserLinks (object $dbConn, int $newUserid) : bool {
    $result0 = $dbConn->query('INSERT INTO `categories` (`userid`, `category`, `text`) VALUES ("'.$newUserid.'", "1", "News")');
    $result1 = $dbConn->query('INSERT INTO `categories` (`userid`, `category`, `text`) VALUES ("'.$newUserid.'", "2", "Work")');
    $result2 = $dbConn->query('INSERT INTO `categories` (`userid`, `category`, `text`) VALUES ("'.$newUserid.'", "3", "Div")');
    
    $result3 = $dbConn->query('INSERT INTO `links` (`userid`, `category`, `text`, `link`) VALUES ("'.$newUserid.'", "1", "NZZ", "https://nzz.ch")');
    $result4 = $dbConn->query('INSERT INTO `links` (`userid`, `category`, `text`, `link`) VALUES ("'.$newUserid.'", "1", "watson", "https://watson.ch")');    

    if ($result0 and $result1 and $result2 and $result3 and $result4) {
      return true;
    } else {
      return false;
    }    
  }
  
  // sets the value in the `user` table as well as the `links` table
  function newUserLoginAndLinks (object $dbConn, int $newUserid, string $pw) : bool {       
    // password_hash("testUserPassword", PASSWORD_DEFAULT) returns '$2y$10$3qc.gl4eDPpXDqM7hDssquu4ThnJ9rbH7wrEkdTZd0Cg0NAjAzm.2';
    $pwHash = password_hash($pw, PASSWORD_DEFAULT); // $pw is potentially unsafe. Shouldn't be an issue as I store the hash
    
    // NB: set a cookie for some random big number. Not the password itself and not the pwHash!
    // NB: will use this number on every cookie for this user, to login on several devices. One cannot guess other users random number                  
    $hexStr64 = bin2hex(random_bytes(32)); // some random value, used for cookie     
    if (!($dbConn->query('UPDATE `user` SET `pwHash` = "'.$pwHash.'", `randCookie` = "'.$hexStr64.'" WHERE `id` = "'.$newUserid.'"'))) { 
      return false;
    }
    return newUserLinks($dbConn, $newUserid); // Adding 1 user, 3 categories, 4 links    
  } 

  // returns the userid which matches to the email given. Returns 0 if something went wrong
  function mail2userid (object $dbConn, string $emailSafe) : int {    
    if (!($result = $dbConn->query('SELECT `id` FROM `user` WHERE `email` = "'.mysqli_real_escape_string($dbConn, $emailSafe).'"'))) {
      return 0;
    }
    if (!($result->num_rows == 1)) {
      return 0;
    }
    
    $row = $result->fetch_row();
    return (int)$row[0];            
  }  
  
  // sends an email to the new user with a special link and updates the database with that email confirmation link
  function newUserEmailConfirmation (object $dbConn, int $newUserid, string $emailSqlSafe) : bool {
    $hexStr64 = bin2hex(random_bytes(32)); // this is stored in the database    
    $emailBody = "Sali,\n\n".getLanguage($dbConn,95)."\n\n".getLanguage($dbConn,96)."\nhttps://widmedia.ch/start/index.php?do=5&userid=".$newUserid."&ver=".$hexStr64."\n\n";
    $emailBody = $emailBody.getLanguage($dbConn,97).'https://widmedia.ch/start/index.php#login '.getLanguage($dbConn,98)."\n";    
    $emailBody = $emailBody."\n\n".getLanguage($dbConn,101)."\nDaniel ".getLanguage($dbConn,102)." widmedia\n\n--\n".getLanguage($dbConn,5).": sali@widmedia.ch\n";
    
    if (!($dbConn->query('UPDATE `user` SET `verCode` = "'.$hexStr64.'" WHERE `id` = "'.$newUserid.'"'))) {
      return false;
    } // update query
    if (!(mail($emailSqlSafe, getLanguage($dbConn,103), $emailBody))) {
      return false;
    } // mail send
    
    return true;
  }
  
  // prints some graph with the user statistics 
  function printUserStat (object $dbConn): void {
    $currentTime = time();
    $year = 2019; // date('Y', $currentTime); // TODO: provide option to select another year
    
    $result = $dbConn->query('SELECT `month`, `numUser` FROM `userStat` WHERE `year` = "'.$year.'" ORDER BY `month`');
    
    $userStatPerMonth = array(0,0,0,0,0,0,0,0,0,0,0,0); // twelve zeros
    $months = array('Jan','Feb',getLanguage($dbConn,58),'Apr',getLanguage($dbConn,59),'Jun','Jul','Aug','Sep',getLanguage($dbConn,60),'Nov',getLanguage($dbConn,61));
    $maxVal = 0;
    while ($row = $result->fetch_assoc()) {
      $userStatPerMonth[($row['month']-1)] = $row['numUser']; // array index is from 0 to 11
      if ($row['numUser'] > $maxVal) { 
        $maxVal = $row['numUser'];
      } 
    } // while
    
    // print a table with the twelve months
    echo '
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns"><hr></div>
    <h3 class="section-heading"><span class="bgCol">'.getLanguage($dbConn,56).$year.'</span></h3>';
    $maxWidth = 300; // max width on mobile is about 300px, otherwise it messes up all the layout

    for ($i = 0; $i < 12; $i++) {
      $width = round($userStatPerMonth[$i] / $maxVal * $maxWidth)+1;
      if ($userStatPerMonth[$i] > 0) { // I started mid-year, omit the early 0-months and the future ones...
        echo '<div class="row twelve columns">
        <span class="userStatBar" style="width:'.$width.'px;"><b>'.$months[$i].':</b> '.$userStatPerMonth[$i].' User</span></div>';
      }
    }
    echo '<div class="row twelve columns"><span class="bgCol">'.getLanguage($dbConn,57).'</span></div>';
  }
  
  // there is a similar function (printUserEdit) in edit.php. However, differs too heavy to merge those two  
  function printNewUserForm (object $dbConn): void {
    echo '<h3 class="section-heading"><span id="newUser" class="bgCol">'.getLanguage($dbConn,32).'</span></h3>
    <form action="index.php?do=3" method="post">
    <div class="row">
      <div class="three columns"><span class="bgCol">'.getLanguage($dbConn,62).':</span> </div>
      <div class="nine columns"><input name="email" type="email" maxlength="127" value="" required size="20"></div>
    </div>    
    <div class="row" id="pwRow">
      <div class="three columns"><span class="bgCol">'.getLanguage($dbConn,63).':</span> </div>
      <div class="nine columns"><input name="password" type="password" maxlength="63" value="" required size="20"></div>
    </div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns"><input name="create" type="submit" value="'.getLanguage($dbConn,64).'"></div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns">&nbsp;</div>
    </form>
    ';   
  } // function
  
  function printTitle(object $dbConn): void {    
    echo '<h2 class="section-heading"><span class="bgCol">widmedia.ch/start</span></h2>
    <div class="row twelve columns" style="font-size: larger; text-align: left"><span class="bgCol">'.getLanguage($dbConn,65).'</span></div>';
    echo '<div class="row twelve columns"><hr></div>';
    echo '
    <div class="row">
      <div class="eight columns">
        <div class="slideshow-container">
          <div class="mySlides fade u-max-full-width"><img src="images/teaser/01.webp" alt="your startpage with all the links" class="imgBorder" style="width:100%; vertical-align:middle;"><div class="captionText">'.getLanguage($dbConn,74).'</div></div>
          <div class="mySlides fade u-max-full-width"><img src="images/teaser/02.webp" alt="click your external link" class="imgBorder" style="width:100%; vertical-align:middle;"><div class="captionText">'.getLanguage($dbConn,75).'</div></div>
          <div class="mySlides fade u-max-full-width"><img src="images/teaser/03.webp" alt="the link opens in a new tab" class="imgBorder" style="width:100%; vertical-align:middle;"><div class="captionText">'.getLanguage($dbConn,76).'</div></div>
          <div class="mySlides fade u-max-full-width"><img src="images/teaser/04.webp" alt="edit your links, add a new one" class="imgBorder" style="width:100%; vertical-align:middle;"><div class="captionText">'.getLanguage($dbConn,77).'</div></div>
          <div class="mySlides fade u-max-full-width"><img src="images/teaser/05.webp" alt="there it is, your new link" class="imgBorder" style="width:100%; vertical-align:middle;"><div class="captionText">'.getLanguage($dbConn,78).'</div></div>
          <div class="mySlides fade u-max-full-width"><img src="images/teaser/06.webp" alt="your new start page" class="imgBorder" style="width:100%; vertical-align:middle;"><div class="captionText">'.getLanguage($dbConn,79).'</div></div>
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
        <li>'.getLanguage($dbConn,66).'</li>
        <li>'.getLanguage($dbConn,67).'</li>
        <li>'.getLanguage($dbConn,68).'</li>
        <li>'.getLanguage($dbConn,69).'</li>
        <li>'.getLanguage($dbConn,70).'</li>
        <li>'.getLanguage($dbConn,71).': <a href="index.php?userid=2">'.getLanguage($dbConn,72).'</a> ... '.getLanguage($dbConn,73).' <a href="index.php?do=2#newUser">'.getLanguage($dbConn,64).'</a></li>        
      </ul></div>
    </div>
    <div class="row twelve columns"><hr></div>
    <div class="row twelve columns">'.getLanguage($dbConn,80).' <img src="images/icon/arrow_right.png" alt="pointing to the open free account form" class="logoImg"> <a href="index.php?do=2#newUser" class="button"><img src="images/icon/plus.png" alt="open your own free account" class="logoImg"> '.getLanguage($dbConn,81).'</a></div>
    <div class="row twelve columns"><hr></div>
    <div class="row twelve columns">'.getLanguage($dbConn,82).' <img src="images/icon/arrow_right.png" alt="pointing to the test user login" class="logoImg"> <a href="index.php?userid=2" class="button">'.getLanguage($dbConn,83).'</a></div>
    <div class="row twelve columns"><hr></div>';
  } // function
  
  function printLogin(object $dbConn, bool $forgotPw): void {
    if ($forgotPw) {
      $title = getLanguage($dbConn,87);
      $doAction = '8';
    } else {
      $title = 'Log in';
      $doAction = '4';
    }
    
    echo '
    <h3 class="section-heading"><span id="login" class="bgCol">'.$title.'</span></h3>
    <form action="index.php?do='.$doAction.'" method="post">
    <div class="row">
      <div class="three columns"><span class="bgCol">Email:</span> </div>
      <div class="nine columns"><input name="email" type="email" maxlength="127" value="" required size="20"></div>
    </div>';
    
    if ($forgotPw) {      
      echo '<div class="row twelve columns"><input name="login" type="submit" value="'.getLanguage($dbConn,112).'"></div></form>';
    } else {
    echo '
    <div class="row">
      <div class="three columns"><span class="bgCol">'.getLanguage($dbConn,84).':</span> </div>
      <div class="nine columns"><input name="password" type="password" maxlength="63" value="" required size="20"></div>
    </div>
    <div class="row twelve columns" style="font-size: smaller;"><input type="checkbox" name="setCookie" value="1" checked><span class="bgCol">'.getLanguage($dbConn,85).'</span></div>
    <div class="row twelve columns"><input name="login" type="submit" value="log in"></div>
    </form>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row">
      <div class="six columns"><a href="index.php?do=2#newUser" class="button"><img src="images/icon/plus.png" alt="open your own account" class="logoImg"> '.getLanguage($dbConn,81).'</a></div>
      <div class="six columns"><a href="index.php?do=7#login" class="button"><img src="images/icon/question.png" alt="get an email with your new password" class="logoImg"> '.getLanguage($dbConn,87).'</a></div>
    </div>';        
    }
    
  } // function 

  // checks whether there is (at least) one entry in the data base and it's not yet expired
  function checkPwForgot(object $dbConn, int $useridGetSafe, $verSqlSafe) : bool {
    if (!($result = $dbConn->query('SELECT `validUntil` FROM `pwForgot` WHERE `userid` = "'.$useridGetSafe.'" AND `hexval` = "'.$verSqlSafe.'" ORDER BY `id` DESC'))) {
      return false;
    }
    // there might be more than one because user might have pressed the send email button several times
    if ($result->num_rows == 0) { 
      return false; 
    }
    $row = $result->fetch_row(); // interested only in the last one, so no for loop
    $validUntil = $row[0];
    if (time() > (strtotime($validUntil))) { 
      return false; 
    }
    
    return true;
  }
  
  // doing the db operations to add a new user into user-table
  function addNewUser(object $dbConn, string $emailUnsafe, string $emailSqlSafe, string $passwordUnsafe): bool {
    if (!(filter_var($emailUnsafe, FILTER_VALIDATE_EMAIL))) { // have a valid email 
      return error($dbConn, 110306);
    }
    // check whether email already exists
    if (!(($result = $dbConn->query('SELECT `verified` FROM `user` WHERE `email` LIKE "'.$emailSqlSafe.'" LIMIT 1')) and ($result->num_rows == 0))) {
      printConfirm($dbConn, 'Error',getLanguage($dbConn,92));
      return error($dbConn, 110304);
    }
    if (strlen($passwordUnsafe) <= 3) {
      printConfirm($dbConn, 'Error',getLanguage($dbConn,91)); 
      return error($dbConn, 110303); 
    }
    if (!($result = $dbConn->query('INSERT INTO `user` (`email`, `lastLogin`) VALUES ("'.$emailSqlSafe.'", CURRENT_TIMESTAMP)'))) { 
      return error($dbConn, 110302);
    }
    $newUserid = (int)$dbConn->insert_id;
    if (!(newUserLoginAndLinks($dbConn, $newUserid, $passwordUnsafe))) {
      return error($dbConn, 110301);
    }
    if(!(newUserEmailConfirmation($dbConn, $newUserid, $emailSqlSafe))) {
      return error($dbConn, 110300);
    }
    $loginText = getLanguage($dbConn,88).' <a href="index.php#login">https://widmedia.ch/start/index.php#login</a>';
    printConfirm($dbConn, getLanguage($dbConn,89), getLanguage($dbConn,90).$loginText.'<br><br>'.getLanguage($dbConn,86).'<span id="accountCreateOkSpan">&nbsp;</span>');
    return true;
  }
  
  function processLoginData(object $dbConn, string $emailUnsafe, string $passwordUnsafe, int $setCookieSafe): bool {
    if (!(filter_var($emailUnsafe, FILTER_VALIDATE_EMAIL))) { // have a valid email
      return error($dbConn, 110403);
    }
    $userid = mail2userid($dbConn, $emailUnsafe);
    if (!($userid > 0) ) { // email found in db
      printConfirm($dbConn, 'Error',getLanguage($dbConn,137,'wrong password').'<a href="index.php#login">login</a>');
      return false; 
    }
    if (!(verifyCredentials($dbConn, 1, $userid, $passwordUnsafe, ''))) { // verification ok
      return false; // This already prints an error message
    }
    if ($setCookieSafe == 1) {
      $expire = time() + (3600 * 24 * 7 * 26); // valid for half a year
      setcookie('userIdCookie', (string)$userid, $expire, '/start/', 'widmedia.ch', true, true); 
      if (!($result = $dbConn->query('SELECT `randCookie` FROM `user` WHERE `id` = "'.$userid.'"' ))) { // this is just a random number which has been set at user creation
        return error($dbConn, 110400); 
      }
      $row = $result->fetch_row();
      setcookie('randCookie', $row[0], $expire, '/start/', 'widmedia.ch', true, true);      
    } // setCookie is selected
    redirectRelative('links.php');        
    return true;    
  }
  
  function confirmEmailAddr(object $dbConn, int $useridGetSafe, string $verSqlSafe): bool {
    if (!(($useridGetSafe > 2) and ($result = $dbConn->query('SELECT `verified` FROM `user` WHERE `id` = "'.$useridGetSafe.'" AND `verCode` = "'.$verSqlSafe.'"')))) {
      return error($dbConn, 110503);
    }
    // NB: I'm not even looking at the date (mentioned a 24 hour limit in the email). That's fine. I'll just delete accounts which have not been verified after some days...      
    if (!($result->num_rows == 1)) {
      return error($dbConn, 110501);
    }
    $row = $result->fetch_row();        
    if (!($dbConn->query('UPDATE `user` SET `verified` = "1" WHERE `id` = "'.$useridGetSafe.'"'))) {
      return error($dbConn, 110500);
    }
    $loginLink = 'https://widmedia.ch/start/index.php#login';
    printConfirm($dbConn, getLanguage($dbConn,93), getLanguage($dbConn,94).' <a href="'.$loginLink.'">log in</a>.');
    return true;
  }
  
  function processPwForgot(object $dbConn, string $emailUnsafe): bool {
    $pwForgotUserid = mail2userid($dbConn, $emailUnsafe); // email exists    
    if (!($pwForgotUserid > 0)) { // pwForgot-db stores a completely unrelated hexval which is valid for 1 hour. DB-layout: id / userid / hexval / validUntil
      return error($dbConn, 110802); 
    }  
    $hexStr64 = bin2hex(random_bytes(32)); // this is stored in the database
    $validUntil = date('Y-m-d H:i:s', time() + 3600);      
    if (!($dbConn->query('INSERT INTO `pwForgot` (`userid`, `hexval`, `validUntil`) VALUES ("'.$pwForgotUserid.'", "'.$hexStr64.'", "'.$validUntil.'")'))) {
      return error($dbConn, 110801); 
    }
    $emailBody = "Sali,\n\n".getLanguage($dbConn,113).":\nhttps://widmedia.ch/start/index.php?do=9&userid=".$pwForgotUserid."&ver=".$hexStr64."\n";
    $emailBody = $emailBody."\n\n".getLanguage($dbConn,114).",\nDaniel ".getLanguage($dbConn,102)." widmedia\n\n--\n".getLanguage($dbConn,3).": sali@widmedia.ch\n";
    if (!(mail($emailUnsafe, getLanguage($dbConn,115).' widmedia.ch/start', $emailBody))) {
      return error($dbConn, 110800);
    }  
    printStartOfHtml($dbConn);    
    printConfirm($dbConn, 'Email '.getLanguage($dbConn,116), getLanguage($dbConn,117).htmlentities($emailUnsafe).').<br>'.getLanguage($dbConn,118).'<br><br><a href="index.php?do=6">'.getLanguage($dbConn,119).'</a>');
    return true;
  }
  
  function processNewPw(object $dbConn, int $useridPostSafe, string $verPost): bool {    
    if (!(checkPwForgot($dbConn, $useridPostSafe, mysqli_real_escape_string($dbConn, $verPost)))) { // check whether this account is really in the pwRecovery data base
      return error($dbConn, 111002);
    }
    if (!(updateUser($dbConn, $useridPostSafe, true) and ($dbConn->query('DELETE FROM `pwForgot` WHERE `userid` = "'.$useridPostSafe.'"')))) {
      return error($dbConn, 111001); 
    }
    printStartOfHtml($dbConn);
    printConfirm($dbConn, getLanguage($dbConn,24), getLanguage($dbConn,121).': <a href="index.php#login">index.php#login</a>');      
    return true;    
  }
  
  function processPwRecLink(object $dbConn, int $useridGetSafe, string $verSqlSafe, string $verGet): bool {
    if (!(checkPwForgot($dbConn, $useridGetSafe, $verSqlSafe))) {
      printConfirm($dbConn, 'Error', 'Recovery link expired');
      return error($dbConn, 110900);
    }    
    printStartOfHtml($dbConn);  
    echo '
    <h3 class="section-heading"><span class="bgCol">'.getLanguage($dbConn,120).'</span></h3>
    <form action="index.php?do=10" method="post">
    <div class="row" id="pwRow">
      <div class="three columns"><span class="bgCol">'.getLanguage($dbConn,50).':</span> </div>
      <div class="nine columns"><input name="passwordNew" type="password" maxlength="63" value="" size="20"></div>
    </div>
    <div class="row twelve columns">&nbsp;<input name="userid" type="hidden" value="'.$useridGetSafe.'"><input name="ver" type="hidden" value="'.$verGet.'"></div>
    <div class="row twelve columns"><input name="create" type="submit" value="'.getLanguage($dbConn,39).'"></div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns">&nbsp;</div>
    </form>';   
    return true;
  }
  
  // -------------------------------------------------------------- //
  // normal page code starting here
  $doSafe           = safeIntFromExt('GET', 'do', 2); // need two digits here
  $useridGetSafe    = safeIntFromExt('GET', 'userid', 11);
  $useridCookieSafe = safeIntFromExt('COOKIE', 'userIdCookie', 11);
  $randCookieSafe   = safeHexFromExt('COOKIE', 'randCookie', 64); 

  if ($doSafe == 0) { // the $_GET-do parameter has higher priority than the rest
    if ($useridGetSafe == 2) { // login like index.php?userid=2 the $_GET-userid has higher priority than the cookie userid
      if (verifyCredentials($dbConn, 1, $useridGetSafe, '', '')) {
        redirectRelative('links.php');
      }
    } elseif (($useridCookieSafe > 0) and (verifyCredentials($dbConn, 2, $useridCookieSafe, '', $randCookieSafe))){
      redirectRelative('links.php');      
    }
  }    
  
  $emailUnsafe = filter_var(safeStrFromExt('POST', 'email', 127), FILTER_SANITIZE_EMAIL);    // email string, max length 127
  $emailSqlSafe = mysqli_real_escape_string($dbConn, $emailUnsafe);  
  $passwordUnsafe = filter_var(safeStrFromExt('POST', 'password', 63), FILTER_SANITIZE_STRING); // generic string, max length 63    
  $setCookieSafe = safeIntFromExt('POST', 'setCookie', 1);  
  $verGet = safeHexFromExt('GET', 'ver', 64);
  $verSqlSafe = mysqli_real_escape_string($dbConn, $verGet);  
  
  if ($doSafe == 0) { // valid use case. Entry point of this site
    printStartOfHtml($dbConn);
    printTitle($dbConn);
    printLogin($dbConn, false);
    printUserStat($dbConn);
  } elseif ($doSafe == 1) { // log out
    $userid = getUserid();
    if (($userid > 0) and ($result = $dbConn->query('SELECT `style` FROM `user` WHERE `id` = "'.$userid.'"'))) {
      $row = $result->fetch_row();
      $style = $row[0]; // safe the current style and store it into the session var, so the user still sees his style
      $_SESSION['style'] = $style;
    }
    logOut();
  } elseif ($doSafe == 2) { // present the new user form
    printStartOfHtml($dbConn);
    printTitle($dbConn);
    printNewUserForm($dbConn);
    printUserStat($dbConn);
  } elseif ($doSafe == 3) { // process the new user form data, add a new user
    printStartOfHtml($dbConn);    
    addNewUser($dbConn, $emailUnsafe, $emailSqlSafe, $passwordUnsafe); 
  } elseif ($doSafe == 4) { // process the login data, maybe set a cookie
    processLoginData($dbConn, $emailUnsafe, $passwordUnsafe, $setCookieSafe);    
  } elseif ($doSafe == 5) { // confirm the email address
    printStartOfHtml($dbConn);
    confirmEmailAddr($dbConn, $useridGetSafe, $verSqlSafe);    
  } elseif ($doSafe == 6) {  // print the normal startpage, do not forward to links.php
    printStartOfHtml($dbConn);
    printTitle($dbConn);
    printLogin($dbConn, false);
    printUserStat($dbConn);
  } elseif ($doSafe == 7) {  // forgot pw, present a form with the email field and a forgot PW title
    printStartOfHtml($dbConn);    
    printLogin($dbConn, true);
  } elseif ($doSafe == 8) {  // process the data from step 7 
    processPwForgot($dbConn, $emailUnsafe);
  } elseif ($doSafe == 9) {  // process the pwRecovery link from the email
    processPwRecLink($dbConn, $useridGetSafe, $verSqlSafe, $verGet);
  } elseif ($doSafe == 10) {  // process the newly set password
    $useridPostSafe = safeIntFromExt('POST', 'userid', 11);
    $verPost = safeHexFromExt('POST', 'ver', 64);    
    processNewPw($dbConn, $useridPostSafe, $verPost);
  } else {
    error($dbConn, 110000);
  } // switch  
  printFooter($dbConn); 
?>
