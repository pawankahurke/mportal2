<!DOCTYPE html>
<html lang="en" xml:lang="en">
<title>Jsonschema2<title>

        <head>
            <!-- ../JSONSchema/ -->
            <meta name=viewport content='width=560'>
            <!--        <link rel="stylesheet" href='../JSONSchema/lib/css/bootstrap.min.css'/>-->
            <link rel="stylesheet" href="../JSONSchema/lib/css/codemirror.css">
            <!--        <link rel="stylesheet" href="../JSONSchema/lib/css/bootstrap-select.min.css">-->
            <link rel="stylesheet" href="../JSONSchema/lib/css/octicons.css">

            <link rel="stylesheet" href='../JSONSchema/lib/css/brutusin-json-forms.css' />

            <style>
                img {
                    max-width: 100%
                }

                .CodeMirror {
                    height: 400px;
                }
            </style>

            <!--        <script src='../JSONSchema/lib/js/jquery-1.11.3.min.js'></script>
        <script src='../JSONSchema/lib/js/bootstrap.min.js'></script>-->
            <script src="../JSONSchema/lib/js/codemirror.js"></script>
            <script src="../JSONSchema/lib/js/codemirror-javascript.js"></script>
            <script src="../JSONSchema/lib/js/markdown.min.js"></script>
            <!--        <script src="../JSONSchema/lib/js/bootstrap-select.min.js"></script>-->
            <script src="../JSONSchema/lib/js/defaults-en_US.min.js"></script>

            <script src="../JSONSchema/lib/js/brutusin-json-forms.js"></script>
            <script src="../JSONSchema/lib/js/brutusin-json-forms-bootstrap.min.js"></script>
            <script lang="javascript">
                var BrutusinForms = brutusin["json-forms"];
                BrutusinForms.bootstrap.addFormatDecorator("inputstream", "file", "glyphicon-search", function(element) {
                    alert("user callback on element " + element)
                });
                BrutusinForms.bootstrap.addFormatDecorator("color", "color");
                BrutusinForms.bootstrap.addFormatDecorator("date", "date");
                BrutusinForms.bootstrap.addFormatDecorator("time", "time");

                var codeMirrors = new Object();
                var input = new Object();
                var inputString = new Object();
                var title;
                var desc;

                var selectedTab = "schema";
                var bf;

                $(document).ready(function() {
                    var schema;
                    var data;
                    var message;
                    var resolver;
                    var tabId;

                    $("#jsonAlert").hide();
                    var sequence = "<?php echo url::requestToAny('data'); ?>";
                    var DynamicVariables = "<?php echo url::requestToAny('dynamicVariables'); ?>";
                    $('#ConfigData').val(DynamicVariables);
                    var schurl = "../JSONSchema/schemaWizFunc.php";
                    var schemaReturn = "";
                    $("#seq_id").val(sequence);
                    $.ajax({
                        url: schurl,
                        type: "POST",
                        data: {
                            'function': 'get_seq_JsonSchema',
                            sequence: sequence,
                            csrfMagicToken: csrfMagicToken
                        },
                        //                                                dataType : "json",
                        success: function(jsonobj) {
                            //                                                    alert("success");
                            schemaReturn = jsonobj;
                            //alert(jsonobj);
                        },
                        error: function(error) {
                            //                                                    alert("error");
                        },
                        async: false
                    });
                    var resultObj = JSON.parse(schemaReturn);
                    schema = resultObj.schemadata;

                    $('#formLink').click();
                    bf = BrutusinForms.create(schema);
                    if (resolver) {
                        bf.schemaResolver = resolver;
                    }
                    var container = document.getElementById('container');
                    while (container.firstChild) {
                        container.removeChild(container.firstChild);
                    }
                    if (title) {
                        document.getElementById('example-title').innerHTML = title;
                    }
                    if (desc) {
                        document.getElementById('example-desc').innerHTML = desc;
                    }

                    bf.render(container, resultObj.valuedata);
                });

                function buttonclick() {
                    console.log("button clicked");
                }

                function validateform() {
                    if (bf.validate()) {
                        return true;
                        alert("true");
                    } else {
                        return false;
                        alert("false");
                    }
                }

                function onSubmit() {
                    debugger
                    //    submitJsonSchemaseq(bf.getData());
                    var Variables304 = $('#ConfigData').val();
                    var det = bf.getData();
                    var sequence = $("#seq_id").val();
                    var dartname = $('#slider-title').html();
                    console.log(det);
                    $.ajax({
                        url: "../JSONSchema/schemaWizFunc.php",
                        type: "POST",
                        dataType: "text",
                        data: {
                            function: 'submit_JsonSchemaseq',
                            data: det,
                            sequence: sequence,
                            dynamicConfig: Variables304,
                            name: dartname,
                            'csrfMagicToken': csrfMagicToken
                        },
                        async: false,
                        success: function(data) {
                            rightContainerSlideClose('config-trbl-container');
                            $.notify("Message pushed successfully");
                            console.log("success : " + data);
                            //$("#submitresult").html(jsonobj);
                            $("#seq_id").html(sequence);
                            triggerseq(data);
                        },
                        error: function(jsonobj) {
                            //                alert("error");
                        }
                    });
                }

                function triggerseq(data) {
                    var params = '';
                    params += "&Dart=286";
                    params += "&variable=NA";
                    params += "&shortDesc=NA";
                    params += "&Jobtype=Custom Profile";
                    params += "&ProfileName=" + encodeURIComponent(data);
                    params += "&NotificationWindow=0";
                    params += "&GroupName=";
                    params += "&OS=windows";
                    params += "&csrfMagicToken=" + csrfMagicToken;
                    params = 'function=Add_RemoteJobs' + params;
                    $.ajax({
                        type: "POST",
                        url: "../communication/communication_ajax.php",
                        data: params,
                        async: false,
                        success: function(data) {
                            console.log("Trigger : " + data);
                            // Trigger job
                            var result = data.split("##");
                            var SupportedMachines = result[0];
                            var NonSupportedMachines = result[1];
                            var ShowProgressServiceTag = result[3];
                            EmitJobsForServiceTags(SupportedMachines, ShowProgressServiceTag);
                        },
                        error: function(jsonobj) {
                            console.log("error");
                        }
                    });
                }
            </script>

        </head>

        <body>

            <div class="container">
                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                    <div class="panel panel-primary" style="border:none;">
                        <div class="panel-heading" role="tab" id="headingTwo" style="display:none;">
                            <h4 class="panel-title">
                                <a class="collapsed" id="formLink" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseForm" aria-expanded="false" aria-controls="collapseForm">
                                    Generated form
                                </a>
                            </h4>
                        </div>
                        <input type="hidden" id="ConfigData">
                        <div id="collapseForm" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
                            <div class="alert alert-info" role="alert" style="display:none;"><strong id="example-title"></strong>
                                <div id="example-desc"></div>
                            </div>
                            <div id='container' style="padding-left:12px;padding-right:12px;padding-bottom: 12px;"></div>
                            <div class="panel-footer">
                                <span id="submitresult" style="color:red"></span>
                                <button class="btn btn-success" onclick="onSubmit()">Submit</button>&nbsp;
                                <!-- <button class="btn btn-primary" onclick="">validate()</button> -->

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </body>

</html>