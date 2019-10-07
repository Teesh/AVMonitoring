<?php
  /*
    Sets the MAC addresses currently being looked at in history into a session variable
    Allows for navigating away from the page without needing to reselect the devices to go back
  */
    session_start();
    echo var_dump($_POST['macs']);
    $_SESSION['history_macs'] = $_POST['macs'];
?>
