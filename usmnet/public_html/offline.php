<?php
  /*
    Shows offline devices, sorting by those that show as DOWN first, then all the OFF devices
  */
  session_start();
  require_once('db_conn.php');

  $sort = $_SESSION['sort']['str'];
  $query = "SELECT * FROM device_info WHERE department = 'Technology Services' AND location IS NOT NULL AND (status = 'DOWN' OR status = 'OFF') ORDER BY status ASC";
  if($sort) $query .= ', '.$sort;
  if(!$result = mysqli_query($conn,$query)) {
    echo "Error: ".$conn->error;
  };

  while ($row = mysqli_fetch_assoc($result)) {
    include 'table.php';
  }
?>
