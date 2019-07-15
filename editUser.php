<?php
  require_once('functions.php');
  $dbConnection = initialize();
  
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
    <div class="row"><div class="twelve columns">&nbsp;</div></div>
    <div class="row">
      <div class="twelve columns"><a href="editUser.php?do=2" class="button differentColor"><img src="images/icon_delete.png" class="logoImg"> delete this account (without any further confirmation)</a></div>
    </div>
    </form>';
  } // function


  printStatic();
  echo '<script defer type="text/javascript" src="js/scripts.js"></script>  
  </head><body>';
  printNavMenu();
  echo '
  <div class="section categories">
    <div class="container">';     
      $userid = getUserid();
      
      // possible actions: 
      // 1=> edit an existing user: present the form
      // 2=> delete an existing user: db operations
      // 3=> update an existing user: db operations
      
      // Form processing
      $doSafe = makeSafeInt($_GET['do'], 1); // this is an integer (range 1 to 3)
      
      $dispErrorMsg = 0;
      $heading = ''; // default value, stays empty if some error happens
 
      if ($doSafe == 1) { // edit an existing user: present the form
        if ($userid) { // have a valid userid
          if ($result = $dbConnection->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"')) {              
            $row = $result->fetch_assoc(); // guaranteed to get only one row
            printUserEdit($row);              
          } else { $dispErrorMsg = 11; } // select query did work
        } else { $dispErrorMsg = 10; } // have a valid userid
      } elseif ($doSafe == 2) { // delete an existing user
        // TODO: might want to verify the pw before deleting an account? (if there is a pw set)
        if (deleteUser($dbConnection, $userid)) {
          sessionAndCookieDelete();
          printConfirm('Deleted the account', 'Deleted userid: '.$userid.' <br/><br/><a class="button differentColor" href="index.php">go back to index.php</a>');
        } else { $dispErrorMsg = 20; } // deleteUser function did return false
      } elseif ($doSafe == 3) { // update an existing user: db operations
        if ($userid > 0) { // have a valid userid
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
                    if ($result = $dbConnection->query('SELECT `verified` FROM `user` WHERE `email` LIKE "'.$emailSqlSafe.'" LIMIT 1')) {
                      if ($result->num_rows == 0) {
                        $emailOk = true; 
                      }
                    }
                  } else { $emailOk = true; }; // no need to check again if the email did not change
                }
                
                if ($emailOk) {
                  if ($result = $dbConnection->query('UPDATE `user` SET `hasPw` = "'.$hasPwCheckBox.'", `pwHash` = "'.$pwHash.'", `email` = "'.$emailSqlSafe.'" WHERE `id` = "'.$userid.'"')) {
                    redirectRelative('links.php?msg=6');
                  } else { $dispErrorMsg = 35; } // update query
                } else { $dispErrorMsg = 34; } // emailCheck
              } else { $dispErrorMsg = 33; } // pwCheck ok                
            } else { $dispErrorMsg = 32; } // select query did work
          } else { $dispErrorMsg = 31; } // testUserCheck
        } else { $dispErrorMsg = 30; } // have a valid userid         
      } else { 
        $dispErrorMsg = 1;
      } // switch
      printError($dispErrorMsg);
      echo '</div> <!-- /container -->';
      printFooter();
    ?>                
  </div> <!-- /section categories -->
<!-- End Document -->
</body>
</html>
