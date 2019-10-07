<?php
/*
  This creates datapoints for the dashboard example chart in exactly the way the Canvas chart needs it
  The options for colors of bars and such can be set in the arrays here
  TODO: Create more charts.  This file will need to be duplicated for multiple charts, not added to
*/
  require_once('db_conn.php');

  $query = "SELECT COUNT(mac_address),status FROM device_info WHERE department = 'Technology Services' AND location IS NOT NULL GROUP BY status";
  $result = mysqli_query($conn,$query);

  $dataPoints = [];
  while ($row = mysqli_fetch_assoc($result)) {
    array_push($dataPoints,array("status"=>$row["status"],"color"=>($row['status']=='UP'? 'green' : ($row['status']=='DOWN' ? 'red' : 'grey')),"y"=>$row['COUNT(mac_address)']));
  }
  echo json_encode($dataPoints, JSON_NUMERIC_CHECK);
?>
