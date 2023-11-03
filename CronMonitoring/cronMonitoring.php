<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?>

<div class="content white-content" onload="siteDataTable();">
  <div class="row mt-2">
    <div class="col-md-12 pl-0 pr-0">
      <div class="card">
        <div class="card-body">
          <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="site_grid">
            <thead>
            <tr>
              <th id="key0" headers="sitename">
                Cron Name
              </th>
              <th id="key1" headers="username">
                Last launch
              </th>
              <th id="key2" headers="firstcontact" >
                Periodicity (minutes)
              </th>
              <th id="key3" headers="customer_name" >
                Time Execution (hours)
              </th>
              <th id="key4" headers="skuname">
                Count Launch
              </th>
            </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="../assets/js/core/jquery.min.js"></script>

<script>
  $.ajax({
    url: '../CronMonitoring/cronMonitoringFunction.php',
    type: 'POST',
    data: {'function':'getCronMonitoring'},
    success: function(data) {
      $('#site_grid').append(data);
    }
  });
</script>
