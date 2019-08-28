<?php declare(strict_types=1);
  require_once('functions.php');
  $dbConn = initialize();
  
  // function to output several links in a formatted way
  // creating a div for every link and div-rows for every $module-th entry
  // has a limit of 100 links per category
  function printLinks($dbConn, int $userid, int $category): void {
    echo '<div class="row">';
    
    // Have 12 columns. Means with modulo 3, I have 'class four columns' and vice versa
    $modulo = 3;
    $divClass = '<div class="halbeReihe four columns linktext">';
    if ($category == 2) { // this category prints more dense
      $modulo = 4;
      $divClass = '<div class="halbeReihe three columns linktext">';      
    }

    if ($result = $dbConn->query('SELECT * FROM `links` WHERE userid = "'.$userid.'" AND category = "'.$category.'" ORDER BY `cntTot` DESC, `text` ASC LIMIT 100')) {
      if ($result->num_rows == 0) { // most probably a new user
        // TODO: category is a post variable otherwise
        // TODO: language of text
        echo '<div class="twelve columns linktext"><a href="editLinks.php?do=1&categoryInput='.$category.'" class="button tooltip linksButton">add your own link<span class="tooltiptext">https://widmedia.ch/start/editLinks.php</span></a></div>';        
      } else {
        $counter = 0;
        while ($row = $result->fetch_assoc()) {
          $link = $row['link'];
          if (strlen($link) > 26) { $link = substr($link,0,23).'...'; }
          echo $divClass.'<a href="link.php?id='.$row['id'].'" target="_blank" class="button tooltip linksButton">'.$row['text'].'<span class="tooltiptext">'.$link.'</span></a><span class="counter">'.$row['cntTot'].'</span></div>';
          $counter++;

          if (($counter % $modulo) == 0) {
            echo '</div>'."\n".'<div class="row">';
          }
        } // while    
      } // have at least one entry
      $result->close(); // free result set
    } // query 
    echo '</div>'; // class row
  } // function   

  $userid = getUserid();
  printStartOfHtml($dbConn);
  
  for ($category = 1; $category <= 3; $category++) {
    echo '<h3 class="section-heading">'.getCategory($dbConn, $userid, $category).'</h3>';
    printLinks($dbConn, $userid, $category);    
  }
  
  printFooter($dbConn);
?>
