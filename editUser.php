<?php
  require_once('functions.php');
  $dbConnection = initialize();
  
  function printUserEdit ($dbConnection, $row) {    
    $hasPwText       = '';
    $displayPwRows   = 'none';
    $pwFieldRequired = '';
    if ($row['hasPw'] == 1) { 
      $hasPwText       = 'checked'; 
      $displayPwRows   = 'initial';
      $pwFieldRequired = 'required';
    }
    echo '
    <h3 class="section-heading"><span class="bgCol">Userid: '.$row['id'].'</span></h3>
    <form action="editUser.php?do=2" method="post">
    <div class="row">
      <div class="twelve columns"><span class="bgCol">'.getLanguage($dbConnection,46).$row['lastLogin'].'</span></div>
    </div>
    <div class="row">
      <div class="twelve columns" style="text-align: left;"><input type="checkbox" id="pwCheckBox" name="hasPw" value="1" '.$hasPwText.' onclick="pwToggle();"> <span class="bgCol">'.getLanguage($dbConnection,47).'</span> <div id="noPwWarning" class="noPwWarning" style="display: none;">'.getLanguage($dbConnection,48).'</div></div>
    </div>
    <div class="row"><div class="twelve columns">&nbsp;</div></div>
    <div class="row">
      <div class="two columns"><span class="bgCol">Email:</span> </div>
      <div class="ten columns"><input name="email" type="email" maxlength="127" value="'.$row['email'].'" required size="20"></div>
    </div>
    <div class="row" id="pwOldRow" style="display: '.$displayPwRows.';">
      <div class="two columns"><span class="bgCol">'.getLanguage($dbConnection,49).':</span></div>
      <div class="ten columns"><input name="password" type="password" maxlength="63" value="" '.$pwFieldRequired.' size="20"></div>
    </div>
    <div class="row" id="pwRow" style="display: '.$displayPwRows.';">
      <div class="two columns"><span class="bgCol">'.getLanguage($dbConnection,50).':</span></div>
      <div class="ten columns"><input name="passwordNew" type="password" maxlength="63" value="" size="20"></div>
    </div>
    <div class="row"><div class="twelve columns">&nbsp;</div></div>
    <div class="row">
      <div class="twelve columns"><input name="create" type="submit" value="'.getLanguage($dbConnection,51).'"></div>
    </div>
    <div class="row"><div class="twelve columns"><hr /></div></div>
    <div class="row"><div class="twelve columns">&nbsp;</div></div>
    <div class="row">
      <div class="twelve columns"><a href="editUser.php?do=1" class="button differentColor"><img src="images/icon_delete.png" class="logoImg"> '.getLanguage($dbConnection,52).'</a></div>
    </div>
    </form>';
  } // function
  
  // possible actions: 
  // 0=> edit an existing user: present the form
  // 1=> delete an existing user: db operations
  // 2=> update an existing user: db operations
  
  // Form processing
  $userid = getUserid();
  $doSafe = makeSafeInt($_GET['do'], 1); // this is an integer (range 0 to 2)
  
  $dispErrorMsg = 0;
  $heading = ''; // default value, stays empty if some error happens

  if ($doSafe == 0) { // edit an existing user: present the form
    if ($userid) { // have a valid userid
      if ($result = $dbConnection->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"')) {              
        $row = $result->fetch_assoc(); // guaranteed to get only one row
        printStartOfHtml($dbConnection);
        printUserEdit($dbConnection, $row);              
      } else { $dispErrorMsg = 11; } // select query did work
    } else { $dispErrorMsg = 10; } // have a valid userid
  } elseif ($doSafe == 1) { // delete an existing user
    // TODO: might want to verify the pw before deleting an account? (if there is a pw set)
    if (deleteUser($dbConnection, $userid)) {
      sessionAndCookieDelete();
      printStartOfHtml($dbConnection);
      printConfirm($dbConnection, getLanguage($dbConnection,53), getLanguage($dbConnection,54).$userid.' <br/><br/><a class="button differentColor" href="index.php">'.getLanguage($dbConnection,55).' index.php</a>');
    } else { $dispErrorMsg = 20; } // deleteUser function did return false
  } elseif ($doSafe == 2) { // update an existing user: db operations
    if ($userid > 0) { // have a valid userid
      updateUser($dbConnection, $userid);
    } else { $dispErrorMsg = 30; } // have a valid userid         
  } else { 
    $dispErrorMsg = 1;
  } // switch
  printError($dbConnection, $dispErrorMsg);  
  printFooter($dbConnection);
?>
