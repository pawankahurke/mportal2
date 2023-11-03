$(document).on('ready', function () {
    setTimeout(function () {
        $('#submenu_admin').mCustomScrollbar('scrollTo', "#scorereport_Admin_lm");
    }, 1000);
    confPage = false;
    //var custToConf = $('#custDropDown').val();

    get_scorecardviewData();
});


$(document).on('change', 'input[type=checkbox]', function () {
    var pval = $(this).attr('id');
    var pval_class = $(this).attr('class');
    
    if ($(this).is(':checked')) {
        var pcheck = pval_class.split('_')[1];
        $('.parent_'+pcheck).prop('checked', true);
        $(this).parents('ul').closest('ul').siblings('input:checkbox').prop('checked', true);
    } else {
        //$(this).find('li input[type=checkbox]').prop('checked', $(this).is(':checked'));
        //$('.childli_'+pval).find('input[type=checkbox]').prop('checked', $(this).is(':checked'));
        $(this).parent().find('li input[type=checkbox]').prop('checked', $(this).is(':checked'));
        if($(this).hasClass('parent_'+pval)) {
            $('.childli_'+pval).find('input[type=checkbox]').prop('checked', $(this).is(':checked'));
        }
    }
});

var subHeaders = [];
subHeaders[1] = 1;


function get_scorecardviewData() {

    if (!confPage) {
        $('#edit_parameter').show();
        $('#scoresearch').show();
        $('#back_scoreGrid').hide();

        $('#confMsg').hide();
        $('.se-pre-con').show();
        $.ajax({
            url: "scorecardFunction.php?function=getscoreGridData"+"&csrfMagicToken=" + csrfMagicToken,
            type: "POST",
            data: "",
            dataType: "json",
            success: function (gridData) {
                if (gridData == '') {
                    $('#confMsg').show();
                }
                $(".se-pre-con").hide();
                $('.se-pre-con').hide();
                $('#scoreCardGrid').DataTable().destroy();
                scoreTable = $('#scoreCardGrid').DataTable({
                    scrollY: jQuery('#scoreCardGrid').data('height'),
                    scrollCollapse: true,
                    paging: true,
                    searching: true,
                    ordering: true,
                    aaData: gridData,
                    bAutoWidth: false,
                    select: false,
                    bInfo: false,
                    responsive: true,
                    stateSave: true,
                    "stateSaveParams": function (settings, data) {
                        data.search.search = "";
                    },
                    "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                    "language": {
                        "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                        searchPlaceholder: "Search"
                    },
                    "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                    columnDefs: [{className: "checkbox-btn", "targets": [0]},
//                             { "width": "30%", "targets": 1 },    
                        {
                            targets: "datatable-nosort",
                            orderable: false
                        }],
                    initComplete: function (settings, json) {
                    },
                    drawCallback: function (settings) {
                        $(".dataTables_scrollBody").mCustomScrollbar({
                            theme: "minimal-dark"
                        });
                        $('.equalHeight').matchHeight();
                        $(".se-pre-con").hide();
                    }
                });
                $('.tableloader').hide();
            },
            error: function (msg) {
                $('.se-pre-con').hide();
            }
        });

        $('#scoreCardGrid').on('click', 'tr', function () {

            var rowID = scoreTable.row(this).data();
            $('#scorename').val(rowID[0]);
            $('#scorevariablename').val(rowID[1]);
            var scorevarId = rowID[2];
            $('#selected').val(scorevarId);

            scoreTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        });

        $("#scorecard_searchbox").keyup(function () {//group search code
            scoreTable.search(this.value).draw();
        });
    } else {
        $('#showMessage').html('');
        getCustomerConfiguration();
    }
}

function getCustomerConfiguration() {
    var parent = '';
    var subChild = '';
    var subChildVal = '';

    $('input:checkbox').removeAttr('checked');
    $('#confMsg').hide();
    $('.se-pre-con').show();
    $.ajax({
        url: "scorecardFunction.php?function=getConfiguredScore"+"&csrfMagicToken=" + csrfMagicToken,
        type: "POST",
        data: "",
        success: function (data) {
            $('.se-pre-con').hide();
            var res = JSON.parse(data);
            //console.log('Len : ' + res.length);
            if (res.length == 0) {
                $('#confMsg').show();
            }
            for (var i = 0; i < res.length; i++) {
                parent = res[i].scorecardId;
                child = res[i].scorevariableId;
                if(res[i].scorechecked != '') {
                    subChild = res[i].scorechecked.split(',');
                    //subChildVal = Object.values(JSON.parse(res[i].scorevariablevalue));
                    subChildVal = Object.keys(JSON.parse(res[i].scorevariablevalue)).map(function(itm) { return JSON.parse(res[i].scorevariablevalue)[itm]; });
                    //console.log(Object.values(subChildVal));
                    $('.parent_' + parent).prop('checked', true);
                    //$('.child_'+parent+'_'+child).prop('checked', true);

                    for (var sc = 0; sc < subChild.length; sc++) {
                        $('.' + subChild[sc]).prop('checked', true);
                        $('.' + subChild[sc]).parents('ul').closest('ul').siblings('input:checkbox').prop('checked', true);
                    }

                    for (var scv = 0; scv < subChildVal.length; scv++) {
                        //console.log('subchildtext_'+parent+'_'+(i+1)+'_'+(scv+1));
                        //console.log('Val : ' + subChildVal[scv]);
                        $('.subchildtext_' + parent + '_' + (i + 1) + '_' + (scv + 1)).val(subChildVal[scv]);
                    }
                }
            }
        },
        error: function (err) {
            $('.se-pre-con').hide();
        }
    });
}


function scoreCardSubmit() {

    var jsondata = {};
    var jsonArray = [];

    var parentCount = $('#parentCount').val();
    for (var i = 1; i <= parentCount; i++) {
        var parentId = $(".parent_" + i).attr('id');
        if ($(".parent_" + i).is(':checked')) {
            var childCount = $('.childli_' + i).length;
            for (var j = 1; j <= childCount; j++) {
                if ($(".child_" + i + "_" + j).is(':checked')) {
                    var childId = $(".child_" + i + "_" + j).attr('id');
                    var subchildCount = $('#subchildCount_' + i + '_' + j).val();
                    var jsonstr = [];
                    var subchildIdclass = [];
                    var subchildValue = [];
                    for (var k = 1; k <= subchildCount; k++) {
                        if ($(".subchild_" + i + "_" + j + "_" + k).is(':checked')) {
                            var subchildId = $(".subchild_" + i + "_" + j + "_" + k).attr('id');
                            subchildIdclass.push($(".subchild_" + i + "_" + j + "_" + k).attr('class'));
                            var subchildTxt = $(".subchildtext_" + i + "_" + j + "_" + k).val();
                            var subchildTxtchecked = $(".subchildtext_" + i + "_" + j + "_" + k).val();
                            subchildValue.push(subchildTxtchecked);
                            jsonstr.push(['"' + subchildId + '":"' + subchildTxt + '"']);
                        } else {
                            var subchildId = $(".subchild_" + i + "_" + j + "_" + k).attr('id');
                            //var subchildTxt = $(".subchildtext_" + i + "_" + j + "_" + k).val();
                            jsonstr.push(['"' + subchildId + '":"' + subchildTxt + '"']);
                        }
                    }

                    jsondata['scoreId'] = parentId;
                    jsondata['scoreVarId'] = childId;
                    jsondata['value'] = "{" + jsonstr + "}";
                    jsondata['text'] = subchildId;
                    jsondata['class'] = subchildIdclass;
                    //jsondata['classtxtval'] = subchildTxtchecked;
                    jsondata['classtxtval'] = subchildValue;
                    jsonArray.push([jsondata]);
                    jsondata = {};
                }
            }
        }
    }
    if (jsonArray.length < 1) {
        $('#showMessage').html("Please select a configuration!").css({'color': 'red'});
        return;
    }
    var temp = JSON.stringify(jsonArray);

    //var confCust = $('#custDropDown').val();
    $.ajax({
        type: 'post',
        dataType: 'json',
        url: 'scorecardFunction.php?function=scoreCreation'+"&csrfMagicToken=" + csrfMagicToken,
        data: {data: temp},
        success: function (data) {
            var result = $.trim(data);
            if (result === 'success') {
                $('#showMessage').html("configuration added successfully").css({'color': 'darkgreen'});
            } else {
                $('#showMessage').html("configuration not added successfully").css({'color': 'red'});
            }
            setTimeout(function () {
                location.href = 'index.php';
            }, 3200);
        }
    });

}

function selectConfirm(value) {

    var scoreId = $('#selected').val();
    var scoreName = $('#scorename').val();
    var scoreVariable = $('#scorevariablename').val();

    if (value == 'add_group_name') {
        $('#groupadd').modal('show');
    } else if (value == 'add_parameter_name') {
        $('#parameteradd').modal('show');
    } else if (value == 'edit_parameter') {
        if (scoreId != '') {

            getscoreOption(scoreId, scoreName, scoreVariable);

        } else {
            $('#warning').modal('show');
        }
    } else if (value == 'scorecard_name') {
        confPage = true;
        $('#viewScoreCard').hide();
        $('#addScoreCard').show();
        $('#scoresearch').hide();
        $('#edit_parameter').hide();
        $('#back_scoreGrid').show();
        $('#scorecard_name').hide();
        $('.month-dropdown').show();

        getCustomerConfiguration();
    } else if (value == 'back_scoreGrid') {
        //location.reload();
        confPage = false;
        $('#viewScoreCard').show();
        $('#addScoreCard').hide();
        $('#scoresearch').show();
        $('#edit_parameter').show();
        $('#back_scoreGrid').hide();
        $('#scorecard_name').show();
        $('.month-dropdown').hide();

        get_scorecardviewData();
    }
}

function addgroupName() {
    var gname = $('#groupname').val();

    if (gname == '') {

        $('#successmsg').html('<span style="color:red">please enter group name</span>');
        $('#successmsg').show();
        return false;

    } else {
        $("#loadingAdd").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
        $.ajax({
            url: 'scorecardFunction.php?function=addScoreParent',
            type: 'post',
            data: 'parentName=' + gname+"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json',
            success: function (response) {
                if (response = 'success') {
                    $('#loadingAdd').hide();
                    $('#successmsg').show();
                    $('#successmsg').html('<span style="color:green">successfully added</span>');
                } else {
                    $('#loadingAdd').hide();
                    $('#successmsg').show();
                    $('#successmsg').html('<span style="color:green">not added successfully </span>');
                }

                setTimeout(function () {
                    $("#groupadd").modal("hide");
                    location.href = 'index.php';
                }, 3200);

            }
        })
    }
}

function addSubheader(header, obj) {
    subHeaders[header] = subHeaders[header] + 1;
    if (subHeaders[header] > 3) {
        $(".section").mCustomScrollbar({theme: "minimal-dark"});
    }
    var header = '<div class="form-group is-empty clearfix row">' +
//                   '<label for="paraname" class="col-sm-3 align-label">Parameter Name</label>'+
            '<div class="col-sm-9" style="margin-left:25%;">' +
            '<a href="javascript:" onclick="addSubheader(1, this)" id="addNewSubheader"  class="addNewSubheader" style="display:block;"><i class="icon-ic_add_24px material-icons"></i></a>' +
            '<div class="col-lg-5 col-md-6 col-sm-6 col-xs-12">' +
            '<input class="form-control abc" id="paramname_' + subHeaders[header] + '" type="text"> ' +
            '</div>' +
            '<div class="col-lg-5 col-md-6 col-sm-6 col-xs-12">' +
            '<input class="form-control" id="scorevalue_' + subHeaders[header] + '" type="text"> ' +
            '</div>' +
            '<a href="#" onclick="removeSubHdr(this)"><i class="material-icons icon-ic_close_24px"></i></a>' +
            '</div>' +
            '</div>';

    $(header).insertAfter($(obj).parent());
}

function editSubheader(value, obj) {
    var temp = value + 1;
//    alert(temp);
//    return false
}

function removeSubHdr(obj) {
    $(obj).parent().remove();
}

function addparmetervalue() {
    var header = '';
    var paramName = '';
    var scoreValue = '';
    var gnameid = $('#add_group_list').val();
    var pname = $('#paraname').val();
    var header = $('.abc').length;
    var paratype = $("#para_type").val();
    var temp = [];

    if (gnameid == '') {
        $('#paramsuccessmsg').html('');
        $('#paramsuccessmsg').html('<span style="color:red">please select group name</span>');
        $('#paramsuccessmsg').show();
        return false;
    }

    if (pname == '') {
        $('#paramsuccessmsg').html('');
        $('#paramsuccessmsg').html('<span style="color:red">please enter param name</span>');
        $('#paramsuccessmsg').show();
        return false;
    }

    if (header > 0) {
        var values = '{';
        for (var i = 1; i <= header; i++) {
            if ($('#paramname_' + i).val() == '') {
                $('#paramsuccessmsg').html('');
                $('#paramsuccessmsg').html('<span style="color:red">please enter parameter value</span>');
                $('#paramsuccessmsg').show();
            } else if ($('#scorevalue_' + i).val() == '') {
                $('#paramsuccessmsg').html('');
                $('#paramsuccessmsg').html('<span style="color:red">please enter score value</span>');
                $('#paramsuccessmsg').show();
            }
            paramName = $('#paramname_' + i).val();
            scoreValue = $('#scorevalue_' + i).val();

            values += '"' + paramName + '":"' + scoreValue + '",';

        }
        var lastChar = values.slice(-1);
        if (lastChar == ',') {
            values = values.slice(0, -1);
        }
        values += '}';
    }
    $("#loadingAddparam").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
    $.ajax({
        url: 'scorecardFunction.php?function=addScoreVariable',
        type: 'post',
        data: 'parentid=' + gnameid + '&paramname=' + pname + '&values=' + values + '&paratype=' + paratype+"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function (data) {
            if (data == 'success') {
                $('#loadingAddparam').hide();
                $('#paramsuccessmsg').show();
                $('#paramsuccessmsg').html('<span style="color:green">successfully added</span>');
            } else if (data == 'failed') {
                $('#loadingAdd').hide();
                $('#successmsg').show();
                $('#successmsg').html('<span style="color:green">not added successfully </span>');
            }

            setTimeout(function () {
                $("#parameteradd").modal("hide");
                location.href = 'index.php';
            }, 3200);
        }
    })
}

function getscoreOption(id, sname, svar) {

    $.ajax({
        url: 'scorecardFunction.php?function=get_scoreoption',
        type: 'post',
        data: 'id=' + id +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function (data) {
            $('#parametereditpopup').modal('show');
            $('#editgname').val(sname);
            $('#editparaname').val(svar);
            var editheader = '';

            for (var i = 0; i < data.length; i++) {
                if (data[i].value != '') {
                    if (data[i].value === '1') {
                        var label = '<label for="paraname" class="col-sm-3 align-label">Parameter Value</label>';
                        var editadd = '<a href="javascript:" onclick="editSubheader(' + data[i].value + ', this)" id="editNewSubheader_' + data[i].value + '"  class="editNewSubheader" style="display:block;"><i class="icon-ic_add_24px material-icons"></i></a>';
                    } else {
                        var editadd = '<a href="javascript:" onclick="editSubheader(' + data[i].value + ', this)" id="editNewSubheader_' + data[i].value + '"  class="editNewSubheader" style="display:block;"><i class="icon-ic_add_24px material-icons"></i></a>';
                        var label = '<label for="paraname" class="col-sm-3 align-label"></label>';
                    }

                    editheader += '<div class="form-group is-empty clearfix row">' +
                            label +
                            editadd +
                            '<div class="col-sm-9" style="margin-left:25%;">' +
                            '<div class="col-lg-5 col-md-6 col-sm-6 col-xs-12">' +
                            '<input class="form-control editabc" id="editparamname_' + data[i].value + '" type="text" value="' + data[i].key + '"> ' +
                            '</div>' +
                            '<div class="col-lg-5 col-md-6 col-sm-6 col-xs-12">' +
                            '<input class="form-control" id="editscorevalue_' + data[i].value + '" type="text" value="' + data[i].value + '"> ' +
                            '</div>' +
                            '<a href="#" onclick="editremoveSubHdr(this,' + data[i].value + ')"><i class="material-icons icon-ic_close_24px"></i></a>' +
                            '</div>' +
                            '</div>';

                    $('#editheadervalue').html(editheader);
                } else {
                    editheader = '<div class="form-group is-empty clearfix row">' +
                            '<label for="paraname" class="col-sm-3 align-label">Parameter Value</label>' +
                            '<a href="javascript:" onclick="editSubheader(1, this)" id="editNewSubheader_1"  class="editNewSubheader" style="display:block;"><i class="icon-ic_add_24px material-icons"></i></a>' +
                            '<div class="col-sm-9" style="margin-left:25%;">' +
                            '<div class="col-lg-5 col-md-6 col-sm-6 col-xs-12">' +
                            '<input class="form-control editabc" id="editparamname_1" type="text"> ' +
                            '</div>' +
                            '<div class="col-lg-5 col-md-6 col-sm-6 col-xs-12">' +
                            '<input class="form-control" id="editscorevalue_1" type="text"> ' +
                            '</div>' +
                            '<a href="#" onclick="editremoveSubHdr(this,1)"><i class="material-icons icon-ic_close_24px"></i></a>' +
                            '</div>' +
                            '</div>';
                    $('#editheadervalue').html(editheader);
                }
            }
        }
    })
}

function editSubheader(val, obj) {
    editvalue = val + 1;

    if (editvalue > 3) {
        $(".editsection").mCustomScrollbar({theme: "minimal-dark"});
    }

    var editheader = '<div class="form-group is-empty clearfix row">' +
            '<div class="col-sm-9" style="margin-left:23%;">' +
            '<a href="javascript:" onclick="editSubheader(' + editvalue + ', this)" id="editNewSubheader_' + editvalue + '"  class="editNewSubheader" style="display:block;"><i class="icon-ic_add_24px material-icons"></i></a>' +
            '<div class="col-lg-5 col-md-6 col-sm-6 col-xs-12">' +
            '<input class="form-control editabc" id="editparamname_' + editvalue + '" type="text"> ' +
            '</div>' +
            '<div class="col-lg-5 col-md-6 col-sm-6 col-xs-12">' +
            '<input class="form-control" id="editscorevalue_' + editvalue + '" type="text"> ' +
            '</div>' +
            '<a href="#" onclick="editremoveSubHdr(this,' + editvalue + ')"><i class="material-icons icon-ic_close_24px"></i></a>' +
            '</div>' +
            '</div>';
//            console.log(editheader);
//            return false;
    $(editheader).insertAfter($(obj).parent());
}

function editremoveSubHdr(obj, id) {
    $(obj).parent().remove();
    $("#editNewSubheader_" + id + "").remove();
}

function editparmetervalue() {
    var editparamName = '';
    var editscoreValue = '';
    var editgname = $('#editgname').val();
    var editpname = $('#editparaname').val();
    var editheader = $('.editabc').length;
    var scoreId = $('#selected').val();
    var temp = [];
    var headervalue = '';


    if (headervalue > 0) {
        var values = '{';
        for (var j = 1; j <= headervalue; j++) {
            if ($('#editheader_' + j).val() == '') {
                $('#headermsuccmessage').html('');
                $('#headersuccessmsg').html('<span style="color:red">please enter parameter value</span>');
                $('#headersuccessmsg').show();
            } else if ($('#headerscoreval_' + j).val() == '') {
                $('#headersuccessmsg').html();
                $('#headersuccessmsg').html('<span style="color:red">please enter header value</span>');
                $('#headersuccessmsg').show();
            }
        }
    }

    if (editheader > 0) {
        var values = '{';
        for (var i = 0; i < editheader; i++) {
            if ($('#editparamname_' + i).val() == '') {
                $('#paramsuccessmsg').html('');
                $('#paramsuccessmsg').html('<span style="color:red">please enter parameter value</span>');
                $('#paramsuccessmsg').show();
            } else if ($('#editscorevalue_' + i).val() == '') {
                $('#paramsuccessmsg').html('');
                $('#paramsuccessmsg').html('<span style="color:red">please enter score value</span>');
                $('#paramsuccessmsg').show();
            }
            editparamName = $('#editparamname_' + i).val();
            editscoreValue = $('#editscorevalue_' + i).val();

            values += '"' + editparamName + '":"' + editscoreValue + '",';

        }

        var lastChar = values.slice(-1);
        if (lastChar == ',') {
            values = values.slice(0, -1);
        }

        values += '}';

    }
    $("#loadingeditparam").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
    $.ajax({
        url: 'scorecardFunction.php?function=editScoreVariable',
        type: 'post',
        data: 'paramname=' + editpname + '&scoreid=' + scoreId + '&values=' + values +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function (data) {
            if (data == 'success') {
                $('#loadingeditparam').hide();
                $('#editparamsuccessmsg').show();
                $('#editparamsuccessmsg').html('<span style="color:green">successfully Updated</span>');
            } else if (data == 'failed') {
                $('#loadingeditparam').hide();
                $('#editparamsuccessmsg').show();
                $('#editparamsuccessmsg').html('<span style="color:green">not updated successfully </span>');
            }

            setTimeout(function () {
                $("#parametereditpopup").modal("hide");
                location.href = 'index.php';
            }, 3200);
        }
    });
}
