<?php
  // Updates how we want to be sorting our cards.
  // This is not akways simple 1 column sorts. We can create more advanced specific sorts based on the available data
  session_start();
  $option = $_POST['option'];
  switch($option) {
    // The ISNULL forces all null values to be at the bottom of the sort no matter which direction we sort in
    case "Building-Room": $sort = "ISNULL(location), location";
      $sort_idx = 0;
      break;
    case "Device Type": $sort = "ISNULL(device_type), device_type";
      $sort_idx = 1;
      break;
    case "MAC Address": $sort = "ISNULL(mac_address), mac_address";
      $sort_idx = 2;
      break;
    case "Make and Model": $sort = "ISNULL(manufacturer), manufacturer, ISNULL(model_number), model_number";
      $sort_idx = 3;
      break;
    case "IP Address": $sort = "ISNULL(ip_address), ip_address";
      $sort_idx = 4;
      break;
    case "Subnet Name": $sort = "ISNULL(subnet_name), subnet_name";
      $sort_idx = 5;
      break;
    case "Switch-Port": $sort = "ISNULL(switch), switch, ISNULL(port), port";
      $sort_idx = 6;
      break;
    case "Building-Jack": $sort = "ISNULL(location), SUBSTRING_INDEX(location,' ',1), ISNULL(jack), jack";
      $sort_idx = 7;
      break;
    case "Status": $sort = "ISNULL(status), status";
      $sort_idx = 8;
      break;
    case "Department": $sort = "ISNULL(department), department";
      $sort_idx = 9;
      break;
    case "Serial Number": $sort = "ISNULL(serial_number), serial_number";
      $sort_idx = 10;
      break;
    case "PTAG": $sort = "ISNULL(ptag_number), ptag_number";
      $sort_idx = 11;
      break;
  }
  $_SESSION['sort'] = array("str"=>$sort,"idx"=>$sort_idx);
?>
