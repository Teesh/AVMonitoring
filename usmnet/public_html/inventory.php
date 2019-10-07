<?php
/*
  This page shows all units which are not listed for a specific location.
  Most of those are part of invetory and show offline, but some which are moved without being edited will show online
*/
  session_start();
  require_once('db_conn.php');

  $sort = $_SESSION['sort']['str'];
  $query = "SELECT * FROM device_info WHERE location IS NULL";
  if($sort) $query .= ' ORDER BY '.$sort;
  if(!$result = mysqli_query($conn,$query)) {
    echo "Error: ".$conn->error;
  };

  while ($row = mysqli_fetch_assoc($result)) {
    include 'table.php';
  }
?>
