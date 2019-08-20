<?php declare(strict_types=1);
  require_once('functions.php');
  $dbConn = initialize();
  
  function printUserEdit ($dbConn, $row): void {    
    $hasPwText       = '';
    $displayPwRows   = 'none';
    $pwFieldRequired = '';
    if ($row['hasPw'] == 1) { 
      $hasPwText       = 'checked'; 
      $displayPwRows   = 'initial';
      $pwFieldRequired = 'required';
    }
    
    // later TODO: will change it to arrays as soon as I have several pictures    
    $currentlySelectedStyle = 'border: 2px solid #faff3b;';  // to do: should use the color definition from printInlineCss. But later on, that's coming from the db anyhow
    $notSel = 'border: 2px dotted #000;';
    
    // 0..6 are valid selectors
    $bgBorderSel = array($notSel,$notSel,$notSel,$notSel,$notSel,$notSel,$notSel);
    $bgBorderSel[$row['styleId']] = $currentlySelectedStyle;
        
    
    echo '
    <h3 class="section-heading"><span class="bgCol">Email / '.getLanguage($dbConn,84).'</span></h3>
    <form action="editUser.php?do=2" method="post">
    <div class="row twelve columns"><span class="bgCol">'.getLanguage($dbConn,46).$row['lastLogin'].'</span></div>    
    <div class="row twelve columns" style="text-align: left;"><input type="checkbox" id="pwCheckBox" name="hasPw" value="1" '.$hasPwText.' onclick="pwToggle();" /> <span class="bgCol">'.getLanguage($dbConn,47).'</span> <div id="noPwWarning" class="noPwWarning" style="display: none;">'.getLanguage($dbConn,48).'</div></div>
    <div class="row"><div class="twelve columns">&nbsp;</div></div>
    <div class="row">
      <div class="two columns"><span class="bgCol">Email:</span> </div>
      <div class="ten columns"><input name="email" type="email" maxlength="127" value="'.$row['email'].'" required size="20" /></div>
    </div>
    <div class="row" id="pwOldRow" style="display: '.$displayPwRows.';">
      <div class="two columns"><span class="bgCol">'.getLanguage($dbConn,49).':</span></div>
      <div class="ten columns"><input name="password" type="password" maxlength="63" value="" '.$pwFieldRequired.' size="20" /></div>
    </div>
    <div class="row" id="pwRow" style="display: '.$displayPwRows.';">
      <div class="two columns"><span class="bgCol">'.getLanguage($dbConn,50).':</span></div>
      <div class="ten columns"><input name="passwordNew" type="password" maxlength="63" value="" size="20" /></div>
    </div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns"><input name="create" type="submit" value="'.getLanguage($dbConn,51).'" /></div>    
    <div class="row twelve columns"><hr /></div>    
    <h3 class="section-heading"><span class="bgCol">'.getLanguage($dbConn,122).'</span></h3>';
    for ($i = 0; $i < 7; $i++) {
      if (($i % 4) == 0) { echo '<div class="row">'; }
      echo '<div class="three columns u-max-full-width"><a href="editUser.php?do=3&styleId='.$i.'" style="background-color:transparent;"><img src="images/bg/'.styleDef($i,'bgImg').'" alt="default background image" style="'.$bgBorderSel_0.' width:100%; vertical-align:middle;"></a></div>';
      if (($i == 3) or ($i == 6)) { // last one does not fit into modulo function ($i % 4) == 3
        echo '</div><div class="row twelve columns">&nbsp;</div>'; 
      } 
    }
    echo '    
    <div class="row twelve columns"><hr /></div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns"><a href="editUser.php?do=1" class="button differentColor" style="white-space:normal; height:auto; min-height:38px;"><img src="images/icon_delete.png" class="logoImg" alt="icon delete"> '.getLanguage($dbConn,52).'</a></div>
    </form>';
  } // function
  
  // possible actions: 
  // 0=> edit an existing user: present the form
  // 1=> delete an existing user: db operations
  // 2=> update an existing user: db operations
  // 3=> change the background image: db operations
  
  // TODO: think about merging this whole file with the editLinks file
  
  // Form processing
  $userid = getUserid();
  $doSafe = safeIntFromExt('GET', 'do', 1); // this is an integer (range 0 to 2)

  if ($doSafe == 0) { // edit an existing user: present the form
    if ($userid) { // have a valid userid
      if ($result = $dbConn->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"')) {              
        $row = $result->fetch_assoc(); // guaranteed to get only one row
        printStartOfHtml($dbConn);
        printUserEdit($dbConn, $row);              
      } else { error($dbConn, 150000); } // select query did work
    } else { error($dbConn, 150001); } // have a valid userid
  } elseif ($doSafe == 1) { // delete an existing user
    // TODO: might want to verify the pw before deleting an account? (if there is a pw set)
    if (deleteUser($dbConn, $userid)) {
      sessionAndCookieDelete();
      printStartOfHtml($dbConn);
      printConfirm($dbConn, getLanguage($dbConn,53), getLanguage($dbConn,54).$userid.' <br/><br/><a class="button differentColor" href="index.php">'.getLanguage($dbConn,55).' index.php</a>');
    } else { error($dbConn, 150100); } // deleteUser function did return false
  } elseif ($doSafe == 2) { // update an existing user: db operations
    if ($userid > 0) { // have a valid userid
      if (updateUser($dbConn, $userid, false)) { 
        redirectRelative('links.php?msg=6');
      } else { error($dbConn, 150200); }
    } else { error($dbConn, 150201); } // have a valid userid         
  } elseif ($doSafe == 3) { // update an existing user: styleId link
    if ($userid > 0) { // have a valid userid
      $styleIdFromGet = safeIntFromExt('GET', 'styleId', 2); // this is an integer, range 0 to 99
      if ($styleIdFromGet <= 6) { // currently the only valid image ids
        if ($result = $dbConn->query('UPDATE `user` SET `styleId` = "'.$styleIdFromGet.'" WHERE `id` = "'.$userid.'"')) {
          redirectRelative('editUser.php'); // stay on the page
        } else { error($dbConn, 150300); } // query
      } else { error($dbConn, 150301); } // valid image id
    } else { error($dbConn, 150301); } // valid userid
  } else { 
    error($dbConn, 150002);
  } // switch  
  printFooter($dbConn);
?>
