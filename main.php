<?php
  require_once('functions.php');
  $dbConnection = initialize();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Startpage</title>
  <meta name="description" content="a modifiable page containing various links, intended to be used as a personal start page">
  <meta name="author" content="Daniel Widmer">

  <!-- Mobile Specific Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS -->
  <link rel="stylesheet" href="css/font.css" type="text/css">
  <link rel="stylesheet" href="css/normalize.css" type="text/css">
  <link rel="stylesheet" href="css/skeleton.css" type="text/css">
  <link rel="stylesheet" href="css/custom.css" type="text/css">
 
  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon-96x96.png">
  
  <script type="text/javascript" src="js/scripts.js"></script>  
</head>
  
    <?php
      $msgSafe = makeSafeInt($_GET['msg'], 1);
      if ($msgSafe > 0) {
        echo '<body onLoad="msgShow();">'; 
        printMessage($msgSafe); 
      } else {
        echo '<body>';
      }
      $userid = getUserid(); 
      if ($userid == 2) { echo '<div style="width: 100%; margin:auto;"><div class="button differentColor" style="position: relative; display: block; top: 1rem; background-color: rgba(255, 47, 25, 0.5); z-index: 3;">This is the (somewhat limited) test account</div></div>'; }

      echo '<div class="section categories noBottom"><div class="container">';
      
      echo '<h3 class="section-heading">'.getCategory($userid, 1, $dbConnection).'</h3><div class="row">';
      printLinks(false, $userid, 1, $dbConnection);
      
      echo '</div><h3 class="section-heading">'.getCategory($userid, 2, $dbConnection).'</h3><div class="row">';
      printLinks(false, $userid, 2, $dbConnection);
      
      echo '</div><h3 class="section-heading">'.getCategory($userid, 3, $dbConnection).'</h3><div class="row">';
      printLinks(false, $userid, 3, $dbConnection);
      echo '</div>
    </div> <!-- /container -->';
    printFooter();
    ?>                
  </div> <!-- /section categories -->
</body>
</html>
