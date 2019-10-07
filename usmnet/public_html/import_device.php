<?php
/*
  This AJAX request receives a form object filled with the file data from
  It treats the data as CSV and imports columns with specific titles
*/
  require_once('db_conn.php');

  // file handler prep
  if ( 0 < $_FILES['file']['error'] ) {
    echo 'Error: ' . $_FILES['file']['error'] . '<br>';
  }
  else {
    $file = $_FILES['file']['name'];
    move_uploaded_file($_FILES['file']['tmp_name'], ROOT . '/resources/uploads/' . $file);
  }

  $file = fopen(ROOT . '/resources/uploads/' . $file,"r");
  if(!$file) {
    echo 'File failed to load';
    die;
  }

  // set up 2D array
  $rows = [[]];
  // get first line, presumably headers
  $header = fgetcsv($file);
  // array search for specific Headers and store their index
  $mac_idx = array_search("MAC Address",$header);
  if(!$mac_idx) {
    echo 'No MAC Address column specified';
    die;
  }
  $loc_idx = array_search("Location",$header);
  $man_idx = array_search("Manufacturer",$header);
  $mod_idx = array_search("Model Number",$header);
  $dev_idx = array_search("Device Type",$header);
  $det_idx = array_search("Additional Info",$header);

  $sn_idx = array_search("Serial Number",$header);
  $ptag_idx = array_search("PTAG Number",$header);
  $dep_idx = array_search("Department",$header);

  // finish file handling
  while(! feof($file)) {
    array_push($rows,fgetcsv($file));
  }
  fclose($file);

  // This query adds a new entry if the MAC address does not exist
  // Otherwise it fills in the value for that MAC address if it was NULL
  // Does not overwrite any data
  $query = $conn->prepare("INSERT INTO physical_info (location,manufacturer,model_number,device_type,mac_address,details,serial_number,ptag_number,department) VALUES (?,?,?,?,?,?,?,?,?)
      ON DUPLICATE KEY UPDATE location = IF(location IS NULL,VALUES(location),location),
      manufacturer = IF(manufacturer IS NULL,VALUES(manufacturer),manufacturer),
      model_number = IF(model_number IS NULL,VALUES(model_number),model_number),
      device_type = IF(device_type IS NULL,VALUES(device_type),device_type),
      details = IF(details IS NULL,VALUES(details),details),
      serial_number = IF(serial_number IS NULL,VALUES(serial_number),serial_number),
      ptag_number = IF(ptag_number IS NULL,VALUES(ptag_number),ptag_number),
      department = IF(department IS NULL,VALUES(department),department)
      ");
  $query->bind_param('sssssssss',$loc,$man,$mod,$dev,$mac,$det,$sn,$ptag,$dep);
  $count_success = 0;
  $count_fail = 0;
  $err_log = '';
  foreach($rows as $idx=>$row) {
    $mac = preg_replace('[\W]',null,strtolower(mysqli_real_escape_string($conn,$row[$mac_idx])));
    if(!$mac) {
      $count_fail++;
      $err_log .= $idx.": Missing MAC Address\n";
      continue;
    }
    // Things have to be NULL, not blank, for the above and a couple other queries to work properly
    $loc_idx !== false ? $loc = mysqli_real_escape_string($conn,$row[$loc_idx]) : $loc = NULL;
    $man_idx !== false ? $man = mysqli_real_escape_string($conn,$row[$man_idx]) : $man = NULL;
    $mod_idx !== false ? $mod = mysqli_real_escape_string($conn,$row[$mod_idx]) : $mod = NULL;
    $dev_idx !== false ? $dev = mysqli_real_escape_string($conn,$row[$dev_idx]) : $dev = NULL;
    $det_idx !== false ? $det = mysqli_real_escape_string($conn,$row[$det_idx]) : $det = NULL;
    $sn_idx !== false ? $sn = mysqli_real_escape_string($conn,$row[$sn_idx]) : $sn = NULL;
    $ptag_idx !== false ? $ptag = mysqli_real_escape_string($conn,$row[$ptag_idx]) : $ptag = NULL;
    $dep_idx !== false ? $dep = mysqli_real_escape_string($conn,$row[$dep_idx]) : $dep = NULL;

    if(!$man) $man = null;
    if(!$loc) $loc = null;
    if(!$mod) $mod = null;
    if(!$dev) $dev = null;
    if(!$det) $det = null;
    if(!$sn) $sn = null;
    if(!$ptag) $ptag = null;
    if(!$dep) $dep = null;

    $result = $query->execute();
    if(!$result) {
      $count_fail++;
      $err_log .= $idx.': '.msqyli_err().'\n';
    } else {
      $count_success++;
    }
  }
  echo $count_success.' device info added successfully. '.$count_fail.' lines failed to add. \n '.$err_log;
?>
