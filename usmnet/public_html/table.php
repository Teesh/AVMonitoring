<?php
  /*
    This file is included in other files whenever a card needs to be created for a MySQL result of a device
    This file only creates one card for a single result, so it usually needs to be included inside a loop through the results
    Many of the PHP variables needs have to be preset in the PHP files which includes this file, e.g. all the MySQL results as an associative array variable named $row
  */
  // this part colors the card based on the device type and includes Basic Auth based on authentication variables included from the config file
  $auth = 'href="http://'.$row['ip_address'].'"';
  switch($row['device_type']) {
    case 'Controller': $main_color = '#433333';
      if($row['manufacturer'] == "Extron") {
        $auth = 'href="'."http://".$extron_user.":".$extron_pw."@".$row['ip_address'].'"';
      } else if($row['manufacturer'] == "AMX") {
        $auth = 'href="'."https://".$amx_user.":".$amx_pw."@".$row['ip_address']."/web/root/login.xml".'"';
      }
      break;
    case 'Touch Panel': $main_color = '#433C33';
      if($row['manufacturer'] == "AMX") {
        $auth = 'href="'."https://".$amx_user.":".$amx_pw."@".$row['ip_address'].'"';
      }
      break;
    case 'Switcher': $main_color = '#433633';
      if($row['manufacturer'] == "Extron") {
        $auth = 'href="'."https://".$extron_user.":".$extron_pw."@".$row['ip_address'].'"';
      }
      break;
    case 'Audio DSP': $main_color = '#433F33';
      if($row['manufacturer'] == "Extron") {
        $auth = 'href="'."http://".$extron_user.":".$extron_pw."@".$row['ip_address']."/nortxe_index.html".'"';
      }
      break;
    case 'Wireless Collaboration': $main_color = '#434333';
      break;
    case 'AV over IP': $main_color = '#334333';
      if($row['manufacturer'] == "AMX") {
        $auth = 'onclick=svsi_login("'.$amx_user.'","'.$amx_pw.'","'.$row['ip_address'].'")';
      }
      break;
    case 'Lecture Capture': $main_color = '#333343';
      if($row['manufacturer'] == "Extron") {
        $auth = 'href="'."https://".$smp_user.":".$smp_pw."@".$row['ip_address']."/www/index.html".'"';
      }
      break;
    case 'PTZ Camera': $main_color = '#3C3343';
      if($row['manufacturer'] == "Axis") {
        $auth = 'href="'."https://".$axis_user.":".$axis_pw."@".$row['ip_address'].'"';
      }
      break;
    default : $main_color = '#343a40';
      break;
  }
  // this part just takes each of the columns of the result and places it in a multicolumn/multirow format on the card and includes links to resources where applicable
  echo '<div class="card text-white bg-dark '.($row['mac_address']==$old_mac ? 'mt-0' : 'mt-2').' rounded-0 device">
          <div class="card-body p-2" style="border-style:solid;border-width:1px;border-color:'.($row['status']=='UP'? 'green' : ($row['status']=='DOWN' ? 'red' : 'grey')).';border-right-width:20px;background-color:'.$main_color.'">
            <div class="container-fluid">

              <div class="row">
              <div style="margin-right:10px;width:2%">
                <span style="margin:5px" title="Copy" class="actions fas fa-copy"></span><br>
                <span style="margin:5px" '.(!$history ? 'title="Edit" class="actions fas' : '').' fa-pen"></span><br>
                <span style="margin:5px" '.(!$history ? 'title="Delete" class="actions far' : '').' fa-trash-alt"></span><br>
              </div>
                <div class="col">
                  <div class="row"><h5>
                    <span class="data loc">'.$row['location'].'</span>
                  </h5></div>
                  <div class="row">
                    <span class="data dev">'.$row['device_type'].'</span>
                  </div>
                  <div class="row">
                    <span class="data det">'.($history ? $row['timestamp'] : $row['details']).'</span>
                  </div>
                </div>
                <div class="col col1" style="display: '.($_SESSION['cols']['col1'] ? 'none' : 'inline').'">
                  <div class="row">';
                    if($row['mac_address']) echo '&nbsp&nbsp&nbsp&nbsp&nbsp<span class="data mac">'.strtoupper(implode(':',str_split($row['mac_address'],2))).'</span>';
                  echo '</div>
                  <div class="row">';
                    if($row['ip_address']) echo '<a target="_blank" '.$auth.'><span class="fas fa-lock-open"></span> <span class="ip">'.$row['ip_address'].'</span></a>';
                  echo '</div>
                  <div class="row">
                    &nbsp&nbsp&nbsp&nbsp&nbsp<span class="data dep">'.$row['department'].'</span>
                  </div>
                </div>
                <div class="col col2" style="display: '.($_SESSION['cols']['col2'] ? 'none' : 'inline').'">
                  <div class="row">
                    &nbsp&nbsp&nbsp&nbsp&nbsp<span class="data man">'.$row['manufacturer'].'</span>
                  </div>
                  <div class="row">
                    &nbsp&nbsp&nbsp&nbsp&nbsp<span class="data mod">'.$row['model_number'].'</span>
                  </div>
                  <div class="row">
                    &nbsp&nbsp&nbsp&nbsp&nbsp<span class="data sn">'.$row['serial_number'].'</span>
                  </div>
                </div>
                <div class="col col3" style="display: '.($_SESSION['cols']['col3'] ? 'none' : 'inline').'">
                  <div class="row">
                    &nbsp&nbsp&nbsp&nbsp&nbsp<span class="data ptag">'.$row['ptag_number'].'</span>
                  </div>
                  <div class="row">
                    &nbsp&nbsp&nbsp&nbsp&nbsp<span class="sub">'.$row['subnet_name'].'</span>
                  </div>
                  <div class="row">
                    &nbsp&nbsp&nbsp&nbsp&nbsp<span class="net">'.$row['subnet'].'</span>
                  </div>
                </div>
                <div class="col col4" style="display: '.($_SESSION['cols']['col4'] ? 'none' : 'inline').'">
                  <div class="row">';
                if($row['switch']) { echo '<a target="_blank" href="https://iris.cites.illinois.edu/?page=cord&switch='.$row['switch'].'.gw.uiuc.edu"><span class="fas fa-link"></span> <span class="sw">'.$row['switch'].'</span></a>'; }
                echo '</div>
                  <div class="row">';
                if($row['port'] != '') echo '<a target="_blank" href="https://iris.cites.illinois.edu/?switch='.$row['switch'].'.gw.uiuc.edu&page=macaddr&port='.$row['port'].'"><span class="fas fa-link"></span> <span class="port">'.$row['port'].'</span></a>';
                echo '</div>
                  <div class="row">
                    &nbsp&nbsp&nbsp&nbsp&nbsp<span class="jack">'.$row['jack'].'</span>
                  </div>
                </div>
                <div style="margin-right:10px;width:2%">
                  <span title="Refresh" style="margin:5px" class="actions fa fa-refresh"></span><br>
                  <span style="margin:5px" class="fas"></span><br>
                  <span title="History" style="margin:5px" class="actions fas fa-history"></span>
                </div>
              </div>
            </div>
          </div>
        </div>';
?>
