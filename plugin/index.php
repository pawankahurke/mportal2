<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
if (!isset($_SESSION['user']['username']) || $_SESSION['user']['username'] == '') {
    header("location:../index.php");
    die();
}

$respmsg = url::issetInRequest('st') ? url::requestToAny('st') : '';

?>

<html>
    <head>
        <title>NH | Event JSON Parser</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="asset/bootstrap.min.css">
        <script src="asset/jquery.min.js"></script>
        <script src="asset/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="jumbotron text-center">
            <h4>JSON Parser</h4>
        </div>
        <div class="container">
            <form id="eventjsonform" name="eventjsonform" method="post" action="jsonParseFunc.php" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-sm-3" style="margin-top: 7px;">
                        <label>Parser Type : </label>
                        <input type="radio" id="jsontypeasset" name="jsontype" value="asset"> Asset &nbsp;&nbsp;
                        <input type="radio" id="jsontypeevent" name="jsontype" value="event"> Event
                    </div>
                    <div class="col-sm-2" style="margin-top: 7px;">
                        <label>Upload File(JSON)</label>
                    </div>
                    <div class="col-sm-3">
                        <input type="file" class="form-control" id="eventjsondata" name="eventjsondata[]" multiple="" />
                    </div>
                    <div class="col-sm-1">
                        <input type="button" class="btn btn-primary" id="eventjsonbtn" name="eventjsonbtn" value="Submit" />
                    </div>
                    <div class="col-sm-3" style="color: red; margin-top: 7px;">
                        <span id="respmsg"><?php echo $respmsg; ?></span>
                    </div>
                </div>
            </form>
        </div>


        <script type="text/javascript">

            $('#eventjsonbtn').click(function () {
                var jsontype = $('input[name=jsontype]:checked').val();
                var fileinfo = $('#eventjsondata').val();
                var file_ext = fileinfo.split('.')[1];
                if(typeof jsontype == 'undefined') {
                    $('#respmsg').html('Please select a type');
                } else if (fileinfo == '') {
                    $('#respmsg').html('Please upload a JSON file.');
                } else if (file_ext != 'json') {
                    $('#respmsg').html('Only JSON File type is allowed.');
                } else {
                    $('#eventjsonform').submit();
                    /*$.ajax({
                        url: 'jsonParseFunc.php',
                        type: 'POST',
                        dataType: 'json',
                        data: $('#eventjsonform').serialize(),
                        success: function (data) {

                        },
                        error: function (error) {

                        }
                    });*/
                }
            });
        </script>
    </body>
</html>
