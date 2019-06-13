<?php
  require_once('functions.php');
  $dbConnection = initialize();
  
  function printErrorNoDo() { 
    echo '<h2 class="section-heading">Error</h2><div class="row">';          
    echo '<div class="row"><div class="six columns">No valid action given</div><div class="six columns"><a class="button differentColor" href="index.php">go back to index.php</a></div></div></div> <!-- /container -->';
    printFooter();
  } // function

  function printUserEdit($row) {    
    $hasPwText       = '';
    $displayPwRows   = 'none';
    $pwFieldRequired = '';
    if ($row['hasPw'] == 1) { 
      $hasPwText       = 'checked'; 
      $displayPwRows   = 'initial';
      $pwFieldRequired = 'required';
    }
    echo '
    <h3 class="section-heading">Userid: '.$row['id'].'</h3>
    <form action="editUser.php?do=3" method="post">
    <div class="row">
      <div class="twelve columns">last login: '.$row['lastLogin'].'</div>
    </div>
    <div class="row">
      <div class="twelve columns" style="text-align: left;"><input type="checkbox" id="pwCheckBox" name="hasPw" value="1" '.$hasPwText.' onclick="pwToggle();"> password protection for this account <div id="noPwWarning" class="noPwWarning" style="display: none;">Please be aware: when not using a password, everybody can log into this account and edit information or delete the account itself</div></div>
    </div>
    <div class="row"><div class="twelve columns">&nbsp;</div></div>
    <div class="row">
      <div class="two columns">email: </div>
      <div class="ten columns"><input name="email" type="email" maxlength="127" value="'.$row['email'].'" required size="20"></div>
    </div>
    <div class="row" id="pwOldRow" style="display: '.$displayPwRows.';">
      <div class="two columns">old password: </div>
      <div class="ten columns"><input name="password" type="password" maxlength="63" value="" '.$pwFieldRequired.' size="20"></div>
    </div>
    <div class="row" id="pwRow" style="display: '.$displayPwRows.';">
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
  <meta charset="utf-8">
  <title>Add, edit or delete user accounts</title>
  <meta name="description" content="page to add, edit or delete user accounts">
  <meta name="author" content="Daniel Widmer">

  <!-- Mobile Specific Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS -->
  <link rel="stylesheet" href="css/font.css" type="text/css">
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">
  <link rel="stylesheet" href="css/custom.css">  
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon-96x96.png">
  
  <script defer type="text/javascript" src="js/scripts.js"></script>  
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
      // 3=> update an existing user: db operations
      
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
        case 2: // delete an existing user
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
        case 3:
          if ($userid) { // have a valid userid
            if (testUserCheck($userid)) { 
              if ($result = $dbConnection->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"')) {              
                $row = $result->fetch_assoc(); // guaranteed to get only one row
                $pwCheck = false;
                if ($row['hasPw'] == 1) { // if there has been a hasPw, then I need to check whether the oldPw matches the stored one (without looking at hasPw-checkbox)
                  $passwordUnsafe = filter_var(substr($_POST['password'], 0, 63), FILTER_SANITIZE_STRING);
                  if (password_verify($passwordUnsafe, $row['pwHash'])) {        
                    $pwCheck = true;
                  } // else, $pwCheck stays at false
                } else { // not an error
                  $pwCheck = true;
                }
                // TODO: merge some of this stuff with the functionality on index.php...addNewUser
                if ($pwCheck) {
                  $hasPwCheckBox = makeSafeInt($_POST['hasPw'],1);
                  if ($hasPwCheckBox == 1) { // if hasPw-checkbox, the newPw must be at least 4 chars long
                    $pwHash = 0;
                    if (strlen($passwordUnsafe) > 3) {  
                      $passwordUnsafe = filter_var(substr($_POST['passwordNew'], 0, 63), FILTER_SANITIZE_STRING);
                      $pwHash = password_hash($passwordUnsafe, PASSWORD_DEFAULT);
                    } else { $dispErrorMsg = 34; }
                  } // else, not an error
                  
                  $emailOk = false;
                  $emailUnsafe = filter_var(substr($_POST['email'], 0, 127), FILTER_SANITIZE_EMAIL);
                  // newEmail must not exist in the db (exclude current user itself)
                  if (filter_var($emailUnsafe, FILTER_VALIDATE_EMAIL)) { // have a valid email 
                    // check whether email already exists
                    $emailSqlSafe = mysqli_real_escape_string($dbConnection, $emailUnsafe);
                    if (strcasecmp($emailSqlSafe, $row['email'])  != 0) { // 0 means they are equal
                      if ($result = $dbConnection->query('SELECT * FROM `user` WHERE `email` LIKE "'.$emailSqlSafe.'" LIMIT 1')) {
                        if ($result->num_rows == 0) {
                          $emailOk = true; 
                        }
                      }
                    } else { $emailOk = true; }; // no need to check again if the email did not change
                  }
                  
                  if ($emailOk) {
                    if ($result = $dbConnection->query('UPDATE `user` SET `hasPw` = "'.$hasPwCheckBox.'", `pwHash` = "'.$pwHash.'", `email` = "'.$emailSqlSafe.'" WHERE `id` = "'.$userid.'"')) {
                      redirectRelative('main.php?msg=6');
                    } else { $dispErrorMsg = 35; } // update query
                  } else { $dispErrorMsg = 34; } // emailCheck
                } else { $dispErrorMsg = 33; } // pwCheck ok                
              } else { $dispErrorMsg = 32; } // select query did work
            } else { $dispErrorMsg = 31; } // testUserCheck
          } else { $dispErrorMsg = 30; } // have a valid userid         
          break;
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
