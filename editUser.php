<?php declare(strict_types=1);
  require_once('functions.php');
  $dbConn = initialize();
  
  function printUserEdit (object $dbConn, $row): void {    
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
    <h3 class="section-heading"><span class="bgCol">Email / '.getLanguage($dbConn,84).'</span></h3>
    <form action="editUser.php?do=8" method="post">        
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
    </form>
    <div class="row twelve columns"><hr /></div>    
    <h3 class="section-heading"><span class="bgCol" id="bgImg">'.getLanguage($dbConn,122).'</span></h3>';
    for ($i = 0; $i < 7; $i++) { // 7 different bg images, 0=default, 1..7 are selectable
      if (($i % 4) == 0) { echo '<div class="row">'; }
      echo '<div class="three columns u-max-full-width"><a href="editUser.php?do=9&styleBgImg='.($i+1).'#bgImg" style="background-color:transparent;"><img src="images/bg/'.styleDefBgImg(($i+1)).'" alt="default background image" style="'.$bgBorderSel[($i+1)].' width:100%; vertical-align:middle;"></a></div>';
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
    <div class="row twelve columns" style="width:100%;"><form action="editUser.php?do=9#brightness" method="post"><input onchange="this.form.submit()" type="range" min="1" max="99" value="'.$currentBrightness.'" class="slider" name="styleBri"></form></div>';
    
    echo '
    <div class="row twelve columns">&nbsp;</div>
    <h3 class="section-heading"><span class="bgCol" id="textStyle">Text</span></h3>        
    <div class="row">';
    for ($i = 1; $i < 5; $i++) { // 1..5 are selectable
      if ($i == 4) { echo '</div><div class="row">'; }
      echo '<div class="four columns"><a href="editUser.php?do=9&styleTxt='.$i.'#textStyle" style="background-color:transparent;"><input name="create" type="submit" value="'.$txtSel[$i].'" 
      style="background-color: rgba('.styleDefTxt($i, 'bgNorm').'); color: rgba('.styleDefTxt($i, 'txtLight').');"></a></div>';
    }
    echo '</div>'; // row
    
    echo '<div class="row twelve columns"><hr /></div>    
    <h3 class="section-heading"><span class="bgCol">Sprache / Language</span></h3>
    <div class="row">
      <div class="six columns"><span class="bgCol">English:</span><a href="editUser.php?ln=en">&nbsp;EN&nbsp;</a></div>
      <div class="six columns"><span class="bgCol">Deutsch:</span><a href="editUser.php?ln=de">&nbsp;DE&nbsp;</a></div>
    </div>
    <div class="row twelve columns"><hr /></div>
    <div class="row twelve columns">&nbsp;</div>
    <div class="row twelve columns"><a href="editUser.php?do=7" class="button differentColor" style="white-space:normal; height:auto; min-height:38px;"><img src="images/icon/delete.png" class="logoImg" alt="icon delete"> '.getLanguage($dbConn,52).'</a></div>
    ';
  } // function
  
  // updates the 'style' column in the data base
  function updateSubStyle(object $dbConn, int $userid, int $subStyleFromGet, int $subStyleNr): bool {
    if (!($userid > 0)) { // have a valid userid, testuser may change it as well
      return error($dbConn, 150300);
    }
    if (!(    
    (($subStyleNr == 0) and ($subStyleFromGet <= 7) and ($subStyleFromGet > 0)) or // currently valid image IDs from 1 to 7
    (($subStyleNr == 1) and ($subStyleFromGet <= 99) and ($subStyleFromGet > 0)) or // brightness 1 to 99 is ok
    (($subStyleNr == 2) and ($subStyleFromGet <= 5) and ($subStyleFromGet > 0))
    )) {
      return error($dbConn, 150301);
    }
    // testUser may not set the brightness to extreme values
    if (($subStyleNr == 1) and (($subStyleFromGet > 80) or ($subStyleFromGet < 20))) {
      if (!(isNotTestUser($dbConn, $userid))) {
        return false;
      }
    }
    if (!($result = $dbConn->query('SELECT `style` FROM `user` WHERE `id` = "'.$userid.'"'))) {
      return error($dbConn, 150302);
    }
    if (!($result->num_rows == 1)) {
      return error($dbConn, 150303);
    }
    $row = $result->fetch_assoc();
    $styles = explode('/', $row['style']);
    $styles[$subStyleNr] = $subStyleFromGet; // the update operation
    $style = implode('/', $styles);
    
    if (!($result = $dbConn->query('UPDATE `user` SET `style` = "'.$style.'" WHERE `id` = "'.$userid.'"'))) {
      return error($dbConn, 150304);
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
  } elseif ($doSafe == 7) { // delete an existing user
    // TODO: might want to verify the pw before deleting an account?
    if (deleteUser($dbConn, $userid)) {
      sessionAndCookieDelete();
      printStartOfHtml($dbConn);
      printConfirm($dbConn, getLanguage($dbConn,53), getLanguage($dbConn,54).$userid.' <br/><br/><a class="button differentColor" href="index.php">'.getLanguage($dbConn,55).' index.php</a>');
    } else { error($dbConn, 150100); } // deleteUser function did return false
  } elseif ($doSafe == 8) { // update an existing user: db operations
    if ($userid > 0) { // have a valid userid
      if (updateUser($dbConn, $userid, false)) { 
        redirectRelative('links.php?msg=6');
      } else { error($dbConn, 150200); }
    } else { error($dbConn, 150201); } // have a valid userid         
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
      redirectRelative('editUser.php'); // stay on the page (without any do-command).
    } // else: I don't do anything. Error msgs are printed on-screen already
  } else { 
    error($dbConn, 150002);
  } // switch  
  printFooter($dbConn);
?>
