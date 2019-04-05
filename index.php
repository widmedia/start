<?php
  require_once('functions.php');
  $dbConnection = initialize('index');
   
  $useridSafe = makeSafeInt($_GET['userid'], 11);  
  $useridCookieSafe = makeSafeInt($_COOKIE['userIdCookie'], 11);
  
  // TODO: security measures
  // TODO: way to delete a cookie (have it expire)
  if ($useridCookieSafe) {    
    verifyCredentials($useridCookieSafe);    
    redirectRelative('main.php');    
  } elseif ($useridSafe) {       
    verifyCredentials($useridSafe); 

    // TODO: add cookie only if verification was ok        
    if (makeSafeInt($_GET['setCookie'], 1)) {
      $expire = 60 * 60 * 24 * 7 * 4; // valid for 4 weeks
      setcookie('userIdCookie', $useridSafe, time() + $expire);
      // echo $_COOKIE["your cookie name"];
    }
    
    redirectRelative('main.php');    
  } // else, present the userid selection page
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
      <h3 class="section-heading">User selection</h3>
      <div class="row">
        <div class="four columns linktext" style="text-align: left;">user selection</div>
        <div class="eight columns" style="text-align: left;">
          <p>login with userid 1: <a href="index.php?userid=1">login</a></p>
          <p>login with userid 2: <a href="index.php?userid=2">login</a></p>
          <p>login with non-existing userid 3: <a href="index.php?userid=3">login</a></p>
          <p>login with userid 1, set a cookie for 4 weeks: <a href="index.php?userid=1&setCookie=1">login</a></p>
        </div>        
      </div>  
    </div> <!-- /container -->
  </div> <!-- /section categories -->
</body>
</html>