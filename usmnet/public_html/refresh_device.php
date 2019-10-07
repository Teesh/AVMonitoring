<?php
  /*
    When refresh is pressed for a list of devices, get the Lens data and ping it again
  */
  require_once('db_conn.php');

  $macs_list = $_POST['macs'];
  for($i=0;$i<count($macs_list);$i++) {
    $macs_list[$i] = preg_replace('[\W]',null,strtolower($macs_list[$i]));
  }
  // Same Lens query as all the other ones
  $opts = array(
    'http'=>array('method'=>'GET',
                    'header' => "Authorization: Basic ".base64_encode("$lens_user:$lens_pw")
            )
  );
  $context = stream_context_create($opts);
  $query_refresh = $conn->prepare("INSERT INTO network_info (mac_address,ip_address,subnet_name,subnet,switch,port,jack,status) VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE timestamp = ?");
  $query_refresh->bind_param('sssssssss',$mac_address,$ip_address,$subnet_name,$subnet,$switch,$port,$jack,$status,$last_seen);
  foreach($macs_list as $mac) {
    $req = 'https://lens-api.cites.illinois.edu/lens/uiuc-lens/ip_mac?mac='.$mac.'&content-type=text/x-json&dressings=ipm_ports,mpt_interface';
    $json = file_get_contents($req,false,$context);
    $data = json_decode($json,true);

    foreach($data['objects']['ip_mac'] as $dev) {
      $mac_address = $dev["mac"];
      $ip_address = $dev['ip'];
      $subnet_name = $dev['subnet_name'];
      $subnet = $dev['subnet'];
      $date = new DateTime($dev['last_seen'], new DateTimeZone('UTC'));
      $date->setTimezone(new DateTimeZone('America/Chicago'));
      $last_seen = $date->format('Y-m-d H:i:s');
      $dev2 = $dev['ports'][0];
      if($dev2) {
        $switch = $data['objects']['mac_port'][$dev2]['device_name'];
        $port = $data['objects']['mac_port'][$dev2]['ifname'];
        $interface_id = $data['objects']['mac_port'][$dev2]['interface_id'];
        $jack = $data['objects']['interface'][$interface_id]['ifalias'];
        if($jack == '') $jack = '';
      } else {
        $switch = '';
        $port = '';
        $jack = '';
      }
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
      if(!$query_refresh->execute()) {
        echo "Error".$query_refresh->error;
        die;
      }
    }
  }
?>
