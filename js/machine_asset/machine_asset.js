var showImage = 0;
var runcontrol = 0;
function displaydiv(env, did, mid, when, db, name, smax, id, obj)
{
//    $(".panel-body li").removeClass('active');
//    $(obj).addClass('active');
    $('.panel-body li').css({'background-color': 'white', 'color': '#595959'});
    $(obj).css({'background-color': '#48b2e4', 'color': 'white'});

    if (showImage != id) {
        $("div.hideDiv").css("display", "none");

        if (showImage != '0') {
            document.getElementById('displayImage' + showImage).innerHTML = "";
        }
    }
    if (id != '')
    {
        if (showImage == id)//to close
        {
            // $("#" + id).slideToggle("slow");
            // $("#loading" + id).css("display", "block");
            // document.getElementById('displayImage' + id).innerHTML = "";
            //showImage = '0';
        } else {//to open  
            if ($("#hiddenDivStatus" + id).val() != 1)
            {

                if (runcontrol == 0)
                {
                    runcontrol = 1;
                    showImage = id;
                    $("#" + id).css({"display": "inline-block"});
                    $.ajax({
                        type: "GET",
                        url: "ajaxAssetData.php",
                        data: "function=show_leaf_onclick&&env=" + env + "&did=" + did + "&mid=" + mid + "&when=" + when + "&name=" + name + "&smax=" + smax + "&id=" + id,
                        success: function(msg) {
                            $("#hiddenDivStatus" + id).val(1);
                            $("#loading" + id).css("display", "none");
                            $("#" + id).html(msg);
                            runcontrol = 0;
                        }
                    });
                    document.getElementById('displayImage' + id).innerHTML = "";
                }
            }
            else
            {
                $("#" + id).css({"display": "inline-block"});
                showImage = id;
                document.getElementById('displayImage' + id).innerHTML = "";
            }
        }
    }
}
function systeminformation()
{
    $('#componentInfo').removeClass('selected');
    $('#softwareInfo').removeClass('selected');
    $('#systemInfo').addClass("selected");
    //$("ul li:nth-child(1)").addClass("active");
    $("#level").css("display", "block");
    $("#level1").css("display", "none");
    $("#level2").css("display", "none");
//    $('#machineAssetnav').attr('onclick', 'component()');
    $('.panel-body li').first().css({'background-color': '#48b2e4', 'color': 'white'});
    $('ul#ul_systemInfoList li:first').addClass('active');
    $('ul#ul_systemInfoList li:first').click();

}
function component() {
    $('#componentInfo').addClass('selected');
    $('#softwareInfo').removeClass('selected');
    $('#systemInfo').removeClass("selected");
    //$("ul li li:nth-child(2)").addClass("active");
    $("#level").css("display", "none");
    $("#level1").css("display", "block");
    $("#level2").css("display", "none");
//    $('#machineAssetnav').attr('onclick', 'software()');
    $('ul#ul_componentsList li:first').addClass('active');
    $('ul#ul_componentsList li:first').click();
}
function software() {
    $('#componentInfo').removeClass('selected');
    $('#softwareInfo').addClass('selected');
    $('#systemInfo').removeClass("selected");
    //$("ul li li:nth-child(3)").addClass("active");
    $("#level").css("display", "none");
    $("#level1").css("display", "none");
    $("#level2").css("display", "block");
//    $('#machineAssetnav').attr('onclick', 'systeminformation()');
    $('ul#ul_SoftwareList li:first').addClass('active');
    $('ul#ul_SoftwareList li:first').click();
}
function DeleteAsset()
{
    var id = '<?php if (isset($id)) echo $id; ?>';
    var level = '<?php if (isset($level)) echo $level ?>';

    var func;
    if (id != '')
    {
        func = "function=clear_machine&machine=<?php if (isset($machinename)) echo urlencode($machinename); ?>&site=<?php if (isset($sitename)) echo urlencode($sitename); ?>" + '&csrfMagicToken=' + csrfMagicToken;
        var deleteconfrim = "Are you sure you want to delete asset information for machine'<?php if (isset($machinename)) echo $machinename; ?>'";
    }
    else
    {
        func = "function=deleteasset&site=<?php if (isset($sitename)) echo urlencode($sitename); ?>" + '&csrfMagicToken=' + csrfMagicToken,
                'csrfMagicToken': csrfMagicToken;
        var deleteconfrim = "Are you sure you want to delete asset information for site'<?php if (isset($sitename)) echo $sitename; ?>'";
    }

    if ($('#rk_container').html()) {

        bootbox.confirm(deleteconfrim, function(result) {
            if (result) {
                $.ajax({
                    type: "POST",
                    url: "machineAssetAjax.php",
                    data: func,
                    success: function(msg) {
                        var output = msg.split('%');
                        if (output[1] == 'DONE') {
                            if (level == 'gorup')
                                window.location.href = "index.php?id=<?php if (isset($id)) echo urlencode($id); ?>&level=<?php if (isset($level)) echo urlencode($level); ?>&group=<?php if (isset($sitename)) echo urlencode($sitename); ?>&name=<?php if (isset($machinename)) echo urlencode($machinename); ?>";
                            else
                                window.location.href = "index.php?id=<?php if (isset($id)) echo urlencode($id); ?>&level=<?php if (isset($level)) echo urlencode($level); ?>&site=<?php if (isset($sitename)) echo urlencode($sitename); ?>&name=<?php if (isset($machinename)) echo urlencode($machinename); ?>";
                        }
                    }
                });
            }
        });

    }
}

function assetchange() {
    window.location.href = "change.php?action=machine&cid=0&host=<?php echo $machinename; ?>&id=<?php echo $id; ?>&log=1&level=<?php echo $level; ?>&site=<?php echo $site; ?>";
}

function assetExport() {
//    var machinename = $("#rmachineName").val();
    if (!document.getElementById("rk_container")) {
        bootbox.alert("There is no data to export!", function() {
        });
    } else {
        window.location.href = "export.php?action=machine&cid=0&host=<?php echo $machinename; ?>&id=<?php echo $assetid; ?>&log=1&level=<?php echo $level; ?>&site=<?php echo $site; ?>&act=xdump&button=Excel";
    }
}

var switchTree = '<?php echo $switchvalue; ?>';
function tagpop()
{
    $('#machine_search').show();
}
function closeMachine_search()
{
    $('#machine_search').hide();
}
function displaylegend()
{
    $('#legend').css("display", "block");
}
function closelegend()
{
    $('#legend').css("display", "none");
}
function switchTreeView()
{

    if (switchTree == 'service')
    {
        switchTree = 'name';

    } else
    if (switchTree == 'name')
    {
        switchTree = 'service';

    } else {
        switchTree = 'name';
    }
    window.location.href = "../dashboard/lefttree.php?username=<?php echo $username; ?>&selectedid=<?php echo $selectedid; ?>&color=<?php echo urlencode($color); ?>&switchTree=" + switchTree;
}

//$(".button-group__item").click(function() {
//    var tab = $(this).attr('list');
//    $('.module-list').hide();
//    $('#' + tab).show();
//    $('.button-group__item').removeClass('is-active');
//    $(this).addClass("is-active");
//});
$(function() {
    $('.panel-body li').first().css({'background-color': '#48b2e4', 'color': 'white'});
//    $('ul#ul_systemInfoList li:first').addClass('active');
    $('ul#ul_systemInfoList li:first').click();
    $('ul#ul_systemInfoList li').css('padding', '9px');
    $('ul#ul_systemInfoList li').css('cursor', 'pointer');
    $('ul#ul_componentsList li').css('padding', '9px');
    $('ul#ul_componentsList li').css('cursor', 'pointer');
    $('ul#ul_SoftwareList li').css('padding', '9px');
    $('ul#ul_SoftwareList li').css('cursor', 'pointer');
    $(".machServ").show();
    $(".machAsset").show();
});

/*==== Asset back code =====*/
function assetBack() {
    window.location.href = " ../dashboard/deviceTypes.php";
}

/* ============= Asset filter function =============== */
function assetfilterClick(){
  var id = $('#assetadvdata').val();
  var auth = $('#authuser').val();
  if(id != ''){
  $.ajax({
      url: 'changestatus.php?action=add&qid=' + id + '&auth=' + auth + '&csrfMagicToken=' + csrfMagicToken,
      type : "post",
       success: function(data) {
        $("#publishPop").modal("show");
        $("#something").html(data);
       }
  })
  }else {
      
  }
  
}

/* ========== Asset information portal popup  ===========*/
function assetinformation() {

    $('#assetinformationGrid').dataTable().fnDestroy();
    $('#assetinformationGrid').DataTable({
        paging: true,
        searching: false,
        processing: true,
        serverSide: true,
        bLengthChange: false,
        pagingType: "full_numbers",
        ajax: {
            url: "processList.php",
            type: "POST"
//            success: function (data) {
//                response = $.parseJSON(data);
//                console.log(data);
//            }
        },
        columns: [
            {"data": "check"},
            {"data": "queryName"},
            {"data": "sitename"},
            {"data": "status"},
            {"data": "startTime"},
            {"data": "fileName"}
        ],
        columnDefs: [
            {
                targets: 0,
                orderable: false
            },
            {
                targets: 1,
                orderable: false
            },
            {
                targets: 2,
                orderable: false
            },
            {
                targets: 3,
                orderable: false
            },
            {
                targets: 4,
                orderable: false
            },
            {
                targets: 5,
                orderable: false
            }
        ]
    })
}

function checkBoxasset(e, obj) {
    e = e || event;/* get IE event ( not passed ) */
    e.stopPropagation ? e.stopPropagation() : e.cancelBubble = true;
    //$('.commonClass').prop('checked', obj.checked);
    $('.commonClass').prop('checked', obj.checked);
}

/*=====asset Delete ======*/
function assetDelete(){
     var checkedValues = $('.commonClass:checked').map(function () {
        return $(this).attr('value');
    }).get();
    $.ajax({
        url : 'changestatus.php?action=delete&id='+checkedValues,
        type :'post',
        success: function (result) {
           assetinformation();
        }
    
    });
} 
