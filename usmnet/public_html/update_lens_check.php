<?php
  // Sets the "Live Lens Check" session variable for when we do MAC address searches
  session_start();
  if($_POST['option'] == 'true') {
    $_SESSION['lens_check'] = 1;
  } else {
    $_SESSION['lens_check'] = 0;
  }
?>
