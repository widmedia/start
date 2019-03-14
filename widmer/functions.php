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
?>                
