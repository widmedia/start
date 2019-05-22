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
        return(false);  // this code is not reached because redirect does an exit but it's anyhow cleaner like this
      }
      
      die('login error. You might want to go to <a href="index.php">start page</a>'); // maybe to do: some more sophisticated real error handling
    }
  }
  
  require_once('php/dbConnection.php'); // this will return the $dbConnection variable as 'new mysqli'
  if ($dbConnection->connect_error) { 
    die('Connection to the data base failed. Please try again later and/or send me an email: sali@widmedia.ch');
  }
  return($dbConnection);
}
  
// function to output several links in a formatted way
// creating a div for every link and div-rows for every $module-th entry
// has a limit of 100 links per category
function printLinks($edit, $userid, $category, $dbConnection) {
  $sql = 'SELECT * FROM `links` WHERE userid = "'.$userid.'" AND category = "'.$category.'" ORDER BY `cntTot` DESC, `text` ASC LIMIT 100';
    
  // Have 12 columns. Means with modulo 3, I have 'class four columns' and vice versa
  $modulo = 3;
  $divClass = '<div class="four columns linktext">';
  if ($category == 2) { // this category prints more dense
    $modulo = 4;
    $divClass = '<div class="three columns linktext">';      
  }
   
  if ($result = $dbConnection->query($sql)) {
    $counter = 0;        
    while ($row = $result->fetch_assoc()) {
      if ($edit) {
        echo $divClass.'<span class="editLeft"><a href="link.php?id='.$row['id'].'" target="_blank" class="button button-primary">'.$row['text'].'</a><span class="counter">'.$row['cntTot'].'</span></span>
           <span class="editRight">
             <div style="padding-bottom: 2px;"><a href="editLinks.php?id='.$row['id'].'&do=4"><img src="images/edit.png"   width="16" height="16" border="0"> edit</a></div>                   
             <div style="padding-top:    2px;"><a href="editLinks.php?id='.$row['id'].'&do=5"><img src="images/delete.png" width="16" height="16" border="0"> delete</a></div>
           </span></div>';          
      } else {
        echo $divClass.'<a href="link.php?id='.$row["id"].'" target="_blank" class="button button-primary">'.$row['text'].'</a><span class="counter">'.$row['cntTot'].'</span></div>';
      } // edit links?
      $counter++;

      if (($counter % $modulo) == 0) {
        echo '</div>'."\n".'<div class="row">';
      }
    } // while    
    $result->close(); // free result set
  } // if  
} // function 

//prints the h3 title and one row
function printConfirmation($heading, $text, $leftSize, $rightSize) {
  echo '
  <h3 class="section-heading">'.$heading.'</h3>
  <div class="row">
    <div class="'.$leftSize.' columns linktext">'.$text.'</div>
    <div class="'.$rightSize.' columns linktext">&nbsp;</div>
  </div>';                           
} 
 
// function returns the text of the category. If something does not work as expected, NULL is returned
function getCategory($userid, $category, $dbConnection) {
  // Data base is organized as follows:
  // SELECT * FROM `titels`
  // id	userid	category	text
  // 1 	1 	    1 	      News
  // 2 	1 	    2 	      Work
  // 3 	1 	    3 	      Div
  $sqlString = 'SELECT * FROM `titels` WHERE userid = "'.$userid.'" AND category = "'.$category.'" LIMIT 1';
  if ($result = $dbConnection->query($sqlString)) {
    $row = $result->fetch_assoc();
    $result->close(); // free result set
    
    return ($row['text']);      
  } // if  
} // function
  
// function does not return anything. Prints the footer at the end of a page. Output depends on the page we are at, given as input  
function printFooter() {
  $currentSiteUnsafe = $_SERVER['SCRIPT_NAME']; // returns something like /start/main.php (without any parameters)
  
  $editString  = 'href="editLinks.php"><img src="images/edit_green.png" class="logoImg"> edit';
  $homeString  = 'href="main.php"><img src="images/home_green.png" class="logoImg"> home';
  $aboutString = 'href="about.php"><img src="images/info_green.png" class="logoImg"> about'; 
  $linkRight   = 'href="index.php?do=1"><img src="images/logout_green.png" class="logoImg"> log out';
  
  // default values. For main.php as current site   
  $linkLeft = $editString;
  $linkMiddle = $aboutString;
  if ($currentSiteUnsafe == '/start/editLinks.php') {
      $linkLeft = $homeString; 
      $linkMiddle = $aboutString; // stays default
  } elseif ($currentSiteUnsafe == '/start/about.php') {
      $linkLeft = $homeString;
      $linkMiddle = $editString;
  }

  echo '      
  <div class="section noBottom">
    <div class="container">
      <div class="row">
        <div class="twelve columns"><hr /></div>
      </div>
      <div class="row">
        <div class="four columns"><a class="button differentColor" '.$linkLeft.'</a></div>
        <div class="four columns"><a class="button differentColor" '.$linkMiddle.'</a></div>
        <div class="four columns"><a class="button differentColor" '.$linkRight.'</a></div>
      </div>
    </div>
  </div>'; 
} // function
  
// returning a single row for the matching id. 
// NB: id will get sql-escaped, userid not.
function getSingleLinkRow ($id, $userid, $dbConnection) {
  // need an additional userid condition. May be ignored by SQL because `id` is a primary key?
  if($result = $dbConnection->query('SELECT * FROM `links` WHERE `userid` = "'.$userid.'" AND `id` = "'.mysqli_real_escape_string($dbConnection, $id).'"')) {
    $row = $result->fetch_assoc();
    return($row);
  } else { return(false); }
} // function 

// TODO:
// a) password verification if one is set. 
// b) without a password, there will be a special link to switch between users (some pseudo-obfuscation)
function verifyCredentials ($temporaryUserid, $dbConnection) {
  $internalUserid = makeSafeInt($temporaryUserid, 11); // might be unnecessary because it's safe already
  if ($result = $dbConnection->query('SELECT `lastLogin` FROM `user` WHERE `id` = "'.$internalUserid.'"')) {
    if ($result->num_rows == 1) { // we are sure the id exists and there is only one
      if ($result = $dbConnection->query('UPDATE `user` SET `lastLogin` = CURRENT_TIMESTAMP WHERE `id` = "'.$internalUserid.'"')) {
        $_SESSION['userid'] = $internalUserid;
        return true;
      } // update query did work      
    } // id does exist
  } // select query did work
  return false;
}

// deletes both the cookie and the session 
function sessionAndCookieDelete () {        
  $_SESSION['userid'] = 0; // the most important one, make sure it's really 0 (before deleting everything)
  setcookie('userIdCookie', 0, time() - 42000);  // some big enough value in the past to make sure things like summer time changes do not affect it
  
  // now the more generic stuff
  $_SESSION = array(); // unset all of the session variables.
  session_destroy(); // finally, destroy the session    
}  

// returns the userid integer
function getUserid () {
  if (isset($_SESSION)) {
    return ($_SESSION['userid']);
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
  return($safe);
}

// does a (relative) redirect
function redirectRelative ($page) {
  // redirecting relative to current page NB: some clients require absolute paths
  $host  = $_SERVER['HTTP_HOST'];
  $uri   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');  
  header('Location: https://'.$host.htmlentities($uri).'/'.$page);
  exit;
}