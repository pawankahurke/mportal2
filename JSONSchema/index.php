<!DOCTYPE html>
<html lang="en" xml:lang="en">
<title>JSON schema</title>
<head>
    <!-- ../JSONSchema/ -->
        <meta name=viewport content='width=560'>
        <link rel="stylesheet" href='lib/css/bootstrap.min.css'/>
        <link rel="stylesheet" href="lib/css/codemirror.css">
        <link rel="stylesheet" href="lib/css/bootstrap-select.min.css">
        <link rel="stylesheet" href="lib/css/octicons.css">

        <link rel="stylesheet" href='lib/css/brutusin-json-forms.css'/>

        <style>
            img {
                max-width: 100%
            }
            .CodeMirror {
                height: 400px;
            }

            .floating-box {
                display: inline-block;
                width: 150px;
                height: 75px;
                margin: 10px;
            }
        </style>

        <script src='lib/js/jquery-1.11.3.min.js'></script>
        <script src='lib/js/bootstrap.min.js'></script>
        <script src="lib/js/codemirror.js"></script>
        <script src="lib/js/codemirror-javascript.js"></script>
        <script src="lib/js/markdown.min.js"></script>
        <script src="lib/js/bootstrap-select.min.js"></script>
        <script src="lib/js/defaults-en_US.min.js"></script>


        <script src="lib/js/brutusin-json-forms.js"></script>
        <script src="lib/js/brutusin-json-forms-bootstrap.min.js"></script>
        <script lang="javascript">
            var BrutusinForms = brutusin["json-forms"];
            BrutusinForms.bootstrap.addFormatDecorator("inputstream", "file", "glyphicon-search", function (element) {
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
            var selectedDemo;
            var schurl="dartWizFunc.php?func=getAllJsonSchemaNew";
            var dartlist="dartWizFunc.php?func=getDartList";

			var schemaReturn="";
            var demos;



            function loadDartList(){
                $.ajax({
						url: dartlist,
						success: function(jsonobj) {
							dartlistReturn = jsonobj;
						},
						async:false
                    });
                    darts =JSON.parse(dartlistReturn);

                     var ddHTML="";
                    //$("#examples").find('option').remove();
                    for (var i = 0; i < darts.length; i++) {
                        if(i==0){
                            ddHTML+="<option "+"value='"+darts[i]+"' selected"+">" + darts[i] + "</option>";
                        }else{
                            ddHTML+="<option "+"value='"+darts[i]+"'"+">" + darts[i] + "</option>";
                        }
                    }
                    document.getElementById("darts").innerHTML= ddHTML;

                    createDARTJsonschma();
            }

            function loadvarlist(){
                var dartno = $('#darts').find(":selected").text();

                $.ajax({
						url: schurl+"&dartno="+dartno,
						success: function(jsonobj) {
							schemaReturn = jsonobj;
						},
						async:false
                    });
                   // console.log(schemaReturn);
                    demos =JSON.parse(schemaReturn);
                    //$("#examples").innerHTML= ddHTML;
            }

                  //  var demos =JSON.parse(schemaReturn);


            var selectedTab = "schema";
            var bf;

            function selectExample(selectedExampleIndex) {

                document.getElementById("examples").selectedIndex = selectedExampleIndex;

                input.schema = demos[selectedExampleIndex][1];
                input.data = demos[selectedExampleIndex][2];
                input.inidata  = demos[selectedExampleIndex][3];

                //eval("input.resolver=" + demos[selectedExampleIndex][3]);

                inputString.schema = JSON.stringify(input.schema, null, 2);
                //inputString.data = input.data ? JSON.stringify(input.data, null, 2) : "";
                inputString.data = JSON.stringify(input.data, null, 2);

                inputString.inidata = JSON.stringify(input.inidata, null, 2);
                //inputString.resolver = demos[selectedExampleIndex][3] ? demos[selectedExampleIndex][3] : "";
                if (codeMirrors[selectedTab]) {
                    codeMirrors[selectedTab].setValue(inputString[selectedTab]);
                }
                title = demos[selectedExampleIndex][0];
                desc = markdown.toHTML(demos[selectedExampleIndex][4]);
            }


            function route() {

                loadvarlist();

                if (!window.onhashchange) {
                    window.onhashchange = route;
                }
                selectedDemo = parseInt(window.location.hash.substring(1));
                if (isNaN(selectedDemo) || selectedDemo < 0 || selectedDemo >= demos.length) {
                    selectedDemo = 0;
                }

                selectExample(selectedDemo);


                var ddHTML="";
                    //$("#examples").find('option').remove();

                    for (var i = 0; i < demos.length; i++) {
                        ddHTML+="<option " + (selectedDemo === i ? "selected=true" : "") + ">" + demos[i][0] + "</option>";
                    }
                    document.getElementById("examples").innerHTML= ddHTML;
            }

            function generateForm() {
                var schema;
                var data;
                var inidata;
                var message;
                var resolver;
                var tabId;

                inputString[selectedTab] = codeMirrors[selectedTab].getValue();
                $("#jsonAlert").hide();
                try {
                    message = "The was a syntax error in the schema JSON";
                    tabId = "schema";
                    eval("schema=" + inputString.schema);
                    if (inputString.data) {
                        message = "The was a syntax error in the parser data JSON";
                        tabId = "data";
                        eval("data=" + inputString.data);
                    } else {
                        data = null;
                    }

                    if (inputString.inidata) {
                        message = "The was a syntax error in the initial data JSON";
                        tabId = "inidata";
                        eval("inidata=" + inputString.inidata);
                    } else {
                        inidata = null;
                    }

                    if (inputString.resolver) {
                        tabId = "resolver";
                        message = "The was a syntax error in the resolver code";
                        eval("resolver=" + inputString.resolver);
                        if ("function" !== typeof resolver) {
                            throw "Schema resolver does not evaluate to a function";
                        }
                    }
                } catch (err) {
                    document.getElementById('error-message').innerHTML = message + (err ? ". " + err : "");
                    $('[href=#' + tabId + ']').tab('show');
                    $("#jsonAlert").show();
                    return;
                }
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

                bf.render(container, inidata);
            }

            function validateparser(){
                var schema;
                var data;
                var message;
                var resolver;
                var tabId;

                inputString[selectedTab] = codeMirrors[selectedTab].getValue();

                $("#jsonAlert").hide();
                try {
                    message = "The was a syntax error in the schema JSON";
                    tabId = "schema";
                    eval("schema=" + inputString.schema);
                    if (inputString.data) {
                        message = "The was a syntax error in the initial data JSON";
                        tabId = "data";
                        eval("data=" + inputString.data);
                    } else {
                        data = null;
                    }
                    if (inputString.resolver) {
                        tabId = "resolver";
                        message = "The was a syntax error in the resolver code";
                        eval("resolver=" + inputString.resolver);
                        if ("function" !== typeof resolver) {
                            throw "Schema resolver does not evaluate to a function";
                        }
                    }
                } catch (err) {
                    document.getElementById('error-message').innerHTML = message + (err ? ". " + err : "");
                    $('[href=#' + tabId + ']').tab('show');
                    $("#jsonAlert").show();
                    return;
                }

                var selName = $('#examples').find(":selected").text();

                var varid="";
                if(selName=="Add new schema"){
                    var jobj=JSON.parse(inputString.schema);
                    //alert(JSON.stringify(jobj.properties));
                    varid=Object.keys(jobj.properties)[0];
                }else{
                    varid=selName;
                }

                if(varid===""){
                    alert("Not Valid Schema");
                }else{
                var resobj = {};
                resobj["varid"]=varid;
                var schemaObj=JSON.parse(inputString.schema);
                var varObj=schemaObj.properties;
                resobj["schema"]=JSON.stringify(bf.getData(), null, 4);
                resobj["parser"]=inputString.data;
                resobj["inidata"]=inputString.inidata;


                var parseObj=inputString.data;


                $.ajax({
                        url: "schemaWizFunc.php?func=validateSchemaParser",
                        type: "POST",
                        dataType: "json",
                        data:JSON.stringify(resobj),
                        async: false,
                        success: function(jsonobj) {
                            //route();
                            alert(JSON.stringify(jsonobj.response));
						}
                    });


            }
        }

            function saveSchemaDetails(){
                var schema;
                var data;
                var message;
                var resolver;
                var tabId;

                inputString[selectedTab] = codeMirrors[selectedTab].getValue();

                $("#jsonAlert").hide();
                try {
                    message = "The was a syntax error in the schema JSON";
                    tabId = "schema";
                    eval("schema=" + inputString.schema);
                    if (inputString.data) {
                        message = "The was a syntax error in the initial data JSON";
                        tabId = "data";
                        eval("data=" + inputString.data);
                    } else {
                        data = null;
                    }
                    if (inputString.resolver) {
                        tabId = "resolver";
                        message = "The was a syntax error in the resolver code";
                        eval("resolver=" + inputString.resolver);
                        if ("function" !== typeof resolver) {
                            throw "Schema resolver does not evaluate to a function";
                        }
                    }
                } catch (err) {
                    document.getElementById('error-message').innerHTML = message + (err ? ". " + err : "");
                    $('[href=#' + tabId + ']').tab('show');
                    $("#jsonAlert").show();
                    return;
                }

                var selName = $('#examples').find(":selected").text();

                var varid="";
                if(selName=="Add new schema"){
                    var jobj=JSON.parse(inputString.schema);
                    //alert(JSON.stringify(jobj.properties));
                    varid=Object.keys(jobj.properties)[0];
                }else{
                    varid=selName;
                }

                if(varid===""){
                    alert("Not Valid Schema");
                }else{
                var resobj = {};
                resobj["varid"]=varid;
                var schemaObj=JSON.parse(inputString.schema);
                var varObj=schemaObj.properties;
                resobj["schema"]=JSON.stringify(varObj[varid]);
                resobj["parser"]=inputString.data;
                resobj["inidata"]=inputString.inidata;

                var schobj=JSON.stringify(varObj[varid]);
                var parseObj=inputString.data;


                $.ajax({
                        url: "dartWizFunc.php?func=submitSchema",
                        type: "POST",
                        dataType: "json",
                        data:JSON.stringify(resobj),
                        async: false,
                        success: function(jsonobj) {
                            route();
                            alert(JSON.stringify(jsonobj));
						}
                    });
                }
            }
        </script>

    </head>
    <body>
        <!-- <a href="https://github.com/brutusin/json-forms/tree/gh-pages"><img style="position: absolute; top: 0; right: 0; border: 0; width: 149px; height: 149px;" src="img/forkme.png" alt="Fork me on GitHub"></a> -->
        <div class="container" >
            <!-- <h1><img alt="Butusin" src="https://avatars0.githubusercontent.com/u/10341159?v=3&s=200" style="border: 0; width: 50px; height: 50px;"/><code>brutusin:json-forms</code> demo</h1>

            <blockquote>
                <p><b>JSON Schema to HTML form generator</b>, supporting dynamic subschemas (on the fly resolution). Extensible and customizable library with zero dependencies. Bootstrap add-ons provided.</p>
                <p>Source code and documentation available at <a href="https://github.com/brutusin/json-forms"><span class="octicon octicon-logo-github"></span></a></p>
                <p>Originally created for <a href="http://rpc.brutusin.org">Brutusin-RPC</a>. See who else is using it <a href="https://github.com/brutusin/json-forms/blob/master/WHO-IS-USING.md">here</a>.</p>
            </blockquote> -->
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                <div class="panel panel-primary">
                    <div class="panel-heading" role="tab" id="headingOne">
                        <h4 class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseInput" aria-expanded="true" aria-controls="collapseInput">
                                Input
                            </a>
                        </h4>
                    </div>
                    <div id="collapseInput" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                        <div class="panel-body">
                        <label for="darts">Dart list:</label>
                            <select class="form-control" id="darts" onchange="createDARTJsonschma()">

                            </select>
                            <br>
                            <label for="examples">Variable id list:</label>
                            <select class="form-control" id="examples" onchange="document.location.hash = this.selectedIndex;">

                            </select>
                            <br>
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#schema" aria-controls="schema" role="tab" data-toggle="tab">Schema</a></li>
                                <li role="presentation"><a href="#data" aria-controls="data" role="tab" data-toggle="tab">Parser data</a></li>
                                <li role="presentation"><a href="#inidata" aria-controls="inidata" role="tab" data-toggle="tab">Initial data</a></li>
                                <!-- <li role="presentation"><a href="#resolver" aria-controls="resolver" role="tab" data-toggle="tab">Schema resolver</a></li> -->
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="schema"></div>
                                <div role="tabpanel" class="tab-pane" id="data"></div>
                                <div role="tabpanel" class="tab-pane" id="inidata"></div>
                                <!-- <div role="tabpanel" class="tab-pane" id="resolver"></div> -->
                            </div>
                            <div class="alert alert-danger in" role="alert" id="jsonAlert" style="display:none">
                                <a href="#" onclick='$("#jsonAlert").hide();' class="close" >&times;</a>
                                <strong>Error!</strong> <span id="error-message"></span>
                            </div>

                        </div>

                        <div class="panel-footer">
                            <button class="btn btn-primary" onclick="generateForm()">Create form</button>
                            &nbsp;<button class="btn btn-primary" onclick="saveSchemaDetails()">Submit-Save Schema</button>

                        </div>
                    </div>
                </div>
                <div class="panel panel-primary">
                    <div class="panel-heading" role="tab" id="headingTwo">
                        <h4 class="panel-title">
                            <a class="collapsed" id="formLink" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseForm" aria-expanded="false" aria-controls="collapseForm">
                                Generated form
                            </a>
                        </h4>
                    </div>
                    <div id="collapseForm" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
                        <div class="alert alert-info" role="alert"><strong id="example-title"></strong><div id="example-desc"></div></div>
                        <div id='container' style="padding-left:12px;padding-right:12px;padding-bottom: 12px;"></div>
                        <div class="panel-footer">
                            <button class="btn btn-primary" onclick="alert(JSON.stringify(bf.getData(), null, 4))">getData()</button>&nbsp;<button class="btn btn-primary" onclick="if (bf.validate()) {
                                        alert('Validation succeeded')
                                    }">validate()</button>
                                    &nbsp;<button class="btn btn-primary" onclick="validateparser()">Validate Parser</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <script lang="javascript">

          loadDartList();


         function createDARTJsonschma(){
            $("#schema").html("");
            route();

            codeMirrors["schema"] = CodeMirror(document.getElementById("schema"), {
                value: JSON.stringify(demos[selectedDemo][1], null, 4),
                mode: "javascript",
                lineNumbers: true
            });

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                selectedTab = $(e.target).attr("aria-controls");
                var pt = $(e.relatedTarget);
                var prevTab;
                if (pt) {
                    prevTab = pt.attr("aria-controls");
                    inputString[prevTab] = codeMirrors[prevTab].getValue();
                }
                if (!codeMirrors[selectedTab]) {
                    codeMirrors[selectedTab] = CodeMirror(document.getElementById(selectedTab), {
                        mode: "javascript",
                        lineNumbers: true
                    });
                }
                codeMirrors[selectedTab].setValue(inputString[selectedTab]);
            }
            );
        }

        </script>
    </div>
</body>
</html>
