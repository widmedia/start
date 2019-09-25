<?php declare(strict_types=1);
  require_once('functions.php');
  $dbConn = initialize();
  
  function printEntryPoint (object $dbConn, int $userid): void {
    echo '
    <h3 class="section-heading"><span class="bgCol">'.getLanguage($dbConn,35).'</span></h3>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row">';          
    for ($i = 1; $i <= 3; $i++) {
      echo '<div class="four columns"><form action="editLinks.php?do=1" method="post">
      <input name="categoryInput" type="hidden" value="'.$i.'">
      <input name="submit" type="submit" value="'.getLanguage($dbConn,36).getCategory($dbConn, $userid, $i).'"></form></div>';         
    }                
    echo '</div>
    <div class="row twelve columns"><hr></div>
    <div class="row twelve columns"><a class="button differentColor" href="editUser.php"><img src="images/icon/db.png" alt="icon data base" class="logoImg"> '.getLanguage($dbConn,28).'</a></div>
    <div class="row twelve columns"><hr></div>';
    printClickRanking($dbConn, $userid);
    echo '
    <div class="row twelve columns"><a class="button differentColor" href="editLinks.php?do=3"><img src="images/icon/zero.png" alt="icon zero" class="logoImg"> '.getLanguage($dbConn,37).'</a></div>
    <div class="row twelve columns"><hr></div>';
    if (!($result = $dbConn->query('SELECT `hideLinkCnt` FROM `user` WHERE `id` = "'.$userid.'" LIMIT 1'))) {
      return;
    }
    if (!($result->num_rows == 1)) {
      return;
    }
    $row = $result->fetch_row(); 
    $borderHideLinkCnt = array('border: 2px dotted #000;', 'border: 2px dotted #000;'); // hideLinkCnt may be 0 or 1. Initialize array with both being not selected
    $borderHideLinkCnt[$row[0]] = 'border: 2px solid #faff3b;';  // some bright color (not related to the designs)
    // bold part does not work
    echo '
    <div class="row" id="linkCounters">   
      <div class="six columns linktext" style="'.$borderHideLinkCnt[0].'"><a href="editLinks.php?do=6&hideLinkCnt=0#linkCounters" class="button tooltip linksButton" style="margin:5px 0px;">'.getLanguage($dbConn,132).'<span class="tooltiptext">'.getLanguage($dbConn,133).'</span></a><span class="counter">27</span></div>
      <div class="six columns linktext" style="'.$borderHideLinkCnt[1].'"><a href="editLinks.php?do=6&hideLinkCnt=1#linkCounters" class="button tooltip linksButton" style="margin:5px 0px;">'.getLanguage($dbConn,134).' <span style="color:white">'.getLanguage($dbConn,135).'</span> '.getLanguage($dbConn,132).'<span class="tooltiptext">'.getLanguage($dbConn,136).'</span></a></div>
    </div>';
  } // function
  
  // prints 1 row to either add a new link or edit an existing one  
  function printSingleLinkFields (object $dbConn, bool $doAdd, int $category, int $linkId, string $link, string $text): void {
    if ($doAdd) { // this means I edit a link
      $submitText = getLanguage($dbConn,38);      
      $deleteText = '';
    } else {
      $submitText = getLanguage($dbConn,39);
      $deleteText = '&nbsp;&nbsp;&nbsp;<a href="editLinks.php?id='.$linkId.'&do=4"><img src="images/icon/delete.png" alt="icon delete" class="logoImg"> '.getLanguage($dbConn,40).'</a>';
    }
    echo '
    <form action="editLinks.php?do=2&id='.$linkId.'" method="post">      
      <div class="row">
        <div class="four columns"><input name="link" type="url"  maxlength="1023" value="'.$link.'" required></div>
        <div class="four columns"><input name="text" type="text" maxlength="63"  value="'.$text.'" required></div>
        <div class="four columns"><input name="categoryInput" type="hidden" value="'.$category.'"><input name="submit" type="submit" value="'.$submitText.'">'.$deleteText.'</div>
      </div>
    </form>';   
  } // function
  
  function printCategoryForm (object $dbConn, int $categorySafe, int $userid): void { 
    $heading = htmlspecialchars(getCategory($dbConn, $userid, $categorySafe)); // returns an empty string if it did not work correctly
    echo '<div class="row twelve columns">
    <form action="editLinks.php?do=5" method="post"><input name="categoryInput" type="hidden" value="'.$categorySafe.'">
    <input name="text" type="text" maxlength="63" value="'.$heading.'" required> &nbsp;<input name="submit" type="submit" value="'.getLanguage($dbConn,41).'"></form><div>';
    echo '<div class="row twelve columns"><h3 class="section-heading"><span class="bgCol">'.getLanguage($dbConn,42).'</span></h3></div>';
    printSingleLinkFields($dbConn, true, $categorySafe, 0, 'https://', 'text');
    echo '<div class="row twelve columns"><hr></div>';
    // print one form per row, an edit form for every link
    if ($result = $dbConn->query('SELECT * FROM `links` WHERE `userid` = "'.$userid.'" AND `category` = "'.$categorySafe.'" ORDER BY `cntTot` DESC, `text` ASC LIMIT 100')) {
      while ($row = $result->fetch_assoc()) { // not an error if there is no row with this query
        printSingleLinkFields($dbConn, false, 0, (int)$row['id'], $row['link'], $row['text']); // category 0 means I'm editing an existing link
      } // while
    } // query ok    
  }
  
  function addOrEditLink(object $dbConn, int $userid, int $categorySafe, int $idSafe, string $textUnsafe, string $linkUnsafe): bool {
    if (!(filter_var($linkUnsafe, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED))) { // have a validUrl, require the http(s)://-part as well
      printConfirm($dbConn, getLanguage($dbConn,43), getLanguage($dbConn,44));
      return $error($dbConn, 160203); 
    }
    $linkSqlSafe = mysqli_real_escape_string($dbConn, $linkUnsafe); // filtering it for sqli insertion        
    $textSqlSafe = mysqli_real_escape_string($dbConn, $textUnsafe); // cannot verify anything for the text itself (just cut it to 63 characters)     
    if (!(isNotTestUser($dbConn, $userid))) {
      return false;
    }
    if ($categorySafe == 0) { // I update one link
      if (!($idSafe > 0)) { // this means I need a link id
        return error($dbConn, 160201);
      }
      if (!($dbConn->query('UPDATE `links` SET `text` = "'.$textSqlSafe.'", `link` = "'.$linkSqlSafe.'" WHERE `userid` = "'.$userid.'" AND `id` = "'.$idSafe.'" LIMIT 1'))) {
        return error($dbConn, 160200);
      }
      return true;
    } else { // I'm adding a new link
      if (!($result = $dbConn->query('INSERT INTO `links` (`userid`, `category`, `text`, `link`) VALUES ("'.$userid.'", "'.$categorySafe.'", "'.$textSqlSafe.'", "'.$linkSqlSafe.'")'))) {
        return error($dbConn, 160202);
      }
      return true;      
    } // distinction between adding and editing    
  }
  
  function deleteLink(object $dbConn, int $userid, int $idSafe): bool {
    if (!($idSafe > 0)) {
      return error($dbConn, 160403);
    }
    if (!(isNotTestUser($dbConn, $userid))) {
      return false;
    }
    if (!($result = $dbConn->query('SELECT * FROM `links` WHERE `userid` = "'.$userid.'" AND `id` = "'.$idSafe.'"'))) {
      return error($dbConn, 160402);
    }      
    if (!($result->num_rows == 1)) {
      return error($dbConn, 160401);
    }
    if (!($dbConn->query('DELETE FROM `links` WHERE `userid` = "'.$userid.'" AND `id` = "'.$idSafe.'"'))) { // should return true
      return error($dbConn, 160400);
    }
    return true;
  }

  function updateCategoryName(object $dbConn, string $textUnsafe, int $userid, int $categorySafe): bool {    
    if (!(isNotTestUser($dbConn, $userid))) {
      return false;
    }
    if (!($categorySafe > 0)) {
      return error($dbConn, 160501);
    }
    $textSqlSafe = mysqli_real_escape_string($dbConn, $textUnsafe);
    if (!($dbConn->query('UPDATE `categories` SET `text` = "'.$textSqlSafe.'" WHERE `userid` = "'.$userid.'" AND `category` = "'.$categorySafe .'" LIMIT 1'))) {
      return error($dbConn, 160500);
    }
    return true;
  }
  
  function storeLinkCounterSetting(object $dbConn, int $hideLinkCntSafe, int $userid): bool {
    if (!($userid > 0)) {
      return false;
    }
    if (!(($hideLinkCntSafe == 0) or ($hideLinkCntSafe == 1))) {
      return error($dbConn, 160600);
    }
    if (!($dbConn->query('UPDATE `user` SET `hideLinkCnt` = "'.$hideLinkCntSafe.'" WHERE `id` = "'.$userid.'" LIMIT 1'))) {
      return error($dbConn, 160601);
    }
    return true;
  }
  
  // generates a table output with the click ranking. Listed are the first three
  function printClickRanking (object $dbConn, int $userid): void {
    if (!($result = $dbConn->query('SELECT SUM(`cntTot`), `userid` FROM `links` WHERE `cntTot` > 0 GROUP BY `userid` ORDER BY SUM(`cntTot`) DESC'))) { //  LIMIT 3
      return; // cannot do much meaningful
    }
    // TODO: maybe exclude admin (id 1) and testuser (id 2)
    $numRows = $result->num_rows;
    
    $sumCntTot = array(0,0,0); // might have less than 3 users with non-zero values. Need to initialize
    $sumUserIds = array(0,0,0);
    $myRanking = 0;
    $myClicks = 0;
    $totalClicks = 0;
    for ($i = 0; $i < $numRows; $i++) {
      $row = $result->fetch_row();
      if ($i < 3) { // save the podium 
        $sumCntTot[$i] = $row[0];
        $sumUserIds[$i] = $row[1];
      }
      if ($userid == $row[1]) {
        $myClicks = $row[0];
        $myRanking = $i + 1; // results are ordered        
      }
      $totalClicks += $row[0]; // NB: this could also be done by SQL
    } // while
    
    // print a table with the podium
    echo '<h3 class="section-heading"><span class="bgCol">'.getLanguage($dbConn,127).'</span></h3>'; // Klick-Rangliste    
    $maxWidth = 300; // max width on mobile is about 300px, otherwise it messes up all the layout
    $widths = array($maxWidth, round($sumCntTot[1] / $sumCntTot[0] * $maxWidth)+1, round($sumCntTot[2] / $sumCntTot[0] * $maxWidth)+1);    
    for ($i = 0; $i < 3; $i++) {
      echo '<div class="row twelve columns">
      <span class="userStatBar" style="width:'.$widths[$i].'px;">'.($i+1).'. '.getLanguage($dbConn,131).': User-ID '.$sumUserIds[$i].'.&nbsp; <b>'.$sumCntTot[$i].'</b> Klicks</span></div>';
    }
    echo '    
    <div class="row twelve columns">&nbsp;</div>
    <div class="row">
      <div class="four columns"><span class="bgCol">'.getLanguage($dbConn,128).': '.$myClicks.'</span></div>
      <div class="four columns"><span class="bgCol">'.getLanguage($dbConn,129).': '.$myRanking.'</span></div>
      <div class="four columns"><span class="bgCol">'.getLanguage($dbConn,130).': '.$totalClicks.'</span></div>
    </div>
    <div class="row twelve columns">&nbsp;</div><div class="row twelve columns">&nbsp;</div>';
  }

    
  // page number for errorCode: 16
  // possible actions:
  // 0 - entry point: present category selection
  // 1 - present links of one category + text field for category + add-Link option
  // 2 - add one link to db or edit one link (of action 1)
  // 3 - reset all cnt to 0
  // 4 - delete one link
  // 5 - do the update of a category (of action 1)
  // 6 - store the linkCounterSetting

  // function list:
  // 20 - printEntryPoint (object $dbConn, int $userid): void
  // 21 - printSingleLinkFields (object $dbConn, bool $doAdd, int $category, int $linkId, string $link, string $text): void
  // 22 - printCategoryForm (object $dbConn, int $categorySafe, int $userid): void
  // 23 - addOrEditLink(object $dbConn, bool $linkOk, int $userid, int $categorySafe, int $idSafe, string $textSqlSafe, string $linkSqlSafe): bool
  // 24 - deleteLink(object $dbConn, int $userid, int $idSafe): bool
  // 25 - updateCategoryName(object $dbConn, string $textSqlSafe, int $userid, int $categorySafe): bool

  $userid = getUserid();
  
  // Form processing
  $doSafe = safeIntFromExt('GET', 'do', 1); // this is an integer (range 1 to 5) or non-existing
  $categorySafe = safeIntFromExt('POST', 'categoryInput', 1); // this is an integer (range 0 to 3) or non-existing
  $idSafe = safeIntFromExt('GET', 'id', 11); // this is an integer (max 11 characters) or non-existing. The link id
  $hideLinkCntSafe = safeIntFromExt('GET', 'hideLinkCnt', 1);
  
  // non-integer values are more complicated, text may be HTML-safe or sqli-safe
  $linkUnsafe = filter_var(safeStrFromExt('POST','link', 1023), FILTER_SANITIZE_URL);  // this is an url (max 1023 characters) or non-existing
  $textUnsafe = filter_var(safeStrFromExt('POST','text', 63), FILTER_SANITIZE_STRING); // this is a generic string (max 63 characters) or non-existing  

  
  if ($doSafe == 0) { // entry point of this site
    printStartOfHtml($dbConn);
    printEntryPoint($dbConn, $userid);
  } elseif ($doSafe == 1) { // present links of one category, have category name as text field
    printStartOfHtml($dbConn);
    printCategoryForm($dbConn, $categorySafe, $userid);
  } elseif ($doSafe == 2) { // add or edit a link
    if (addOrEditLink($dbConn, $userid, $categorySafe, $idSafe, $textUnsafe, $linkUnsafe)) { // distinction between adding or editing is done by the category: category = 0 means I'm editing a link
      if ($categorySafe == 0) { // update one link
        redirectRelative('links.php?msg=1');
      } else { // add a new link
        redirectRelative('links.php?msg=5');
      }
    }
  } elseif ($doSafe == 3) { // I want to reset all the link counters to 0
    if ($dbConn->query('UPDATE `links` SET `cntTot` = "0" WHERE `userid` = "'.$userid.'"')) { // should return true
      redirectRelative('links.php?msg=4');
    } else { error($dbConn, 160300); } // update query did work
  } elseif ($doSafe == 4) { // delete a link. Displaying a confirmation message
    if (deleteLink($dbConn, $userid, $idSafe)) {
      redirectRelative('links.php?msg=3');
    }
  } elseif ($doSafe == 5) { // update a category name
    if (updateCategoryName($dbConn, $textUnsafe, $userid, $categorySafe)) {
      redirectRelative('links.php?msg=2');
    }
  } elseif ($doSafe == 6) { // store the link counter setting
    if (storeLinkCounterSetting($dbConn, $hideLinkCntSafe, $userid)) {
      // TODO: maybe redirection to links.php is better? Result is visible but cannot change back directly
      printStartOfHtml($dbConn);
      printEntryPoint($dbConn, $userid);
    }
  } else {
    error($dbConn, 160000);
  } // switch
  printFooter($dbConn);
?>
