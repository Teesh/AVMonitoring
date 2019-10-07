<!DOCTYPE html>
<?php
include 'PHPExcel.php';
include 'PHPExcel/Writer/Excel2007.php';
include 'PHPExcel/IOFactory.php';
?>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>USMnet</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="bootstrap-menu/dist/BootstrapMenu.min.js"></script>
</head>
<body>
<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">USMnet</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Filter<span class="caret"></span></a>
          <ul class="dropdown-menu">
	    <li><a href="#" onclick=clearAll() id="clear_button">Clear All</a></li>
            <li><a><input class="devices" onclick=checkSome("appletv") type="checkbox" id="appletv" checked>Apple TV</a></li>
            <li><a><input class="devices" onclick=checkSome("solstice") type="checkbox" id="solstice" checked>Mersive Solstice</a></li>
            <li><a><input class="devices" onclick=checkSome("via") type="checkbox" id="via" checked>Kramer VIA</a></li>
            <li role="separator" class="divider"></li>
            <li><a><input class="devices" onclick=checkSome("svsi") type="checkbox" id="svsi" checked>AMX SVSI</a></li>
            <li role="separator" class="divider"></li>
            <li><a><input class="devices" onclick=checkSome("smp") type="checkbox" id="smp" checked>Extron SMP</a></li>
            <li role="separator" class="divider"></li>
            <li><a><input class="devices" onclick=checkSome("amx") type="checkbox" id="amx" checked>AMX</a></li>
            <li><a><input class="devices" onclick=checkSome("crestron") type="checkbox" id="crestron" checked>Crestron</a></li>
            <li><a><input class="devices" onclick=checkSome("extron") type="checkbox" id="extron" checked>Extron</a></li>
            <li role="separator" class="divider"></li>
            <li><a><input class="devices" onclick=checkSome("unknown") type="checkbox" id="unknown" checked>Other</a></li>
          </ul>
        </li>
      </ul>
      <form class="navbar-form navbar-left">
        <div class="form-group">
		<input type="text" class="form-control" id='filter1' oninput=filterTable() placeholder="Search" onclick="this.select()" autofocus>
	</div>
	<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#search_one">
	  Find
	</button>
      </form>
      <ul class="nav navbar-nav navbar-right">
	<li><a style="margin-left: 10px" id="active_count">Selected: 0</a></li>
	<li><a style="margin-left: 10px" id="row_count">Visible: 0</a></li>
	<li><a style="margin-left: 10px" id="total_count">Total: 0</a></li>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<br><br><br>
<div>
	<?php
		/*
		 *	The url is a link to the Box file which has been shared to all with link.  Curl is a must for this as 'FOLLOWLOCATION' is necessary to navigate the redirections
		 *	When adding columns to the table, the numbering must stay in order from left to right as it is used by the script for sorting.  Not all columns require sort
		 */
		$url = "https://uofi.box.com/shared/static/n05n0somswtd6xk9wmi5r269jurpgai5.csv";
		//$url = "https://uofi.box.com/shared/static/xxjguvfprliot0dmx24n3jsuodn9ymwj.csv";
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_ENCODING,'');
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
		$ret = curl_exec($ch);
		curl_close($ch);
		$rows = explode("\n",$ret);
		// each column header needs a sorting arrow inside an anchor for sorting and a text input field for filtering
		echo "<table class='table table-bordered table-responsive' id='netTable'><tr id='startRow'>
			<th class='sorter' id='col0' onclick=sortTable(0)>Location &#9650;</th>
			<th class='sorter' id='col1' onclick=sortTable(1)>Device &#9650;</th>
			<th class='sorter' id='col2' onclick=sortTable(2)>MAC Address &#9650;</th>
			<th class='sorter' id='col3' onclick=sortTable(3)>IP Address &#9650;</th>
			<th class='sorter' id='col4' onclick=sortTable(4)>Subnet &#9650;</th>
			<th class='sorter' id='col5' onclick=sortTable(5)>Switch &#9650;</th>
			<th class='sorter' id='col6' onclick=sortTable(6)>Port &#9650;</th>
			<th class='sorter' id='col7' onclick=sortTable(7)>Jack &#9650;</th>
			<th class='sorter' id='col8' onclick=sortTable(8)>Status &#9650;</th>";
		$id = 0;
		/*
		 *	The following switch-case must be updated for every instance of new type of tech added to the CSV, otherwise it will fall under Unidentified
		 *	Entries under the device column in the CSV must match the following cases exactly
		 *	The type and subtype must match an entry in the checklist from above, or an entry must be added to the checklist for it
		 */
		foreach($rows as $row) {
			$id++;
			$cols = explode(',',$row);
			if(strlen($cols[0]) < 2) continue; // This line causes the table to ignore any lines with blank Building entry (less than 3 chars)
			$test = explode(' ',trim($cols[1]));
			switch($test[0]) {
				case 'VIA': $type = 'via';
					$subtype = 'mstream';
					$color_row = '#F5B7B1';
					break;
				case 'Apple': $type = 'appletv';
					$subtype = 'mstream';
					$color_row = '#F5B7B1';
					break;
				case 'Mersive': $type = 'solstice';
					$subtype = 'mstream';
					$color_row = '#F5B7B1';
					break;
				case 'SMP': $type = 'smp';
					$subtype = 'lcapture';
					$color_row = '#F9E79F';
					break;
				case 'SVSI': $type = 'svsi';
					$subtype = 'avip';
					$color_row = '#F5CBA7';
					break;
				case 'Extron': $type = 'extron';
					$subtype = 'control';
					$color_row = '#AED6F1';
					break;
				case 'AMX': $type = 'amx';
					$subtype = 'control';
					$color_row = '#AED6F1';
					break;
				case 'Crestron': $type = 'crestron';
					$subtype = 'control';
					$color_row = '#AED6F1';
					break;
				default: $type = 'unknown';
					$color_row = '#C1DCB3';
					break;
			}
			/*
			 *	Build each row here for the table
			 *	Every row needs a unique id for sorting and a type and subtype for filtering
			 *	Each cell needs an id which is the column id concatenated with the row id for filling in AJAX data
			 */
			echo "<tr style='background-color:".$color_row."' id='".$id."' class='".$type." ".$subtype." clickable-row'>";
			echo '<td class="editable" id="bldg'.$id.'">'.$cols[0].'</td><td class="editable" id="dev'.$id.'">'.$cols[1].'</td>';
			if(strlen($cols[2]) < 12) { // this checks to make sure the MAC address column has something resembling a MAC address (12 chars). Replace with data validation if possible
				echo '<td class="editable" id="mac'.$id.'"></td><td></td><td></td><td></td><td></td><td></td><td style="background-color:#B0B0B0">UNKNOWN</td>';
			} else { // initializes all non-empty rows with a loading status
				echo '<td class="editable" id="mac'.$id.'">'.strtoupper(str_replace("-",":",$cols[2])).'</td><td id="ip'.$id.'"></td><td id="sub'.$id.'"><td id="sw'.$id.'"></td><td id="port'.$id.'"></td><td id="jack'.$id.'"></td></td><td id="stat'.$id.'" style="background-color:#F2F539">LOADING</td>';
			}
		}
		echo "</table>";
	?>
</div>
<!-- Modal -->
<div class="modal fade" id="search_one" tabindex="-1" role="dialog" aria-labelledby="MAC Look-up" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLongTitle">Find MAC on Network</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form class="form-inline">
					<div class="form-group">
						<input id="mac1a" type="text" class="form-control" placeholder="00:11:22:33:44:AA">
					</div>
					<div class="form-group">
						<button type="button" class="btn btn-primary" onclick=findOne()>Find</button>
					</div>
				</form>
				<table class='table table-bordered'>
					<tr><th width="50%">IP Address</th><td id="ip1a"></td></tr>
					<tr><th>Subnet</th><td id="sub1a"></td></tr>
					<tr><th>Switch</th><td id="sw1a"></td></tr>
					<tr><th>Port</th><td id="port1a"></td></tr>
					<tr><th>Jack</th><td id="jack1a"></td></tr>
					<tr><th>Status</th><td id="stat1a"></td></tr>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<script>
	var filtarr = []; // used to keep track of row visibility when filtering
	var ajaxarr = []; // AJAX queue used for AJAX reprioritization

	/*
	 *	Main function on document load
	 *	Grabs MAC address and row number and calls getNet()
	 */
	$(document).ready(function(){
		var id = 0;
		var len = $('#netTable tr').length;
		var dataVal;

		$("#row_count").text("Visible: "+(len-1));
		$("#total_count").text("Total: "+(len-1));
		while(id < len) {
			filtarr[id] = 0;
			id++;
			dataVal = $("#mac"+id).text();
			if(dataVal == '') continue;
			getNet(dataVal,id);
		}
	});

	function recount() {
		var count = $("#netTable tr:visible").length;
		$("#row_count").text("Visible: "+(count-1));
	}

	var timeout = null; // used to delay AJAX reprioritization of text input

	/*
	 *	Function to filter rows based on text input in column headers
	 *	Filtering happens immediately, the AJAX reprioritization happens after the function hasn't been called for at least 1 sec
	 */
	function filterTable() {
		var devid, cell,x,check;
		var str = $("#filter1").val().toLowerCase();
		$('#netTable tr').each(function() {
			if(this.id != 'startRow') {
				check = 1;
				devid = $(this).attr("class").split(' ')[0];
				for(var col = 0; col < 9;col++) {
					cell = $(this).find('td').eq(col).text();
					if(cell.toLowerCase().indexOf(str) != -1) { // filter holds a bool mask for all columns in each row
						check = 0;
					}
				}
				filtarr[this.id] = check;
				if($('#'+devid).is(':checked')) { // also check for checklist value
					if(filtarr[this.id]) {
						$(this).hide();
					} else {
						$(this).show();
					}
				}
			}
		});
		recount();
		select_count();
		if(timeout != null) { // wait until typing has stopped for 1 sec before calling reOrderAJAX because it is a costly function
			clearTimeout(timeout);
		}
		timeout = setTimeout(function() {
			reOrderAjax();
		},1e3);
	}

	function findOne() {
		getNet($('#mac1a').val(),'1a');
		var vis, data, end, req, stat;
		$('#netTable tr').each(function() {
			if(this.id != 'startRow') {
				vis = $(this).is(':visible');
				data = $(this).find('td').eq(2).text().trim();
				req = ajaxarr['r'+this.id];
				stat = $(this).find('td').eq(8).text();
				if(data != '' && stat == 'LOADING') {
					if(req) req.abort();
					getNet(data,this.id);
				}
			}
		});
	}

	/*
	 *	AJAX request for Lens API data
	 *	Sends MAC address (dataVal) and gets back JSON array filled with data.  Updates row (id) with data
	 */
	function getNet(dataVal,id) {
		var str, jack, i;
		var str,str2,check;
		var req = $.ajax({
			type:'POST',
			url:"network_query.php",
			data: {mac:dataVal},
			dataType:"text",
		}).done(function(data){
			str = JSON.parse(data);
			if(str['ip'] != null) $("#ip"+id).html('<a target="_blank" href="http://'+str['ip']+'">'+str['ip']+'</a>');
			$("#sub"+id).html(str['sub']);
			$("#sw"+id).html(str['switch']);
			if(str['port']) $("#port"+id).html('<a target="_blank" href="https://iris.cites.illinois.edu/?switch=' + str['switch'] + '.gw.uiuc.edu&page=macaddr&port=' + str['port'] +'">' + str['port'] + '</a>');
			$("#stat"+id).html(str['stat']);
			$("#stat"+id).css('background-color',str['bg']);
			if(str['jack'] != null) jack = str['jack'].split(" "); // these lines clip off additional data in Jack field on IRIS (e.g. room number)
			else jack = '';
			for(i = 0;i < jack.length; i++) {
				if(jack[i][0] == 'H') break;
			}
			$('#jack'+id).html(jack[i]);
			check = 1;
			for(i = 0;i < 9;i++) {
				str = $('#'+id).find('td').eq(i).text().toLowerCase();
				str2 = $('#filter1').val().toLowerCase();
				if(str.indexOf(str2) != -1) {
					check = 0;
				}
			}
			filtarr[id] = check;
			var devid = $('#'+id).attr("class").split(' ')[0];
			if($('#'+devid).is(':checked')) {
				if(filtarr[id]) {
					$('#'+id).hide();
				} else {
					$('#'+id).show();
				}
			}
			delete ajaxarr['r'+id]; // remove AJAX call from queue once complete

		})
		ajaxarr['r'+id] = req; // update queue with active AJAX call
	}

	/*
	 *	AJAX reprioritization
	 *	Deletes all AJAX calls in queue from non-visible rows and leaves others as is, then calls all the ones that were deleted again, effectively pushing them down in the queue
	 */
	function reOrderAjax() {
		var vis, data, end, req, stat;
		$('#netTable tr').each(function() {
			if(this.id != 'startRow') {
				vis = $(this).is(':visible');
				data = $(this).find('td').eq(2).text().trim();
				req = ajaxarr['r'+this.id];
				stat = $(this).find('td').eq(8).text();
				if(vis == false && data != '' && stat == 'LOADING') {
					if(req) req.abort();
					getNet(data,this.id);
				}
			}
		});
	}
	/*
	 *	Clears all checklist selections, making it easier to select a specific device
	 */
	function clearAll() {
		var dtype;
		if($('#clear_button').text() == 'Clear All') {
			$('.devices').each(function() {
				dtype = $(this).attr('id');
				$(this).prop('checked',false);
				$('.'+dtype).hide();
			});
			$('#clear_button').text('Select All');
		} else if($('#clear_button').text() == 'Select All') {
			var check,dtype;
			$('.devices').each(function() {
				dtype = $(this).attr('id');
				$(this).prop('checked',true);
				$('.'+dtype).each(function() {
					if(!filtarr[this.id]) {
						$(this).show();
					}
				});
			});
			$('#clear_button').text('Clear All');
		}
		select_count()
		recount();
	}

	/*
	 *	Checklist first level call
	 *	Checks/Unchecks the parent, does same to all children
	 */
	function checkAll(dtype) {
		var checkstatus = $('#'+dtype).is(':checked');
		if(checkstatus) {
			$('.'+dtype).each(function() {
				if(!filtarr[this.id]) {
					$(this).show();
				}
			});
			$('#clear_button').val('Clear All');
		} else {
			$('.'+dtype).hide();
		}
		reOrderAjax();
		select_count();
		recount();
	}

	/*
	 *	Checklist second level call
	 *	Checks/unchecks child, sets the parent to checked/unchecked/intermediete based on values of other children
	 */
	function checkSome(device) {
		var visible = $('#'+device).is(':checked');
		if(visible) {
			$('.'+device).each(function() {
					$(this).show();
			});
			$('#clear_button').val('Clear All');
		} else {
			$('.'+device).hide();
		}
		reOrderAjax();
		select_count();
		recount();
	}

	/*
	 *	This sort function calls the quickSort function
	 *	Replaces the sorting arrow in the header of each column with its inverse
	 *	And uses it to keep track whether we are sorting asc or desc
	 */
	function sortTable(col) {
		var heading = $('#col'+col).html();
		if(heading.slice(-1) == String.fromCharCode(9660)) {
			order = 0;
			$('#col'+col).html(heading.slice(0,-1) + String.fromCharCode(9650));
		} else {
			order = 1;
			$('#col'+col).html(heading.slice(0,-1) + String.fromCharCode(9660));
		}
		var trList = [];
		var colList = ["bldg","dev","mac","ip","sub","sw","port","jack","stat"]; // these items should match the column id prefixes used when making the table
		var type = colList[col];
		$('#netTable tr').each(function() { // create an array to make sorting easier
			if(this.id != 'startRow') trList.push(this.id);
		});
		quickSort(type,trList,0,trList.length-1);
		if(order) {
			for(var i = ((trList.length) - 1); i >= 0; i--) {
				$('#'+trList[i]).insertAfter("#startRow");
			}
		} else {
			for(var i = 0; i < trList.length; i++) {
				$('#'+trList[i]).insertAfter("#startRow");
			}
		}
	}

	/*
	 *	quickSort() uses the quicksort algorithm, which is an avg. O(n log n)
	 *	quickSort also calls partition() and swap() as helper functions
	 *	quicksort is a recursize algorithm.
	 */
	function quickSort(type,arr,left,right) {
		var len = arr.length,
		pivot,
		partitionIndex;
		if(left < right) {
			pivot = right;
			partitionIndex = partition(arr,type,pivot,left,right);
			quickSort(type,arr,left, partitionIndex - 1);
			quickSort(type,arr,partitionIndex + 1, right);
		}
		return arr;
	}

	/* See above */
	function partition(arr, type, pivot, left, right) {
		var pivotValue = $('#'+type+arr[pivot]).text(),
			partitionIndex = left;
		for(var i = left; i < right; i++) {
			if($('#'+type+arr[i]).text() < pivotValue) {
				swap(arr,i,partitionIndex);
				partitionIndex++;
			}
		}
		swap(arr,right,partitionIndex);
		return partitionIndex;
	}

	/* See above */
	function swap(arr, i, j) {
		var temp = arr[i];
		arr[i] = arr[j];
		arr[j] = temp;
	}

	/*
	 *	Refreshes a specific row (id)
	 *	Grabs MAC address and row number and calls getNet().  Sets LOADING status while waiting
	 */
	function pingAgain() {
		var dataVal,req;
		$('#netTable tr').each(function() {
			if($(this).hasClass('active')) {
				dataVal = $('#mac'+this.id).html();
				dataVal = dataVal.trim();
				getNet(dataVal,this.id);
				$('#stat'+this.id).html('LOADING');
				$('#stat'+this.id).css('background-color','#F2F539');
			} else {
				if($(this).find('td').eq(8).text() == 'LOADING') {
		//			ajaxarr['r'+this.id].abort();
				}
			}
		});
		reOrderAjax();
	}

	function pingAll() {
		var dataVal;
		$('#netTable tr').each(function() {
			if(this.id != 'startRow') {
				if($(this).is(':visible')) {
					dataVal = $(this).find('td').eq(2).text().trim();
					getNet(dataVal,this.id);
					$('#stat'+this.id).html('LOADING');
					$('#stat'+this.id).css('background-color','#F2F539');
				}
			}
		});
	}

	var menu = new BootstrapMenu('#netTable', {
	  actions: [{
	      name: 'Refresh',
	      onClick: function() {
	      	pingAgain();
	      }
	    }, {
	      name: 'Export',
	      onClick: function() {
	      	exportCSV();
	      }
	    }, {
	      name: 'Edit',
	      onClick: function() {
	        $('#loc1b').val(this.id);
	      	$('#edit_one').modal('show');
	      }
	    }, {
	      name: 'Delete',
	      onClick: function() {
	      }
	    }, {
	    	name: 'Back to Top',
		onClick: function() {
			$(document).scrollTop(0);
		}
	  }]
	});

	var lastSelect = null;
	var dragSel = false;
	var dragUnSel = true;
	var winY,userY,curY;
	var scrollInt2;
	$('#netTable tr').mousedown(function(event) {
		dragSel = true;
		if(event.which == 1) {
			if($('#editing').length && (this.id != lastSelect)) editRowCSV();
			if(event.shiftKey) {
				$(this).siblings().removeClass('active');
				$(this).addClass('active');
				if(this.id != lastSelect) {
					if($('#'+lastSelect).nextAll().filter(this).length !== 0) {
						if(this.is(':visible')) $('#'+lastSelect).addClass('active').nextUntil(this).addClass('active');
					} else {
						if(this.is(':visible')) $('#'+lastSelect).addClass('active').prevUntil(this).addClass('active');
					}
				}
				if (window.getSelection) {
				  if (window.getSelection().empty) {  // Chrome
				    window.getSelection().empty();
				  } else if (window.getSelection().removeAllRanges) {  // Firefox
				    window.getSelection().removeAllRanges();
				  }
				} else if (document.selection) {  // IE?
				  document.selection.empty();
				}
			} else if(event.ctrlKey) {
				lastSelect = this.id;
				$(this).toggleClass('active');
			} else {
				lastSelect = this.id;
				$(this).addClass('active');
				$(this).siblings().removeClass('active');
			}
			curY = event.pageY;
			winY = $(window).scrollTop();
			userY = $(window).height();
			scrollInt2 = setInterval(function() {
				if(curY > (parseInt(userY) + parseInt(winY) - 100)) {
					$(window).scrollTop(parseInt(winY) + 5);
					winY = $(window).scrollTop();
				} else if(curY < (parseInt(winY) + 100)) {
					$(window).scrollTop(parseInt(winY) - 5);
					winY = $(window).scrollTop();
				}
			},20);
		}
		select_count();
	}).mousemove(function(event) {
		if(event.which == 1) {
			if(dragUnSel & dragSel) {
				if(!event.ctrlKey) $(this).siblings().removeClass('active');
				if($('#editing').length && (this.id != lastSelect)) editRowCSV();
				dragUnSel = false;
			}
			if(dragSel) {
				curY = event.pageY;
				winY = $(window).scrollTop();
				userY = $(window).height();
				if(this.id == lastSelect) {
					if(!event.ctrlKey) $(this).siblings().removeClass('active');
				} else {
					if(!event.ctrlKey) $(this).siblings().removeClass('active');
					if($('#'+lastSelect).nextAll().filter(this).length !== 0) {
						$('#'+lastSelect).addClass('active').nextUntil(this).addClass('active');
						winY = $(window).scrollTop();
					} else {
						$('#'+lastSelect).addClass('active').prevUntil(this).addClass('active');
						winY = $(window).scrollTop();
					}
				}
				$(this).addClass('active');
				select_count();
			}
		}
	}).mouseup(function(event) {
		console.log('here3');
		clearInterval(scrollInt2);
	});

	$('#netTable').on('contextmenu', '.clickable-row', function(event) {
		if(!$(this).hasClass('active')) {
			lastSelect = this.id;
			$(this).addClass('active').siblings().removeClass('active');
		}
	});

	$('.editable').dblclick(function(event) {
		if($('#editing').length) $('#editing').parent().html($('#editing').val());
		$(this).html('<form id="editForm"><input id="editing" type="text" value="'+$(this).text()+'"></form></input>');
		$('#editing').select();
		$('#editForm').submit(function(event) {
			event.preventDefault();
			editRowCSV();
			return false;
		});
	});

	function editRowCSV() {
		$('#editing').parent().html($('#editing').val());
	}

	$(document).mouseup(function(event) {
		dragSel = false;
		dragUnSel = true;
	});

	function exportCSV() {
		var table = '<table>';
		$('#netTable .active').each(function() {
			table += '<tr>';
			$(this).find('td').each(function() {
				table += '<td>'+$(this).text()+'</td>';
			});
			table += '</tr>';
		});
		table += '</table>';
		window.open('data:application/vnd.ms-excel,'+ table);
	}

	$('#netTable td').hover(
		function() {
			$(this).addClass('selCell');
		},
		function() {
			$(this).removeClass('selCell');
		}
	);

	ctrlDown = 0;
	$(document).keydown(function(e) {
		if(e.ctrlKey) ctrlDown = 1;
		if(ctrlDown && e.keyCode == 67) {
			var $temp = $("<input>");
			$("body").append($temp);
			$temp.val($('.selCell').text()).select();
			document.execCommand("copy");
			$temp.remove();
		}
		if(ctrlDown && e.keyCode == 65) {
			$('#netTable tr:visible').addClass('active');
			select_count();
			return false;
		}
		if(e.keyCode == 27) {
			$('#netTable tr:visible').removeClass('active');
			select_count();
			return false;
		}
	}).keyup(function(e) {
		if(e.ctrlKey) ctrlDown = 0;
	});

	function select_count() {
		$('#netTable tr:hidden').removeClass('active');
		$('#startRow').removeClass('active');
		$('#active_count').text('Selected: '+$('#netTable .active').length);
	}
</script>
<style>
	.sorter {
		cursor: pointer;
	}

	#netTable td:hover {
		background-color: #FFF;
		cursor: pointer;
	}

	#netTable {
		-webkit-touch-callout: none;
		-webkit-user-select: none;
		-khtml-user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		user-select: none;

	}

</style>
</body>
</html>
