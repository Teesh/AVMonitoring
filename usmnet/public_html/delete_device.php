<?php
/*
  This AJAX call receives an array of MAC addresses that we want to delete
*/
  require_once('db_conn.php');

  $macs_list = $_POST['macs'];

  for($i=0;$i<count($macs_list);$i++) {
    $macs_list[$i] = preg_replace('[\W]',null,strtolower($macs_list[$i])); // lowercase and remove symbols
  }

  // delete the MAC address entries from both tables
  $query_physical = $conn->prepare("DELETE FROM physical_info WHERE mac_address = ?");
  $query_network = $conn->prepare("DELETE FROM network_info WHERE mac_address = ?");
  $query_physical->bind_param('s',$del_mac);
  $query_network->bind_param('s',$del_mac);

  // don't commit deletions unless all deletions are successful
  $conn->autocommit(FALSE);
  foreach($macs_list as $mac) {
    $del_mac = $mac;
    if(!($query_physical->execute() & $query_network->execute())) {
      echo 'Delete failed: '.$conn->mysqli_error();
      $conn->rollback();
      die;
    }
  }

  $conn->commit();
  echo "Successfully deleted";
?>
