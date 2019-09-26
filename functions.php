<?php declare(strict_types=1);
// This file is a pure function definition file. It is included in other sites

// function list: 
// 20 - initialize ()
// 21 - printConfirm ($dbConn, string $heading, string $text): void
// 22 - printErrorAndDie (string $heading, string $text): void
// 23 - 
// 24 - error ($dbConn, int $errorMsgNum): void  
// 25 - getCategory ($dbConn, int $userid, int $category): string
// 26 - printStartOfHtml ($dbConn): void
// 27 - printFooter ($dbConn): void
// 28 - overlayDiv (bool $disappearing, int $zIndex, string $text): void
// 29 - printOverlayGeneric ($dbConn, int $messageNumber): void    
// 30 - printOverlayAccountVerify ($dbConn, int $userid): void
// 31 - getCurrentSite ()
// 32 - printNavMenu ($dbConn): void
// 33 - isNotTestUser ($dbConn, int $userid): bool
// 34 - sessionAndCookieDelete (): void
// 35 - deleteUser ($dbConn, int $userid): bool
// 36 - getUserid (): int
// 37 - makeSafeInt ($unsafe, int $length): int
// 38 - makeSafeHex ($unsafe, int $length): string
// 39 - makeSafeStr ($unsafe, int $length): string
// 40 - redirectRelative (string $page): void
// 41 - printStatic ($dbConn): void
// 42 - printInlineCss ($dbConn): void   
// 43 - getLanguage ($dbConn, int $textId): string // NB: ln and id variables are safe
// 44 - updateUser ($dbConn, int $userid, bool $forgotPw): bool  
// 45 - safeIntFromExt (string $source, string $varName, int $length): int
// 46 - safeHexFromExt (string $source, string $varName, int $length): string
// 47 - getStyle(object $dbConn, int $userid, string $item): string {  
// 48 - styleDefTxt(int $subStyle, string $item): string {
// 49 - styleDefBgImg(int $subStyle): string {
// 50 - styleDefBri(int $subStyle): string {

  
// this function is called on every (user related) page on the very start  
// it does the session start and opens connection to the data base. Returns the dbConn variable or a boolean
function initialize () {
  session_start(); // this code must precede any HTML output
  
  $siteSafe = getCurrentSite();   
  if ($siteSafe != 'about.php') { // on every other page than about, I need the userid already set
    if ($siteSafe != 'index.php') { // index is special, I might do forwarding when cookies are set
      if (!getUserid()) {
        // there might be two reasons: 
        // - user is connecting directly to links.php from where-ever (common case as you might store the links-page as bookmark). If so, just redirect to index.php
        // - session is really destroyed (e.g. user logged out). In this case, print an error message
        if ($siteSafe == 'links.php') { // redirect to index
          redirectRelative('index.php');
          return false;  // this code is not reached because redirect does an exit but it's anyhow cleaner like this
        }        
        printErrorAndDie('Login error', 'You might want to go to <a href="index.php">the start page</a>');
      }
    }
  }  
  require_once('php/dbConn.php'); // this will return the $dbConn variable as 'new mysqli'
  if ($dbConn->connect_error) {
    printErrorAndDie('Connection to the data base failed', 'Please try again later and/or send me an email: sali@widmedia.ch');
  }
  $dbConn->set_charset('utf8');
  return $dbConn;
}

//prints the h4 title and one row
function printConfirm (object $dbConn, string $heading, string $text): void {
  if (!headers_sent()) {
    printStartOfHtml($dbConn);
  } // headers
  echo '<div class="row twelve columns textBox"><h4>'.$heading.'</h4><p>'.$text.'</p></div>';
} 

// prints a valid html error page and stops php execution
function printErrorAndDie (string $heading, string $text): void {
  // cannot use printStatic as I don't have a dbConn
  echo '
<!DOCTYPE html><html><head>
  <meta charset="utf-8" />
  <title>Error page</title>
  <meta name="description" content="a generic error page" />  
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon.png" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/font.css" type="text/css" />
  <link rel="stylesheet" href="css/normalize.css" type="text/css" />
  <link rel="stylesheet" href="css/skeleton.css" type="text/css" />';
  printInlineCss((object)'');
  echo '</head><body><div class="row twelve columns textBox"><h4>'.$heading.'</h4><p>'.$text.'</p></div></body></html>';
  die();  
}

// checks whether not the test user and displays some very generic failure message
function error (object $dbConn, int $errorMsgNum): bool {  
  if (getUserid() != 2) { // no error is printed for the test user    
    printConfirm($dbConn, 'Error', getLanguage($dbConn,33).$errorMsgNum.getLanguage($dbConn,34).' sali@widmedia.ch');
  }
  return false; // always returning false to simplify coding. Can write "return error($dbCon, 1234);" which will return false.
}

// function returns the text of the category. If something does not work as expected, 0 is returned
function getCategory (object $dbConn, int $userid, int $category): string {
  if (!($result = $dbConn->query('SELECT `text` FROM `categories` WHERE userid = "'.$userid.'" AND category = "'.$category.'" LIMIT 1'))) {
    return '';
  }
  $row = $result->fetch_assoc();
  return $row['text'];    
} // function

// required for most use cases but for some I cannot print any HTML output before redirecting
function printStartOfHtml (object $dbConn): void {
  printStatic($dbConn);     
  $msgSafe = safeIntFromExt('GET', 'msg', 1);
  
  if ($msgSafe > 0) {
    echo '<body onLoad="overlayMsgFade();">'; 
    printOverlayGeneric($dbConn, $msgSafe); 
  } else {
    echo '<body>';
  }
  echo '<div class="brightness">';
  $userid = getUserid();
  $siteSafe = getCurrentSite();
  printNavMenu($dbConn, $userid, $siteSafe);  
  if ($userid == 2) { overlayDiv(false, 3, getLanguage($dbConn,105).' &nbsp;<a href="index.php?do=2#newUser" style="background-color:transparent; color:#000; text-decoration:underline;">'.getLanguage($dbConn,32).'</a>'); }  
  printOverlayAccountVerify($dbConn, $userid);  
  echo '<div class="section categories noBottom"><div class="container">';
}
 
// function does not return anything. Prints the footer at the end of a page. Output depends on the page we are at and whether the user is logged in  
function printFooter (object $dbConn): void {
  echo '</div>'; // close the container
  $siteSafe = getCurrentSite();
  if ($siteSafe == '') { // return value of getCurrentSite if not on a valid page
    echo '</div></div></body></html>'; // close the html page correctly
    return;
  }
  $linkBegin = '<a class="button differentColor" href=';
  $edit      = $linkBegin.'"edit.php"><img src="images/icon/edit.png" class="logoImg" alt="icon edit"> '.getLanguage($dbConn,27).'</a>';  
  $links     = $linkBegin.'"links.php"><img src="images/icon/links.png" class="logoImg" alt="icon links"> Links</a>';
  $about     = $linkBegin.'"about.php"><img src="images/icon/info.png" class="logoImg" alt="icon info"> '.getLanguage($dbConn,1).'</a>'; 
  $logout    = $linkBegin.'"index.php?do=1"><img src="images/icon/logout.png" class="logoImg" alt="icon logout"> Log out</a>';
  $login     = $linkBegin.'"index.php#login"><img src="images/icon/login.png" class="logoImg" alt="icon login"> Log in</a>';
  $newUser   = $linkBegin.'"index.php?do=2#newUser"><img src="images/icon/plus.png" class="logoImg" alt="icon new user"> '.getLanguage($dbConn,29).'</a>';
  $index     = $linkBegin.'"index.php?do=6"><img src="images/icon/home.png" class="logoImg" alt="icon home"> Home</a>';
  $blank     = '&nbsp;'; // I need at least a space because otherwise the div is not printed
  
  $loggedOut = (getUserid() == 0);  
  $loginOrLogout  = ($loggedOut) ? $login : $logout;
  $linksOrBlank   = ($loggedOut) ? $blank : $links;
  $newUserOrBlank = ($loggedOut) ? $newUser : $blank;
  
  $footerLinks = // two dimensional array. First dimension is working with keys, second one with index
    array( // current page    left    middle           right
      'index.php'    => array($about, $newUserOrBlank, $loginOrLogout),
      'about.php'    => array($index, $linksOrBlank,   $loginOrLogout),
      'links.php'    => array($edit,  $blank,          $logout),
      'edit.php'     => array($links, $blank,          $logout),      
      'admin.php'    => array($edit,  $blank,          $logout)
    );  
  echo '      
  <div class="section noBottom">
    <div class="container">
      <div class="row twelve columns"><hr /></div>
      <div class="row">
        <div class="four columns">'.$footerLinks[$siteSafe][0].'</div>
        <div class="four columns">'.$footerLinks[$siteSafe][1].'</div>
        <div class="four columns">'.$footerLinks[$siteSafe][2].'</div>
      </div>
    </div>
  </div>
</div></div></body></html>';
} // function

// displays a red-colored div, either disappearing or not
function overlayDiv (bool $disappearing, int $zIndex, string $text): void {
  $divId = '';
  if ($disappearing) { $divId = ' id="overlay" '; } // will disappear (using javascript) if the div gets an id
  echo '<div '.$divId.'class="overlayMessage" style="z-index: '.$zIndex.';">'.$text.'</div>';  
}

// prints some disappearing message box. used on links.php and index.php
function printOverlayGeneric ($dbConn, int $messageNumber): void {    
  if (($messageNumber >= 1) and ($messageNumber <= 7)) { 
    $message = getLanguage($dbConn,($messageNumber+18)); 
  } else { 
    $message = getLanguage($dbConn,26); 
  }  
  overlayDiv(true, 2, $message);  
}  

// prints a message when the email of this account has not been verified (I'm not checking whether I actually get a result. Just diplaying the message if 'verified' is 0)
function printOverlayAccountVerify ($dbConn, int $userid): void {
  if ($userid === 0) { // user is not logged in
    return;
  }
  if (!($result = $dbConn->query('SELECT `verified` FROM `user` WHERE `id` = "'.$userid.'"'))) {
    return; // no meaninful message to display
  }    
  $row = $result->fetch_row();
  if ($row[0] == 0) {
    overlayDiv(false, 4, getLanguage($dbConn,104));
  }
  return;  
} // function

// returns the current site in the format 'about.php' in a safe way. Any do=xy parameters are obmitted
function getCurrentSite (): string {  
  $siteUnsafe = substr($_SERVER['SCRIPT_NAME'],7); // SERVER[...] is something like /start/links.php (without any parameters)   
  if (
      ($siteUnsafe == 'about.php') or
      ($siteUnsafe == 'admin.php') or
      ($siteUnsafe == 'edit.php') or      
      ($siteUnsafe == 'index.php') or 
      ($siteUnsafe == 'link.php') or
      ($siteUnsafe == 'links.php')
     ) {
        return $siteUnsafe;
      }
  return ''; 
}

// prints the navigation menu on top left corner. Output depends on current_site and wheter one is logged in or not
// does set the language session variable as well
function printNavMenu (object $dbConn, int $userid, string $siteSafe): void {   
  $notLoggedIn = ($userid == 0);
  
  if (isset($_GET['ln'])) { // this means the user is changing the language. This has precedence over whatever    
    $getLnSafe = safeStrFromExt('GET','ln', 2);
      
    if (($getLnSafe == 'en') or ($getLnSafe == 'de')) { // those are valid values
      $_SESSION['ln'] = $getLnSafe;
      if ($userid > 0) { // user is logged in
        $dbConn->query('UPDATE `user` SET `ln` = "'.$getLnSafe.'" WHERE `id` = "'.$userid.'"');
      }      
    } // don't do anything for invalid values
  } else { // no GET, meaning nobody wants to change it    
    if ($userid > 0) { // use the db value when user is logged in
      if ($result = $dbConn->query('SELECT `ln` FROM `user` WHERE `id` = "'.$userid.'"')) { // not using a guard here because want to print the menu also when an error is happening
        $row = $result->fetch_row();    
        if (($row[0] == 'de') or ($row[0] == 'en')) {
          $_SESSION['ln'] = $row[0]; // set it to the data base value
        } // valid value in the data base. if not, I don't do anything
      } // query ok        
    } else { // user is not logged in
      if (!isset($_SESSION['ln'])) { // session var is not yet set
        $_SESSION['ln'] = 'de'; // don't have any other info, will set it to the default
      }
    }    
  }  
    
  $home   = ($siteSafe == 'index.php') ? '<li class="menuCurrentPage">Home</li>' : '<li><a href="index.php?do=6">Home</a></li>';
  $login  = ($notLoggedIn)             ? '<li><a href="index.php#login">- log in</a></li>' : '';
  $newAcc = ($notLoggedIn)             ? '<li><a href="index.php?do=2#newUser">- '.getLanguage($dbConn,29).'</a></li>' : '';
  $about  = ($siteSafe == 'about.php') ? '<li class="menuCurrentPage">'.getLanguage($dbConn,1).'</li>' : '<li><a href="about.php">'.getLanguage($dbConn,1).'</a></li>';
  $links  = ($siteSafe == 'links.php') ? '<li class="menuCurrentPage">Links</li>' : '<li><a href="links.php">Links</a></li>';
  $edit   = ($siteSafe == 'edit.php')  ? '<li class="menuCurrentPage">'.getLanguage($dbConn,27).'</li>' :  '<li><a href="edit.php">'.getLanguage($dbConn,27).'</a></li>';
  
  if ($notLoggedIn) { // remove the link, replace it with a strikethrough for those site where a login is a must
    $strikeThrough = ' style="text-decoration: line-through;"';
    $links = '<li'.$strikeThrough.'>Links</li>';
    $edit  = '<li'.$strikeThrough.'>'.getLanguage($dbConn,27).'</li>';
  } 
  if ($notLoggedIn) { $logOut = ''; } else { $logOut = '<li><a href="index.php?do=1">log out</a></li>'; }
    
  if(isset($_GET['do'])) { // don't want to present the language sel on pages which are not default pages, where form entries are processed or similar
    $languageSelection = ''; 
  } else {    
    $languageSelection = '<li>&nbsp;</li><li style="font-size:smaller"><a href="'.$siteSafe.'?ln=de">&nbsp;DE</a>&nbsp;&nbsp;&nbsp;<a href="'.$siteSafe.'?ln=en">EN&nbsp;</a></li>';
  }
  
  echo '
  <nav style="width:400px">
    <div id="menuToggle">
      <input type="checkbox">
      <span></span>
      <span></span>
      <span></span>
      <ul id="menu">
        '.$home.'
        '.$login.'
        '.$newAcc.'
        '.$about.'
        '.$links.'
        '.$edit.'
        '.$logOut.'
        '.$languageSelection.'
      </ul>
    </div>
  </nav>';
}

// checks whether userid is not 2 (= not test user)
function isNotTestUser (object $dbConn, int $userid): bool {
  if ($userid == 2) {    
    printConfirm($dbConn, getLanguage($dbConn,30), getLanguage($dbConn,31).' <a href="index.php?do=2#newUser">'.getLanguage($dbConn,32).'</a>');
    return false;
  } else {
    return true;
  }
}

// deletes the userid cookie and the userid session. 
// NB: Leaves all other session info (like the ln-variable, otherwise I cannot print the 'logout-successful' message in non-default language)
function sessionAndCookieDelete (): void {
  $_SESSION['userid'] = 0; // the most important one, make sure it's really 0
  setcookie('userIdCookie', '0', (time() - 42000), '/start/', 'widmedia.ch', true, true); // some big enough value in the past to make sure things like summer time changes do not affect it  
}

// does the db operations to remove a certain user. Does some checks as well
function deleteUser (object $dbConn, int $userid): bool {
  if (!($userid > 0)) { // have a valid userid
    return false;
  }
  if (!($result = $dbConn->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"'))) {
    return false;
  }
  // make sure this id actually exists and it's not id=1 (admin user) or id=2 (test user)
  $rowCnt = $result->num_rows;
  if (!(isNotTestUser($dbConn, $userid) and ($userid != 1))) { // admin has userid 1, test user has userid 2
    return false; // for userid = 1 there is no meaningful error message. But that's ok, it only affects the admin    
  }  
  if (!($rowCnt == 1)) {                
    return false;
  }
  $result_delLinks = $dbConn->query('DELETE FROM `links` WHERE `userid` = "'.$userid.'"');
  $result_delCategories = $dbConn->query('DELETE FROM `categories` WHERE `userid` = "'.$userid.'"');                  
  $result_delUser = $dbConn->query('DELETE FROM `user` WHERE `id` = "'.$userid.'"');
  
  if ($result_delLinks and $result_delCategories and $result_delUser) {
    return true;
  } else {
    return false; // should not reach this point
  }
}

// returns the userid integer from the session variable
function getUserid (): int {
  if (isset($_SESSION)) {
    return (int)$_SESSION['userid'];
  } else {
    return 0;  // rather return 0 (means userid is not valid) than false
  }
}

// returns a 'safe' integer. Return value is 0 if the checks did not work out
function makeSafeInt ($unsafe, int $length): int {  
  $unsafe = filter_var(substr($unsafe, 0, $length), FILTER_SANITIZE_NUMBER_INT); // sanitize a length-limited variable
  if (filter_var($unsafe, FILTER_VALIDATE_INT)) { 
    return (int)$unsafe;
  } else { 
    return 0;
  }  
}

// returns a 'safe' character-as-hex value
function makeSafeHex ($unsafe, int $length): string {  
  $unsafe = substr($unsafe, 0, $length); // length-limited variable  
  if (ctype_xdigit($unsafe)) {
    return (string)$unsafe;
  } else {
    return '0';
  }
}

// returns a 'safe' string. Not that much to do though for a string
function makeSafeStr ($unsafe, int $length): string {
  return (htmlentities(substr($unsafe, 0, $length))); // length-limited variable, HTML encoded
}

// does a (relative) redirect
function redirectRelative (string $page): void {
  // redirecting relative to current page NB: some clients require absolute paths
  $host  = $_SERVER['HTTP_HOST'];
  $uri   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');  
  header('Location: https://'.$host.htmlentities($uri).'/'.$page);
  exit;
}

// prints static header information and sets title and description depending on the page
function printStatic (object $dbConn): void {
  // description tag and title are different for every site  
  $siteSafe = getCurrentSite(); // NB: link.php is special as only in the error case a HTML site is generated
  
  // TODO: rewrite this as 2d-array
  if ($siteSafe == 'about.php') {
    $title = getLanguage($dbConn,1);
    $description = getLanguage($dbConn,107);    
  } elseif ($siteSafe == 'admin.php') {
    $title = 'Adminseite';
    $description = 'Adminseite, nicht öffentlich';
  } elseif ($siteSafe == 'edit.php') {
    $title = getLanguage($dbConn,27);
    $description = getLanguage($dbConn,108);
  } elseif ($siteSafe == 'index.php') {  
    $title = 'Startpage';
    $description = getLanguage($dbConn,65);    
  } elseif ($siteSafe == 'links.php') {  
    $title = 'Links';
    $description = getLanguage($dbConn,110);    
  } else {
    $title = 'Error page';
    $description = 'page not found';    
  }
  
  $url = 'https://widmedia.ch/start/'.$siteSafe;

  echo '
<!DOCTYPE html>
<html lang="'.getLanguage($dbConn,111).'">
<head>
  <meta charset="utf-8" />
  <title>'.$title.'</title>
  <meta property="og:title" content="'.$title.'" />
  <meta name="author" content="Daniel Widmer" />
  <meta name="description" content="'.$description.'" />  
  <link rel="canonical" href="'.$url.'" />  
  <meta name="robots" content="index, follow" />    
  <meta property="og:description" content="'.$description.'" />
  <meta property="og:url" content="'.$url.'" />
  <meta property="og:type" content="website" />  
  <meta property="og:image" content="images/linkList900x600.jpg" />
  <meta property="og:image:type" content="image/jpeg" />
  <meta property="og:image:width" content="900" />
  <meta property="og:image:height" content="600" />
  <meta property="og:image" content="images/linkList800x800.jpg" />
  <meta property="og:image:type" content="image/jpeg" />
  <meta property="og:image:width" content="800" />
  <meta property="og:image:height" content="800" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon.png" />
  <link rel="stylesheet" href="css/font.css" type="text/css" />
  <link rel="stylesheet" href="css/normalize.css" type="text/css" />
  <link rel="stylesheet" href="css/skeleton.css" type="text/css" />';
  printInlineCss($dbConn);
  
  echo '
  <script>  
  function overlayMsgFade() {  // fades out a message and does a display: none when it is fully faded out
    element = document.getElementById("overlay");
    var op = 0.8;  // initial opacity
    var timer = setInterval(function () {
      if (op <= 0.3){
          clearInterval(timer);
          element.style.display = "none";
      }
      element.style.opacity = op;
      element.style.filter = "alpha(opacity=" + op * 100 + ")";
      op -= op * 0.05;
    }, 200);
  }
  </script>
  </head>';    
}

// defines all the styles with color in it. NB: borders are defined with the 1px solid #color shortcut in the skeleton css. Color attribute is then overwritten here
function printInlineCss (object $dbConn): void {  
  $userid = getUserid();
  
  $txtLight = 'rgba('.getStyle($dbConn, $userid, 'txtLight').')'; // yellowish (works good on blue, works on gray as well) = #faff3b;
  $txtDark =  'rgba('.getStyle($dbConn, $userid, 'txtDark').')'; // darker version of above settings  
  
  $bg_norm  = 'rgba('.getStyle($dbConn, $userid, 'bgNorm').')'; // default blueish
  $bg_norm2 = 'rgba('.getStyle($dbConn, $userid, 'bgNorm2').')'; // same color, different transparency for navMenu
  $bg_diff  = 'rgba('.getStyle($dbConn, $userid, 'bgDiff').')'; // default reddish 
  $bg_diff2 = 'rgba('.getStyle($dbConn, $userid, 'bgDiff2').')'; // same color, different transparency for overlay and borders  
  
  $bgImg = getStyle($dbConn, $userid, 'bgImg');
  $brightness = getStyle($dbConn, $userid, 'brightness');
    
  echo '
  <style>
    body { color: '.$txtLight.'; background-image: url("images/bg/'.$bgImg.'");}
    .brightness { background-color: rgba('.$brightness.'); }
    .button,
    button,
    input[type="submit"],
    input[type="button"] { background-color: '.$bg_norm.'; border-color: '.$txtDark.'; }
    .button:hover,
    button:hover,
    input[type="submit"]:hover,
    input[type="button"]:hover,
    .button:focus,
    button:focus,
    input[type="submit"]:focus,
    input[type="button"]:focus { color: '.$txtDark.'; background-color: '.$bg_diff.'; border-color: '.$bg_diff2.'; }
    .differentColor { background-color: '.$bg_diff.'; }
    .textBox { background-color: '.$bg_norm.'; border-color: '.$txtDark.'; }
    .overlayMessage { background-color: '.$bg_diff2.'; }
    .userStatBar { background-color: '.$bg_norm.'; border-color: '.$txtDark.'; }
    .imgBorder { border-color: '.$txtDark.'; }
    .tooltip,
    .tooltiptext { color: '.$txtLight.'; background-color: '.$bg_norm.'; }
    #menu { background-color: '.$bg_norm2.'; border-color: '.$txtDark.'; }
    #menu a:hover, #menu a:focus { color: '.$txtDark.'; }
    .menuCurrentPage { color: '.$txtDark.'; }
    #menuToggle input:checked ~ span { background: '.$txtLight.'; }
    .bgCol { background-color: '.$bg_norm.'; }
  </style>';
}

// returns various text in the session-stored language. language-db organized as follows: id(int_11) / en(text) / de(text)
function getLanguage (object $dbConn, int $textId): string { // NB: ln and id variables are safe
  $lang = 'de';
  if (isset($_SESSION['ln'])) {
    $lang = $_SESSION['ln'];
  }
  
  if ($result = $dbConn->query('SELECT `'.$lang.'` FROM `language` WHERE `id` = "'.$textId.'"')) {
    $row = $result->fetch_row();    
    return $row[0];
  } // no else case because can't do that much otherwise
  return '';
}

// 44. used in editUser to update email and password and in index to set a new pw when it has been forgotten.
function updateUser (object $dbConn, int $userid, bool $forgotPw): bool {  
  if (!(isNotTestUser($dbConn, $userid))) {
    return false;
  }
  if (!($result = $dbConn->query('SELECT * FROM `user` WHERE `id` = "'.$userid.'"'))) {
    return error($dbConn, 104404);
  }
  
  $row = $result->fetch_assoc(); // guaranteed to get only one row      
  $passwordUnsafe = safeStrFromExt('POST','password', 63);
  if (!(($forgotPw) or (password_verify($passwordUnsafe, $row['pwHash'])))) {        
    return error($dbConn, 104403);
  }
    
  $passwordUnsafe = filter_var(safeStrFromExt('POST','passwordNew', 63), FILTER_SANITIZE_STRING);
  if (strlen($passwordUnsafe) <= 3) {
    return error($dbConn, 104400);
  }
  $pwHash = password_hash($passwordUnsafe, PASSWORD_DEFAULT);
  
  // TODO: quiet ugly statements...
  $emailOk = false;
  if (!$forgotPw) {
    $emailUnsafe = filter_var(safeStrFromExt('POST','email', 127), FILTER_SANITIZE_EMAIL);
    // newEmail must not exist in the db (exclude current user itself)
    if (filter_var($emailUnsafe, FILTER_VALIDATE_EMAIL)) { // have a valid email 
      // check whether email already exists
      $emailSqlSafe = mysqli_real_escape_string($dbConn, $emailUnsafe);
      if (strcasecmp($emailSqlSafe, $row['email'])  != 0) { // 0 means they are equal
        if ($result = $dbConn->query('SELECT `verified` FROM `user` WHERE `email` LIKE "'.$emailSqlSafe.'" LIMIT 1')) {
          if ($result->num_rows == 0) {
            $emailOk = true; 
          }
        }
      } else { $emailOk = true; }; // no need to check again if the email did not change
    }
  }
    
  if ($emailOk) {
    if (!($dbConn->query('UPDATE `user` SET `pwHash` = "'.$pwHash.'", `email` = "'.$emailSqlSafe.'" WHERE `id` = "'.$userid.'"'))) {
      return error($dbConn, 104401);
    }
    return true;
  } else { 
    if (!$forgotPw) { 
      return error($dbConn, 104405);
    }
    if (!($dbConn->query('UPDATE `user` SET `pwHash` = "'.$pwHash.'" WHERE `id` = "'.$userid.'"'))) {
      return error($dbConn, 104402);      
    }
    return true;
  } // emailOK-else  
}

// checks whether a get/post/cookie variable exists and makes it safe if it does. If not, returns 0
function safeIntFromExt (string $source, string $varName, int $length): int {
  if (($source === 'GET') and (isset($_GET[$varName]))) {
    return makeSafeInt($_GET[$varName], $length);    
  } elseif (($source === 'POST') and (isset($_POST[$varName]))) {
    return makeSafeInt($_POST[$varName], $length);    
  } elseif (($source === 'COOKIE') and (isset($_COOKIE[$varName]))) {
    return makeSafeInt($_COOKIE[$varName], $length);  
  } else {
    return 0;
  }
}

// same as int above...
function safeHexFromExt (string $source, string $varName, int $length): string {
 if (($source === 'GET') and (isset($_GET[$varName]))) {
    return makeSafeHex($_GET[$varName], $length);
  } elseif (($source === 'POST') and (isset($_POST[$varName]))) {
    return makeSafeHex($_POST[$varName], $length);
  } elseif (($source === 'COOKIE') and (isset($_COOKIE[$varName]))) {
    return makeSafeHex($_COOKIE[$varName], $length);
  } else {
    return '0';
  }
}

// same as hex above...
function safeStrFromExt (string $source, string $varName, int $length): string {
 if (($source === 'GET') and (isset($_GET[$varName]))) {
    return makeSafeStr($_GET[$varName], $length);
  } elseif (($source === 'POST') and (isset($_POST[$varName]))) {
    return makeSafeStr($_POST[$varName], $length);
  } elseif (($source === 'COOKIE') and (isset($_COOKIE[$varName]))) {
    return makeSafeStr($_COOKIE[$varName], $length);
  } else {
    return '';
  }
}

// returns the style item (an image name or a color code) 
function getStyle(object $dbConn, int $userid, string $item): string {  
  if (isset($_SESSION['style'])) {
    $style = $_SESSION['style'];
  } else {
    $style = '0'.rand(1,7).'/00/00'; // default value, also for all non-logged in users
    $_SESSION['style'] = $style; // store it for this session. Otherwise every new site needs to load a new bgImg
  }
  
  if ($userid > 0) { // logged in users, overrides the session info
    if ($result = $dbConn->query('SELECT `style` FROM `user` WHERE `id` = "'.$userid.'"')) {
      $row = $result->fetch_row();
      $style = $row[0];
    }
  }
  $styles = explode('/', $style); // style is divided into 3 pieces (img-bri-txt = 00/00/00), two digit numbers, divided by /
  
  if ($item == 'bgImg') {
    return styleDefBgImg((int)$styles[0]);
  } elseif ($item == 'brightness') {
    return styleDefBri((int)$styles[1]);  
  } else {
    return styleDefTxt((int)$styles[2], $item);
  }
}

// input: a style id (number from 1 to x, 0 is valid as well), output: a color string
function styleDefTxt(int $subStyle, string $item): string {
  // following styles items are defined. The default values of the items are:
  $bgNorm   = '  0,113,255, 0.40';
  $bgNorm2  = '  0,113,255, 0.80';
  $bgDiff   = '255, 47, 25, 0.30';
  $bgDiff2  = '255, 47, 25, 0.60';
  $txtLight = '250,255, 59, 0.85';
  $txtDark  = '182,189,  0, 0.85';  
    
  $styles = // two dimensional array. First dimension is working with keys, second one with index.
    array(// 0 = undefined, same as 1          2                  3                  4                
      'bgNorm'   => array($bgNorm,  $bgNorm,  '117, 89,217,0.60','191,23,37,0.40',  '0,  0,  0,0.50'),
      'bgNorm2'  => array($bgNorm2, $bgNorm2, '117, 89,217,0.80','191,23,37,0.80',  '0,  0,  0,0.80'),
      'bgDiff'   => array($bgDiff,  $bgDiff,  '210,242,141,0.50',' 0, 0, 0,0.30',   '71,95,36,0.60'),
      'bgDiff2'  => array($bgDiff2, $bgDiff2, '210,242,141,0.60',' 0, 0, 0,0.60',   '71,95,36,0.80'),
      'txtLight' => array($txtLight,$txtLight,'240,240,240,0.85','240,222,134,0.85','250,232,148,0.90'),
      'txtDark'  => array($txtDark ,$txtDark ,'180,180,180,0.85','174,158,81,0.85' ,'174,158,81,0.85')
    );
  return $styles[$item][$subStyle];          
}

// returns the image name matching the style number
function styleDefBgImg(int $subStyle): string {
  //               0         1         2            3           4          5          6           7        
  $styles = array('ice.jpg','ice.jpg','bamboo.jpg','water.jpg','pigs.jpg','monk.jpg','stone.jpg','smoke.jpg');
  return $styles[$subStyle];
}
// returns the brightness of the background-overlay. May be more dark or more bright.
function styleDefBri(int $subStyle): string {
  if ($subStyle == 0) { // the default, more dark. Same as styleDefBri(35);
    return '0,0,0,0.30';
  } else { // steps in 2% range, from dark to bright
    // input:  |  1        ... 25        ... 50  ... 75          ... 99          |
    // output: |  98%dark, ... 50% dark, ... 0%  ... 50% bright, ... 98% bright  |
    if ($subStyle < 51) { // from 1 to 50. darker
      $color = 0;
      $intensity = 1 - ($subStyle * 0.02);
    } else { // 51 to 99. brighter
      $color = 255;
      $intensity = ($subStyle - 50) * 0.02;
    }
    return $color.','.$color.','.$color.','.$intensity;
  }
}
