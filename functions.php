<?php
// This file is a pure function definition file. It is included in other sites

// function list:
// 20 - initialize ()
// 21 - printConfirm ($dbConn, $heading, $text)
// 22 - printErrorAndDie ($heading, $text)
// 23 - 
// 24 - error ($dbConn, $errorMsgNum)
// 25 - getCategory ($dbConn, $userid, $category)
// 26 - printStartOfHtml ($dbConn)
// 27 - printFooter ($dbConn)
// 28 - overlayDiv ($disappearing, $zIndex, $text)
// 29 - printOverlayGeneric ($dbConn, $messageNumber)    
// 30 - printOverlayAccountVerify ($dbConn, $userid)
// 31 - getCurrentSite ()
// 32 - printNavMenu ($dbConn)
// 33 - testUserCheck ($dbConn, $userid)
// 34 - sessionAndCookieDelete ()
// 35 - deleteUser ($dbConn, $userid)
// 36 - getUserid ()
// 37 - makeSafeInt ($unsafe, $length)
// 38 - makeSafeHex ($unsafe, $length)
// 39 - makeSafeStr ($unsafe, $length)
// 40 - redirectRelative ($page)
// 41 - printStatic ($dbConn)
// 42 - printInlineCss ()
// 43 - getLanguage ($dbConn, $textId)
// 44 - updateUser ($dbConn, $userid, $forgotPw)

  
// this function is called on every (user related) page on the very start  
// it does the session start and opens connection to the data base. Returns the dbConn variable
function initialize () {
  session_start(); // this code must precede any HTML output
  
  $siteSafe = getCurrentSite();   
  if ($siteSafe != 'about.php') { // on every other page than about, I need the userid already set
    if ($siteSafe != 'index.php') { // index is special, I might do forwarding when cookies are set
      if (!getUserid()) {
        // there might be two reasons: 
        // - user is connecting directly to links.php from where-ever (common case as you might store the links-page as bookmark). If so, just redirect to index.php
        // - session is really destroyed (e.g. user logged out). In this case, print an error message
        if ($siteSafe == 'links.php') { // redirect to index
          redirectRelative('index.php');
          return false;  // this code is not reached because redirect does an exit but it's anyhow cleaner like this
        }        
        printErrorAndDie('Login error', 'You might want to go to <a href="index.php">the start page</a>');
      }
    }
  }  
  require_once('php/dbConn.php'); // this will return the $dbConn variable as 'new mysqli'
  if ($dbConn->connect_error) {
    printErrorAndDie('Connection to the data base failed', 'Please try again later and/or send me an email: sali@widmedia.ch');
  }
  $dbConn->set_charset('utf8');
  return $dbConn;
}

//prints the h4 title and one row
function printConfirm ($dbConn, $heading, $text) {
  if (!headers_sent()) {
    printStartOfHtml($dbConn);
  } // headers
  echo '<div class="row twelve columns textBox"><h4>'.$heading.'</h4><p>'.$text.'</p></div>';
} 

// prints a valid html error page and stops php execution
function printErrorAndDie ($heading, $text) {
  // cannot use printStatic as I don't have a dbConn
  echo '
<!DOCTYPE html><html><head>
  <meta charset="utf-8">
  <title>Error page</title>
  <meta name="description" content="a generic error page">  
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/font.css" type="text/css">
  <link rel="stylesheet" href="css/normalize.css" type="text/css">
  <link rel="stylesheet" href="css/skeleton.css" type="text/css">';
  printInlineCss();
  echo '</head><body><div class="row twelve columns textBox"><h4>'.$heading.'</h4><p>'.$text.'</p></div></body></html>';
  die();  
}

// checks whether not the test user and displays some very generic failure message
function error ($dbConn, $errorMsgNum) {  
  if (getUserid() != 2) { // no error is printed for the test user    
    printConfirm($dbConn, 'Error', getLanguage($dbConn,33).$errorMsgNum.getLanguage($dbConn,34).' sali@widmedia.ch');
  }
}

// function returns the text of the category. If something does not work as expected, 0 is returned
function getCategory ($dbConn, $userid, $category) {
  if ($result = $dbConn->query('SELECT `text` FROM `categories` WHERE userid = "'.$userid.'" AND category = "'.$category.'" LIMIT 1')) {
    $row = $result->fetch_assoc();
    return $row['text'];
  } else { 
    return 0; // should never reach this point
  } // if 
} // function

// required for most use cases but for some I cannot print any HTML output before redirecting
function printStartOfHtml ($dbConn) {
  printStatic($dbConn);  
  
  $msgSafe = makeSafeInt($_GET['msg'], 1);
  if ($msgSafe > 0) {
    echo '<body onLoad="overlayMsgFade();">'; 
    printOverlayGeneric($dbConn, $msgSafe); 
  } else {
    echo '<body>';
  }
  printNavMenu($dbConn);
  $userid = getUserid();
  if ($userid == 2) { overlayDiv(false, 3, getLanguage($dbConn,105).' &nbsp;<a href="index.php?do=2#newUser" style="background-color:transparent; color:#000; text-decoration:underline;">'.getLanguage($dbConn,32).'</a>'); }  
  printOverlayAccountVerify($dbConn, $userid);  
  echo '<div class="section categories noBottom"><div class="container">';
}
 
// function does not return anything. Prints the footer at the end of a page. Output depends on the page we are at, given as input  
function printFooter ($dbConn) {
  echo '</div>'; // close the container
  $siteSafe = getCurrentSite(); 
  $edit   = '<a class="button differentColor" href="editLinks.php"><img src="images/icon_edit.png" class="logoImg"> '.getLanguage($dbConn,45).'</a>';
  $home   = '<a class="button differentColor" href="links.php"><img src="images/icon_home.png" class="logoImg"> Links</a>';
  $about  = '<a class="button differentColor" href="about.php"><img src="images/icon_info.png" class="logoImg"> '.getLanguage($dbConn,1).'</a>'; 
  $logout = '<a class="button differentColor" href="index.php?do=1"><img src="images/icon_logout.png" class="logoImg"> Log out</a>';
  
  // default values. For links.php as current site   
  $linkLeft   = $edit;
  $linkMiddle = $about;
  $linkRight  = $logout;
  if (($siteSafe == 'editLinks.php') or ($siteSafe == 'editUser.php')) {
    $linkLeft   = $home; 
    $linkMiddle = $about;
    $linkRight  = $logout;
  } elseif ($siteSafe == 'about.php') {
    $linkLeft   = '&nbsp;';
    $linkMiddle = $home;
    $linkRight  = '&nbsp;';
  }  elseif ($siteSafe == 'index.php') {
    $linkLeft   = $home;  // always have a home button. Even if I'm already on index page
    $linkMiddle = '&nbsp;';
    $linkRight  = $about;
  }

  echo '      
  <div class="section noBottom">
    <div class="container">
      <div class="row twelve columns"><hr /></div>
      <div class="row">
        <div class="four columns">'.$linkLeft.'</div>
        <div class="four columns">'.$linkMiddle.'</div>
        <div class="four columns">'.$linkRight.'</div>
      </div>
    </div>
  </div>
</div>
</body>
</html>'; 
} // function

// displays a red-colored div, either disappearing or not
function overlayDiv ($disappearing, $zIndex, $text) {
  $divId = '';
  if ($disappearing) { $divId = ' id="overlay" '; } // will disappear (using javascript) if the div gets an id
  echo '<div '.$divId.'class="overlayMessage" style="z-index: '.$zIndex.';">'.$text.'</div>';  
}

// prints some disappearing message box. used on links.php and index.php
function printOverlayGeneric ($dbConn, $messageNumber) {    
  if (($messageNumber >= 1) and ($messageNumber <= 7)) { 
    $message = getLanguage($dbConn,($messageNumber+18)); 
  } else { 
    $message = getLanguage($dbConn,26); 
  }  
  overlayDiv(true, 2, $message);  
}  

// prints a message when the email of this account has not been verified
function printOverlayAccountVerify ($dbConn, $userid) {
  if ($userid > 0) {
    $verified = false;
    if ($result = $dbConn->query('SELECT `verified` FROM `user` WHERE `id` = "'.$userid.'"')) {
      $row = $result->fetch_row();
      if ($row[0] == 1) {
        $verified = true;
      } // verified
    } // select query
    
    if (!$verified) { overlayDiv(false, 4, getLanguage($dbConn,104)); }
  }
} // function

// returns the current site in the format 'about.php' in a safe way. Any do=xy parameters are obmitted
function getCurrentSite () {
  $siteSafe = '';
  $siteUnsafe = substr($_SERVER['SCRIPT_NAME'],7); // SERVER[...] is something like /start/links.php (without any parameters)   
  if (
      ($siteUnsafe == 'about.php') or
      ($siteUnsafe == 'editLinks.php') or
      ($siteUnsafe == 'editUser.php') or
      ($siteUnsafe == 'index.php') or 
      ($siteUnsafe == 'link.php') or
      ($siteUnsafe == 'links.php')
     ) {
        $siteSafe = $siteUnsafe;
      }
  return ($siteSafe); 
}

function printNavMenu ($dbConn) {
  // set the session var only if I did get the ln-variable
  if (isset($_GET['ln'])) { // TODO: might take it from cookie and/or from data base
    $lang = 'de';  
    $langDiv = makeSafeStr($_GET['ln'], 2);
    if ($langDiv == 'en') { // otherwise it stays
      $lang = 'en';
    }
    $_SESSION['ln'] = $lang;
    // TODO: might store it in a cookie and/or into user data base
  }
  
  $siteSafe = getCurrentSite();
  $notLoggedIn = (getUserid() == 0);
  
  if ($siteSafe == 'index.php') { $home = '<li class="menuCurrentPage">Home</li>'; } else { $home = '<li><a href="index.php?do=6">Home</a></li>'; }
  if ($notLoggedIn) { $login = '<li><a href="index.php#login">- log in</a></li>'; } else { $login = ''; }
  if ($notLoggedIn) { $newAcc = '<li><a href="index.php?do=2#newUser">- '.getLanguage($dbConn,29).'</a></li>'; } else { $newAcc = ''; }
  if ($siteSafe == 'about.php') { $about = '<li class="menuCurrentPage">'.getLanguage($dbConn,1).'</li>'; }  else { $about = '<li><a href="about.php">'.getLanguage($dbConn,1).'</a></li>'; } 
  if ($siteSafe == 'links.php')     { $links      = '<li class="menuCurrentPage">Links</li>'; } else { $links = '<li><a href="links.php">Links</a></li>'; }
  if ($siteSafe == 'editLinks.php') { $editLinks  = '<li class="menuCurrentPage">- '.getLanguage($dbConn,27).'</li>'; } else { $editLinks = '<li><a href="editLinks.php">- '.getLanguage($dbConn,27).'</a></li>'; }
  if ($siteSafe == 'editUser.php')  { $editUser   = '<li class="menuCurrentPage">- '.getLanguage($dbConn,28).'</li>'; } else { $editUser = '<li><a href="editUser.php">- '.getLanguage($dbConn,28).'</a></li>'; }
  
  if ($notLoggedIn) { // remove the link, replace it with a strikethrough for those site where a login is a must
    $strikeThrough = ' style="text-decoration: line-through;"';
    $links     = '<li'.$strikeThrough.'>Links</li>';
    $editLinks = '<li'.$strikeThrough.'>- '.getLanguage($dbConn,27).'</li>';
    $editUser  = '<li'.$strikeThrough.'>- '.getLanguage($dbConn,28).'</li>';    
  } 
  if ($notLoggedIn) { $logOut = ''; } else { $logOut = '<li><a href="index.php?do=1">'.getLanguage($dbConn,106).'</a></li>'; }
  
  // TODO: design of the language selection
  if(isset($_GET['do'])) { // don't want to present the language sel on pages which are not default pages, where form entries are processed or similar
    $languageSelection = ''; 
  } else {
    $languageSelection = '<li>&nbsp;</li><li style="font-size:smaller;"><a href="'.$siteSafe.'?ln=de">DE</a>&nbsp;&nbsp;&nbsp;<a href="'.$siteSafe.'?ln=en">EN</a></li>';
  }
  
  echo '
  <nav role="navigation" style="width:400px">
    <div id="menuToggle">
      <input type="checkbox">
      <span></span>
      <span></span>
      <span></span>
      <ul id="menu">
        '.$home.'
        '.$login.'
        '.$newAcc.'
        '.$about.'
        '.$links.'
        '.$editLinks.'
        '.$editUser.'
        '.$logOut.'
        '.$languageSelection.'
      </ul>
    </div>
  </nav>';
}

// checks whether userid is 2 (= test user)
function testUserCheck ($dbConn, $userid) { // actually it is returning true, if it is not the testUser
  if ($userid == 2) {    
    printConfirm($dbConn, getLanguage($dbConn,30), getLanguage($dbConn,31).' <a href="index.php?do=2#newUser">'.getLanguage($dbConn,32).'</a>');
    return false;
  } else {
    return true;
  }
}

// deletes the userid cookie and the userid session. 
// NB: Leaves the ln-variables, otherwise I cannot print the 'logout-successful' message in non-default language
function sessionAndCookieDelete () {
  $_SESSION['userid'] = 0; // the most important one, make sure it's really 0
  setcookie('userIdCookie', 0, (time() - 42000)); // some big enough value in the past to make sure things like summer time changes do not affect it
}  

// does the db operations to remove a certain user. Does some checks as well
function deleteUser ($dbConn, $userid) {
  if ($userid > 0) { // have a valid userid
    if ($result = $dbConn->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"')) {
      // make sure this id actually exists and it's not id=1 (admin user) or id=2 (test user)
      $rowCnt = $result->num_rows;
      if (testUserCheck($dbConn, $userid) and ($userid != 1)) { // admin has userid 1, test user has userid 2
        if ($rowCnt == 1) {                
          $result_delLinks = $dbConn->query('DELETE FROM `links` WHERE `userid` = "'.$userid.'"');
          $result_delCategories = $dbConn->query('DELETE FROM `categories` WHERE `userid` = "'.$userid.'"');                  
          $result_delUser = $dbConn->query('DELETE FROM `user` WHERE `id` = "'.$userid.'"');
          
          if ($result_delLinks and $result_delCategories and $result_delUser) {
            return true;
          }
        }
      } // for userid = 1 there is no meaningful error message. But that's ok, it only affects the admin
    }
  }
  return false; // should not reach this point
}

// returns the userid integer
function getUserid () {
  if (isset($_SESSION)) {
    return $_SESSION['userid'];
  } else {
    return 0;  // rather return 0 (means userid is not valid) than false
  }
}

// returns a 'safe' integer. Return value is 0 if the checks did not work out
function makeSafeInt ($unsafe, int $length) : int {
  $safe = 0;
  $unsafe = filter_var(substr($unsafe, 0, $length), FILTER_SANITIZE_NUMBER_INT); // sanitize a length-limited variable  
  if (filter_var($unsafe, FILTER_VALIDATE_INT)) { 
    $safe = (int)$unsafe;
  }
  return $safe;
}

// returns a 'safe' character-as-hex value
function makeSafeHex ($unsafe, int $length) : string {
  $safe = '0';
  $unsafe = substr($unsafe, 0, $length); // length-limited variable  
  if (ctype_xdigit($unsafe)) {
    $safe = (string)$unsafe;
  }
  return $safe;
}

// returns a 'safe' string. Not that much to do though for a string
function makeSafeStr ($unsafe, int $length) {
  return (htmlentities(substr($unsafe, 0, $length))); // length-limited variable, HTML encoded
}

// does a (relative) redirect
function redirectRelative ($page) {
  // redirecting relative to current page NB: some clients require absolute paths
  $host  = $_SERVER['HTTP_HOST'];
  $uri   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');  
  header('Location: https://'.$host.htmlentities($uri).'/'.$page);
  exit;
}

// prints static header information and sets title and description depending on the page
function printStatic ($dbConn) {
  // description tag and title are different for every site  
  $siteSafe = getCurrentSite(); // NB: link.php is special as only in the error case a HTML site is generated
    
  if ($siteSafe == 'about.php') {
    $title = getLanguage($dbConn,1);
    $description = getLanguage($dbConn,107);    
  } elseif ($siteSafe == 'editLinks.php') {
    $title = getLanguage($dbConn,27);
    $description = getLanguage($dbConn,108);
  } elseif ($siteSafe == 'editUser.php') {
    $title = getLanguage($dbConn,28);
    $description = getLanguage($dbConn,109);    
  } elseif ($siteSafe == 'index.php') {  
    $title = 'Startpage';
    $description = getLanguage($dbConn,65);    
  } elseif ($siteSafe == 'links.php') {  
    $title = 'Links';
    $description = getLanguage($dbConn,110);    
  } else {
    $title = 'Error page';
    $description = 'page not found';    
  }
  
  $url = 'https://widmedia.ch/start/'.$siteSafe;
     
  echo '
<!DOCTYPE html>
<html lang="'.getLanguage($dbConn,111).'">
<head>
  <meta charset="utf-8">
  <title>'.$title.'</title>
  <meta property="og:title" content="'.$title.'" />
  <meta name="author" content="Daniel Widmer">
  <meta name="description" content="'.$description.'">  
  <link rel="canonical" href="'.$url.'" />  
  <meta name="robots" content="index, follow">    
  <meta property="og:description" content="'.$description.'" />
  <meta property="og:url" content="'.$url.'" />
  <meta property="og:type" content="website" />  
  <meta property="og:image" content="images/linkList900x600.jpg" />
  <meta property="og:image:type" content="image/jpeg" />
  <meta property="og:image:width" content="900" />
  <meta property="og:image:height" content="600" />
  <meta property="og:image" content="images/linkList800x800.jpg" />
  <meta property="og:image:type" content="image/jpeg" />
  <meta property="og:image:width" content="800" />
  <meta property="og:image:height" content="800" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon.png">    
  <link rel="stylesheet" href="css/font.css" type="text/css">
  <link rel="stylesheet" href="css/normalize.css" type="text/css">
  <link rel="stylesheet" href="css/skeleton.css" type="text/css">';
  printInlineCss();
  
  echo '
  <script type="text/javascript">
  // changes the display property of the pw-text field (actually the whole row) and some warning message
  function pwToggle() {
    if (document.getElementById("pwCheckBox").checked == 1) {
      document.getElementById("pwRow").style.display = "initial";
      document.getElementById("noPwWarning").style.display = "none";
    } else {
      document.getElementById("pwRow").style.display = "none";
      document.getElementById("noPwWarning").style.display = "block";
    }
  }

  // fades out a message and does a display: none when it is fully faded out
  function overlayMsgFade() {
    element = document.getElementById("overlay");
    var op = 0.8;  // initial opacity
    var timer = setInterval(function () {
      if (op <= 0.3){
          clearInterval(timer);
          element.style.display = "none";
      }
      element.style.opacity = op;
      element.style.filter = "alpha(opacity=" + op * 100 + ")";
      op -= op * 0.05;
    }, 200);
  }
  </script>
  </head>';    
}

// defines all the styles with color in it. NB: borders are defined with the 1px solid #color shortcut in the skeleton css. Color attribute is then overwritten here
function printInlineCss () {   
  $lightMain = 'rgba(250, 255, 59, 0.85)'; // yellowish (works good on blue, works on gray as well) = #faff3b;
  $darkMain =  'rgba(182, 189, 0, 0.85)'; // darker version of above settings  
  
  $font_link     = '#8d3a53'; // some red  
  $borders_lines = '#e1e1e1'; // whitish  
  
  $bg_norm  = 'rgba(0, 113, 255, 0.40)'; // blueish
  $bg_norm2 = 'rgba(0, 113, 255, 0.80)'; // same color, different transparency for navMenu
  $bg_diff  = 'rgba(255, 47, 25, 0.3)'; // reddish 
  $bg_diff2 = 'rgba(255, 47, 25, 0.6)'; // same color, different transparency for overlay and borders
  $bg_link  = 'rgba(180, 180, 180, 0.5)'; // grayish
  
  echo '
  <style>
    body { color: '.$lightMain.'; } 
    a { color: '.$font_link.'; background-color: '.$bg_link.';}
    a:hover { color: '.$lightMain.'; }
    .button,
    button,
    input[type="submit"],    
    input[type="button"] { color: '.$lightMain.'; background-color: '.$bg_norm.'; border-color: '.$darkMain.'; } 
    .button:hover,
    button:hover,
    input[type="submit"]:hover,    
    input[type="button"]:hover,
    .button:focus,
    button:focus,
    input[type="submit"]:focus,    
    input[type="button"]:focus { color: '.$darkMain.'; background-color: '.$bg_diff.'; border-color: '.$bg_diff2.'; }
    th,
    td { border-color: '.$borders_lines.'; }
    hr { border-color: '.$borders_lines.'; }
    .differentColor { color: '.$lightMain.'; background-color: '.$bg_diff.'; }
    .textBox { color: '.$lightMain.'; background-color: '.$bg_norm.'; border-color: '.$darkMain.'; }
    .noPwWarning { color: '.$lightMain.'; background-color: '.$bg_diff.'; }
    .overlayMessage { color: '.$lightMain.'; background-color: '.$bg_diff2.'; }
    .userStatBar { color: '.$lightMain.'; background-color: '.$bg_norm.'; border-color: '.$darkMain.'; }
    .imgBorder { border-color: '.$darkMain.'; }
    .tooltip .tooltiptext { color: '.$lightMain.'; background-color: '.$bg_norm.'; }
    #menu { background-color: '.$bg_norm2.'; border-color: '.$darkMain.'; }
    #menu a { color: '.$lightMain.'; }
    #menu a:hover, #menu a:focus { color: '.$darkMain.'; }
    .menuCurrentPage { color: '.$darkMain.'; }
    .bgCol { background-color: '.$bg_norm.'; }
  </style>'; 
}

// returns various text in the session-stored language. language-db organized as follows: id(int_11) / en(text) / de(text)
function getLanguage ($dbConn, $textId) { // NB: ln and id variables are safe
  $lang = 'de';
  if (isset($_SESSION['ln'])) {
    $lang = $_SESSION['ln'];
  }
  
  if ($result = $dbConn->query('SELECT `'.$lang.'` FROM `language` WHERE `id` = "'.$textId.'"')) {
    $row = $result->fetch_row();    
    return $row[0];
  } // no else case because can't do that much otherwise
}

// 44. used in editUser to update email and password and in index to set a new pw when it has been forgotten.
function updateUser ($dbConn, $userid, $forgotPw) {  
  if (testUserCheck($dbConn, $userid)) {
    if ($result = $dbConn->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"')) {              
      $row = $result->fetch_assoc(); // guaranteed to get only one row
      $pwCheck = false;
      if ($row['hasPw'] == 1) { // if there has been a hasPw, then I need to check whether the oldPw matches the stored one (without looking at hasPw-checkbox)
        $passwordUnsafe = filter_var(substr($_POST['password'], 0, 63), FILTER_SANITIZE_STRING);
        if (password_verify($passwordUnsafe, $row['pwHash'])) {        
          $pwCheck = true;
        } // else, $pwCheck stays at false
        if ($forgotPw) { $pwCheck = true; } // not verifying the old password
      } else { 
        $pwCheck = true; // not an error
        if ($forgotPw) { $pwCheck = false; } // an error
      }
      // could maybe merge some of this stuff with the functionality on index.php...addNewUser
      if ($pwCheck) {
        $hasPwCheckBox = makeSafeInt($_POST['hasPw'],1);
        if ($forgotPw) { $hasPwCheckBox = 1; }
        if ($hasPwCheckBox == 1) { // if hasPw-checkbox, the newPw must be at least 4 chars long
          $pwHash = 0;
          if (strlen($_POST['passwordNew']) > 3) {  
            $passwordUnsafe = filter_var(substr($_POST['passwordNew'], 0, 63), FILTER_SANITIZE_STRING);
            $pwHash = password_hash($passwordUnsafe, PASSWORD_DEFAULT);
          } else { error($dbConn, 104400); return false; }
        } // else, not an error
        
        $emailOk = false;
        $emailUnsafe = filter_var(substr($_POST['email'], 0, 127), FILTER_SANITIZE_EMAIL);
        // newEmail must not exist in the db (exclude current user itself)
        if (filter_var($emailUnsafe, FILTER_VALIDATE_EMAIL)) { // have a valid email 
          // check whether email already exists
          $emailSqlSafe = mysqli_real_escape_string($dbConn, $emailUnsafe);
          if (strcasecmp($emailSqlSafe, $row['email'])  != 0) { // 0 means they are equal
            if ($result = $dbConn->query('SELECT `verified` FROM `user` WHERE `email` LIKE "'.$emailSqlSafe.'" LIMIT 1')) {
              if ($result->num_rows == 0) {
                $emailOk = true; 
              }
            }
          } else { $emailOk = true; }; // no need to check again if the email did not change
        }
        
        if ($emailOk) {
          if ($result = $dbConn->query('UPDATE `user` SET `hasPw` = "'.$hasPwCheckBox.'", `pwHash` = "'.$pwHash.'", `email` = "'.$emailSqlSafe.'" WHERE `id` = "'.$userid.'"')) {            
            return true;
          } else { error($dbConn, 104401); return false; } // update query
        } else { 
          if ($forgotPw) { 
            if ($result = $dbConn->query('UPDATE `user` SET `pwHash` = "'.$pwHash.'" WHERE `id` = "'.$userid.'"')) {              
              return true;
            } else { error($dbConn, 104402); return false; } // update query
          } // forgotPW
        } // emailOK-else
      } else { error($dbConn, 104403); return false; } // pwCheck ok                
    } else { error($dbConn, 104404); return false; } // select query did work
  } // testUserCheck
}

// checks whether a get/post/cookie variable exists and makes it safe if it does. If not, returns 0
function safeIntFromExt (string $type, string $varName, int $length) : int {
  if ($type === 'GET') {
    return makeSafeInt($_GET[$varName], $length);
  } elseif ($type === 'POST') {
    return makeSafeInt($_POST[$varName], $length);
  } elseif ($type === 'COOKIE') {
    return makeSafeInt($_COOKIE[$varName], $length);
  } else {
    return 0;
  }
}





