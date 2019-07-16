<?php
  require_once('functions.php');
  $dbConnection = initialize();
  printStatic();
  echo '</head><body>';
  printNavMenu();
  echo '
  <div class="section categories noBottom">
    <div class="container">
      <h3 class="section-heading">'.getLanguage($dbConnection,1).'</h3>';
  echo '
      <div class="row">
        <div class="four columns u-max-full-width"><img src="images/myself.jpg" alt="'.getLanguage($dbConnection,2).' Daniel Widmer" class="imgBorder" style="width:100%;"></div>
        <div class="eight columns textBox">
          <h4>'.getLanguage($dbConnection,3).'</h4>
          <p>widmedia.ch/start '.getLanguage($dbConnection,4).' <a href="https://github.com/widmedia/start">github</a> (open source)</p>
          <p>'.getLanguage($dbConnection,5).': <a href="mailto:sali@widmedia.ch">sali@widmedia.ch</a></p>
        </div>        
      </div>
      <div class="row"><div class="twelve columns"><hr /></div></div>
      <div class="row">
        <div class="twelve columns textBox">
          <h4>'.getLanguage($dbConnection,6).'</h4>
          <p>'.getLanguage($dbConnection,7).'</p>
          <p>'.getLanguage($dbConnection,8).' <a href="https://github.com/widmedia/start">github '.getLanguage($dbConnection,9).'.</a></p>
          <p>'.getLanguage($dbConnection,10).'</p>
          <p>'.getLanguage($dbConnection,11).' <br> 
          '.getLanguage($dbConnection,12).' widmedia.ch '.getLanguage($dbConnection,13).'</p>
          <p>widmedia '.getLanguage($dbConnection,14).'</p>
        </div>        
      </div>
      <div class="row"><div class="twelve columns"><hr /></div></div>
      <div class="row">
        <div class="twelve columns textBox">
          <h4>'.getLanguage($dbConnection,15).'</h4>
          <p>'.getLanguage($dbConnection,16).'</p>          
        </div>        
      </div>
      <div class="row"><div class="twelve columns"><hr /></div></div>
      <div class="row">
        <div class="twelve columns textBox">
          <h4>'.getLanguage($dbConnection,17).'</h4>
          <p>'.getLanguage($dbConnection,18).' <a href="mailto:sali@widmedia.ch">sali@widmedia.ch</a></p>          
        </div>        
      </div>
    </div> <!-- /container -->';    
      printFooter(); 
    ?>                
  </div> <!-- /section categories -->
</body>
</html>
