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
      
      die('login error. You might want to go to <a href="index.php">start page</a>'); // maybe to do: some more sophisticated real error handling
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
  if ($errorMsgNum > 0) {
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
  
  $edit   = '<a class="button differentColor" href="editLinks.php"><img src="images/edit_green.png" class="logoImg"> edit</a>';
  $home   = '<a class="button differentColor" href="main.php"><img src="images/home_green.png" class="logoImg"> home</a>';
  $about  = '<a class="button differentColor" href="about.php"><img src="images/info_green.png" class="logoImg"> about</a>'; 
  $logout = '<a class="button differentColor" href="index.php?do=1"><img src="images/logout_green.png" class="logoImg"> log out</a>';
  
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
    printConfirm('Testuser cannot be changed', 'I\'m sorry but when logged in as the testuser, you cannot change any settings. Might want to open your own account? <a href="index.php?do=2">open account</a><br><br>(btw: you may ignore the error message below)');
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