<?php
  // A page to allow advanced options to be set.  Currently only has the "Live Lens Check" option available
  session_start();
  require_once('db_conn.php');
?>
<div class="form-check">
  <input class="form-check-input" type="checkbox" value="" id="lensCheck" <?php echo ($_SESSION['lens_check'] ? 'checked' : ''); ?>>
  <label class="form-check-label" for="lensCheck" style="color:white">
    Check Lens when searching for MAC Addresses
  </label>
</div>
<script>
  $('#lensCheck').change(function() {
    var option = $(this).is(':checked');
    $.post("update_lens_check.php",
    {
      option:option
    });
  });
</script>
