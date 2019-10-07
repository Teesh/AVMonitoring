<!--
  This page is just an example dashboard for a user.  Various types of predefined data and charts could be shown WHERE
  TODO: Maybe create a way to dynamically define what data is visible for different users or groups, or have templates
-->
<div class="row">
  <div class="col">
    <div id="chartContainer" style="height: 370px; width: 100%;"></div>
  </div>
  <div style="height: 370px; width: 67%;">
  <?php
    require_once('db_conn.php');

    $query = "SELECT * FROM device_info WHERE status = 'DOWN' ORDER BY status ASC, location ASC";
    if(!$result = mysqli_query($conn,$query)) {
      echo "Error: ".$conn->error;
    };

    while ($row = mysqli_fetch_assoc($result)) {
      include 'table.php';
    }
  ?>
  </div>
<!--  <a target="_blank" href='http://admin:g0$l0@172.21.134.153/index.php'>Log In</a>
  <form action="http://172.21.134.143/login.php" method="post" target="_blank">
    <input name="username"></input>
    <input name="password"></input>
    <button type="submit">Submit</button>
  </form>
  <a onclick=svsi_login("admin","g0$l0","172.21.134.134")>Log Out</a>
--></div>
