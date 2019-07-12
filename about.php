<?php
  require_once('functions.php');
  session_start(); // this code must precede any HTML output  
  printStatic();
  echo '</head><body>';
  printNavMenu();
?>
  <div class="section categories noBottom">
    <div class="container">
      <h3 class="section-heading">About</h3>
      <div class="row">
        <div class="four columns u-max-full-width"><img src="images/myself.jpg" alt="some picture of Daniel Widmer" class="imgBorder" style="width:100%;"></div>
        <div class="eight columns textBox">
          <h4>Contact</h4>
          <p>widmedia.ch/start is developed by Daniel Widmer. Please find the complete code at <a href="https://github.com/widmedia/start">github</a> (open source)</p>
          <p>Contact (German, English): <a href="mailto:sali@widmedia.ch">sali@widmedia.ch</a></p>
        </div>        
      </div>
      <div class="row"><div class="twelve columns"><hr /></div></div>
      <div class="row">
        <div class="twelve columns textBox">
          <h4>Data privacy</h4>
          <p>Be aware: without password protection for your account, all your user data are openly visible and may be edited. When using the password protection, widmedia tries to secure your data as good as possible, however, widmedia cannot guarantee full protection.</p>
          <p>Only data required for the functionality of this website is stored. The data base layout and structure as well as the underlying code is available for inspection on the open source <a href="https://github.com/widmedia/start">github project.</a></p>
          <p>The data will not be sold or transferred otherwise to any external party.</p>
          <p>On the other hand, widmedia cannot guarantee regular backups of your data, your data might be deleted or get lost in a different way. <br> 
          Do not rely on <a href="https://widmedia.ch/start">widmedia.ch/start</a> as your only data source and do not store any sensitive information on this site.</p>
          <p>widmedia will not be held accountable for the material created, stored or available on this site, especially the links to external sites.</p>
        </div>        
      </div>
      <div class="row"><div class="twelve columns"><hr /></div></div>
      <div class="row">
        <div class="twelve columns textBox">
          <h4>External sources</h4>
          <p>No external sources are used.</p>          
        </div>        
      </div>
      <div class="row"><div class="twelve columns"><hr /></div></div>
      <div class="row">
        <div class="twelve columns textBox">
          <h4>Financing</h4>
          <p>...well, there is none. If you like to contribute, please contact me: <a href="mailto:sali@widmedia.ch">sali@widmedia.ch</a></p>          
        </div>        
      </div>
    </div> <!-- /container -->
    <?php    
      printFooter(); 
    ?>                
  </div> <!-- /section categories -->
</body>
</html>
