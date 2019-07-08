<?php
// This file contains functions to be included in other blocks and rely heavily on the context around them
  
// this function is called on every (user related) page on the very start  
// it does the session start and opens connection to the data base. Returns the dbConnection variable
function initialize () {
  session_start(); // this code must precede any HTML output
  
  $currentSiteUnsafe = $_SERVER['SCRIPT_NAME']; // special treatment for index.php and main.php
  
  if ($currentSiteUnsafe != '/start/index.php') { // on every other page than index, I need the userid already set
    if (!getUserid()) {
      // there might be two reasons: 
      // - user is connecting directly to main.php from where-ever (common case as you might store the main-page as bookmark). If so, just redirect to index.php
      // - session is really destroyed (e.g. user logged out). In this case, print an error message
      if ($currentSiteUnsafe == '/start/main.php') { // redirect to index
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
  echo '<div id="overlay" class="overlayMessage" style="background-color: rgba(255, 47, 25, 0.8); z-index: 2;">'.$message.'</div>';
}  

// checks whether the number is bigger than 0 and displays some very generic failure message
function printError($errorMsgNum) {
  $userid = getUserid();
  if ($errorMsgNum > 0 and $userid != 2) {
    printConfirm('Error', '"Something" at step '.$errorMsgNum.' went wrong when processing user input data (very helpful error message, I know...). Might try again? <br/>If you think you did everything right, please send me an email: sali@widmedia.ch');
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
  $currentSiteUnsafe = $_SERVER['SCRIPT_NAME']; // returns something like /start/main.php (without any parameters)
  
  $edit   = '<a class="button differentColor" href="editLinks.php"><img src="images/icon_edit.png" class="logoImg"> edit</a>';
  $home   = '<a class="button differentColor" href="main.php"><img src="images/icon_home.png" class="logoImg"> home</a>';
  $about  = '<a class="button differentColor" href="about.php"><img src="images/icon_info.png" class="logoImg"> about</a>'; 
  $logout = '<a class="button differentColor" href="index.php?do=1"><img src="images/icon_logout.png" class="logoImg"> log out</a>';
  
  // default values. For main.php as current site   
  $linkLeft   = $edit;
  $linkMiddle = $about;
  $linkRight  = $logout;
  if (($currentSiteUnsafe == '/start/editLinks.php') or ($currentSiteUnsafe == '/start/editUser.php')) {
      $linkLeft   = $home; 
      $linkMiddle = $about;
      $linkRight  = $logout;
  } elseif ($currentSiteUnsafe == '/start/about.php') {
      $linkLeft   = $home;
      $linkMiddle = '&nbsp;';
      $linkRight  = $logout;
  }  elseif ($currentSiteUnsafe == '/start/index.php') {
      $linkLeft   = '&nbsp;';
      $linkMiddle = $about;
      $linkRight  = '&nbsp;';
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
  $currentSiteUnsafe = $_SERVER['SCRIPT_NAME']; // returns something like /start/main.php (without any parameters)
  
  // NB: link.php is special as only in the error case a html site is generated
  if ($currentSiteUnsafe == '/start/about.php') {
    $title   = 'About';
    $description = 'a modifiable page containing various links, intended to be used as a personal start page';
  } elseif ($currentSiteUnsafe == '/start/editLinks.php') {
    $title   = 'Edit my links';
    $description = 'page to add, edit or delete links';
  } elseif ($currentSiteUnsafe == '/start/editUser.php') {
    $title   = 'Edit or delete user account';
    $description = 'page to edit or delete the user account';
  } elseif ($currentSiteUnsafe == '/start/index.php') {  
    $title   = 'Startpage';
    $description = 'a modifiable page containing various links, intended to be used as a personal start page';
  } elseif ($currentSiteUnsafe == '/start/main.php') {  
    $title   = 'Startpage';
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


// defines all the styles with a color in it
function printInlineCss() { 
  // available colors
  // - font  
  $greenish = '#80b466';  // darker
  $greenish2 = 'rgba(171, 204, 20, 0.7)';
  
  // - background
  $blueish = 'rgba(0, 113, 255, 0.40)';
  $reddish = 'rgba(255, 47, 25, 0.3)';
  
  // - borders
  $whiteish = '#E1E1E1';
  
  echo '
  <style>
    body { color: rgba(171, 204, 20, 0.7); } 
    a { color: #8d3a53; background-color: rgba(180, 180, 180, 0.5); }
    .button,
    button,
    input[type="submit"],
    input[type="reset"],
    input[type="button"] { color: #80b466; background-color: rgba(0, 113, 255, 0.35); border-color: #80b466; }    
    .button.button-primary,
    button.button-primary,
    input[type="submit"].button-primary,
    input[type="reset"].button-primary,
    input[type="button"].button-primary { color: #ABCC14; background-color: rgba(0, 113, 255, 0.35); border-color: rgba(0, 113, 255, 0.8); }    
    th,
    td { border-color: #E1E1E1; }
    hr { border-color: #E1E1E1; }
    .differentColor { color: #ABCC14; background-color: rgba(255, 47, 25, 0.3); }
    .textBox { color: #80b466; background-color: rgba(0, 113, 255, 0.40); border-color: #80b466; }
    .noPwWarning { color: #ABCC14; background-color: rgba(255, 47, 25, 0.3); }
    .overlayMessage { color: #ABCC14; } 
  </style>'; 
}