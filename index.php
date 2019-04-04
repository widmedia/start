<?php
  require_once('functions.php');
  $dbConnection = initialize('index');
   
  verifyCredentials(1); // TODO: change here
  
  // redirecting. NB: some clients require absolute paths
  $host  = $_SERVER['HTTP_HOST'];
  $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');  
  header('Location: https://'.$host.$uri.'/main.php');
  exit;
?>