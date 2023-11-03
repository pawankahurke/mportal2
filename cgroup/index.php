<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

$host = getenv('DB_HOST') ?: "mysql-svc";
$user = getenv('DB_USERNAME') ?: "weblog";
$passwd = getenv('DB_PASSWORD') ?: "b6Q4qT17xyfYJS9CJP2019#";
$pdo = new PDO("mysql:host=$host;dbname=core",  $user, $passwd);


$sqlcheck = $pdo->query("select mgp.name,mgp.style,mgp.boolstring,count(mgm.mgmapid) as mcount from MachineGroups mgp left outer join MachineGroupMap mgm on mgm.mgroupuniq = mgp.mgroupuniq where mgp.style != 1 group by mgp.mgroupid");
$sqlresultres = $sqlcheck->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">
<title>index</title>

<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <style>
        .loader {
            border: 5px solid #f3f3f3;
            border-radius: 50%;
            border-top: 5px solid #3498db;
            width: 25px;
            height: 25px;
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

    <form method="post" name="exportform" action="generate.php" enctype="multipart/form-data">
        <button type="submit" name="Export">Download CSV</button>
    </form>

    <form action="groupfunc.php" method="post" id="importForm" name="upload_excel" enctype="multipart/form-data">
        <fieldset>
            <!-- Form Name -->
            <legend>Form Name</legend>
            <!-- File Button -->
            <div>
                <label for="filebutton">Select File</label>
                <div>
                    <input type="file" name="file" id="file" />
                </div>
            </div>
            <!-- Button -->
            <div>
                <label for="singlebutton">Import data</label>
                <div>
                    <button type="button" id="submit" name="Import" data-loading-text="Loading...">Import</button>
                </div>
            </div>
        </fieldset>
    </form>
    <br><br>
    <span id="sloader">
        <div class="loader"></div>Processing...
    </span>
    <div>
        <?php if (!empty($sqlresultres)) {  ?>
            <table border="1">
                <thead>
                    <th>Group name</th>
                    <th>Style number</th>
                    <th>Style name</th>
                    <th>Machine Count</th>
                </thead>
                <tbody>

                    <?php
                    foreach ($sqlresultres as $sqlr) { ?>


                        <tr>
                            <td><?php echo $sqlr['name']; ?> </td>
                            <td><?php echo $sqlr['style']; ?></td>
                            <td><?php echo $sqlr['boolstring']; ?></td>
                            <td><?php echo $sqlr['mcount']; ?></td>


                        </tr>







                    <?php }
                    ?>
                </tbody>
            </table>
        <?php } ?>
    </div>



</body>
<script>
    function downloadSample() {

        $.ajax({
            url: 'generate.php',
            type: 'POST',
            data: {},
            success: function(res) {},
            cache: false,
            contentType: false,
            processData: false
        });
    }
    $(document).ready(function() {
        var files;
        $("#sloader").hide();
        $('input[type=file]').on('change', prepareUpload);

        function prepareUpload(event) {
            files = event.target.files;

        };


        $('#submit').click(function() {
            if (files.length > 0) {
                var formData = new FormData();
                var groups = [];
                $.each(files, function(key, value) {

                    formData.append('file', value);
                });
                // alert(formData);
                $.ajax({
                    url: 'groupfunc.php',
                    type: 'POST',
                    data: formData,
                    success: function(res) {
                        var result = $.parseJSON(res);
                        //alert(r);
                        var htmlstr = '';

                        $.each(result, function(index, value) {
                            groups.push(value);
                            htmlstr = htmlstr + '' + index + ':' + value.toString() + '\n\n';

                        });
                        htmlstr = htmlstr + "These are all the groups which mentioned in CSV file "
                        // alert(htmlstr);
                        swal({
                                title: "Are you sure?",
                                text: htmlstr,
                                icon: "warning",
                                buttons: ['Cancel', 'Yes'],
                                dangerMode: true,
                            })
                            .then((willDelete) => {
                                if (willDelete) {
                                    // alert(groups.toString());

                                    formData.append('userlist', 'JohnSatya,ShamantG');
                                    formData.append('global', '1');
                                    formData.append('groups', groups.toString());
                                    $("#sloader").show();
                                    $.ajax({
                                        url: 'processcsv.php',
                                        type: 'POST',
                                        data: formData,
                                        success: function(data) {
                                            console.log(data);
                                            var det = $.parseJSON(data);
                                            if (det.status == 'success') {

                                                var textstr = "Groups created successfully and machines are grouped successfully\n\n" + "Total : " + det.total + "\nInserted record : " + det.inserted + "\nFailed records : " + det.failed + "\n\n";
                                                swal({
                                                    title: "Success",
                                                    text: textstr,
                                                    icon: "success",
                                                    button: "Ok",
                                                });

                                            } else {

                                                var textstr = "Groups creation and machines grouping failed\n\n" + "Total : " + det.total + "\nInserted record : " + det.inserted + "\nFailed records : " + det.failed + "\n\n";
                                                swal({
                                                    title: "Error",
                                                    text: textstr,
                                                    icon: "error",
                                                    button: "Ok",
                                                });


                                            }


                                            $("#sloader").hide();
                                        },
                                        cache: false,
                                        contentType: false,
                                        processData: false

                                    });
                                } else {
                                    //swal("Your imaginary file is safe!");
                                }
                            });


                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            } else {
                alert("Please input a csv file to proceed");
            }
        });
    });
</script>


</html>