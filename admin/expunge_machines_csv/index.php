<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

?>

<style>
  .input-file {
    display: inline-block;
    position: relative;
    overflow: hidden;
    background-color: #3498db;
    color: #fff;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    display: block;
    margin: 0 auto
  }

  .button-upload {
    display: inline-block;
    position: relative;
    width: 200px;
    overflow: hidden;
    background-color: #13bb2c;
    color: #fff;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    display: block;
    border: 0;
    margin: 20px auto
  }
</style>

<html>
<div class="content white-content commonTwo">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body" >
          <div style="margin: 200px auto;width: 800px;background-color: #e3e3e3; padding: 25px 0">
            <p style="font-size: 22px;text-align: center">Select the CSV file for expunge machine</p>
              <input type="file" accept=".csv" class="input-file" id="fileInput">
              <button class="button-upload">
                UPLOAD AND START
              </button>
            <p style="text-align: center; color: #13bb2c; font-size: 20px" id="result"></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</html>

<script src="../../assets/js/core/jquery.min.js"></script>
<script>
  $('.button-upload').click(function () {
    var fileInput = $("#fileInput")[0].files[0];
    if (fileInput) {
      var formData = new FormData();
      formData.append("file", fileInput);

      $.ajax({
        url: "expunge.php", // Замените на URL вашего серверного скрипта
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          $('#result').html(response)
          console.log(response);
        }
      });
    }
  })
</script>
