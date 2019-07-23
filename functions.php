<?php
// This file contains functions to be included in other blocks and rely heavily on the context around them
  
// this function is called on every (user related) page on the very start  
// it does the session start and opens connection to the data base. Returns the dbConnection variable
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
  require_once('php/dbConnection.php'); // this will return the $dbConnection variable as 'new mysqli'
  if ($dbConnection->connect_error) {
    printErrorAndDie('Connection to the data base failed', 'Please try again later and/or send me an email: sali@widmedia.ch');
  }
  $dbConnection->set_charset('utf8');
  return $dbConnection;
}

//prints the h4 title and one row. Assumes that the start of the html page is printed already
function printConfirm($heading, $text) {
  echo '
<div class="row twelve columns textBox">
  <h4>'.$heading.'</h4>
  <p>'.$text.'</p>
</div>';
} 

// prints a valid html error page and stops php execution
function printErrorAndDie($heading, $text) {
  // cannot use printStatic as I don't yet have a dbConnection
  echo '
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Error page</title>
  <meta name="description" content="a generic error page">  
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/font.css" type="text/css">
  <link rel="stylesheet" href="css/normalize.css" type="text/css">
  <link rel="stylesheet" href="css/skeleton.css" type="text/css">';
  printInlineCss();
  echo '</head><body>';
  printConfirm($heading, $text);
  echo '</body></html>';
  die();  
}

// prints some disappearing message box. used on links.php and index.php
function printMessage ($dbConnection, $messageNumber) {    
  if (($messageNumber >= 1) and ($messageNumber <= 7)) { 
    $message = getLanguage($dbConnection,($messageNumber+18)); 
  } else { 
    $message = getLanguage($dbConnection,26); 
  }    
  echo '<div id="overlay" class="overlayMessage" style="z-index: 2;">'.$message.'</div>';
}  

// checks whether the number is bigger than 0 and displays some very generic failure message
function printError($dbConnection, $errorMsgNum) {
  $userid = getUserid();
  if ($errorMsgNum > 0 and $userid != 2) { // no error is printed for the test user
    if (!headers_sent()) {
      printStatic($dbConnection);
      echo '</head><body>';
      printNavMenu($dbConnection);
      echo '<div class="section categories noBottom"><div class="container">';
    } // headers
    printConfirm('Error', getLanguage($dbConnection,33).$errorMsgNum.getLanguage($dbConnection,34).' sali@widmedia.ch');
  }
}

// function returns the text of the category. If something does not work as expected, 0 is returned
function getCategory($dbConnection, $userid, $category) {
  if ($result = $dbConnection->query('SELECT `text` FROM `categories` WHERE userid = "'.$userid.'" AND category = "'.$category.'" LIMIT 1')) {
    $row = $result->fetch_assoc();
    return $row['text'];
  } else { 
    return 0; // should never reach this point
  } // if 
} // function

// required for most use cases but for some I cannot print any HTML output before redirecting
function printStartOfHtml($dbConnection) {
  printStatic($dbConnection);
  echo '<script type="text/javascript" src="js/scripts.js"></script></head>';
  
  $msgSafe = makeSafeInt($_GET['msg'], 1);
  if ($msgSafe > 0) {
    echo '<body onLoad="overlayMsgFade();">'; 
    printMessage($dbConnection, $msgSafe); 
  } else {
    echo '<body>';
  }
  printNavMenu($dbConnection);
  echo '<div class="section categories noBottom"><div class="container">';
}

  
// function does not return anything. Prints the footer at the end of a page. Output depends on the page we are at, given as input  
function printFooter($dbConnection) {  
  $siteSafe = getCurrentSite(); 
  $edit   = '<a class="button differentColor" href="editLinks.php"><img src="images/icon_edit.png" class="logoImg"> '.getLanguage($dbConnection,45).'</a>';
  $home   = '<a class="button differentColor" href="links.php"><img src="images/icon_home.png" class="logoImg"> Links</a>';
  $about  = '<a class="button differentColor" href="about.php"><img src="images/icon_info.png" class="logoImg"> '.getLanguage($dbConnection,1).'</a>'; 
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
  </div>'; 
} // function

// returns the current site in the format 'about.php' in a safe way
function getCurrentSite() {
  $siteSafe = '';
  $siteUnsafe = substr($_SERVER['SCRIPT_NAME'],7); // SERVER[...] is something like /start/links.php (without any parameters) 
  
  if (($siteUnsafe == 'about.php') or
      ($siteUnsafe == 'editLinks.php') or
      ($siteUnsafe == 'editUser.php') or
      ($siteUnsafe == 'index.php') or 
      ($siteUnsafe == 'link.php') or
      ($siteUnsafe == 'links.php')) {
        $siteSafe = $siteUnsafe;
      }
  return ($siteSafe); 
}

function printNavMenu($dbConnection) {
  $siteSafe = getCurrentSite();
  $notLoggedIn = (getUserid() == 0);
  
  if ($siteSafe == 'index.php') { $home = '<li class="menuCurrentPage">Home</li>'; } else { $home = '<li><a href="index.php?do=6">Home</a></li>'; }
  if ($notLoggedIn) { $login = '<li><a href="index.php#login">- log in</a></li>'; } else { $login = ''; }
  if ($notLoggedIn) { $newAcc = '<li><a href="index.php?do=2#newUser">- '.getLanguage($dbConnection,29).'</a></li>'; } else { $newAcc = ''; }
  if ($siteSafe == 'about.php') { $about = '<li class="menuCurrentPage">'.getLanguage($dbConnection,1).'</li>'; }  else { $about = '<li><a href="about.php">'.getLanguage($dbConnection,1).'</a></li>'; } 
  if ($siteSafe == 'links.php')     { $links      = '<li class="menuCurrentPage">Links</li>'; } else { $links = '<li><a href="links.php">Links</a></li>'; }
  if ($siteSafe == 'editLinks.php') { $editLinks  = '<li class="menuCurrentPage">- '.getLanguage($dbConnection,27).'</li>'; } else { $editLinks = '<li><a href="editLinks.php">- '.getLanguage($dbConnection,27).'</a></li>'; }
  if ($siteSafe == 'editUser.php')  { $editUser   = '<li class="menuCurrentPage">- '.getLanguage($dbConnection,28).'</li>'; } else { $editUser = '<li><a href="editUser.php">- '.getLanguage($dbConnection,28).'</a></li>'; }
  
  if ($notLoggedIn) { // remove the link, replace it with a strikethrough for those site where a login is a must
    $strikeThrough = ' style="text-decoration: line-through;"';
    $links     = '<li'.$strikeThrough.'>Links</li>';
    $editLinks = '<li'.$strikeThrough.'>- '.getLanguage($dbConnection,27).'</li>';
    $editUser  = '<li'.$strikeThrough.'>- '.getLanguage($dbConnection,28).'</li>';    
  } 
  if ($notLoggedIn) { $logOut = ''; } else { $logOut = '<li><a href="index.php?do=1">'.getLanguage($dbConnection,106).'</a></li>'; }
  
  // TODO: design of the language selection
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
        <li>&nbsp;</li>
        <li style="font-size:smaller;"><a href="'.$siteSafe.'?ln=de">DE</a>&nbsp;&nbsp;&nbsp;<a href="'.$siteSafe.'?ln=en">EN</a></li>
      </ul>
    </div>
  </nav>';
  setLnSession();
} // function

  
// checks whether userid is 2 (= test user)
function testUserCheck($dbConnection, $userid) {
  if ($userid == 2) {
    if (!headers_sent()) {
      printStatic($dbConnection);
      echo '</head><body>';
      printNavMenu($dbConnection);
      echo '<div class="section categories noBottom"><div class="container">';
    } // headers
    printConfirm(getLanguage($dbConnection,30), getLanguage($dbConnection,31).' <a href="index.php?do=2#newUser">'.getLanguage($dbConnection,32).'</a>');
    return false;
  } else {
    return true;
  }
}

// deletes the userid cookie and the userid session. 
// NB: Leaves the ln-variables, otherwise I cannot print the 'logout-successful' message in German
function sessionAndCookieDelete () {
  $_SESSION['userid'] = 0; // the most important one, make sure it's really 0
  setcookie('userIdCookie', 0, (time() - 42000)); // some big enough value in the past to make sure things like summer time changes do not affect it
}  

// does the db operations to remove a certain user. Does some checks as well
function deleteUser($dbConnection, $userid) {
  if ($userid > 0) { // have a valid userid
    if ($result = $dbConnection->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"')) {
      // make sure this id actually exists and it's not id=1 (admin user) or id=2 (test user)
      $rowCnt = $result->num_rows;
      if (testUserCheck($dbConnection, $userid) and ($userid != 1)) { // admin has userid 1, test user has userid 2
        if ($rowCnt == 1) {                
          $result_delLinks = $dbConnection->query('DELETE FROM `links` WHERE `userid` = "'.$userid.'"');
          $result_delCategories = $dbConnection->query('DELETE FROM `categories` WHERE `userid` = "'.$userid.'"');                  
          $result_delUser = $dbConnection->query('DELETE FROM `user` WHERE `id` = "'.$userid.'"');
          
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
function makeSafeInt ($unsafe, $length) {
  $safe = 0;
  $unsafe = filter_var(substr($unsafe, 0, $length), FILTER_SANITIZE_NUMBER_INT); // sanitize a length-limited variable  
  if (filter_var($unsafe, FILTER_VALIDATE_INT)) { 
    $safe = $unsafe;
  }
  return $safe;
}

// returns a 'safe' character-as-hex value
function makeSafeHex($unsafe, $length) {
  $safe = 0;
  $unsafe = substr($unsafe, 0, $length); // length-limited variable  
  if (ctype_xdigit($unsafe)) {
    $safe = $unsafe;
  }
  return $safe;
}

// returns a 'safe' string. Not that much to do though for a string
function makeSafeStr($unsafe, $length) {
  return (htmlentities(substr($unsafe, 0, $length))); // length-limited variable, html encoded
}

// does a (relative) redirect
function redirectRelative ($page) {
  // redirecting relative to current page NB: some clients require absolute paths
  $host  = $_SERVER['HTTP_HOST'];
  $uri   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');  
  header('Location: https://'.$host.htmlentities($uri).'/'.$page);
  exit;
}

// prints a horizontal ruler over twelve columns
function printHr () {
  echo '<div class="row twelve columns"><hr></div>';  
}

// prints static header information which is the same on all pages
function printStatic($dbConnection) {
  // description tag and title are different for every site  
  $siteSafe = getCurrentSite(); // NB: link.php is special as only in the error case a html site is generated
  
  // TODO-language
  if ($siteSafe == 'about.php') {
    $title = getLanguage($dbConnection,1);
    $description = 'Some background info about the widmedia.ch/start project';    
  } elseif ($siteSafe == 'editLinks.php') {
    $title   = 'Edit my links';
    $description = 'page to add, edit or delete links';
  } elseif ($siteSafe == 'editUser.php') {
    $title   = 'Edit or delete your user account';
    $description = 'page to edit or delete the user account';    
  } elseif ($siteSafe == 'index.php') {  
    $title   = 'Startpage';
    $description = 'your new personal start page, a modifiable page with all your links';    
  } elseif ($siteSafe == 'links.php') {  
    $title   = 'Links';
    $description = 'the main page with all your links, your personal start page';    
  } else {
    $title   = 'Error page';
    $description = 'page not found';    
  }
  
  
  $url = 'https://widmedia.ch/start/'.$siteSafe;
  
     
  echo '
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>'.$title.'</title>
  <meta property="og:title" content="'.$title.'" />
  <meta name="author" content="Daniel Widmer">
  <meta name="description" content="'.$description.'">  
  <link rel="canonical" href="'.$url.'" />
  
  <meta name="robots" content="index, follow">    
  <meta name="content-language" content="en">
  <meta name="language" content="english, en"> 

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

  <!-- Mobile Specific Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon.png">
  
  <!-- CSS -->
  <link rel="stylesheet" href="css/font.css" type="text/css">
  <link rel="stylesheet" href="css/normalize.css" type="text/css">
  <link rel="stylesheet" href="css/skeleton.css" type="text/css">';
  printInlineCss();
  // some sites include a js page as well before the header part is finished  
}


// defines all the styles with color in it. NB: borders are defined with the 1px solid #color shortcut in the skeleton css. Color attribute is then overwritten here
function printInlineCss() {   
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
  </style>'; 
}

function setLnSession() {
  // set the session var only if I did get the ln-variable
  if (isset($_GET['ln'])) { // TODO: might take it from cookie and/or from data base
    $lang = 'en';
  
    $langDiv = makeSafeStr($_GET['ln'], 2);
    if ($langDiv == 'de') { // otherwise it stays at 'en'
      $lang = 'de';
    }
    $_SESSION['ln'] = $lang;
    // TODO: might store it in a cookie and/or into user data base
  }  
}

function getLanguage($dbConnection, $textId) {
  // db organized as follows: id(int_11) / en(text) / de(text)
  
  $lang = 'en';
  if (isset($_SESSION['ln'])) {
    $lang = $_SESSION['ln'];
  }
  
  if ($result = $dbConnection->query('SELECT `'.$lang.'` FROM `language` WHERE `id` = "'.$textId.'"')) {
    $row = $result->fetch_row();    
    return $row[0];
  } // no else case because can't do that much otherwise
}