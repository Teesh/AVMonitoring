<?php
//  Takes data from the add device form and enters it into the DB if the MAC is not a duplicate
  require_once('db_conn.php');

  $man = mysqli_real_escape_string($conn,$_POST['man']);
  $loc = mysqli_real_escape_string($conn,$_POST['loc']);
  $mod = mysqli_real_escape_string($conn,$_POST['mod']);
  $dev = mysqli_real_escape_string($conn,$_POST['dev']);
  $mac = preg_replace('[\W]',null,strtolower(mysqli_real_escape_string($conn,$_POST['mac'])));
  $det = mysqli_real_escape_string($conn,$_POST['det']);
  $sn = mysqli_real_escape_string($conn,$_POST['sn']);
  $ptag = mysqli_real_escape_string($conn,$_POST['ptag']);
  $dep = mysqli_real_escape_string($conn,$_POST['dep']);

  // enter nulls instead of blank strings for sorting purposes
  if(!$man) $man = null;
  if(!$loc) $loc = null;
  if(!$mod) $mod = null;
  if(!$dev) $dev = null;
  if(!$det) $det = null;
  if(!$sn) $sn = null;
  if(!$ptag) $ptag = null;
  if(!$dep) $dep = null;

  // MAC address is the primary key so query auto-detects duplicates
  $query = $conn->prepare("INSERT INTO physical_info (location,manufacturer,model_number,device_type,mac_address,details,serial_number,ptag_number,department) VALUES (?,?,?,?,?,?,?,?,?)");
  $query->bind_param('sssssssss',$loc,$man,$mod,$dev,$mac,$det,$sn,$ptag,$dep);

  if(!$query->execute()) {
    echo 'Add failed: '.$query->error;
    die;
  } else {
    echo 'Add successful';
  }

  // get Lens data for newly added MAC address, same script as main server script
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
