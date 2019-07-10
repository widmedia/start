<?php
  require_once('functions.php');
  $dbConnection = initialize();
  
  // prints a message when logged in as a test user
  function printMsgTestUser ($userid) {
    if ($userid == 2) { 
      echo '<div class="overlayMessage" style="z-index: 3;">This is the (somewhat limited) test account. Get your own account? &nbsp;<a href="index.php?do=2#newUser" style="background-color:transparent; color:rgba(0, 0, 0, 0.85); text-decoration:underline;">&gt; Open account</a></div>'; 
    }
  } 

  // prints a message when the email of this account has not been verified
  function printMsgAccountVerify ($dbConnection, $userid) {
    $verified = false;
    if ($result = $dbConnection->query('SELECT `verified` FROM `user` WHERE `id` = "'.$userid.'"')) {
      $row = $result->fetch_row();
      if ($row[0] == 1) {
        $verified = true;
      } // verified
    } // select query
    
    if (!$verified) {
      echo '<div class="overlayMessage" style="z-index: 4;">Your email address has not yet been verified. Please do so within 24 hours, otherwise this account will be deleted.</div>';
    }
  } // function
    
  // function to output several links in a formatted way
  // creating a div for every link and div-rows for every $module-th entry
  // has a limit of 100 links per category
  function printLinks($dbConnection, $userid, $category) {
      
    // Have 12 columns. Means with modulo 3, I have 'class four columns' and vice versa
    $modulo = 3;
    $divClass = '<div class="halbeReihe four columns linktext">';
    if ($category == 2) { // this category prints more dense
      $modulo = 4;
      $divClass = '<div class="halbeReihe three columns linktext">';      
    }
     
    if ($result = $dbConnection->query('SELECT * FROM `links` WHERE userid = "'.$userid.'" AND category = "'.$category.'" ORDER BY `cntTot` DESC, `text` ASC LIMIT 100')) {
      $counter = 0;        
      while ($row = $result->fetch_assoc()) {
        echo $divClass.'<a href="link.php?id='.$row["id"].'" target="_blank" class="button">'.$row['text'].'</a><span class="counter">'.$row['cntTot'].'</span></div>';        
        $counter++;

        if (($counter % $modulo) == 0) {
          echo '</div>'."\n".'<div class="row">';
        }
      } // while    
      $result->close(); // free result set
    } // if  
  } // function   


  printStatic();
  echo '<script type="text/javascript" src="js/scripts.js"></script></head>';
  
  $msgSafe = makeSafeInt($_GET['msg'], 1);
  if ($msgSafe > 0) {
    echo '<body onLoad="overlayMsgFade();">'; 
    printMessage($msgSafe); 
  } else {
    echo '<body>';
  }
  $userid = getUserid();
  
  printNavMenu(1);
  
  printMsgTestUser($userid);      
  printMsgAccountVerify($dbConnection, $userid);

  echo '<div class="section categories noBottom"><div class="container">';
  
  echo '<h3 class="section-heading">'.getCategory($dbConnection, $userid, 1).'</h3><div class="row">';
  printLinks($dbConnection, $userid, 1);
  
  echo '</div><h3 class="section-heading">'.getCategory($dbConnection, $userid, 2).'</h3><div class="row">';
  printLinks($dbConnection, $userid, 2);
  
  echo '</div><h3 class="section-heading">'.getCategory($dbConnection, $userid, 3).'</h3><div class="row">';
  printLinks($dbConnection, $userid, 3);
  echo '</div>
  </div> <!-- /container -->';
  printFooter();
?>                
  </div> <!-- /section categories -->
</body>
</html>
