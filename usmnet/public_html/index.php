<?php
  session_start();
  require_once('db_conn.php');

  // table sorting in session variables, generic column names to allow for changes in data ordering
  $sort = $_SESSION['sort']['idx'];
  if(!isset($_SESSION['cols'])) {
    $_SESSION['cols'] = array("col1"=>0,"col2"=>0,"col3"=>0,"col4"=>0);
  }
  $cols = $_SESSION['cols'];

  // variable that determines if searching for a MAC will check Lens.  Set by checkbox in Utilities
  if(!isset($_SESSION['lens_check'])) {
    $_SESSION['lens_check'] = 1;
  }

  // curl an API key for CA if starting new session or our session has expired
  if(!isset($_SESSION['ca_api_key'])) {
    $curl = curl_init();

    // curl options copied from POSTman tests
    curl_setopt_array($curl, array(
      CURLOPT_PORT => "7443",
      CURLOPT_URL => "https://support.uillinois.edu:7443/caisd-rest/rest_access?_type=json",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPAUTH => CURLAUTH_ANY,
      CURLOPT_USERPWD => "$ca_user:$ca_pw",
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "<rest_access/>",
      CURLOPT_HTTPHEADER => array(
        "Content-Type: application/xml",
        "cache-control: no-cache"
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      console.log($err);
    } else {
      $key = json_decode($response);
      $_SESSION['ca_api_key'] = $key->rest_access->access_key;
    }
  }

  // grabs dropdown values for fields in the the Add Device form to help in standardizing naming
  $query = 'SELECT DISTINCT RTRIM(REVERSE(SUBSTRING(REVERSE(`location`),LOCATE(" ",REVERSE(`location`))))) as building FROM physical_info';
  $result_loc = mysqli_query($conn,$query);
  $query = "SELECT DISTINCT device_type FROM physical_info";
  $result_dev = mysqli_query($conn,$query);
  $query = "SELECT DISTINCT model_number FROM physical_info";
  $result_mod = mysqli_query($conn,$query);
  $query = "SELECT DISTINCT manufacturer FROM physical_info";
  $result_man = mysqli_query($conn,$query);
  $query = "SELECT DISTINCT department FROM physical_info";
  $result_dep = mysqli_query($conn,$query);

?>
<html>
<!-- HTML/CSS design copied from template for Bootstrap 4 -->
<head>
  <title>AV Console</title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="img/icon1.png">

  <!-- Bootstrap 4 CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Local CSS -->
  <link href="dashboard.css" rel="stylesheet">
  <!-- jQuery 3.3.1 -->
  <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <!-- Bootstrap 4 JS -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <!-- CanvasJS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/canvasjs/1.7.0/canvasjs.min.js" integrity="sha256-CIc5A981wu9+q+hmFYYySmOvsA3IsoX+apaYlL0j6fg=" crossorigin="anonymous"></script>
  <script>
    /*
      This load function controls the hashbang method of navigating the page.
      The function is bound to a change in the URL hash
      Each menu item click changes the #menu_name at the end of the URL and re-triggers this load function
      The function reads the hash  and loads menu_name.php into the main view window
      #dashboard is the default page when the hash is blank
    */
    $(function() {
      $(window).bind('hashchange',function() {
        var hash = window.location.hash;
        if(!hash) location.href = '#dashboard'; // #dashboard default
        $(".nav-link").removeClass('active');
        $(hash).addClass('active');
        // Generates page title by grabbing the hash, removing the symbol and uppercasing the first letter
        $('#pageTitle').text(hash.charAt(1).toUpperCase() + hash.substring(2));
        // grabs the search value for the pages that need it
        var search = $('#searchBar').val();
        // places a loading spinner in the view window while waiting for the data to load
        $("#content").html("<h5><span class='fa fa-spinner fa-spin' style='font-size:48px'></span></h5>");
        // Controls which pages show which menu options, since not all buttons are needed in every context
        // TODO: create a more dynamic button visibility system that can be defined by each individual page
        if(hash == '#search' || hash == '#offline' || hash == '#inventory') {
          $('#sort-options').show();
          $('#column-options').show();
        } else if(hash == '#history' || hash == '#dashboard') {
          $('#column-options').show();
          $('#sort-options').hide();
        } else {
          $('#sort-options').hide();
          $('#column-options').hide();
        }
        // calls AJAX to load the data page, and passes in search regardless of whether the page is looking for it or not
        $.post(hash.substring(1)+'.php',
          { input:search },
          function(data) {
            $('#content').html(data);
            tableAnimate(); // for pages with CanvasJS charts
            if(hash == '#dashboard') chartsReset(); // reloads data in charts if renavigating to dashboard
          }
        );
      }).trigger('hashchange'); // some navigation, like pressing back, does not trigger hashchange, so this forces it to trigger it on load
      return false; // not sure why, but it fixed a small bug
    });
  </script>
</head>
<body>
  <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
     <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="#"><img src="img/icon1.png" height="24px" width="24px">&nbsp;&nbsp;&nbsp;AV Console</a>
     <!-- Loads search bar with session search value to allow page refreshing -->
     <input id="searchBar" value="<?php if(isset($_SESSION['search'])) echo $_SESSION['search']; ?>" class="form-control form-control-dark form-control form-control-dark-dark w-100" type="text" placeholder="Search" aria-label="Search">
     <ul class="navbar-nav px-3">
       <li class="nav-item text-nowrap">
         <a id="searchBtn" style="cursor:pointer" class="nav-link">Search</a>
       </li>
     </ul>
   </nav>
   <!-- Menu items -->
   <div class="container-fluid">
     <div class="row">
       <nav class="col-md-2 d-none d-md-block bg-light sidebar">
         <div class="sidebar-sticky">
           <ul class="nav flex-column">
             <li class="nav-item">
               <a id="dashboard" class="nav-link active" href="#dashboard">
                 <span class="fas fa-home"></span>&nbsp&nbsp
                 <span class="d-none d-xl-block">Dashboard</span>
               </a>
             </li>
             <li class="nav-item">
               <a id="offline" class="nav-link" href="#offline">
                 <span class="fas fa-exclamation-triangle"></span>&nbsp&nbsp
                 Offline
               </a>
             </li>
             <li class="nav-item">
               <a id="inventory" class="nav-link" href="#inventory">
                 <span class="fas fa-box-open"></span>&nbsp&nbsp
                 Inventory
               </a>
             </li>
             <li class="nav-item">
               <a id="search" class="nav-link" href="#search">
                 <span class="fas fa-search"></span>&nbsp&nbsp
                 Search
               </a>
             </li>
             <li class="nav-item">
               <a id="database" class="nav-link" href="#database">
                 <span class="fas fa-database"></span>&nbsp&nbsp
                 Database
               </a>
             </li>
             <li class="nav-item">
               <a id="tickets" class="nav-link" href="#tickets">
                 <span class="fas fa-concierge-bell"></span>&nbsp&nbsp
                 Tickets
               </a>
             </li>
             <li class="nav-item">
               <a id="schedule" class="nav-link" href="#schedule">
                 <span class="far fa-calendar"></span>&nbsp&nbsp&nbsp&nbsp
                 Schedule
               </a>
             </li>
             <li class="nav-item">
               <a id="reports" class="nav-link" href="#reports">
                 <span class="fas fa-chart-bar"></span>&nbsp&nbsp
                 Reports
               </a>
             </li>
             <li class="nav-item">
               <a id="utilities" class="nav-link" href="#utilities">
                 <span class="fas fa-cog"></span>&nbsp&nbsp
                 Utilities
               </a>
             </li>
             <li class="nav-item">
               <a id="help" class="nav-link" href="#help">
                 <span class="fas fa-question-circle"></span>&nbsp&nbsp
                 Help
               </a>
             </li>
           </ul>
           <!-- Saved Searches section
            TODO: Allow users to save searches by pressing the plus icon.  Requires Access Control -->
           <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
             <span>Saved searches</span>
             <a class="d-flex align-items-center text-muted" href="#">
               <span class="fas fa-plus-circle"></span>&nbsp&nbsp
             </a>
           </h6>
           <ul class="nav flex-column mb-2">
             <li class="nav-item">
               <a id="search1" class="nav-link" href="#search1">
                 <span class="far fa-file-alt"></span>&nbsp&nbsp
                 Lecture Recording
               </a>
             </li>
             <li class="nav-item">
               <a id="search2" class="nav-link" href="#search2">
                 <span class="far fa-file-alt"></span>&nbsp&nbsp
                 AV over IP
               </a>
             </li>
             <li class="nav-item">
               <a id="search3" class="nav-link" href="#search3">
                 <span class="far fa-file-alt"></span>&nbsp&nbsp
                 Controllers
               </a>
             </li>
             <li class="nav-item">
               <a id="search4" class="nav-link" href="#search4">
                 <span id="trythis" class="far fa-file-alt"></span>&nbsp&nbsp
                 Wireless Collaboation
               </a>
             </li>
           </ul>
         </div>
       </nav>
      <!-- Main page title and menu buttons -->
      <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
          <h1 id="pageTitle" class="h2">Dashboard</h1>
          <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group mr-2">
              <button id="add-btn" class="btn btn-sm btn-outline-secondary rounded-0" data-toggle="modal" data-target="#addDevice"><span class="fas fa-plus-square"></span> Add</button>
              <button id="import-btn" class="btn btn-sm btn-outline-secondary rounded-0" data-toggle="modal" data-target="#importDevice"><span class="fas fa-file-import"></span> Import</button>
              <button id="export-btn" class="btn btn-sm btn-outline-secondary rounded-0" onclick=export_device()><span class="fas fa-file-export"></span> Export</button>
            </div>
            <div id="sort-options" class="btn-group">
              <button data-toggle="dropdown" id="sortList" class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-0">
                <span class="fas fa-sort"></span> Sort By
              </button>
              <!-- Defines sorting options for all pages -->
              <div class="dropdown-menu rounded-0" aria-labelledby="sortList">
                <button class="dropdown-item sort-choose <?php echo ($sort == 0 ? 'active' : ''); ?>">Building-Room</button>
                <button class="dropdown-item sort-choose <?php echo ($sort == 1 ? 'active' : ''); ?>">Device Type</button>
                <button class="dropdown-item sort-choose <?php echo ($sort == 2 ? 'active' : ''); ?>">MAC Address</button>
                <button class="dropdown-item sort-choose <?php echo ($sort == 3 ? 'active' : ''); ?>">Make and Model</button>
                <button class="dropdown-item sort-choose <?php echo ($sort == 4 ? 'active' : ''); ?>">IP Address</button>
                <button class="dropdown-item sort-choose <?php echo ($sort == 5 ? 'active' : ''); ?>">Subnet Name</button>
                <button class="dropdown-item sort-choose <?php echo ($sort == 6 ? 'active' : ''); ?>">Switch-Port</button>
                <button class="dropdown-item sort-choose <?php echo ($sort == 7 ? 'active' : ''); ?>">Building-Jack</button>
                <button class="dropdown-item sort-choose <?php echo ($sort == 8 ? 'active' : ''); ?>">Status</button>
                <button class="dropdown-item sort-choose <?php echo ($sort == 9 ? 'active' : ''); ?>">Department</button>
                <button class="dropdown-item sort-choose <?php echo ($sort == 10 ? 'active' : ''); ?>">Serial Number</button>
                <button class="dropdown-item sort-choose <?php echo ($sort == 11 ? 'active' : ''); ?>">PTAG</button>
              </div>
            </div>
            <div id="column-options" class-"btn-group">
              <button data-toggle="dropdown" id="columnList" class="btn btn-sm btn-outline-secondary dropdown-toggle ml-2 rounded-0">
                <span class="fas fa-eye"></span> Columns
              </button>
              <!-- Defines columns for changing visibility -->
              <div class="dropdown-menu dropdown-menu-right rounded-0" aria-labelledby="columnList">
                <button id="col1" class="dropdown-item col-choose <?php echo (!$cols['col1'] ? 'active' : ''); ?>">MAC-IP-Department</button>
                <button id="col2" class="dropdown-item col-choose <?php echo (!$cols['col2'] ? 'active' : ''); ?>">Make-Model-SN</button>
                <button id="col3" class="dropdown-item col-choose <?php echo (!$cols['col3'] ? 'active' : ''); ?>">PTAG-subnet-IP space</button>
                <button id="col4" class="dropdown-item col-choose <?php echo (!$cols['col4'] ? 'active' : ''); ?>">Switch-Port-Jack</button>
              </div>
            </div>
          </div>
        </div>
        <div class="container-fluid" id="content">
        </div>
      </main>
      <textarea id="copyText" style="display:none"></textarea>
    </div>
  </div>

  <!-- Add Device Modal -->
  <div class="modal fade" id="addDevice" tabindex="-1" role="dialog" aria-labelledby="addDeviceModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content rounded-0" style="color:white;background-color:#111111">
        <div class="modal-header">
          <h5 class="modal-title" id="addDeviceTitle">Add Device</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:white;opacity:1">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form onsubmit="return add_device()">
            <div class="form-group row">
              <label for="loc_input" class="col-sm-3 col-form-label">Location</label>
              <div class="col-sm-9">
                <input list="locations" type="input" class="form-control form-control-dark rounded-0" id="loc_input" placeholder="Bevier 89" style="">
                <datalist id="locations">
                  <?php
                    foreach($result_loc as $row) {
                      echo '<option value="'.$row['building'].'">';
                    }
                  ?>
                </datalist>
              </div>
            </div>
            <div class="form-group row">
              <label for="man_input" class="col-sm-3 col-form-label">Manufacturer</label>
              <div class="col-sm-9">
                <input list="manufacturers" type="input" class="form-control form-control-dark rounded-0" id="man_input" placeholder="AMX">
                <datalist id="manufacturers">
                  <?php
                    foreach($result_man as $row) {
                      echo '<option value="'.$row['manufacturer'].'">';
                    }
                  ?>
                </datalist>
              </div>
            </div>
            <div class="form-group row">
              <label for="mod_input" class="col-sm-3 col-form-label">Model</label>
              <div class="col-sm-9">
                <input list="models" type="input" class="form-control form-control-dark rounded-0" id="mod_input" placeholder="NX-1200">
                <datalist id="models">
                  <?php
                    foreach($result_mod as $row) {
                      echo '<option value="'.$row['model_number'].'">';
                    }
                  ?>
                </datalist>
              </div>
            </div>
            <div class="form-group row">
              <label for="dev_input" class="col-sm-3 col-form-label">Device Type</label>
              <div class="col-sm-9">
                <input list="device_types" type="input" class="form-control form-control-dark rounded-0" id="dev_input" placeholder="Controller">
                <datalist id="device_types">
                  <?php
                    foreach($result_dev as $row) {
                      echo '<option value="'.$row['device_type'].'">';
                    }
                  ?>
                </datalist>
              </div>
            </div>
            <div class="form-group row">
              <label for="mac_input" class="col-sm-3 col-form-label">MAC Address <font color="red">*</font></label>
              <div class="col-sm-9">
                <input type="input" class="form-control form-control-dark rounded-0" id="mac_input" placeholder="00:11:22:33:44:55" required>
              </div>
            </div>
            <div class="form-group row">
              <label for="det_input" class="col-sm-3 col-form-label">Details</label>
              <div class="col-sm-9">
                <input type="input" class="form-control form-control-dark rounded-0" id="det_input" placeholder="Additional Notes">
              </div>
            </div>
            <div class="form-group row">
              <label for="sn_input" class="col-sm-3 col-form-label">Serial Number</label>
              <div class="col-sm-9">
                <input type="input" class="form-control form-control-dark rounded-0" id="sn_input" placeholder="SN0123456789">
              </div>
            </div>
            <div class="form-group row">
              <label for="ptag_input" class="col-sm-3 col-form-label">PTAG Number</label>
              <div class="col-sm-9">
                <input type="input" class="form-control form-control-dark rounded-0" id="ptag_input" placeholder="P10R54321">
              </div>
            </div>
            <div class="form-group row">
              <label for="dep_input" class="col-sm-3 col-form-label">Department</label>
              <div class="col-sm-9">
                <input list="departments" type="input" class="form-control form-control-dark rounded-0" id="dep_input" placeholder="Technology Services">
                <datalist id="departments">
                  <?php
                    foreach($result_dep as $row) {
                      echo '<option value="'.$row['department'].'">';
                    }
                  ?>
                </datalist>
              </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary rounded-0" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-dark rounded-0">Add Device</button>
        </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Import Modal -->
  <div class="modal fade" id="importDevice" tabindex="-1" role="dialog" aria-labelledby="importDeviceModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="color:white;background-color:#111111">
        <div class="modal-header">
          <h5 class="modal-title" id="importDeviceTitle">Import Devices</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:white;opacity:1">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form onsubmit="return import_device()">
            <input type="file" id="import_file" accept=".csv"/>
            <output id="import_list"></output><br><br>
            <div id="import_data">
              <h6>Include any of the following columns in a CSV file in any order.
                All column names are case sensitive.
                Copy/paste to ensure correctness.
                The MAC Address field is reuired.</h6>
              <span class="badge badge-dark rounded-0 p-2 mb-1"><font size="+1">MAC Address <font color="red">*</font></font></span>
              <span class="badge badge-dark rounded-0 p-2 mb-1"><font size="+1">Manufacturer</font></span><br>
              <span class="badge badge-dark rounded-0 p-2 mb-1"><font size="+1">Model Number</font></span>
              <span class="badge badge-dark rounded-0 p-2 mb-1"><font size="+1">Device Type</font></span><br>
              <span class="badge badge-dark rounded-0 p-2 mb-1"><font size="+1">Details</font></span>
              <span class="badge badge-dark rounded-0 p-2 mb-1"><font size="+1">Serial Number</font></span><br>
              <span class="badge badge-dark rounded-0 p-2 mb-1"><font size="+1">PTAG Number</font></span>
              <span class="badge badge-dark rounded-0 p-2 mb-1"><font size="+1">Department</font></span>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary rounded-0" data-dismiss="modal">Close</button>
          <button type="button" onclick=import_device() class="btn btn-dark rounded-0">Import</button>
        </div>
        </form>
      </div>
    </div>
  </div>
</body>
<script src="dashboard.js"></script>
<style>
  a {
   color: white;
  }
  a:hover {
   color:white;
  }
  /* Defines hover and chlick functionality for tables */
  .fas,.far {
    opacity: .5;
  }
  .device .actions {
    opacity: 0;
  }
  .device.active .actions {
    opacity: .5;
    cursor: pointer;
  }
  .device.active .actions:hover {
    opacity: .8;
  }
  .nav-item:hover .fas,.nav-item:hover .far {
    opacity: .8;
  }
  .nav-link.active .fas,.nav-link.active .far {
    opacity: 1;
  }
  .device {
   opacity:0.8;
  }
  .device:hover {
   opacity:1;
  }
  .device.active {
    opacity:1;
  }
</style>
</html>
