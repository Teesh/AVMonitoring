<?php
/*
  Old AJAX call to individually query Lens API by MAC addresses
  This function has been integrated into search.php
*/
  require_once('db_conn.php');

  $mac = preg_replace('[\W]',null,strtolower($_POST['mac']));
  $opts = array(
    'http'=>array('method'=>'GET',
                    'header' => "Authorization: Basic ".base64_encode("$lens_user:$lens_pw")
            )
  );
  $context = stream_context_create($opts);
  $req = 'https://lens-api.cites.illinois.edu/lens/uiuc-lens/ip_mac?mac='.$mac.'&content-type=text/x-json&dressings=ipm_ports,mpt_interface';
  $json = file_get_contents($req,false,$context);
  $data = json_decode($json,true);

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
    echo  '<table class="table table-bordered table-striped" style="width:100%"><tr><th>IP Address</th><td>'.$ip_address.'</td></tr>'.
          '<tr><th>Subnet Name</th><td>'.$subnet_name.'</td></tr>'.
          '<tr><th>Subnet</th><td>'.$subnet.'</td></tr>'.
          '<tr><th>Switch</th><td>'.$switch.'</td></tr>'.
          '<tr><th>Port</th><td>'.$port.'</td></tr>'.
          '<tr><th>Jack</th><td>'.$jack.'</td></tr>'.
          '<tr><th>Status</th><td>'.$status.'</td></tr>'.
          '<tr><th>Last Seen</th><td>'.$last_seen.'</td></tr></table>';
  }
?>
