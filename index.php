<?php
  require_once('functions.php');
  $dbConnection = initialize();
  
  // Form processing
  $doSafe     = makeSafeInt($_GET['do'], 1);       // this is an integer (range 1 to 1)
  $useridSafe = makeSafeInt($_GET['userid'], 11);  
  $useridCookieSafe = makeSafeInt($_COOKIE['userIdCookie'], 11);
  
  // TODO: security measures
  
  if ($doSafe == 0) { // the $_GET-do parameter has higher priority than the rest
    if ($useridSafe) { // the $_GET-userid has higher priority than the cookie userid
      verifyCredentials($useridSafe); 

      // TODO: add cookie only if verification was ok        
      if (makeSafeInt($_GET['setCookie'], 1)) {
        $expire = 60 * 60 * 24 * 7 * 4; // valid for 4 weeks
        setcookie('userIdCookie', $useridSafe, time() + $expire);      
      }    
      redirectRelative('main.php');    
      
    } elseif ($useridCookieSafe) {    
      verifyCredentials($useridCookieSafe);    
      redirectRelative('main.php');    
    } // else, present the userid selection page
  }
  
  // deletes both the cookie and the session 
  function logOut () {        
    sessionAndCookieDelete();    
    echo '<h3 class="section-heading">Logged out</h3>
          <div class="row">
            <div class="four columns">&nbsp;</div>
            <div class="eight columns">go back to <a href="index.php">the start page</a></div>
          </div>';
  }  
  
  function printEntryPoint() {
    // TODO: this will change...
    // TODO: do an sql query to find the (max 10) existing userids
    // Add some non-existing ones to check the behavior then
    echo '<h3 class="section-heading">User selection</h3>
          <div class="row">            
            <div class="twelve columns" style="text-align: left;">
              <p>login with userid 1: <a href="index.php?userid=1">login</a></p>
              <p>login with test userid 2: <a href="index.php?userid=2">login</a></p>
              <p>login with non-existing userid 3: <a href="index.php?userid=3">login</a></p>
              <p>login with userid 1, set a cookie for 4 weeks: <a href="index.php?userid=1&setCookie=1">login</a></p>
              <p><hr/></p>
              <p>User changes</p>
              <p>add a new user (limit of 10 users): <a href="editUser.php?do=1">add a user</a></p>
            </div>        
          </div>';
  } // function 


  
?>

<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs -->
  <meta charset="utf-8">
  <title>Start - user selection</title>
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

</head>
<body>
  <div class="section categories noBottom">
    <div class="container">
    <?php           
      // possible actions: 
      // 0/on-existing: normal case
      // 1=> logout
      
      if ($doSafe == 0) { // entry point of this site (NB: this if statement is only required if no session or cookie is set)
        printEntryPoint();        
      } else { // currently have only one possible 'do'-action. Logout will be done for all values > 0
        logOut();        
      } // action = integer          
    ?> 
    </div> <!-- /container -->
  </div> <!-- /section categories -->
</body>
</html>