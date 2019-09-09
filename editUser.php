<?php declare(strict_types=1);
  require_once('functions.php');
  $dbConn = initialize();
  
  function printUserEdit (object $dbConn, $row): void {    
    $notSel = 'border: 2px dotted #000;';        
    $bgBorderSel = array($notSel,$notSel,$notSel,$notSel,$notSel,$notSel,$notSel,$notSel); // 1..7 are valid selectors, 0=undefined is valid as well
    $bgBorderSel[$row['styleId']] = 'border: 2px solid #faff3b;';  // some bright color (not related to the designs)
    
    echo '
    <h3 class="section-heading"><span class="bgCol">Email / '.getLanguage($dbConn,84).'</span></h3>
    <form action="editUser.php?do=2" method="post">        
    <div class="row"><div class="twelve columns">&nbsp;</div></div>
    <div class="row">
      <div class="four columns"><span class="bgCol">Email:</span> </div>
      <div class="eight columns"><input name="email" type="email" maxlength="127" value="'.$row['email'].'" required size="20" /></div>
    </div>
    <div class="row" id="pwOldRow">
      <div class="four columns"><span class="bgCol">'.getLanguage($dbConn,49).':</span></div>
      <div class="eight columns"><input name="password" type="password" maxlength="63" value="" required size="20" /></div>
    </div>
    <div class="row" id="pwRow">
      <div class="four columns"><span class="bgCol">'.getLanguage($dbConn,50).':</span></div>
      <div class="eight columns"><input name="passwordNew" type="password" maxlength="63" value="" size="20" /></div>
    </div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns"><input name="create" type="submit" value="'.getLanguage($dbConn,51).'" /></div>    
    <div class="row twelve columns"><hr /></div>    
    <h3 class="section-heading"><span class="bgCol">'.getLanguage($dbConn,122).'</span></h3>';
    for ($i = 0; $i < 7; $i++) {
      if (($i % 4) == 0) { echo '<div class="row">'; }
      echo '<div class="three columns u-max-full-width"><a href="editUser.php?do=3&styleId='.($i+1).'" style="background-color:transparent;"><img src="images/bg/'.styleDef(($i+1),'bgImg').'" alt="default background image" style="'.$bgBorderSel[($i+1)].' width:100%; vertical-align:middle;"></a></div>';
      if (($i == 3) or ($i == 6)) { // last one does not fit into modulo function ($i % 4) == 3
        echo '</div><div class="row twelve columns">&nbsp;</div>'; 
      } 
    }
    echo '<div class="row twelve columns"><hr /></div>    
    <h3 class="section-heading"><span class="bgCol">Sprache / Language</span></h3>
    <div class="row">
      <div class="six columns"><span class="bgCol">English:</span><a href="editUser.php?ln=en">&nbsp;EN&nbsp;</a></div>
      <div class="six columns"><span class="bgCol">Deutsch:</span><a href="editUser.php?ln=de">&nbsp;DE&nbsp;</a></div>
    </div>
    <div class="row twelve columns"><hr /></div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns"><a href="editUser.php?do=1" class="button differentColor" style="white-space:normal; height:auto; min-height:38px;"><img src="images/icon/delete.png" class="logoImg" alt="icon delete"> '.getLanguage($dbConn,52).'</a></div>
    </form>';
  } // function
  
  function updateStyleId(object $dbConn, int $userid, int $styleIdFromGet) {
    if (!($userid > 0)) { // have a valid userid, testuser may change it as well
      return error($dbConn, 150301);
    }
    if (!(($styleIdFromGet < 8) and ($styleIdFromGet > 0))) { // currently valid image IDs from 1 to 7
      return error($dbConn, 150302);
    }
    if (!($dbConn->query('UPDATE `user` SET `styleId` = "'.$styleIdFromGet.'" WHERE `id` = "'.$userid.'"'))) {
      return error($dbConn, 150300);
    }
    return true;
  }
 
  
  // possible actions: 
  // 0=> edit an existing user: present the form
  // 1=> delete an existing user: db operations
  // 2=> update an existing user: db operations
  // 3=> change the background image: db operations  
  
  // Form processing
  $userid = getUserid();
  $doSafe = safeIntFromExt('GET', 'do', 1); // this is an integer (range 0 to 2)

  if ($doSafe == 0) { // edit an existing user: present the form
    if ($userid > 0) { // have a valid userid
      if ($result = $dbConn->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"')) {              
        $row = $result->fetch_assoc(); // guaranteed to get only one row
        printStartOfHtml($dbConn);
        printUserEdit($dbConn, $row);              
      } else { error($dbConn, 150000); } // select query did work
    } else { error($dbConn, 150001); } // have a valid userid
  } elseif ($doSafe == 1) { // delete an existing user
    // TODO: might want to verify the pw before deleting an account?
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
    $styleIdFromGet = safeIntFromExt('GET', 'styleId', 2); // this is an integer, range 0 to 99
    if (updateStyleId($dbConn, $userid, $styleIdFromGet)) {
      redirectRelative('editUser.php'); // stay on the page (without any do-command).
    } // else: I don't do anything. Error msgs are printed on-screen already
  } else { 
    error($dbConn, 150002);
  } // switch  
  printFooter($dbConn);
?>
