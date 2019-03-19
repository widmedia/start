 <?php
  // This file contains functions to be included in other blocks and rely heavily on the context around them
  
  // function to output several links in a formatted way
  // creating a div for every link and div-rows for every $module-th entry
  function printLinks($modulo, $divClass, $sqlString, $dbConnection) {
    if ($result = $dbConnection->query($sqlString)) {
      $counter = 0;
         
      while ($row = $result->fetch_assoc()) {
        echo $divClass."<a href=\"link.php?id=".$row["id"]."\" target=\"_blank\" class=\"button button-primary\">".
             $row["text"]."</a><span class=\"counter\">".$row["cntTot"]."</span></div>\n";
        $counter++;

        if (($counter % $modulo) == 0) {
          echo "</div><div class=\"row\">";
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
    $sqlString = "SELECT * FROM `titels` WHERE userid = ".$userid." AND category = ".$category." LIMIT 1";
    if ($result = $dbConnection->query($sqlString)) {
      $row = $result->fetch_assoc();
      $result->close(); // free result set
      
      return ($row["text"]);      
    } // if  
  } // function
?>                
