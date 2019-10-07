<?php
/*
  This file parses the search parameters, takes into account any utilities selected
  and returns a card for each row of result.
*/
  session_start();
  require_once('db_conn.php');

  $search = $_POST['input'];
  $sort = $_SESSION['sort']['str'];
  if($search) {
    $_SESSION['search'] = $search;
    // If the search parameter is a MAC address or most of a MAC address and the checkbox for live Lens searching MAC addresses is selected
    if(preg_match('#(?:[0-9a-f]{2}[:|-]{0,1}){3,6}#i',$search,$mac_list) && $_SESSION['lens_check']) {
      $opts = array(
        'http'=>array('method'=>'GET',
                        'header' => "Authorization: Basic ".base64_encode("$lens_user:$lens_pw")
                )
      );
      // Same lens search as all the others
      $context = stream_context_create($opts);
      $req = 'https://lens-api.cites.illinois.edu/lens/uiuc-lens/ip_mac?';
      foreach($mac_list as $mac) $req .= 'mac=~'.$mac.'&';
      $req .= 'content-type=text/x-json&dressings=ipm_ports,mpt_interface';
      $json = file_get_contents($req,false,$context);
      $data = json_decode($json,true);
      echo '<h5>Live Search:</h5><br>';
      foreach($data['objects']['ip_mac'] as $key=>$dev) {
        $row['location'] = 'Lens Entry';
        $row['device_type'] = $key;
        $row['mac_address'] = $dev["mac"];
        $row['ip_address'] = $dev['ip'];
        $row['subnet_name'] = $dev['subnet_name'];
        $row['subnet'] = $dev['subnet'];
        $last_seen = str_replace("T"," ",$dev['last_seen']);
        $dev2 = $dev['ports'][0];
        $row['switch'] = $data['objects']['mac_port'][$dev2]['device_name'];
        $row['port'] = $data['objects']['mac_port'][$dev2]['ifname'];
        $interface_id = $data['objects']['mac_port'][$dev2]['interface_id'];
        $row['jack'] = $data['objects']['interface'][$interface_id]['ifalias'];
        if($row['jack'] == '') $row['jack'] = 'Empty';
        if($row['ip_address'] != '') {
          exec("ping -c 1 -W 2 ".$row['ip_address'],$output,$stat);
          if($stat == 0) {
            $row['status'] = 'UP';
            $last_seen = date('Y-m-d H:i:s',time());
          } else {
            $row['status'] = 'DOWN';
          }
        } else {
          $row['status'] = 'OFF';
        }
        include 'table.php';
      }
      echo '<hr><h5>From Database:</h5><br>';
    }
    // Stuff already saved in the database
    // TODO: This search algorithm could be cleaned up and made to match order of operations better, but it is very efficient as it stands, which is why it was difficult to properly trim down
    $search_list = preg_split('/[,&]/',$search); // split search parameters on commas and ampersands
    foreach($search_list as $key=>$val) $search_list[$key] = preg_replace("(\|\s+)","|",preg_replace("#[^0-9a-z_| =!]#i",null,trim(strtolower($search_list[$key])))); // sanitize search parameters and handle pipes
    $query = "SELECT * FROM device_info WHERE mac_address IN "; // start building the query.  Each paramter will be a subquery
    foreach($search_list as $key=>$search_item) {
      if(strpos($search_item,'!=') !== false) { // handle NOT EQUAL operator
        $adv_search = preg_split('/!=/',$search_item);
        $query .= "(SELECT mac_address FROM device_info WHERE ".$adv_search[0];
        if(strtolower($adv_search[1]) == 'null') $adv_search[1] = null;
        if($adv_search[1]) {
          $query .= " NOT REGEXP '".$adv_search[1]."' AND mac_address IN "; // REGEXP natively handles pipe operators as OR
        } else {
          $query .= " IS NOT NULL AND mac_address IN ";
        }
      } else if(strpos($search_item,'=') !== false) { // hanlde EQUALS operator
        $adv_search = preg_split('/=/',$search_item);
        $query .= "(SELECT mac_address FROM device_info WHERE ".$adv_search[0];
        if(strtolower($adv_search[1]) == 'null') $adv_search[1] = null;
        if($adv_search[1]) {
          $query .= " REGEXP '".$adv_search[1]."' AND mac_address IN ";
        } else {
          $query .= " IS NULL AND mac_address IN ";
        }
      } else if(substr($search_item,0,1)=='!') { // handle NOT operator
        $search_item = substr($search_item,1);
        // this specific query format allows for full text searching without a full text index, and still returns results quickly
        // it is not very malleable, but it is efficient and it also handle pipes as OR operators natively (which is why order of operations is off in the search)
        $query .= "(SELECT mac_address FROM device_info WHERE (location NOT REGEXP '".$search_item."' AND
        manufacturer NOT REGEXP '".$search_item."' AND
        model_number NOT REGEXP '".$search_item."' AND
        device_type NOT REGEXP '".$search_item."' AND
        mac_address NOT REGEXP '".$search_item."' AND
        details NOT REGEXP '".$search_item."' AND
        ip_address NOT REGEXP '".$search_item."' AND
        subnet_name NOT REGEXP '".$search_item."' AND
        subnet NOT REGEXP '".$search_item."' AND
        switch NOT REGEXP '".$search_item."' AND
        port NOT REGEXP '".$search_item."' AND
        jack NOT REGEXP '".$search_item."' AND
        status NOT REGEXP '".$search_item."' AND
        serial_number NOT REGEXP '".$search_item."' AND
        ptag_number NOT REGEXP '".$search_item."' AND
        department NOT REGEXP '".$search_item."')
        AND mac_address IN ";
      } else { // plain search without NOT
        $query .= "(SELECT mac_address FROM device_info WHERE (location REGEXP '".$search_item."' OR
        manufacturer REGEXP '".$search_item."' OR
        model_number REGEXP '".$search_item."' OR
        device_type REGEXP '".$search_item."' OR
        mac_address REGEXP '".$search_item."' OR
        details REGEXP '".$search_item."' OR
        ip_address REGEXP '".$search_item."' OR
        subnet_name REGEXP '".$search_item."' OR
        subnet REGEXP '".$search_item."' OR
        switch REGEXP '".$search_item."' OR
        port REGEXP '".$search_item."' OR
        jack REGEXP '".$search_item."' OR
        status REGEXP '".$search_item."' OR
        serial_number REGEXP '".$search_item."' OR
        ptag_number REGEXP '".$search_item."' OR
        department REGEXP '".$search_item."')
        AND mac_address IN ";
      }
    }
    $query .= "(SELECT mac_address FROM device_info)";
    foreach($search_list as $item) $query .= ')';
    if($sort) $query .= ' ORDER BY '.$sort;
    $result = mysqli_query($conn,$query);
    // create a card for each result and also show a results count at the top
    if(mysqli_num_rows($result)) {
      echo '<div class="card text-white bg-dark mt-2 rounded-0">
              <div class="card-body p-2" style="border-style:solid;border-width:1px">
                <div class="container-fluid">
                  <div class="row">
                    <div class="col">Results: '.mysqli_num_rows($result).'
                    </div>
                  </div>
                </div>
              </div>
            </div>';
      while ($row = mysqli_fetch_assoc($result)) {
        include 'table.php';
      }
    } else {
      echo '<h3>No results found</h3>';
    }
  } else {
    echo '<h3>Search for something</h3>';
  }
?>
