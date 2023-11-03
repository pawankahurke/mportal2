<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">
<title>ConfigImportExport Index</title>

<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<style>
.loader {
  border: 8px solid #f3f3f3;
  border-radius: 50%;
  border-top: 8px solid #3498db;
  width: 20px;
  height: 20px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
</head>

<body>

    <fieldset>
        <lable> Config Export file</lable><br><br>
        <form name="dartform">
            <select id="ShowSites">
                
            </select>
            <input type="hidden" name="action" id="uaction" value="Upload" />
            <button type="button" id="exportSiteDetails" onclick="exportDetails();">Export</button> 
        </form>

    </fieldset>
    <p>&nbsp;</p>
   <fieldset>
        <lable> Config Import file</lable><br><br>
        <form name="dartform">
            <select id="ShowSites2">

            </select>
            <input type="hidden" name="action" id="uaction" value="Upload" />
            <input type="file" name="csvfile" id="csvfile" required />

            <button type="button" id="uloadbtn" onclick="postCSVFile();">Import</button> <br/><br/><div class="loader" id="uloader" style="display:none"></div>

        </form>

    </fieldset>
    <p>&nbsp;</p>
    <div id="msg" style="font-size:15px;display:none;color:blue;"></div>

    <script>
        $(document).ready(function () {
            getAllSites();
        });
        
        function getAllSites(){
            var logo_data = new FormData();
            logo_data.append("action", 'getSites');
            $.ajax({
                url: 'configFunction.php',
                type: 'POST',
                data: logo_data,
                success: function(res) {
                    $('#ShowSites').html(res);
	 	    $('#ShowSites2').html(res);      
		},
                cache: false,
                contentType: false,
                processData: false
            });
        }
        
        function exportDetails(){
            var selected = $('#ShowSites').val();
		window.location.href = 'configFunction.php?action=export&mgroupuniq='+selected;
        }
        
	function postCSVFile() {
	    var mgroupuniq = $('#ShowSites2').val();
            $("#msg").hide();
            var file_data = $("#csvfile").prop("files")[0];
            var logo_data = new FormData();
            var csv_name = $("#csvfile").prop("files")[0]["name"];
            var action = $("#uaction").val();

            if(csv_name == ''){
                $("#msg").html("Please upload CSV file");
                $("#msg").show();
                return false;
            }

            logo_data.append("csvfile", file_data);
	    logo_data.append("action", action);
	    logo_data.append("mgroupuniq",mgroupuniq);
            $("#uloadbtn").prop('disabled',true);
            $("#uloader").show();


            $.ajax({
                url: 'configFunction.php',
                type: 'POST',
                data: logo_data,
		success: function(res) {
			if(res>0){
                    $("#msg").html("Import Successful!");
                    $("#msg").show();
                    $("#uloader").hide();}

                },
                cache: false,
                contentType: false,
                processData: false
            });

        }

    </script>


</body>


</html>
