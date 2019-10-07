/*
  The various function that allow the main page to function as a single page application
*/
var ctrled; // var tp track whether the Ctrl key is pressed, used for modifying selections
var shifted; // var to track whether the Shift key is pressed, used for modifying selections
var lastSelectedDevice; // var to track which device we clicked last, used for tracking shift-clicks

$(document).on('keyup keydown', function(e){ctrled = e.ctrlKey} ); // set ctrl key var
$(document).on('keyup keydown', function(e){shifted = e.shiftKey} ); // set shift key var

// When Esc is pressed, deselect all devices and animate them back into normal position
$(document).on('keyup', function (e) {
  if (e.keyCode == 27) {

    $('.device.active').css({ 'right': '30px', 'left': '' }).animate({
      'left' : '0px'
    }).removeClass('active');
  }
});

// When Esc is pressed and devices were being edited, ask user to save or cancel edit, and deselect all devices
$(document).on('keyup', function (e) {
  if (e.keyCode == 27) {
    if($('.editing').length) {
      save_device();
      return;
    }
    $('.device.active').css({ 'right': '30px', 'left': '' }).animate({
      'left' : '0px'
    }).removeClass('active');
  }
});

// If clicking on page while device is selected, deselect all. If editing a device, ask user to save or cancel edit
$('body').click(function(e) {
    if ($(e.target).closest('.device').length === 0) {
      if($('.editing').length) {
        save_device();
        return;
      }
      $('.device.active').css({ 'right': '30px', 'left': '' }).animate({
        'left' : '0px'
      }).removeClass('active');
    }
});

// When enter is pressed inside search bar, trigger a search on its value
$("#searchBar").on('keyup', function (e) {
  if (e.keyCode == 13) {
    search();
  }
});

// If the search button is pressed, trigger a search on the search bar's value
$("#searchBtn").click(function() {
  search();
});

// Uses HTML5 History API to set a new state every time the search is called to allow navigating back and forward through past searches
function search() {
  var search = $('#searchBar').val();
  var stateObj = { query: search };
  history.pushState(stateObj, "search", "./#search");
  location.href = "#search";
  $(window).trigger('hashchange');
}

//
function svsi_login(user,pw,ip) {
  var form_svsi = '<form id="temp_svsi_form" target="_blank" method="post" action="http://'+ip
  +'/login.php"><input name="username" value="'+user
  +'"/><input name="password" value="'+pw
  +'"></form>';
  $(form_svsi).appendTo('body').submit();
  //$("temp_svsi_form").remove();
}

// When using the forward or back button, checks if there was a stored search parameter and loads it
window.onpopstate = function(e) {
  if(e.state.query) {
    $('#searchBar').val(e.state.query);
    $(window).trigger('hashchange');
  }
}

// gets new datapoints for the dashboard chart
function chartsReset() {
  $.post("datapoints_device_status.php",
    function(data) {
      initChart(JSON.parse(data));
    }
  );
}

// set of functions dealing with the table data, selecting, and action buttons
function tableAnimate() {
  // Checks the title of the buttons on the device card to see what was pressed
  $(".actions").click(function (e) {
    var action = this.title;
    switch(action) {
      case 'Copy':
        copy_device();
        break;
      case 'Edit':
        edit_device();
        break;
      case 'Delete':
        delete_device();
        break;
      case 'Refresh':
        refresh_device();
        break;
      case 'Save':
        save_device();
        break;
      case 'History':
        show_history();
        break;
      default:
        break;
    }
    e.stopPropagation();
  });

  // clicking on a link in a card does not click the card itself for selection
  $(".device a").click(function (e) {
    e.stopPropagation();
  });

  // logic for selecting, ctrl-slecting, shift-selecting, and animating selections of cards
  $(".device").click(function(e) {
    e.preventDefault();
    if($(this).hasClass('editing')) return;
    if($('.editing').length) {
      save_device();
      return;
    }
    // if no modifier, de-select all other cards and select this one
    if(!ctrled & !shifted) {
      $(this).siblings('.active').css({ 'right': '30px', 'left': '' }).animate({
        'left' : '0px'
      }).removeClass('active');
    }
    // if shift is pressed select all cards from the last card to this card
    if(shifted) {
      if($(lastSelectedDevice).index() < $(this).index()) {
        $(lastSelectedDevice).nextUntil(this).not('.active').addClass('active').css({ 'right': '0px', 'left': '' }).animate({
          'right' : '30px'
        });
      } else {
        $(lastSelectedDevice).prevUntil(this).not('.active').addClass('active').css({ 'right': '0px', 'left': '' }).animate({
          'right' : '30px'
        });
      }
      document.getSelection().removeAllRanges();
    }
    $(this).toggleClass('active');
    if($(this).hasClass('active')) {
      lastSelectedDevice = this;
      $(this).css({ 'right': '0px', 'left': '' }).animate({
        'right' : '30px'
      });
    } else {
      $(this).css({ 'right': '30px', 'left': '' }).animate({
        'left' : '0px'
      });
    }
  });
}

// if a different sorting method is chosen through the menu, update session var and refresh page
$('.sort-choose').click(function() {
  var option = $(this).text();
  $(this).siblings().removeClass('active');
  $(this).addClass('active');
  $.post("update_sort.php",
  {
    option:option
  },
  function(data) {
    $(window).trigger('hashchange');
  });
});

// if column visibility is changed through the menu, update session var and refresh page
$('.col-choose').click(function() {
  var option = this.id;
  $(this).toggleClass('active');
  $.post("update_col.php",
  {
    option:option
  },
  function(data) {
    $(window).trigger('hashchange');
  });
});

/*
  If refresh button is pressed on a card, put all selected cards' MAC addresses in an array
  get new network data through lens AJAX call, put new data in DB, and refresh page
*/
function refresh_device() {
  var macs = [];
  $(".device.active .card-body").css({"border-color":"white"});
  $(".device.active .fa-refresh").addClass("fa-spin");
  $(".device.active").each(function() {
    macs.push($(this).find(".mac").eq(0).text());
  });
  if(macs.length) {
    $.post("refresh_device.php",
    {
      macs:macs
    },
    function(data) {
      $(window).trigger('hashchange');
    });
  }
}

/*
  If refresh button is pressed on a card, put all selected cards' MAC addresses in an array
  And call AJAX function to delete records for all those MACs
*/
function delete_device() {
  var macs = [];
  $(".device.active").each(function() {
    macs.push($(this).find(".mac").eq(0).text());
  });
  if(macs.length) {
    if(confirm("Delete these devices?")) {
      $.post("delete_device.php",
      {
        macs:macs
      },
      function(data) {
        alert(data);
        $(window).trigger('hashchange');
      });
    }
  }
}

/*
  I the copy button is pressed on any card, go through each active card
  copy data as CSV to clipboard, and separate each card with newline
  Does so by creating temporary input field, placing data in there, focusing on it, and executing a system copy
  TODO: Only copy columns which are visible
*/
function copy_device() {
  $('#copyText').css('display','inline');
  $(".device.active").each(function() {
    var loc = $(".loc",this).text();
    var man = $(".man",this).text();
    var mod = $(".mod",this).text();
    var dev = $(".dev",this).text();
    var mac = $(".mac",this).text();
    var det = $(".det",this).text();
    var ip = $(".ip",this).text();
    var sub = $(".sub",this).text();
    var net = $(".net",this).text();
    var sw = $(".sw",this).text();
    var port = $(".port",this).text();
    var jack = $(".jack",this).text();
    $('#copyText').val($('#copyText').val()+loc+', '+man+', '+mod+', '+dev+', '+mac+', '+det+', '+ip+', '+sub+', '+net+', '+sw+', '+port+', '+jack+'\n\n');
  });
  $('#copyText').select();
  document.execCommand("copy");
  $('#copyText').css('display','none');
}

// When edit is pressed on any card, turn all editable fields on selected cards to input fields
function edit_device() {
  $(".device.active").addClass('editing');
  $(".device.active").find(".fa-pen").removeClass('fa-pen').addClass('fa-save').prop('title','Save');
  $(".device.active").find(".data").each(function() {
    data = $(this).text();
    var placeholder = '';
    // Fill in placeholders so empty fields are labeled
    if($(this).hasClass('loc')) placeholder = 'Location';
    if($(this).hasClass('dev')) placeholder = 'Device Type';
    if($(this).hasClass('det')) placeholder = 'Additional Notes';
    if($(this).hasClass('mac')) placeholder = '00:11:22:33:44:FF';
    if($(this).hasClass('sn')) placeholder = 'Serial Number';
    if($(this).hasClass('ptag')) placeholder = 'PTAG';
    if($(this).hasClass('dep')) placeholder = 'Department';
    if($(this).hasClass('mac')) placeholder = 'MAC Address';
    if($(this).hasClass('man')) placeholder = 'Manufacturer';
    if($(this).hasClass('mod')) placeholder = 'Model Number';
    // Otherwise, fill the input with the current value
    $(this).html('<input value="'+data+'" placeholder="'+placeholder+'" ></input>');
  });
}

// When save is pressed on any card being edited, save all cards being edited by sending an AJAX request for each
function save_device() {
  if(confirm('Save changes to '+$('.editing').length+' devices?')) { // allow user to cancel changes
    var count = 0; // we want to count how many were successfully edited
    var ajaxCount = 0; // to keep track of how many requests we have left to complete
    $(".editing").each(function() {
      var loc = $(".loc input",this).val();
      var man = $(".man input",this).val();
      var mod = $(".mod input",this).val();
      var dev = $(".dev input",this).val();
      var mac = $(".mac input",this).val();
      var det = $(".det input",this).val();
      var sn = $(".sn input",this).val();
      var ptag = $(".ptag input",this).val();
      var dep = $(".dep input",this).val();
      var old_mac = $(".old_mac",this).val();
      ajaxCount++;
      $.post("edit_device.php",
      {
        loc:loc,
        man:man,
        mod:mod,
        dev:dev,
        mac:mac,
        det:det,
        sn:sn,
        ptag:ptag,
        dep:dep,
        old_mac:old_mac
      },
      function(data) {
        // since the requests are asynchronous, we need to dynamically keep track of when all requests are complete
        count++;
        ajaxCount--;
        if(ajaxCount == 0) { // don't trigger refresh until all requests are done
          alert(count+" devices edited");
          $(window).trigger('hashchange');
        }
      });
    });
  } else {
    $(window).trigger('hashchange'); // if user cancels, refresh page to reset data
  }
}

// AJAX request sent when Add Device form is submitted
function add_device() {
  var loc = $('#loc_input').val();
  var man = $('#man_input').val();
  var mod = $('#mod_input').val();
  var dev = $('#dev_input').val();
  var mac = $('#mac_input').val();
  var det = $('#det_input').val();
  var sn = $('#sn_input').val();
  var ptag = $('#ptag_input').val();
  var dep = $('#dep_input').val();
  $('#addDevice').modal('toggle');
  $.post("add_device.php",
  {
    loc:loc,
    man:man,
    mod:mod,
    dev:dev,
    mac:mac,
    det:det,
    sn:sn,
    ptag:ptag,
    dep:dep
  },
  function(data) {
    alert(data);
    $(window).trigger('hashchange');
  });
  return false;
}

/*
  When the History button is clicked on a card,
  put the MACs of all selected cards into an array
  Set that as the history session variables
  Then call the history page, which will use the session variable to retrieve data
*/
function show_history() {
  var macs = [];
  $(".device.active").each(function() {
    macs.push($(this).find(".mac").eq(0).text());
  });
  if(macs.length) {
    $.post("set_history.php",
    {
      macs:macs
    },
    function(data) {
      window.location.hash = '#history';
      $(window).trigger('hashchange');
    });
  }
}

/*
  When the Import Device form is submitted, grab the file data from the client,
  create a form object to put that file data in, and send it through an AJAX request
*/
function import_device() {
  if (window.File && window.FileReader && window.FileList && window.Blob) {
    var file_data = $('#import_file').prop('files')[0];
    var form_data = new FormData();
    form_data.append('file', file_data);
    $('#importDevice').modal('toggle');
    $.ajax({
      url:"import_device.php",
      type:'POST',
      cache: false,
      contentType: false,
      processData: false,
      data:form_data,
      success: function(data) {
        alert(data);
      },
      error: function() {
        alert('error');
      }
    });
  } else {
    alert('The File APIs are not fully supported in this browser.');
  }
  return false;
}

function export_device() {
  let export_data = "data:text/csv;charset=utf-8,";
  $(".device").each(function() {
    var loc = $(".loc",this).text();
    var man = $(".man",this).text();
    var mod = $(".mod",this).text();
    var dev = $(".dev",this).text();
    var mac = $(".mac",this).text();
    var det = $(".det",this).text();
    var sn = $(".sn",this).text();
    var ptag = $(".ptag",this).text();
    var dep = $(".dep",this).text();
    var ip = $(".ip",this).text();
    var sub = $(".sub",this).text();
    var net = $(".net",this).text();
    var sw = $(".sw",this).text();
    var port = $(".port",this).text();
    var jack = $(".jack",this).text();
    export_data += loc+","+man+","+mod+","+dev+","+mac+","+det+","+sn+","+ptag+","+dep+","+ip+","+sub+","+net+","+sw+","+port+","+jack+"\r\n";
  });
  var encodedUri = encodeURI(export_data);
  link = document.createElement('a');
  link.setAttribute('href', export_data);
  link.setAttribute('download', 'export.csv');
  link.click();
}

// grabbed straight from canvasJS site for initializing charts
function initChart(data) {
  var chart = new CanvasJS.Chart("chartContainer", {
    theme: "dark2",
    animationEnabled: true,
    backgroundColor: "transparent",
    title: {
      verticalAlign: "center",
      dockInsidePlotArea: true,
      fontColor: "white",
      text: "Device Status"
    },
    toolTip:{
      content:"{status}: {y}"
    },
    data: [{
      type: "doughnut",
      dataPoints: data
    }]
  });

  function updateChart() {
    var color,deltaY, yVal;
    $.post("datapoints_device_status.php",
      function(data) {
        chart.options.data[0].dataPoints = JSON.parse(data);
        chart.render();
        $('.canvasjs-chart-credit').hide();
      }
    );

  };
  updateChart();
  setInterval(function () { updateChart() }, 300000); // update chart every 5 minutes because that's how frequently the server script runs
}
