<?php
/*
  This page loads for the History menu item
  The goal is to show additional stored details for the devices selected
  Currently we can grab old device statuses stored on the local DB and IPAM fixed address if one exists
  TODO: Rename this function to be more intuitive and allow for additonal/different info
  TODO: Find a way to cache the results for that navigating doesn't cause the page to recalculate the results
*/
  session_start();
  require_once('db_conn.php');
  // selected devices are kept in a session variable to allow refreshing and navigating without losing the selection
  $macs_list = $_SESSION['history_macs'];
  if($macs_list) {
    $history = 1; // set the history modifier for table.php
    for($i=0;$i<count($macs_list);$i++) {
      $macs_list[$i] = preg_replace('[\W]',null,strtolower($macs_list[$i])); // remove any symbols from MAC addresses and set to lowercase
    }
    foreach($macs_list as $mac) {
      // search the raw data tables for all info on MAC instead of the pre-created view with latest info
      $query = "SELECT * FROM physical_info p, network_info n WHERE n.mac_address = '".$mac."' AND n.mac_address = p.mac_address ORDER BY n.mac_address ASC, n.timestamp ASC";
      $result = mysqli_query($conn,$query);
      if(mysqli_num_rows($result)) {
        while ($row = mysqli_fetch_assoc($result)) {
          include 'table.php';
          $old_mac = $row['mac_address']; // allows grouping of the same MAC address
        }
        // get the fixed address in IPAM for this MAC address
        $curl = curl_init();
        $url = "https://ipam.illinois.edu/wapi/v2.7.3/fixedaddress?_return_type=json&_return_fields%2B=mac&mac=".implode(':',str_split($mac,2));
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_HTTPAUTH => CURLAUTH_ANY,
          CURLOPT_USERPWD => "$ipam_user:$ipam_pw",
          CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          console.log($err);
        } else {
          // add the fixed address data to the end of the current table group as a card
          $ipam_data = json_decode($response);
            echo '<div class="card text-white bg-dark m-0 rounded-0" style="opacity:.8">
                  <div class="card-body p-2" style="border-style:solid;border-width:1px">
                    <div class="container-fluid">
                      <div class="row">';
            if($ipam_data[0]->ipv4addr) {
              echo '<div class="col">IPAM Fixed Address: &nbsp;&nbsp;&nbsp;<a target="_blank" href="http://'.$ipam_data[0]->ipv4addr.'"><span class="fas fa-link"></span>'.$ipam_data[0]->ipv4addr.'</a>';
            } else {
              echo '<div class="col">No IPAM Fixed Address found';
            }
            echo        '</div>
                      </div>
                    </div>
                  </div>
                </div><hr>';
        }
      } else {
        echo '<h3>No results found</h3><hr>';
      }
    }
  } else {
    // if no selection available
    echo '<h3>Select items to display history</h3>';
  }
?>
