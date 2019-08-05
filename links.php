<?php
  require_once('functions.php');
  $dbConn = initialize();
  
  
    
  // function to output several links in a formatted way
  // creating a div for every link and div-rows for every $module-th entry
  // has a limit of 100 links per category
  function printLinks($dbConn, $userid, $category) {
      
    // Have 12 columns. Means with modulo 3, I have 'class four columns' and vice versa
    $modulo = 3;
    $divClass = '<div class="halbeReihe four columns linktext">';
    if ($category == 2) { // this category prints more dense
      $modulo = 4;
      $divClass = '<div class="halbeReihe three columns linktext">';      
    }

    if ($result = $dbConn->query('SELECT * FROM `links` WHERE userid = "'.$userid.'" AND category = "'.$category.'" ORDER BY `cntTot` DESC, `text` ASC LIMIT 100')) {
      $counter = 0;        
      while ($row = $result->fetch_assoc()) {
        $link = $row['link'];
        if (strlen($link) > 26) { $link = substr($link,0,23).'...'; }
        echo $divClass.'<a href="link.php?id='.$row['id'].'" target="_blank" class="button tooltip">'.$row['text'].'<span class="tooltiptext">'.$link.'</span></a><span class="counter">'.$row['cntTot'].'</span></div>';        
        $counter++;

        if (($counter % $modulo) == 0) {
          echo '</div>'."\n".'<div class="row">';
        }
      } // while    
      $result->close(); // free result set
    } // if  
  } // function   

  $userid = getUserid();
  printStartOfHtml($dbConn);
  
  echo '<h3 class="section-heading">'.getCategory($dbConn, $userid, 1).'</h3><div class="row">';
  printLinks($dbConn, $userid, 1);
  
  echo '</div><h3 class="section-heading">'.getCategory($dbConn, $userid, 2).'</h3><div class="row">';
  printLinks($dbConn, $userid, 2);
  
  echo '</div><h3 class="section-heading">'.getCategory($dbConn, $userid, 3).'</h3><div class="row">';
  printLinks($dbConn, $userid, 3);
  echo '</div>';
  printFooter($dbConn);
?>
