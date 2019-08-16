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
    
    // later to do: will change it to arrays as soon as I have several pictures
    $bgImgName_0 = 'bg_ice_1920x1080.jpg'; // 
    $bgImgName_1 = 'bg_bamboo_1920x1080.jpg'; 
    $currentlySelectedStyle = 'border: 2px solid #faff3b;';
    $notSelectedStyle = 'border: 2px dotted #000;';
    $bgBorderSel_0 = $currentlySelectedStyle;
    $bgBorderSel_1 = $notSelectedStyle;
    if ($row['bgImgId'] == 1) { // background image, stored with an id in the user data base, 0 and 1 are valid items
      $bgBorderSel_0 = $notSelectedStyle;
      $bgBorderSel_1 = $currentlySelectedStyle;
    }
    
    
    echo '
    <h3 class="section-heading"><span class="bgCol">Userid: '.$row['id'].'</span></h3>
    <form action="editUser.php?do=2" method="post">
    <div class="row twelve columns"><span class="bgCol">'.getLanguage($dbConn,46).$row['lastLogin'].'</span></div>    
    <div class="row twelve columns" style="text-align: left;"><input type="checkbox" id="pwCheckBox" name="hasPw" value="1" '.$hasPwText.' onclick="pwToggle();"> <span class="bgCol">'.getLanguage($dbConn,47).'</span> <div id="noPwWarning" class="noPwWarning" style="display: none;">'.getLanguage($dbConn,48).'</div></div>    
    <div class="row"><div class="twelve columns">&nbsp;</div></div>
    <div class="row">
      <div class="two columns"><span class="bgCol">Email:</span> </div>
      <div class="ten columns"><input name="email" type="email" maxlength="127" value="'.$row['email'].'" required size="20"></div>
    </div>
    <div class="row" id="pwOldRow" style="display: '.$displayPwRows.';">
      <div class="two columns"><span class="bgCol">'.getLanguage($dbConn,49).':</span></div>
      <div class="ten columns"><input name="password" type="password" maxlength="63" value="" '.$pwFieldRequired.' size="20"></div>
    </div>
    <div class="row" id="pwRow" style="display: '.$displayPwRows.';">
      <div class="two columns"><span class="bgCol">'.getLanguage($dbConn,50).':</span></div>
      <div class="ten columns"><input name="passwordNew" type="password" maxlength="63" value="" size="20"></div>
    </div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns"><input name="create" type="submit" value="'.getLanguage($dbConn,51).'"></div>    
    <div class="row twelve columns"><hr /></div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row">
      <div class="six columns"><a href="editUser.php?do=3&imgId=0" style="background-color:transparent;"><img src="images/'.$bgImgName_0.'" width="240" height="135" style="'.$bgBorderSel_0.'"></a></div>
      <div class="six columns"><a href="editUser.php?do=3&imgId=1" style="background-color:transparent;"><img src="images/'.$bgImgName_1.'" width="240" height="135" style="'.$bgBorderSel_1.'"></a></div>
    </div>
    <div class="row twelve columns"><hr /></div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns"><a href="editUser.php?do=1" class="button differentColor"><img src="images/icon_delete.png" class="logoImg"> '.getLanguage($dbConn,52).'</a></div>
    </form>';
  } // function
  
  // possible actions: 
  // 0=> edit an existing user: present the form
  // 1=> delete an existing user: db operations
  // 2=> update an existing user: db operations
  
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
  } elseif ($doSafe == 3) { // update an existing user: bgImgLink
    if ($userid > 0) { // have a valid userid
      $imgIdFromGet = safeIntFromExt('GET', 'imgId', 2); // this is an integer, range 0 to 99
      if ($imgIdFromGet == 0 or $imgIdFromGet == 1) { // currently the only valid image ids
        if ($result = $dbConn->query('UPDATE `user` SET `bgImgId` = "'.$imgIdFromGet.'" WHERE `id` = "'.$userid.'"')) {
          redirectRelative('editUser.php'); // stay on the page
        } else { error($dbConn, 150300); } // query
      } else { error($dbConn, 150301); } // valid image id
    } else { error($dbConn, 150301); } // valid userid
  } else { 
    error($dbConn, 150002);
  } // switch  
  printFooter($dbConn);
?>
