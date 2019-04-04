<?php
// This file contains functions to be included in other blocks and rely heavily on the context around them
  
// function to output several links in a formatted way
// creating a div for every link and div-rows for every $module-th entry
function printLinks($edit, $userid, $category, $dbConnection) {
  // TODO: change the ORDER BY. It should depend on the count (and maybe after that on the 'sort' column, especially important after resetting all counts)
  $sql = 'SELECT * FROM `links` WHERE userid = '.$userid.' AND category = '.$category.' ORDER BY `links`.`sort` ASC LIMIT 100';
    
  // Have 12 columns. Means with modulo 3, I have 'class four columns' and vice versa
  $modulo = 3;
  $divClass = '<div class="four columns linktext">';
  if ($category == 2) { // this category prints more dense
    $modulo = 4;
    $divClass = '<div class="three columns linktext">';      
  }
   
  if ($result = $dbConnection->query($sql)) {
    $counter = 0;        
    while ($row = $result->fetch_assoc()) {
      if ($edit) {
      echo $divClass.'<span class="editLeft"><a href="link.php?id='.$row['id'].'" target="_blank" class="button button-primary">'.$row['text'].'</a><span class="counter">'.$row['cntTot'].'</span></span>
           <span class="editRight">
             <div style="padding-bottom: 2px;"><a href="editLinks.php?id='.$row['id'].'&do=4"><img src="images/edit.png"   width="16" height="16" border="0"> edit</a></div>                   
             <div style="padding-top:    2px;"><a href="editLinks.php?id='.$row['id'].'&do=5"><img src="images/delete.png" width="16" height="16" border="0"> delete</a></div>
           </span></div>';          
      } else {
        echo $divClass.'<a href="link.php?id='.$row["id"].'" target="_blank" class="button button-primary">'.$row['text'].'</a><span class="counter">'.$row['cntTot'].'</span></div>';
      } // edit links?
      $counter++;

      if (($counter % $modulo) == 0) {
        echo '</div>'."\n".'<div class="row">';
      }
    } // while    
    $result->close(); // free result set
  } // if  
} // function 
  
 
// function returns the text of the category. If something does not work as expected, NULL is returned
function getCategory($userid, $category, $dbConnection) {
  // Data base is organised as follows:
  // SELECT * FROM `titels`
  // id	userid	category	text
  // 1 	1 	    1 	      News
  // 2 	1 	    2 	      Work
  // 3 	1 	    3 	      Div
  $sqlString = 'SELECT * FROM `titels` WHERE userid = '.$userid.' AND category = '.$category.' LIMIT 1';
  if ($result = $dbConnection->query($sqlString)) {
    $row = $result->fetch_assoc();
    $result->close(); // free result set
    
    return ($row['text']);      
  } // if  
} // function
  
// function does not return anything. Prints the footer at the end of a page. Output depends on the page we are at, given as input  
function printFooter($currentSite) {
   // default values. For index.php as current site
  $linkLeft = 'href="editLinks.php">&nbsp;edit&nbsp;';
  $linkRight = 'href="about.php">&nbsp;about&nbsp;';
  if ($currentSite == 'editLinks') {
      $linkLeft = 'href="index.php">&nbsp;home&nbsp;'; // linkRight stays default
  } elseif ($currentSite == 'about') {
      $linkLeft = 'href="index.php">&nbsp;home&nbsp;';
      $linkRight = 'href="editLinks.php">&nbsp;edit&nbsp;';
  }
  echo '      
  <div class="section noBottom">
    <div class="container">
      <div class="row">
        <div class="twelve columns"><hr /></div>
      </div>
      <div class="row">
        <div class="six columns"><a class="button differentColor" '.$linkLeft.'</a></div>
        <div class="six columns"><a class="button differentColor" '.$linkRight.'</a></div>
      </div>
    </div>
  </div>
  '; 
} // function
  
// returning a single row for the matching id. 
// NB: id will get sql-escaped, userid not.
function getSingleLinkRow ($id, $userid, $dbConnection) {
  // need an additional userid condition. May be ignored by SQL because `id` is a primary key?
  if($result = $dbConnection->query('SELECT * FROM `links` WHERE `userid` = '.$userid.' AND `id` = '.mysqli_real_escape_string($dbConnection, $id))) {
    $row = $result->fetch_assoc();
    return($row);
  } else { return false; }
} // function 


// does the session start and opens connection to the data base. Returns the dbConnection variable
function initialize ($page) {
  session_start(); // this code must precede any html output
  
  if ($page != 'index') { // on every other page than index, I need the userid already set
    if (!getUserid()) {
      die('login error'); // TODO: real error handling
    }
  }
  
  require_once('php/dbConnection.php'); // this will return the $dbConnection variable as 'new mysqli'
  if ($dbConnection->connect_error) { 
    die('Connection failed: ' . $dbConnection->connect_error); // TODO: real error handling
  }
  return($dbConnection);
}


// TODO: might want to verify username and pwd if the account is set to use pwd-protection
// otherwise, there will be a special link to switch between users
function verifyCredentials ($temporaryUserid) {  
  // TODO: userid is fixed. Currently only single user application...
  $_SESSION["userid"] = $temporaryUserid; 
}


// returns the userid integer
function getUserid () {
  return ($_SESSION["userid"]);
}
?>
































?>                
