<?php
  // called when column selectors are clicked to toggle which columns should be visible
  session_start();
  $option = $_POST['option'];
  switch($option) {
    case "col1": $_SESSION['cols']['col1'] = !$_SESSION['cols']['col1'];
      break;
    case "col2": $_SESSION['cols']['col2'] = !$_SESSION['cols']['col2'];
      break;
    case "col3": $_SESSION['cols']['col3'] = !$_SESSION['cols']['col3'];
      break;
    case "col4": $_SESSION['cols']['col4'] = !$_SESSION['cols']['col4'];
      break;
  }
?>
