<?php
// This file contains functions to be included in other blocks and rely heavily on the context around them
  
// this function is called on every (user related) page on the very start  
// it does the session start and opens connection to the data base. Returns the dbConnection variable
function initialize () {
  session_start(); // this code must precede any HTML output
  
  $siteUnsafe = getCurrentSite();   
  
  if ($siteUnsafe != 'index.php') { // on every other page than index, I need the userid already set
    if (!getUserid()) {
      // there might be two reasons: 
      // - user is connecting directly to main.php from where-ever (common case as you might store the main-page as bookmark). If so, just redirect to index.php
      // - session is really destroyed (e.g. user logged out). In this case, print an error message
      if ($siteUnsafe == 'main.php') { // redirect to index
        redirectRelative('index.php');
        return false;  // this code is not reached because redirect does an exit but it's anyhow cleaner like this
      }
      
      printStatic();
      echo '</head><body>';
      printConfirm('Login error', 'You might want to go to <a href="index.php">the start page</a>');
      echo '</body></html>';
      die();
    }
  }
  
  require_once('php/dbConnection.php'); // this will return the $dbConnection variable as 'new mysqli'
  if ($dbConnection->connect_error) { 
    die('Connection to the data base failed. Please try again later and/or send me an email: sali@widmedia.ch');
  }
  return $dbConnection;
}
  
//prints the h4 title and one row
function printConfirm($heading, $text) {
  echo '
  <div class="row twelve columns textBox">
    <h4>'.$heading.'</h4>
    <p>'.$text.'</p>
  </div>  
  ';
} 

// prints some disappearing message box. used on main.php and index.php
function printMessage ($messageNumber) {    
  if     ($messageNumber == 1) { $message = 'link has been updated'; } // ugly but still nicer than a switch statement with all the break commands
  elseif ($messageNumber == 2) { $message = 'category has been updated'; }
  elseif ($messageNumber == 3) { $message = 'link has been deleted'; }
  elseif ($messageNumber == 4) { $message = 'counters have been reset to 0'; }
  elseif ($messageNumber == 5) { $message = 'link has been added'; }
  elseif ($messageNumber == 6) { $message = 'user account has been updated'; }
  elseif ($messageNumber == 7) { $message = 'logout successful, cookie has been deleted as well'; }
  else                         { $message = 'updated'; }    
  echo '<div id="overlay" class="overlayMessage" style="z-index: 2;">'.$message.'</div>';
}  

// checks whether the number is bigger than 0 and displays some very generic failure message
function printError($errorMsgNum) {
  $userid = getUserid();
  if ($errorMsgNum > 0 and $userid != 2) {
    printConfirm('Error', '"Something" at step '.$errorMsgNum.' went wrong when processing user input data (very helpful error message, I know...). Might try again? <br>If you think you did everything right, please send me an email: sali@widmedia.ch');
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
  
// function does not return anything. Prints the footer at the end of a page. Output depends on the page we are at, given as input  
function printFooter() {  
  $siteUnsafe = getCurrentSite(); 
  $edit   = '<a class="button differentColor" href="editLinks.php"><img src="images/icon_edit.png" class="logoImg"> Edit</a>';
  $home   = '<a class="button differentColor" href="main.php"><img src="images/icon_home.png" class="logoImg"> Links</a>';
  $about  = '<a class="button differentColor" href="about.php"><img src="images/icon_info.png" class="logoImg"> About</a>'; 
  $logout = '<a class="button differentColor" href="index.php?do=1"><img src="images/icon_logout.png" class="logoImg"> Log out</a>';
  
  // default values. For main.php as current site   
  $linkLeft   = $edit;
  $linkMiddle = $about;
  $linkRight  = $logout;
  if (($siteUnsafe == 'editLinks.php') or ($siteUnsafe == 'editUser.php')) {
    $linkLeft   = $home; 
    $linkMiddle = $about;
    $linkRight  = $logout;
  } elseif ($siteUnsafe == 'about.php') {
    $linkLeft   = '&nbsp;';
    $linkMiddle = $home;
    $linkRight  = '&nbsp;';
  }  elseif ($siteUnsafe == 'index.php') {
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

// returns the current site in the format 'about.php'
function getCurrentSite() {
  return (substr($_SERVER['SCRIPT_NAME'],7)); // SERVER[...] is something like /start/main.php (without any parameters) 
}

function printNavMenu() {
  $siteUnsafe = getCurrentSite(); 
  $notLoggedIn = (getUserid() == 0);
  
  $home      = '<li><a href="index.php?do=6">Home</a></li>';  
  $about     = '<li><a href="about.php">About</a></li>';  
  $links     = '<li><a href="main.php">Links</a></li>';
  $editLinks = '<li><a href="editLinks.php">- edit links</a></li>';
  $editUser  = '<li><a href="editUser.php?do=1">- edit user account</a></li>';
  $logOut    = '<li><a href="index.php?do=1">log out</a></li>';
  
  if ($siteUnsafe == 'index.php')     { $home       = '<li class="menuCurrentPage">Home</li>'; }
  if ($siteUnsafe == 'about.php')     { $about      = '<li class="menuCurrentPage">About</li>'; }  
  if ($siteUnsafe == 'main.php')      { $links      = '<li class="menuCurrentPage">Links</li>'; }
  if ($siteUnsafe == 'editLinks.php') { $editLinks  = '<li class="menuCurrentPage">- edit links</li>'; }
  if ($siteUnsafe == 'editUser.php')  { $editUser   = '<li class="menuCurrentPage">- edit user account</li>'; }

  if ($notLoggedIn) { // user is not logged in
    $strikeThrough = ' style="text-decoration: line-through;"';

    $links     = '<li'.$strikeThrough.'>Links</li>';
    $editLinks = '<li'.$strikeThrough.'>- edit links</li>';
    $editUser  = '<li'.$strikeThrough.'>- edit user account</li>';
    $logOut    = '<li'.$strikeThrough.'>log out</li>';   
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
        '.$about.'
        '.$links.'
        '.$editLinks.'
        '.$editUser.'
        '.$logOut.'
      </ul>
    </div>
  </nav>';
} // function

  
// checks whether userid is 2 (= test user)
function testUserCheck($userid) {
  if ($userid == 2) {
    printConfirm('Testuser cannot be changed', 'I\'m sorry but when logged in as the testuser, you cannot change any settings. Might want to open your own account? <a href="index.php?do=2#newUser">open account</a><br><br>(btw: you may ignore the error message(s) below)');
    return false;
  } else {
    return true;
  }
}

// deletes both the cookie and the session 
function sessionAndCookieDelete () {
  $expire = time() - 42000; // some big enough value in the past to make sure things like summer time changes do not affect it  
  
  $_SESSION['userid'] = 0; // the most important one, make sure it's really 0 (before deleting everything)
  setcookie('userIdCookie', 0, $expire); 
  $_SESSION['randCookie'] = 0;
  setcookie('randCookie', 0, $expire);
  
  // now the more generic stuff
  $_SESSION = array(); // unset all of the session variables.
  session_destroy(); // finally, destroy the session    
}  

// does the db operations to remove a certain user. Does some checks as well
function deleteUser($dbConnection, $userid) {
  if ($userid > 0) { // have a valid userid
    if ($result = $dbConnection->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"')) {
      // make sure this id actually exists and it's not id=1 (admin user) or id=2 (test user)
      $rowCnt = $result->num_rows;
      if ($userid > 2) { // admin has userid 1, test user has userid 2
        if ($rowCnt == 1) {                
          $result_delLinks = $dbConnection->query('DELETE FROM `links` WHERE `userid` = "'.$userid.'"');
          $result_delCategories = $dbConnection->query('DELETE FROM `categories` WHERE `userid` = "'.$userid.'"');                  
          $result_delUser = $dbConnection->query('DELETE FROM `user` WHERE `id` = "'.$userid.'"');
          
          if ($result_delLinks and $result_delCategories and $result_delUser) {
            return true;
          }
        }
      } else { printConfirm('Forbidden', 'Sorry but the test user account and the admin account cannot be deleted'); }
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
// - randCookie is defined as 64-long hex value
function makeSafeHex($unsafe, $length) {
  $safe = 0;
  $unsafe = substr($unsafe, 0, $length); // length-limited variable  
  if (ctype_xdigit($unsafe)) {
    $safe = $unsafe;
  }
  return $safe;
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
  echo '<div class="row twelve columns"><hr /></div>';  
}

// prints static header information which is the same on all pages
function printStatic () {
  // description tag and title are different for every site  
  $siteUnsafe = getCurrentSite(); // NB: link.php is special as only in the error case a html site is generated
  if ($siteUnsafe == 'about.php') {
    $title   = 'About';
    $description = 'a modifiable page containing various links, intended to be used as a personal start page';
  } elseif ($siteUnsafe == 'editLinks.php') {
    $title   = 'Edit my links';
    $description = 'page to add, edit or delete links';
  } elseif ($siteUnsafe == 'editUser.php') {
    $title   = 'Edit or delete user account';
    $description = 'page to edit or delete the user account';
  } elseif ($siteUnsafe == 'index.php') {  
    $title   = 'Startpage';
    $description = 'a modifiable page containing various links, intended to be used as a personal start page';
  } elseif ($siteUnsafe == 'main.php') {  
    $title   = 'Links';
    $description = 'a modifiable page containing various links, intended to be used as a personal start page';
  } else {
    $title   = 'Error page';
    $description = 'page not found';    
  }

 
  echo '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>'.$title.'</title>
  <meta name="author" content="Daniel Widmer">
  <meta name="description" content="'.$description.'">
  <link rel="canonical" href="https://widmedia.ch/start" />    
  <meta name="robots" content="index, follow">    
  <meta name="content-language" content="en">
  <meta name="language" content="english, en">

  <!-- Mobile Specific Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- CSS -->
  <link rel="stylesheet" href="css/font.css" type="text/css">
  <link rel="stylesheet" href="css/normalize.css" type="text/css">
  <link rel="stylesheet" href="css/skeleton.css" type="text/css">';
  printInlineCss();
  echo '
  <!-- Favicon -->  
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon.png">
  ';
  // some sites include a js page as well before the header part is finished  
}


// defines all the styles with a color in it. NB: borders are defined with the 1px solid #color shortcut in the skeleton css. Color attribute is then overwritten here
function printInlineCss() { 
  // yellowish (works good on blue, works on gray as well): rgba(250, 255, 59, 0.85) = #faff3b;
  $lightMain = 'rgba(250, 255, 59, 0.85)';
  $darkMain =  'rgba(182, 189, 0, 0.85)'; // darker version of above settings  
  
  $font_link = '#8d3a53'; // some red
  
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
    #menu { background-color: '.$bg_norm2.'; border-color: '.$darkMain.'; }
    #menu a { color: '.$lightMain.'; }
    #menu a:hover, #menu a:focus { color: '.$darkMain.'; }
    .menuCurrentPage { color: '.$darkMain.'; }  
  </style>'; 
}