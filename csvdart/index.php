<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

nhRole::dieIfNotSuperAdminRole();
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();


?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">
<title>csvdart index</title>

<head>
    <script src="../assets/js/core/jquery.min.js"></script>
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->
    <style>
        .loader {
            border: 8px solid #f3f3f3;
            border-radius: 50%;
            border-top: 8px solid #3498db;
            width: 20px;
            height: 20px;
            -webkit-animation: spin 2s linear infinite;
            /* Safari */
            animation: spin 2s linear infinite;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

    <fieldset>
        <a href="dartConfiguration.csv" download>Download sample file</a><br><br>
        <lable data-qa="Upload-dart-config-file"> Upload dart config file</lable><br><br>
        <form name="dartform">
            <input type="hidden" name="action" id="uaction" value="Upload" />
            <input type="file" name="csvfile" id="csvfile" data-qa="dart-config-file" required />

            <button type="button" id="uloadbtn" onclick="postCSVFile();">Import</button> <br /><br />
            <div class="loader" id="uloader" style="display:none"></div>

        </form>

    </fieldset>
    <p>&nbsp;</p>
    <fieldset>
        <label>Clear all records</label><br><br>
        <form name="dartdeleteform">
            <input type="hidden" name="action" id="daction" value="Delete" />

            <button type="button" onclick="deleteall();">Clear all records</button>

        </form>
    </fieldset>
    <p>&nbsp;</p>
    <div id="msg" style="font-size:15px;display:none;color:blue;"></div>

    <script>
        function postCSVFile() {
            $("#msg").hide();
            var file_data = $("#csvfile").prop("files")[0];
            var logo_data = new FormData();
            var csv_name = $("#csvfile").prop("files")[0]["name"];
            var action = $("#uaction").val();

            if (csv_name == '') {
                $("#msg").html("Please upload CSV file");
                $("#msg").show();
                return false;
            }

            logo_data.append("csvfile", file_data);
            logo_data.append("action", action);
            $("#uloadbtn").prop('disabled', true);
            $("#uloader").show();
            logo_data.append('csrfMagicToken', csrfMagicToken);

            $.ajax({
                url: 'csvdart.php',
                type: 'POST',
                data: logo_data,
                success: function(res) {


                    $("#msg").html(res);
                    $("#msg").show();
                    $("#uloader").hide();

                },
                cache: false,
                contentType: false,
                processData: false
            });

        }

        function deleteall() {


            var action = $("#daction").val();

            var logo_data = new FormData();

            logo_data.append("action", action);
            logo_data.append('csrfMagicToken', csrfMagicToken);


            $.ajax({
                url: 'csvdart.php',
                type: 'POST',
                data: logo_data,
                success: function(res) {

                    console.log(res);
                    $("#msg").html(res);
                    $("#msg").show();


                },
                cache: false,
                contentType: false,
                processData: false
            });

        }
    </script>


</body>


</html>