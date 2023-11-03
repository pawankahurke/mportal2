<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?>

<link rel="stylesheet" href="../JSONSchema/lib/css/codemirror.css">
<link rel="stylesheet" href="../JSONSchema/lib/css/octicons.css">
<link rel="stylesheet" href='../JSONSchema/lib/css/brutusin-json-forms.css' />
<!-- <script src="../assets/js/core/jquery.min.js"></script> -->
<script src="../JSONSchema/lib/js/codemirror.js"></script>
<script src="../JSONSchema/lib/js/codemirror-javascript.js"></script>
<script src="../JSONSchema/lib/js/markdown.min.js"></script>
<script src="../JSONSchema/lib/js/defaults-en_US.min.js"></script>

<script src="../profileJSONSchema/lib/js/brutusin-json-forms.js"></script>
<script src="../JSONSchema/lib/js/brutusin-json-forms-bootstrap.min.js"></script>



<style>
    img {
        max-width: 100%
    }

    .CodeMirror {
        height: 400px;
    }

    #servicesajax-response-wrapper {
        transition: none;
    }
</style>

<script lang="javascript">
    var BrutusinForms = brutusin["json-forms"];
    BrutusinForms.bootstrap.addFormatDecorator("inputstream", "file", "glyphicon-search", function(element) {
        console.log("user callback on element " + element)
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
        $('#loader_submit').hide();
        var schema;
        var data;
        var message;
        var resolver;
        var tabId;
        $("#jsonAlert").hide();
        var dartno = "<?php echo url::requestToText('dartno'); ?>";
        var schurl = "../profileJSONSchema/schemaWizFunc.php";
        // var data = { "function": "get_dartJsonSchema", "dartno": dartno,csrfMagicToken:csrfMagicToken}
        var schemaReturn = "";
        $.ajax({
            url: schurl,
            type: "post",
            data: {
                'function': 'get_dartJsonSchema',
                'dartno': dartno,
                'dartindx': '<?php echo url::requestToText('dartindx'); ?>',
                'dartseqn': '<?php echo url::requestToText('dartseqn'); ?>',
                'dartTileToken': $('#dartTileToken').val(),
                'csrfMagicToken': csrfMagicToken
            },
            dataType: "json",
            success: function(jsonobj) {
                const schema = jsonobj?.schemadata?.properties;

                for (const key of Object.keys(jsonobj?.valuedata)) {
                    if(schema[key]?.type === 'integer') {
                        const newValue = jsonobj.valuedata[key];
                        jsonobj.valuedata[key] = parseInt(newValue);
                    }
                }

                schemaReturn = jsonobj;
            },
            async: false

        });

        var resultObj = schemaReturn;
        // var resultObj = JSON.parse(schemaReturn);
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

        $('#servicesajax-response-wrapper').find('button.btn-primary').addClass('btn-success').removeClass('btn-primary');
        $('#servicesajax-response-wrapper').find('button.btn-xs').addClass('btn-sm').removeClass('btn-xs');
        $('#servicesajax-response-wrapper').find('.glyphicon.glyphicon-info-sign').html('<i class="tim-icons icon-alert-circle-exc"></i>');

        $('#servicesajax-response-wrapper textarea').css('border', '1px solid #fa0f4b');
        $('button.glyphicon-remove').html('<i class="tim-icons icon-simple-remove"></i>').addClass('btn-success').removeClass('btn-primary');
    });


    function buttonclick() {
        console.log("button clicked");
    }

    function validateform() {
        if (bf.validate()) {
            return true;
        } else {
            return false;
        }
    }

    function makeBootstrapCheckBox(inputHtm) {
        return '<div class="form-check"><label class="form-check-label">' + inputHtm + '<span class="form-check-sign"></span></label></div>';
    }


    function findAndReplace(object, replacekey, replacevalue) {
        for (const key in object) {
            if (object.hasOwnProperty(key)) {

                if (key == replacekey) {
                    const element = object[key];
                    object[key] = replacevalue;
                }

            }
        }
    }

    function onExecuteButtonClick(varid) {
        var tempdata = bf.getData();
        findAndReplace(tempdata, varid, 2);
        onSubmit(tempdata);
    }

    function isEmpty(obj) {
        var result = true;
        if (obj == null)
            return result = true;

        if (obj.length > 0)
            return result;
        if (obj.length === 0)
            return result = true;

        if (typeof obj !== "object")
            return result = true;

        var containsVal = false;
        for (var key in obj) {
            if (obj[key] == 0 || obj[key] == null || obj[key] == "") {} else {
                console.log("key val: " + key + "----" + obj[key]);
                containsVal = true;
                break;
            }
        }
        console.log("containsVal: " + containsVal);
        if (containsVal) {
            return false;
        }
        return result = true;
    }

    function getClass(obj) {
        return Object.prototype.toString.call(obj);
    }

    function objectTester(a, b) {

        if (a === b)
            return true;

        if (typeof a != typeof b)
            return false;

        if (typeof a == 'number' && isNaN(a) && isNaN(b))
            return true;

        var aClass = getClass(a);
        var bClass = getClass(b)

        if (aClass != bClass)
            return false;

        if (aClass == '[object Boolean]' || aClass == '[object String]' || aClass == '[object Number]') {
            if (a.valueOf() != b.valueOf())
                return false;
        }

        if (aClass == '[object RegExp]' || aClass == '[object Date]' || aClass == '[object Error]') {
            if (a.toString() != b.toString())
                return false;
        }

        if (aClass == '[object Function]' && a.toString() != b.toString())
            return false;

        var aKeys = Object.keys(a);
        var bKeys = Object.keys(b);

        if (aKeys.length != bKeys.length)
            return false;

        if (!aKeys.every(function(key) {
                return b.hasOwnProperty(key)
            }))
            return false;

        return aKeys.every(function(key) {
            return objectTester(a[key], b[key])
        });
        return false;
    }

    function fetchOnlyChangedValue(dartno, submitting_value) {
        return new Promise((resolve, reject) => {
            var finaldata = {};
            $.ajax({
                url: "../profileJSONSchema/schemaWizFunc.php",
                type: "POST",
                dataType: "json",
                data: {
                    'function': 'get_dartJsonSchema',
                    'dartno': dartno,
                    'dartindx': '<?php echo url::requestToText('dartindx'); ?>',
                    'dartseqn': '<?php echo url::requestToText('dartseqn'); ?>',
                    'csrfMagicToken': csrfMagicToken
                },
                success: function(jsonobj) {
                    var Obj = jsonobj;
                    var existingdata = Obj.valuedata;
                    var submitObj = JSON.parse(submitting_value);

                    resolve(submitObj)
                },
                error: reject
            });
        })

    }

    function compareDifferenceObj(Obj1, Obj2) {
        // find keys
        keyObj1 = Object.keys(Obj1);
        keyObj2 = Object.keys(Obj2);

        // find values
        valueObj1 = Object.values(Obj1);
        valueObj2 = Object.values(Obj2);

        // find max length to iterate
        if (keyObj1.length > keyObj2.length) {
            var biggestKey = keyObj1.length;
        } else {
            var biggestKey = keyObj2.length;
        }
        var diffObj = {};

        // now compare their keys and values
        for (var i = 0; i < biggestKey; i++) {
            if (keyObj1[i] == keyObj2[i] && valueObj1[i] == valueObj2[i]) {

            } else {
                diffObj[keyObj1[i]] = valueObj1[i];
            }
        }

        return diffObj;
    }

    function isEmptyJSONObj(myObject) {
        for (var key in myObject) {
            if (myObject.hasOwnProperty(key)) {
                return false;
            }
        }
        return true;
    }

    async function onSubmit(exeValdata = "") {
        // Dashboard/profileJSONSchema/jsonschema.php

        // if (exeValdata == "") {
        //     $(".brutusin-form table tr td.prop-value button.btn-success").trigger('click')
        //     return;
        // }

        var precedence = $('#precedenceValue').val();
        $('#loader_submit').show();
        if (validateform()) {
            var dartno = "<?php echo url::requestToText('dartno'); ?>";
            var dartindx = "<?php echo url::requestToText('dartindx'); ?>";
            var dartseqn = "<?php echo url::requestToText('dartseqn'); ?>";

            if (exeValdata != "") {
                var tempdata = JSON.stringify(exeValdata);
            } else {
                var tempdata = JSON.stringify(bf.getData());
            }
            var submit_val = {};
            var submitObj = JSON.parse(tempdata);
            $.each(submitObj, function(key, value) {
                if (value == 'null') {
                    value.replace('null', " ");
                    submit_val[key] = "";
                } else {
                    submit_val[key] = value;
                }
            });

            var temp = JSON.stringify(submit_val);

            var finalSubValues = await fetchOnlyChangedValue(dartno, temp);

            console.log("FinalSubValues:" + JSON.stringify(finalSubValues) + " | Response : " + isEmptyJSONObj(finalSubValues));

            if (exeValdata == "" && (isEmptyJSONObj(finalSubValues) || JSON.stringify(finalSubValues).length <= 2)) {
                $('#loader_submit').hide();
                $.notify("No changes found in configuration");
            } else {
                updateVariablesData(finalSubValues, dartno, dartindx, dartseqn); // defined in js/profiles/profilewiz.js
            }

        } else {
            $('#loader_submit').hide();
        }
    }
</script>

<div id="servicesajax-response-wrapper" class="container">
    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">

    </div>
    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
        <div class="panel panel-primary" style="border:none;">
            <div class="panel-heading" role="tab" id="headingTwo" style="display:none;">
                <h4 class="panel-title">
                    <a class="collapsed" id="formLink" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseForm" aria-expanded="false" aria-controls="collapseForm">
                        Generated form
                    </a>
                </h4>
            </div>
        </div>

        <div id="collapseForm" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
            <div class="alert alert-info" role="alert" style="display:none;"><strong id="example-title"></strong>
                <div id="example-desc"></div>
            </div>
            <div id='container' style="padding-left:12px;padding-right:12px;padding-bottom: 12px;"></div>
            <div class="panel-footer">
                <!--							<button class="btn btn-success" onclick="onSubmit()">Submit</button>&nbsp;-->
                <!-- <button class="btn btn-primary" onclick="">validate()</button> -->
            </div>
        </div>
    </div>
</div>
<div id="loader_submit" style="display: none; text-align: center;">
    <br>
    <img src="../assets/img/loader.gif" />
    <br>
    <h5>Please wait while loading configuration..!</h5>
</div>
</div>
</div>
<link rel="stylesheet" href="../assets/css/services.css">