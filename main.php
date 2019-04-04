<?php
  require_once('functions.php');
  $dbConnection = initialize('main');
?>                

<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs -->
  <meta charset="utf-8">
  <title>Startpage</title>
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
      $userid = getUserid(); 
      echo '<h3 class="section-heading">'.getCategory($userid, 1, $dbConnection).'</h3><div class="row">';
      printLinks(false, $userid, 1, $dbConnection);
      
      echo '</div><h3 class="section-heading">'.getCategory($userid, 2, $dbConnection).'</h3><div class="row">';
      printLinks(false, $userid, 2, $dbConnection);
      
      echo '</div><h3 class="section-heading">'.getCategory($userid, 3, $dbConnection).'</h3><div class="row">';
      printLinks(false, $userid, 3, $dbConnection);
      echo '</div>
    </div> <!-- /container -->';
    printFooter('index');
    ?>                
  </div> <!-- /section categories -->
</body>
</html>
