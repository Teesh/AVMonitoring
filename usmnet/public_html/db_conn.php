<?php
  define("ROOT","/var/www/html/usm.illinois.edu"); // web root folder
  require_once(ROOT . '/resources/config.php'); // config file outside web root
  // Not sure how this will work outside this timezone.  The timestamps on the server and the clients need to be synced
  date_default_timezone_set('America/Chicago');
  // The variables in the section below are pulled from a config file outside the web root.  DO NOT HARD CODE KEYS HERE
  // TODO: either use environment variables or AWS keys manager for handling these passwords
  $servername = $config["db"]["host"];
  $username = $config["db"]["username"];
  $password = $config["db"]["password"];
  $dbname = $config["db"]["dbname"];
  $lens_user = $config["lens"]["user"];
  $lens_pw = $config["lens"]["password"];
  $ca_user = $config["ca"]["user"];
  $ca_pw = $config["ca"]["password"];
  $ipam_user = $config["ipam"]["user"];
  $ipam_pw = $config["ipam"]["password"];
  $extron_user = $config["extron_controller"]["user"];
  $extron_pw = $config["extron_controller"]["password"];
  $amx_user = $config["amx_controller"]["user"];
  $amx_pw = $config["amx_controller"]["password"];
  $smp_user = $config["extron_lecture_capture"]["user"];
  $smp_pw = $config["extron_lecture_capture"]["password"];
  $axis_user = $config["axis_camera"]["user"];
  $axis_pw = $config["axis_camera"]["password"];

  // Create MySQL connection
  $conn = mysqli_connect($servername, $username, $password,$dbname);

  // Check connection - if it fails, output will include the error message
  if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
  }
?>
