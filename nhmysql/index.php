<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';

$allowedUsers = ['admin@nanoheal.com'];

if (!in_array($_SESSION["user"]["adminEmail"], $allowedUsers)) {
    header('Location: ../index.php');
}

if (getenv('ACCESS_NHMYSQL') != 'true'){
  header('Location: ../index.php');
}
?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">

<head>
    <title>Database Master</title>

    <script src="jquery.min.js"></script>
    <link rel="stylesheet" href="bootstrap.min.css" crossorigin="anonymous">
    <script src="bootstrap.min.js" crossorigin="anonymous"></script>

    <style>
        body {
            border: 5px solid lightgray;
            padding: 5px;
        }

        * {
            font: 12px Verdana;
        }

        #query-valu {
            resize: none;
            height: 70vh;
            max-height: 80vh;
            width: 100%;
            padding: 5px;
        }

        #query-data {
            resize: none;
            height: 73%;
            width: 100%;
            overflow-x: scroll;
        }

        table {
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        td {
            padding: 5px;
        }

        td>span {
            font-weight: bold;
        }

        img {
            height: 5%;
        }

        /*input[type=button] {
                padding: 5px;
                border-radius: 5px;
                border: 2px solid darkgrey;
            }*/
    </style>
</head>

<body>
    <form name="query-form" id="query-form">
        <textarea name="query-valu" id="query-valu" placeholder="Enter your query here..."></textarea>
        <br /><br />
        <input type="button" class="btn btn-success" name="query-submit" id="query-submit" value="Run Query">
        <input type="button" class="btn btn-danger" name="query-clear" id="query-clear" value="Clear">
        <input type="button" class="btn btn-primary" name="new-tab" id="new-tab" value="New Tab">
        <img src="../vendors/images/loader2.gif" alt="loading..." style="display: none;" id="qry-loader">
        <br /><br />
    </form>
    <!--<textarea name="query-data" id="query-data" placeholder="Resulting data..."></textarea>-->
    <div name="query-data" id="query-data" placeholder="Resulting data...">Result appears here...</div>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#query-valu').focus();
        });

        $('#query-submit').click(function() {
            $('#query-data').val('');
            $('#qry-loader').show();
            $.ajax({
                url: 'query-master.php',
                method: 'POST',
                data: $('#query-form').serialize(),
                success: function(data) {
                    $('#qry-loader').hide();
                    $('#query-data').html($.trim(data));
                },
                error: function(err) {
                    console.log(err);
                }
            });
        });

        $('#query-clear').click(function() {
            $('input[name=query-type]').prop('checked', false);
            $('textarea').val('');
        });

        $('#new-tab').click(function() {
            window.open('../nhmysql', '_blank');
        });
    </script>
</body>

</html>
