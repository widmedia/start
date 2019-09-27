<?php declare(strict_types=1);
  require_once('functions.php');
  $dbConn = initialize();
      
  function printEntryPoint (object $dbConn, int $userid): void {
    echo '
    <h3 class="section-heading"><span class="bgCol">'.getLanguage($dbConn,35).'</span></h3>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row">';          
    for ($i = 1; $i <= 3; $i++) {
      echo '<div class="four columns"><form action="edit.php?do=1&categoryInput='.$i.'" method="post">      
      <input name="submit" type="submit" value="'.getLanguage($dbConn,36).getCategory($dbConn, $userid, $i).'"></form></div>';         
    }                
    echo '</div>
    <div class="row twelve columns"><hr></div>';
    echo '
    <h3 class="section-heading"><span class="bgCol">Email / '.getLanguage($dbConn,84).'</span></h3>
    <form action="edit.php?do=8" method="post">        
      <div class="row"><div class="twelve columns">&nbsp;</div></div>
      <div class="row">
        <div class="four columns"><span class="bgCol">Email:</span> </div>
        <div class="eight columns"><input name="email" type="email" maxlength="127" value="'.$row['email'].'" required size="20"></div>
      </div>
      <div class="row" id="pwOldRow">
        <div class="four columns"><span class="bgCol">'.getLanguage($dbConn,49).':</span></div>
        <div class="eight columns"><input name="password" type="password" maxlength="63" value="" required size="20"></div>
      </div>
      <div class="row" id="pwRow">
        <div class="four columns"><span class="bgCol">'.getLanguage($dbConn,50).':</span></div>
        <div class="eight columns"><input name="passwordNew" type="password" maxlength="63" value="" size="20"></div>
      </div>
      <div class="row twelve columns">&nbsp;</div>
      <div class="row twelve columns"><input name="create" type="submit" value="'.getLanguage($dbConn,51).'"></div>
    </form>';
    
    echo '<div class="row twelve columns"><hr /></div>    
    <h3 class="section-heading"><span class="bgCol">Sprache / Language</span></h3>
    <div class="row">
      <div class="six columns"><span class="bgCol">English:</span><a href="edit.php?ln=en">&nbsp;EN&nbsp;</a></div>
      <div class="six columns"><span class="bgCol">Deutsch:</span><a href="edit.php?ln=de">&nbsp;DE&nbsp;</a></div>
    </div>
    <div class="row twelve columns"><hr /></div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns"><a href="edit.php?do=7" class="button differentColor" style="white-space:normal; height:auto; min-height:38px;"><img src="images/icon/delete.png" class="logoImg" alt="icon delete"> '.getLanguage($dbConn,52).'</a></div>
    <div class="row twelve columns"><hr /></div>
    ';
        
    if (!($result = $dbConn->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"'))) {
      return;
    }
    $row = $result->fetch_assoc(); // guaranteed to get only one row    
    
    $styles = explode('/', $row['style']);
    $notSelBg = 'border: 2px dotted #000;';        
    $notSelTxt = getLanguage($dbConn,125); // "select this"
    
    $bgBorderSel = array($notSelBg,$notSelBg,$notSelBg,$notSelBg,$notSelBg,$notSelBg,$notSelBg,$notSelBg); // 1..7 are valid selectors, 0=undefined is valid as well
    $txtSel = array($notSelTxt,$notSelTxt,$notSelTxt,$notSelTxt,$notSelTxt,$notSelTxt); // // 1..5 are valid selectors, 0=undefined is valid as well
    
    $bgBorderSel[$styles[0]] = 'border: 2px solid #faff3b;';  // some bright color (not related to the designs)
    $currentBrightness = ($styles[1] == 0) ? 35 : $styles[1]; // default value is zero which matches to a brightness of 35
    if ($styles[2] == 0) { $styles[2] = 1; } // not yet set, default is style 1
    $txtSel[$styles[2]] = getLanguage($dbConn,126); // selected
    
    echo '    
    <h3 class="section-heading"><span class="bgCol" id="bgImg">'.getLanguage($dbConn,122).'</span></h3>';
    for ($i = 0; $i < 7; $i++) { // 7 different bg images, 0=default, 1..7 are selectable
      if (($i % 4) == 0) { echo '<div class="row">'; }
      echo '<div class="three columns u-max-full-width"><a href="edit.php?do=9&styleBgImg='.($i+1).'#bgImg" style="background-color:transparent;"><img src="images/bg/'.styleDefBgImg(($i+1)).'" alt="default background image" style="'.$bgBorderSel[($i+1)].' width:100%; vertical-align:middle;"></a></div>';
      if (($i == 3) or ($i == 6)) { // last one does not fit into modulo function ($i % 4) == 3
        echo '</div><div class="row twelve columns">&nbsp;</div>'; 
      } 
    }
    // have some inline CSS as it's used only on this site. NB: unclear whether all the browser directives are necessary
    echo '
    <style>
    .slider {
      -webkit-appearance: none;
      width: 100%;
      height: 12px;
      border: 1px solid black;
      border-radius: 5px;    
      background: linear-gradient(to right, black, white);
      outline: none;
      opacity: 0.7;
      -webkit-transition: .2s;
      transition: opacity .2s;
    }
    .slider:hover {
      opacity: 1; /* Fully shown on mouse-over */
    }
    /* The slider handle (use -webkit- (Chrome, Opera, Safari, Edge) and -moz- (Firefox) to override default look) */
    .slider::-webkit-slider-thumb {
      -webkit-appearance: none;
      appearance: none;
      width: 24px;
      height: 24px;
      border: 0;
      background: url("images/icon/brightness.png");
      cursor: pointer;
    }
    .slider::-moz-range-thumb {
      width: 24px;
      height: 24px;
      border: 0;
      background: url("images/icon/brightness.png");
      cursor: pointer;
    }
    </style>
    <div class="row twelve columns">&nbsp;</div>
    <h3 class="section-heading"><span class="bgCol" id="brightness">'.getLanguage($dbConn,124).'</span></h3>    
    <div class="row twelve columns" style="width:100%;"><form action="edit.php?do=9#brightness" method="post"><input onchange="this.form.submit()" type="range" min="1" max="99" value="'.$currentBrightness.'" class="slider" name="styleBri"></form></div>';
    
    echo '
    <div class="row twelve columns">&nbsp;</div>
    <h3 class="section-heading"><span class="bgCol" id="textStyle">Text</span></h3>        
    <div class="row">';
    for ($i = 1; $i < 5; $i++) { // 1..5 are selectable
      if ($i == 4) { echo '</div><div class="row">'; }
      echo '<div class="four columns"><a href="edit.php?do=9&styleTxt='.$i.'#textStyle" style="background-color:transparent;"><input name="create" type="submit" value="'.$txtSel[$i].'" 
      style="background-color: rgba('.styleDefTxt($i, 'bgNorm').'); color: rgba('.styleDefTxt($i, 'txtLight').');"></a></div>';
    }
    echo '</div>'; // row
    
    echo '<div class="row twelve columns"><hr /></div><div class="row twelve columns">&nbsp;</div>';
    
    printClickRanking($dbConn, $userid);
    if (!($result = $dbConn->query('SELECT `hideLinkCnt` FROM `user` WHERE `id` = "'.$userid.'" LIMIT 1'))) {
      return;
    }
    if (!($result->num_rows == 1)) {
      return;
    }
    $row = $result->fetch_row(); 
    $borderHideLinkCnt = array('border: 2px dotted #000;', 'border: 2px dotted #000;'); // hideLinkCnt may be 0 or 1. Initialize array with both being not selected
    $borderHideLinkCnt[$row[0]] = 'border: 2px solid #faff3b;';  // some bright color (not related to the designs)    
    echo '
    <div class="row" id="linkCounters">   
      <div class="six columns linktext" style="'.$borderHideLinkCnt[0].'"><a href="edit.php?do=6&hideLinkCnt=0#linkCounters" class="button tooltip linksButton" style="margin:5px 0px;">'.getLanguage($dbConn,132).'<span class="tooltiptext">'.getLanguage($dbConn,133).'</span></a><span class="counter">27</span></div>
      <div class="six columns linktext" style="'.$borderHideLinkCnt[1].'"><a href="edit.php?do=6&hideLinkCnt=1#linkCounters" class="button tooltip linksButton" style="margin:5px 0px;">'.getLanguage($dbConn,134).' <span style="color:white">'.getLanguage($dbConn,135).'</span> '.getLanguage($dbConn,132).'<span class="tooltiptext">'.getLanguage($dbConn,136).'</span></a></div>
    </div>';
    
    echo '<div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns"><a class="button differentColor" href="edit.php?do=3"><img src="images/icon/zero.png" alt="icon zero" class="logoImg"> '.getLanguage($dbConn,37).'</a></div>
    ';
    
  } // function
  
  // prints 1 row to either add a new link or edit an existing one  
  function printSingleLinkFields (object $dbConn, bool $doAdd, int $category, int $linkId, string $link, string $text): void {
    if ($doAdd) { // this means I edit a link
      $submitText = getLanguage($dbConn,38);      
      $deleteText = '';
    } else {
      $submitText = getLanguage($dbConn,39);
      $deleteText = '&nbsp;&nbsp;&nbsp;<a href="edit.php?id='.$linkId.'&do=4"><img src="images/icon/delete.png" alt="icon delete" class="logoImg"> '.getLanguage($dbConn,40).'</a>';
    }
    echo '
    <form action="edit.php?do=2&id='.$linkId.'&categoryInput='.$category.'" method="post">      
      <div class="row">
        <div class="four columns"><input name="link" type="url"  maxlength="1023" value="'.$link.'" required></div>
        <div class="four columns"><input name="text" type="text" maxlength="63"  value="'.$text.'" required></div>
        <div class="four columns"><input name="submit" type="submit" value="'.$submitText.'">'.$deleteText.'</div>
      </div>
    </form>';   
  } // function
  
  function printCategoryForm (object $dbConn, int $categorySafe, int $userid): void { 
    $heading = htmlspecialchars(getCategory($dbConn, $userid, $categorySafe)); // returns an empty string if it did not work correctly
    echo '<div class="row twelve columns">
    <form action="edit.php?do=5&categoryInput='.$categorySafe.'" method="post">
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
  
  // updates the 'style' column in the data base
  function updateSubStyle(object $dbConn, int $userid, int $subStyleFromGet, int $subStyleNr): bool {
    if (!($userid > 0)) { // have a valid userid, testuser may change it as well
      return error($dbConn, 160900);
    }
    if (!(    
    (($subStyleNr == 0) and ($subStyleFromGet <= 7) and ($subStyleFromGet > 0)) or // currently valid image IDs from 1 to 7
    (($subStyleNr == 1) and ($subStyleFromGet <= 99) and ($subStyleFromGet > 0)) or // brightness 1 to 99 is ok
    (($subStyleNr == 2) and ($subStyleFromGet <= 5) and ($subStyleFromGet > 0))
    )) {
      return error($dbConn, 160901);
    }
    // testUser may not set the brightness to extreme values
    if (($subStyleNr == 1) and (($subStyleFromGet > 80) or ($subStyleFromGet < 20))) {
      if (!(isNotTestUser($dbConn, $userid))) {
        return false;
      }
    }
    if (!($result = $dbConn->query('SELECT `style` FROM `user` WHERE `id` = "'.$userid.'"'))) {
      return error($dbConn, 160902);
    }
    if (!($result->num_rows == 1)) {
      return error($dbConn, 160903);
    }
    $row = $result->fetch_assoc();
    $styles = explode('/', $row['style']);
    $styles[$subStyleNr] = $subStyleFromGet; // the update operation
    $style = implode('/', $styles);
    
    if (!($result = $dbConn->query('UPDATE `user` SET `style` = "'.$style.'" WHERE `id` = "'.$userid.'"'))) {
      return error($dbConn, 160904);
    }    
    return true;
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
  // 7 - delete an existing user: db operations
  // 8 - update an existing user: db operations
  // 9 - change the background image: db operations

  // function list:
  // 20 - printEntryPoint (object $dbConn, int $userid): void
  // 21 - printSingleLinkFields (object $dbConn, bool $doAdd, int $category, int $linkId, string $link, string $text): void
  // 22 - printCategoryForm (object $dbConn, int $categorySafe, int $userid): void
  // 23 - addOrEditLink(object $dbConn, bool $linkOk, int $userid, int $categorySafe, int $idSafe, string $textSqlSafe, string $linkSqlSafe): bool
  // 24 - deleteLink(object $dbConn, int $userid, int $idSafe): bool
  // 25 - updateCategoryName(object $dbConn, string $textSqlSafe, int $userid, int $categorySafe): bool

  $userid = getUserid();
  
  // Form processing
  $doSafe = safeIntFromExt('GET', 'do', 2); // this is an integer (range 1 to 99) or non-existing
  $categorySafe = safeIntFromExt('GET', 'categoryInput', 1); // this is an integer (range 0 to 3) or non-existing
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
  } elseif ($doSafe == 7) { // delete an existing user
    // TODO: might want to verify the pw before deleting an account?
    if (deleteUser($dbConn, $userid)) {
      sessionAndCookieDelete();
      printStartOfHtml($dbConn);
      printConfirm($dbConn, getLanguage($dbConn,53), getLanguage($dbConn,54).$userid.' <br/><br/><a class="button differentColor" href="index.php">'.getLanguage($dbConn,55).' index.php</a>');
    } else { error($dbConn, 160700); } // deleteUser function did return false
  } elseif ($doSafe == 8) { // update an existing user: db operations
    if ($userid > 0) { // have a valid userid
      if (updateUser($dbConn, $userid, false)) { 
        redirectRelative('links.php?msg=6');
      } else { error($dbConn, 160800); }
    } else { error($dbConn, 160801); } // have a valid userid         
  } elseif ($doSafe == 9) { // update an existing user: style link
    // only one out of below 3 variables is set. Others will be 0
    $styleBgImgFromGet = safeIntFromExt('GET', 'styleBgImg', 1);
    $styleBriFromPost = safeIntFromExt('POST', 'styleBri', 2);
    $styleTxtFromGet = safeIntFromExt('GET', 'styleTxt', 1);
    if (
         (($styleBgImgFromGet > 0) and updateSubStyle($dbConn, $userid, $styleBgImgFromGet, 0)) or 
         (($styleBriFromPost > 0) and updateSubStyle($dbConn, $userid, $styleBriFromPost, 1)) or // range 1..99
         (($styleTxtFromGet > 0) and updateSubStyle($dbConn, $userid, $styleTxtFromGet, 2))
       ) 
    {
      redirectRelative('edit.php'); // stay on the page (without any do-command).
    } // else: I don't do anything. Error msgs are printed on-screen already  
  } else {
    error($dbConn, 160000);
  } // switch
  printFooter($dbConn);
?>
