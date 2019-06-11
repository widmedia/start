<?php
  require_once('functions.php');
  $dbConnection = initialize();
  
  function printErrorNoDo() { // TODO: whole function
    echo '<h2 class="section-heading">Error</h2><div class="row">';          
    echo '<div class="row"><div class="six columns">No valid action given</div><div class="six columns"><a class="button differentColor" href="index.php">go back to index.php</a></div></div></div> <!-- /container -->';
    printFooter();
  } // function

  function printUserEdit($row) {    
    $hasPwText = '';
    $displayPwRows = 'none';
    if ($row['hasPw'] == 1) { 
      $hasPwText = 'checked'; 
      $displayPwRows = 'initial';
    }
    // distinct cases:
    // - did have a pw, still will have one: show all
    // - did have a pw, now does not have one: show old pw
    // - did not have a pw, still will have none: show none
    // - did not have a pw, will have one: show new pw
    
    // settings: initially have both or have none
    // - have both: toggle the new one (from checked-to-not checked, remove new) 
    // - have none: toggle the new one
    echo '
    <h3 class="section-heading">Userid: '.$row['id'].'</h3>
    <form action="editUser.php?do=3" method="post">
    <div class="row">
      <div class="twelve columns">last login: '.$row['lastLogin'].'</div>
    </div>
    <div class="row">
      <div class="twelve columns" style="text-align: left;"><input type="checkbox" id="pwCheckBox" name="hasPw" value="1" '.$hasPwText.' onclick="pwToggle();"> password protection for this account</div>
    </div>
    <div class="row"><div class="twelve columns">&nbsp;</div></div>
    <div class="row">
      <div class="two columns">email: </div>
      <div class="ten columns"><input name="email" type="email" maxlength="127" value="'.$row['email'].'" required size="20"></div>
    </div>
    <div class="row" id="pwRow" style="display: '.$displayPwRows.';">
      <div class="two columns">old password: </div>
      <div class="ten columns"><input name="password" type="password" maxlength="63" value="" size="20"></div>
    </div>
    <div class="row" id="pwNewRow" style="display: '.$displayPwRows.';">
      <div class="two columns">new password: </div>
      <div class="ten columns"><input name="passwordNew" type="password" maxlength="63" value="" size="20"></div>
    </div>
    <div class="row"><div class="twelve columns">&nbsp;</div></div>
    <div class="row">
      <div class="twelve columns"><input name="create" type="submit" value="save changes"></div>
    </div>
    <div class="row"><div class="twelve columns"><hr /></div></div>          
    <div class="row">
      <div class="twelve columns"><a href="editUser.php?do=2" class="button differentColor"><img src="images/delete.png" class="logoImg"> delete this account (without any further confirmation)</a></div>
    </div>
    </form>';
  } // function
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs -->
  <meta charset="utf-8">
  <title>Add, edit or delete user accounts</title>
  <meta name="description" content="page to add, edit or delete user accounts">
  <meta name="author" content="Daniel Widmer">

  <!-- Mobile Specific Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FONT -->
  <link rel="stylesheet" href="css/font.css" type="text/css">

  <!-- CSS -->
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">
  <link rel="stylesheet" href="css/custom.css">
  
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon-96x96.png">
  <script> 
    function pwToggle() {
      if (document.getElementById("pwCheckBox").checked == 1) {
        document.getElementById("pwNewRow").style.display = "initial";
      } else {
        document.getElementById("pwNewRow").style.display = "none"; 
      }
    }
  </script>
</head>
<body>
  <!-- Primary Page Layout -->
  <div class="section categories">
    <div class="container">
     <?php          
      $userid = getUserid();
      
      // possible actions: 
      // 1=> edit an existing user: present the form
      // 2=> delete an existing user: db operations
      // 3=> TODO: not yet implemented. update an existing user: db operations
      
      // Form processing
      $doSafe = makeSafeInt($_GET['do'], 1); // this is an integer (range 1 to 3)
      
      $dispErrorMsg = 0;
      $heading = ''; // default value, stays empty if some error happens

      // sanity checking. Check first if I have a valid 'do'
      if ($doSafe == 0) { // error, there is no valid thing to 'do'. Should not happen
        printErrorNoDo();
        die(); // exit the php part
      } elseif ($doSafe > 0) {
        
        switch ($doSafe) {
        case 1: // edit an existing user: present the form
          if ($userid) { // have a valid userid
            if ($result = $dbConnection->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"')) {              
              $row = $result->fetch_assoc(); // guaranteed to get only one row
              printUserEdit($row);              
            } else { $dispErrorMsg = 11; } // select query did work
          } else { $dispErrorMsg = 10; } // have a valid userid
          break;        
        case 2: // update/delete an existing user
          if ($userid) { // have a valid userid
            if ($result = $dbConnection->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"')) {
              // make sure this id actually exists and it's not id=1 (admin user) or id=2 (test user)
              $rowCnt = $result->num_rows;
              if ($userid > 2) { // admin has userid 1, test user has userid 2
                if ($rowCnt == 1) {                
                  $result_delLinks = $dbConnection->query('DELETE FROM `links` WHERE `userid` = "'.$userid.'"');
                  $result_delCategories = $dbConnection->query('DELETE FROM `categories` WHERE `userid` = "'.$userid.'"');                  
                  $result_delUser = $dbConnection->query('DELETE FROM `user` WHERE `id` = "'.$userid.'"');
                  
                  if ($result_delLinks and $result_delCategories and $result_delUser) {
                    sessionAndCookieDelete();
                    echo '<h2 class="section-heading">Deleting the account did work</h2>';
                    echo '<div class="row">
                            <div class="six columns">Deleted userid: '.$userid.'</div>
                            <div class="six columns"><a class="button differentColor" href="index.php">go back to index.php</a></div>
                         </div>';                  
                  } else { $dispErrorMsg = 24; } // deleting did work                
                } else { $dispErrorMsg = 23; } // id does exists
              } else { printConfirmation('Forbidden', 'Sorry but the test user account and the admin account cannot be deleted', 'nine', 'three'); }
            } else { $dispErrorMsg = 21; } // select query did work              
          } else { $dispErrorMsg = 20; } // have a valid userid
          break;
        case 3: // TODO
          $dispErrorMsg = 30;
        default: 
          $dispErrorMsg = 1;
        } // switch
        if ($dispErrorMsg > 0) {
          printConfirmation('Error', '"Something" at step '.$dispErrorMsg.' went wrong when processing user input data (very helpful error message, I know...). Might try again?', 'nine', 'three');
          echo '</div> <!-- /container -->';
          printFooter();
          die(); // finish the php part
        } // dispErrorMsg > 0        
        echo '</div> <!-- /container -->';
        printFooter();
      } // action = integer          
    ?>                
  </div> <!-- /section categories -->
<!-- End Document -->
</body>
</html>
