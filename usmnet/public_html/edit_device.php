<?php
/*
  This AJAX call takes in edited data for a single device entry
  If the MAC address has been altered, it deletes all data associated with the old MAC Address and adds in the new one
  Otherwise it updates the old MAC address entry with the new data
  Then it updates the network info for the device by running a Lens query
  TODO: Make this process more efficient or more it to the background so the user doesn't have to wait for it to complete in the UI
*/
  require_once('db_conn.php');

  // All the fields passed in for the edited device
  $man = mysqli_real_escape_string($conn,$_POST['man']);
  $loc = mysqli_real_escape_string($conn,$_POST['loc']);
  $mod = mysqli_real_escape_string($conn,$_POST['mod']);
  $dev = mysqli_real_escape_string($conn,$_POST['dev']);
  $mac = preg_replace('[\W]',null,strtolower(mysqli_real_escape_string($conn,$_POST['mac'])));
  $det = mysqli_real_escape_string($conn,$_POST['det']);
  $sn = mysqli_real_escape_string($conn,$_POST['sn']);
  $ptag = mysqli_real_escape_string($conn,$_POST['ptag']);
  $dep = mysqli_real_escape_string($conn,$_POST['dep']);
  // Old MAC address used to compare with current one to see if it was changed
  $old_mac = preg_replace('[\W]',null,strtolower(mysqli_real_escape_string($conn,$_POST['old_mac'])));

  // We want to change blank values to be changed to null values to allow for better sorting
  // MySQL allows nulls to be sorted to the bottom but not blanks
  if(!$man) $man = null;
  if(!$loc) $loc = null;
  if(!$mod) $mod = null;
  if(!$dev) $dev = null;
  if(!$det) $det = null;
  if(!$sn) $sn = null;
  if(!$ptag) $ptag = null;
  if(!$dep) $dep = null;

  // check if MAC address was changed, and delete old_mac if it was
  if($old_mac != $mac) {
    $query_physical = $conn->prepare("DELETE FROM physical_info WHERE mac_address = ?");
    $query_physical->bind_param('s',$old_mac);
    if(!$query_physical->execute()) {
      echo 'Delete failed: '.$conn->mysqli_error();
      $conn->rollback();
      die;
    }
  }

  // Insert or update on duplicate so we don't need two different queries for when we did and didn't delete
  $query = $conn->prepare("INSERT INTO physical_info (location,manufacturer,model_number,device_type,mac_address,details,serial_number,ptag_number,department) VALUES (?,?,?,?,?,?,?,?,?)
                            ON DUPLICATE KEY UPDATE location = VALUES(location), manufacturer = VALUES(manufacturer), model_number = VALUES(model_number),
                            device_type = VALUES(device_type), details = VALUES(details), serial_number = VALUES(serial_number),
                            ptag_number = VALUES(ptag_number), department = VALUES(department)");
  $query->bind_param('sssssssss',$loc,$man,$mod,$dev,$mac,$det,$sn,$ptag,$dep);

  if(!$query->execute()) {
    echo 'Edit failed: '.$query->error;
  } else {
    echo 'Edit successful';
  }

  // A copy of the Lens query from the main script to get network info on the newly added/edited devices
  $opts = array(
    'http'=>array('method'=>'GET',
                    'header' => "Authorization: Basic ".base64_encode("$lens_user:$lens_pw")
            )
  );
  $context = stream_context_create($opts);
  $req = 'https://lens-api.cites.illinois.edu/lens/uiuc-lens/ip_mac?mac='.$mac.'&content-type=text/x-json&dressings=ipm_ports,mpt_interface';
  $json = file_get_contents($req,false,$context);
  $data = json_decode($json,true);
  $query_new = $conn->prepare("INSERT INTO network_info (mac_address,ip_address,subnet_name,subnet,switch,port,jack,status) VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE timestamp = ?");
  $query_new->bind_param('sssssssss',$mac_address,$ip_address,$subnet_name,$subnet,$switch,$port,$jack,$status,$last_seen);
  foreach($data['objects']['ip_mac'] as $dev) {
    $mac_address = $dev["mac"];
    $ip_address = $dev['ip'];
    $subnet_name = $dev['subnet_name'];
    $subnet = $dev['subnet'];
    $last_seen = str_replace("T"," ",$dev['last_seen']);
    $dev2 = $dev['ports'][0];
    $switch = $data['objects']['mac_port'][$dev2]['device_name'];
    $port = $data['objects']['mac_port'][$dev2]['ifname'];
    $interface_id = $data['objects']['mac_port'][$dev2]['interface_id'];
    $jack = $data['objects']['interface'][$interface_id]['ifalias'];
    if($jack == '') $jack = 'Empty';
    if($ip_address != '') {
      exec("ping -c 1 -W 2 ".$ip_address,$output,$stat);
      if($stat == 0) {
        $status = 'UP';
        $last_seen = date('Y-m-d H:i:s',time());
      } else {
        $status = 'DOWN';
      }
    } else {
      $status = 'OFF';
    }
    if(!$query_new->execute()) {
      echo '/n Data failed to update: '.$query_new->error;
    }
  }
?>
