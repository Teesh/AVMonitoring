<?php
/*
  This page just gives a table description of the database we are using
  Used mainly for developer debug purposes. The field names are helpful for defining search parameters
  TODO: Integrate this into the help docs
*/
  require_once('db_conn.php');

  $query = "SHOW FULL TABLES";
  $result = mysqli_query($conn,$query);
  echo '<div class="container-fluid"><div class="row">';
  while ($row = mysqli_fetch_assoc($result)) {
    echo '<div class="card bg-dark m-3" style="height:auto">
            <div class="card-header"><h5 style="color:white">'.$row['Tables_in_'.$dbname].' ('.($row['Table_type'] == 'BASE TABLE' ? 'TABLE' : $row['Table_type']).')</h5></div>
            <div class="card-body">
              <ul class="list-group list-group-flush bg-dark">';
    $query_attr = "DESCRIBE ".$row['Tables_in_'.$dbname];
    $result_attr = mysqli_query($conn,$query_attr);
    while ($row_attr = mysqli_fetch_assoc($result_attr)) {
      echo '<li class="list-group-item bg-dark" style="color:white;text-decoration:'.($row_attr['Null'] == 'NO' ? 'underline' : 'none').'">'.$row_attr['Field'].' : '.$row_attr['Type'].'</li>';
    }
    echo '</div></div>';
  }
  echo '</div></div>';
?>
