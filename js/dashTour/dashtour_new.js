$(document).ready(function () {
    tour_datatablelist();
});

function tour_datatablelist() {
    
    $.ajax({
        url: "dashboardHelpDialog.php?function=getAllHelpers" + "&csrfMagicToken=" + csrfMagicToken,
        type: "POST",
        dataType: "json",
        success: function (gridData) {
//            $(".se-pre-con").hide();
            $('#tour_Grid').DataTable().destroy();
            auditTable = $('#tour_Grid').DataTable({
// scrollY: jQuery('#auditTable').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: true,
                select: false,
                bInfo: false,
                responsive: true,
                stateSave: true,
                scrollY: 'calc(100vh - 240px)',
                "pagingType": "full_numbers",
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                order: [[0, "desc"]],
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records",
                },
                "dom": '<"top"f>rt<"bottom"lp><"clear">',
                columnDefs: [{className: "checkbox-btn", "targets": [0]},
                    {"width": "10%", "targets": 1},
                    {"width": "10%", "targets": 0},
                    {"width": "40%", "targets": 2},
                    {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                initComplete: function (settings, json) {
                    
                },
               
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });
    $('#tour_Grid tbody').on('click', 'tr', function() {
        var rowID = auditTable.row(this).data();
        //console.log(rowID);  
        $('#touridhidden').val(rowID[4]);
        $("#cnthidden").val(rowID[5]);
        auditTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });
}



$(document).ready(function () {
    $("#imgForm").hide();
});

function submitForm(action, updateId, count) {
                        //alert("hi");
                        var id=$("#touridhidden").val();
                        console.log(id);
                        var insertArr = [];
                        var j=0;
                        if (action === 'update') {
                            console.log("check ");
                            if(boxCount === 0)
                                boxCount = count;
                                updateId=id;
                            
                        }
                        var msgArr = [];
                        var name = $("#name").val();
                        //console.log(name);
                        if (name === "") {
                            $("#msgImg").hide();
                            $("#msgText").show();
                            $("#msgText").css("color", "red");
                            $("#msgText").html("<b> Name can not be empty </b>");
                            return;
                        }
                        var descr = $("#descr").val();
                        if (descr === "") {
                            $("#msgImg").hide();
                            $("#msgText").show();
                            $("#msgText").css("color", "red");
                            $("#msgText").html("<b> Description can not be empty </b>");
                            return;
                        }
                        
                        var searchKey = encodeURIComponent($("#search_key").val());
//                        var searchKey = $("#searchKey").val();
                        //var searchKey = decodeURIComponent($("#searchKey").val());
                        //console.log($("#search_key").val());
                        if (searchKey === "") {
                            $("#msgImg").hide();
                            $("#msgText").show();
                            $("#msgText").css("color", "red");
                            $("#msgText").html("<b> searchKey can not be empty </b>");
                            return;
                        }
                        if (path === "") {
                            $("#msgImg").hide();
                            $("#msgText").show();
                            $("#msgText").css("color", "red");
                            $("#msgText").html("<b> Path can not be empty </b>");
                            return;
                        }
                        var path = $("#path").val();
                        if (path !== '' && path.indexOf("tipAction=true") === -1) {
                            if (path.indexOf("?") === -1) {
                                path += "?tipAction=true";
                            } else {
                                path += "&tipAction=true";
                            }
                        }
                        path = encodeURIComponent(path);
                        var mainObj = {};
                        mainObj.name = name;
                        mainObj.descr = descr;
                        mainObj.searchKey = searchKey;
                        mainObj.path = path;

                        var anyBox = $(".addCnt")[0];
                        if (parseInt(boxCount) === 0 || typeof anyBox === "undefined") {
                            $("#msgImg").hide();
                            $("#msgText").show();
                            $("#msgText").css("color", "red");
                            $("#msgText").html("<b>Atleast one Message box should Add....</b>");
                            return;
                        }
                        
                        if (boxCount > 0) {
                            for (var i = 1; i < boxCount; i++) {
                                var id = "tourEdit" + i;
                                var isNullId = document.getElementById(id);
                                if(typeof isNullId === 'undefined' || isNullId === null){
                                    if(insertIds.length > 0 && i === parseInt(insertIds[j])){
                                        j++;
                                    }
                                    continue;
                                }
                                var msgBoxNum = $("#"+id+" .addCnt").html();
                                var str1 = $("#windowName"+i).val();
                                var str2 = $("#elementId"+i).val();
                                var str3 = $("#title"+i).val();
                                var str4 = $("#message"+i).val();
                                var str5 = $("#position"+i).val();
                                var str6 = $("#path"+i).val();
                                
                                if (str1 === "") {
                                    $("#msgImg").hide();
                                    $("#msgText").show();
                                    $("#msgText").css("color", "red");
                                    $("#msgText").html("<b>In msg box " + msgBoxNum + " Window name can not be empty </b>");
                                    return;
                                }
                                if (str2 === "") {
                                    $("#msgImg").hide();
                                    $("#msgText").show();
                                    $("#msgText").css("color", "red");
                                    $("#msgText").html("<b>In msg box " + msgBoxNum + " Element name can not be empty </b>");
                                    return;
                                }
                                if (str4 === "") {
                                    $("#msgImg").hide();
                                    $("#msgText").show();
                                    $("#msgText").css("color", "red");
                                    $("#msgText").html("<b>In msg box " + msgBoxNum + " Message can not be empty </b>");
                                    return;
                                }
                                var pathVar = decodeURIComponent(str6);
                                if (pathVar !== '' && pathVar.indexOf("tipAction=true") === -1) {
                                    if (pathVar.indexOf("?") === -1) {
                                        pathVar += "?tipAction=true";
                                    } else {
                                        pathVar += "&tipAction=true";
                                    }
                                }
                                var obj = {};
                                obj.currentWindow = str1;
                                obj.elementName = str2;
                                obj.title = str3;
                                obj.text = str4;
                                obj.position = str5;
                                obj.path = pathVar;
                                var isTitleEnable = $("#enableTitle"+i).is(":checked");
                                obj.isTitleEnable = ""+isTitleEnable;
                                var nyroPopup = $("#nyroPopup"+i).is(":checked");
                                if(nyroPopup){
                                    obj.path = "";
                                }
                                obj.nyroPopup = ""+nyroPopup;
                                if (action === 'update') {
                                    console.log("check2");
                                    var str7 = $("#hiddenF"+i).val();
                                    obj.hiddenF = str7;
                                }
                                if(insertIds.length > 0 && i === parseInt(insertIds[j])){
                                    j++;
                                    insertArr.push(obj);
                                }else{
                                    msgArr.push(obj);
                                }
                            }
                            mainObj.msgArr = msgArr;
                            mainObj.insertArr = insertArr;
                            mainObj = encodeURIComponent(JSON.stringify(mainObj));
//                            mainObj = JSON.stringify(mainObj);
                            //console.log(mainObj);
                            var fname = '';
                            var obj112 = {};
                            if (action === 'add') {
                                fname = "addHelper";
                                obj112.function = "addHelper";
                            } else if (action === 'update') {
                                console.log("inside update else if");
                                fname = "updateHelper&id=" + updateId + "&deleteArr=" + JSON.stringify(deleteFromDb);
                                obj112.function = "updateHelper";
                                obj112.id = updateId;
                                obj112.deleteArr = JSON.stringify(deleteFromDb);
                            }
                            obj112.mainObj = mainObj;
                            obj112.csrfMagicToken = csrfMagicToken;
                            
                            //console.log("OBJ = "+JSON.stringify(obj112));
                            var url = "dashboardHelpDialog.php"; //?function=" + fname; // + "&mainObj=" + mainObj;
                            $("#msgImg").show();
                            $("#msgText").hide();
                            //console.log("url = "+url);
                            setTimeout(function(){
                            $.ajax({url: url,
                                dataType: 'text',
                                type: 'post',
                                data: obj112,
                                success: function (data) {
                                    //console.log(typeof(data));
//                                    if (typeof data === 'object') {
////                                        alert(data.error);
//console.log("inside if");
//                                        $("#msgImg").hide();
//                                        $("#msgText").show();
//                                        $("#msgText").css("color", "red");
//                                        $("#msgText").html("<b>Error Occured.....</b>");
//                                        if(action === 'add'){
//                                            var url = "dashboardHelpDialog.php?function=deleteHelper&id=" + data.id;
//                                            $.ajax({url: url,
//                                                success: function (data) {
//                                                    //console.log(data);
//                                                }, error: function (error) {
//                                                    //console.log(data);
//                                                }
//                                            });
//                                        }
//                                    }else if(data > 0){
                                        //console.log("inside else");
                                        $("#msgImg").hide();
                                        $("#msgText").show();
                                        $("#msgText").css("color", "green");
                                        if (action === 'update') {
                                            $("#msgText").html("<b>Successfully Updated..</b>");
                                        } else {
                                            $("#msgText").html("<b>Successfully Addded..</b>");
                                        }
                                        setTimeout(function () {
                                            window.location.href = "dashtour.php";
                                        }, 1000);
                                    
                                }, error: function (error) {
                                    var err = error.responseText;
                                    err = err.trim();
                                    //console.log(error);
                                    //console.log(JSON.stringify(error));
                                    $("#msgImg").hide();
                                    $("#msgText").show();
                                    $("#msgText").css("color", "red");
                                    $("#msgText").html("<b>Error Occured.....</b>");
                                }
                            });
                            },1000);
                        }
                    }


                    
function submitFormedit(action, updateId, count) {
                        alert("hi");
                        var id=$("#touridhidden").val();
                        console.log(id);
                        var count=$("#cnthidden").val();
                        console.log(count);
  //                      Main window
                        var name =$('#name2').val();
                        var descrp =$('#descr2').val();
                        var path =$('#path2').val();
                        var searchkey=$('#searchKey2').val();
//                       Sub-Window 
                                var cnt=1;
                                for(var i=1;i<=cnt;i++)
                                {
                                var Windowname = $("#window-Name-edit"+i).val();
                                var elementid= $("#element-Id-edit"+i).val();
                                var title = $("#title2-edit"+i).val();
                                var msg = $("#message2-edit"+i).val();
                                var position = $("#position2-edit"+i).val();
                                var pathpage = $("#path2-edit"+i).val();
                                var pathVar = decodeURIComponent(pathpage); 
                                var isTitleEnable = $("#enable-Title-edit"+i).is(":checked");
                                var nyroPopup = $("#nyroPopup2-edit"+i).is(":checked");
                                }
                        $.ajax({
                            url:"dashboardHelpDialog.php?function=editlinkHelper&id="+id,
                            data:{name:name,Description:descrp,PagePath:path,SearchKeywords:searchkey,Windowname:Windowname,elementid:elementid,title:title,msg:msg,position:position,pathVar:pathVar,isTitleEnable:isTitleEnable,nyroPopup:nyroPopup, csrfMagicToken: csrfMagicToken},
                            success:function(data){
                                alert("success");
                            },
                            error:function(data){
                                alert("error");
                            }
                        });
                    }
                        
                    $("#selectType").change(function(){
                        var type = $("#selectType").val();
                        console.log(type);
                        if(type === 'link'){
                            $("#selectTypeField").show();
                            var extraDivs = '<a onclick="getMsgBoxs()" style="cursor: pointer;">Click Here </a> To Add Message Boxes';
                            $("#addMsgBox").html(extraDivs);
                            $("#addproduct").attr('onclick','submitForm(\'add\', \'\', 0);');
                            
                            var str = '<label class="textCss">Path To Page : </label>'
                                    +'<input type="text"  class="form-control" id="path" name="path" placeholder="Enter path of the page to Start this HelpTour">';
                        }else if(type === 'image'){
                            $("#selectTypeField").hide();
                            var extraDivs = '<a onclick="getImageBoxs()" style="cursor: pointer;">Click Here </a> To Add Images';
                            $("#addMsgBox").html(extraDivs);
                            $("#addproduct").attr('onclick','submitImages(\'add\',\'\');');
                            
                        }else if(type === 'video'){
                            $("#selectTypeField").hide();
                            var extraDivs = '<a onclick="getVideoBoxs()" style="cursor: pointer;">Click Here </a> To Add Videos';
                            $("#addMsgBox").html(extraDivs);
                            $("#addproduct").attr('onclick','submitVideo(\'add\',\'\');');
                            
                        }else if(type === 'videoLink'){
                            $("#selectTypeField").hide();
                            var extraDivs = '<a onclick="getVideoLinkBox()" style="cursor: pointer;">Click Here </a> To Add Video Link';
                            $("#addMsgBox").html(extraDivs);
                            $("#addproduct").attr('onclick','submitVideoLink(\'add\',\'\');');
                        }
                    });
                    
                    var imgBoxCount = 0;
                    function getImageBoxs(){
                        imgBoxCount++;
                        $("#selectType").attr("disabled","");
                        $("#msgBoxDiv").show();
                        var str = '<form id="imgForm" method="POST" enctype="multipart/form-data"></form>';
                        $("#msgBoxDiv").html(str);
                        $("#addMsgBox").hide();
                        getMoreImgBox();
                    }
                    $cnt = 1;
                    var imgBoxCountforUpdate = 0;
                    
                    //console.log($cnt);
                    
                    function getImageBoxsForUpdate(){
                        imgBoxCountforUpdate = imgBoxCount;
                        $("#addMsgBox").hide();
                        
                        getMoreImgBox();
                        //console.log($cnt);
                    }
                    
                    function getMoreImgBox(){
                        $("#imgForm").show();
                        $cnt++;
                        var str = '<div style="padding: 1%;border: 1px solid #E8E3E3; margin-top:2%; margin-left: 2%; width: 95%; height: 50%;" id="imgId' + imgBoxCount + '">'
                                    +'<label class="textCss addCnt" style="margin-left: -3.5%;float: left;"></label>'
                                    + '<i class="tim-icons icon-simple-remove" style="margin-right: 0.5%;" onclick="removeImgDiv(\'' + imgBoxCount + '\',\'\')"></i>'
                                    +'<label class="textCss" style="margin-left: 4%;">Select Images : </label>'
                                    +'<input type="file" style="width:70%; margin-left: 4%;" class="form-control" id="imageField'+imgBoxCount+'" name="imageField'+imgBoxCount+'[]" placeholder="Select images" accept="image/png" multiple>'
                                    +'</div>';
                        var imgText = '<span id="addMoreImgBox"><a onclick="getMoreImgBox()" style="cursor: pointer;">Click Here </a> To Add Images</span>';
                        $("#imgForm").append(str);
                        $("#addMoreImgBox").remove();
                        $("#imgForm").append(imgText);
                        changeNums();
                        imgBoxCount++;
                    }
                    
                    var deleteFileNames=[];
                    function removeImgDiv(id,action){
                        console.log(action);
                        var isTrue = confirm("Are you sure to Delete this box?\nClick OK to delete..\n\n");
                        if(isTrue){
                            alert("hello");
//                            if (action === 'edit') {
//                                var fileName = $("#lable"+id).attr('filename');
//                                deleteFileNames.push(fileName);
//                            }
                            if(action=='edit'){
                                alert("inside");
                                var fileName = $("#lable"+id).attr('filename');
                                deleteFileNames.push(fileName);
                                $("#imgId2"+id).slideUp('slow', function(){
                                      $("#imgId2"+id).remove();
                                      changeNums();
                                    });
//                                $.ajax({
//                                    url:"dashboardHelpDialog.php?function=removeimage&id="+id,
//                                    success:function(data){
//                                        //alert("success");
//                                        console.log("#imgId2"+id);
//                                        $("#imgId2"+id).slideUp('slow', function(){
//                                        $("#imgId2"+id).remove();
//                                        changeNums();
//                                    });
//                                    },
//                                    error:function(data){
//                                        alert("failure");
//                                    }
//                                });
                            }
                            $("#imgId"+id).slideUp('slow', function(){
                                $("#imgId"+id).remove();
                                changeNums();
                            });
                            
                        }
                    }
                    
                    function getVideoBoxs(){
                        $("#selectType").attr("disabled","");
                        var str = '<label class="textCss" style="margin-left: 6%;">Select Video : </label>'
                                    +'<input type="file" style="width:82%; margin-left: 4%;height: 236%;opacity: 1" class="form-control" id="videoField" name="videoField" placeholder="Select video" accept="video/mp4">';
                        $("#msgBoxDiv").html(str);
                    }
                    
                    function getVideoLinkBox(){
                        $("#selectType").attr("disabled","");
                        var str = '<label class="textCss" style="margin-left: 4%;">Add Video Link : </label>'
                                    +'<input type="text" style="width:70%; margin-left: 4%;" class="form-control" id="videoLinkField" name="videoLinkField" placeholder="Enter video Link for this HelpTour">';
                        $("#msgBoxDiv").html(str);
                    }
                    
                    function submitImages(action,id){
                        var oldFileNames = [];
                        var res = verifyFields();
                        if(res.error !== ""){
                            return;
                        }
                        //console.log(imgBoxCount);
                        if(imgBoxCount === 0){
                            $("#msgImg").hide();
                            $("#msgText").show();
                            $("#msgText").css("color", "red");
                            $("#msgText").html("<b> Add Image </b>");
                        }
                        
                        var myFormData = new FormData();
                        var imgC = 0;
                        var stratfrom = 0;
                        if(action === 'edit'){
                            stratfrom = imgBoxCountforUpdate;
                        }
                        for(var i = stratfrom; i<imgBoxCount; i++){
                            var img = $("#imageField"+i);
                            if(typeof img === "undefined"){
                                continue;
                            }
                            if (img === "") {
                                $("#msgImg").hide();
                                $("#msgText").show();
                                $("#msgText").css("color", "red");
                                $("#msgText").html("<b> Image Field can not be empty </b>");
                                return;
                            }
                            $.each($("#imageField"+i),function(j,obj){
                                $.each(obj.files, function(k,file){
                                    myFormData.append('fileToUpload['+imgC+']', file);
                                    imgC++;
                                });
                            });
                        }
                        $("#msgImg").show();
                        $("#msgText").hide();
                        res.type = "image";
                        var url = '';
                        if(action === 'add'){
                            res = encodeURIComponent(JSON.stringify(res));
                            //console.log(res);
                            url = "dashboardHelpDialog.php?function=addImgHelper&obj="+res + "&csrfMagicToken=" + csrfMagicToken;
                        }else if(action === 'edit'){
                            for(var i =1; i< imgBoxCount; i++){
                                var fileName = $("#lable"+i).attr('filename');
                                if(fileName == "undefined" || fileName == null){
                                    continue;
                                }
                                else{
                                    oldFileNames.push(fileName);
                                }
                            }
                            res.oldFileNames = oldFileNames;
                            res = encodeURIComponent(JSON.stringify(res));
                            url = "dashboardHelpDialog.php?function=updateImgHelper&obj="+res+"&id="+id + "&csrfMagicToken=" + csrfMagicToken;
                        }
                        $.ajax({
                            url: url,
                            type: 'POST',
                            processData: false, // important
                            contentType: false, // important
                            data: myFormData,
                            success:function(res){
                                //console.log(res);
                                res = res.trim();
                                //console.log(res);
                                if(res === "success"){
                                   // console.log("success");
                                    $("#msgImg").hide();
                                    $("#msgText").show();
                                    $("#msgText").css("color","green");
                                    $("#msgText").html("Images Uploaded Successfully...");
                                    setTimeout(function () {
                                        window.location.href = "dashtour.php";
                                    }, 1000);
                                }else if(res.search("duplicate") > -1){
                                    res = res.replace("duplicate","");
                                    $("#msgImg").hide();
                                    $("#msgText").show();
                                    $("#msgText").css("color","red");
                                    $("#msgText").html(res+" filename already persent...");
                                    return;
                                }else if(res.search("error") > -1){
                                    $("#msgImg").hide();
                                    $("#msgText").show();
                                    $("#msgText").css("color","red");
                                    $("#msgText").html("Db Error Occured.");
                                    return;
                                }
                            },error:function(res){
                                $("#msgImg").hide();
                                $("#msgText").show();
                                $("#msgText").css("color","red");
                                $("#msgText").html("Error Occured...");
                            }
                        });
                    }
                    
                    function submitVideo(action,id,file){
                        var res = verifyFields();
                        if(res.error !== ""){
                            return;
                        }
                        var video = $("#videoField").val();
                        if (typeof video === "undefined") {
                            $("#msgImg").hide();
                            $("#msgText").show();
                            $("#msgText").css("color", "red");
                            $("#msgText").html("<b> Add Video Link Field </b>");
                            return;
                        }
                        if (video === "") {
                            if(action !== "update"){
                                $("#msgImg").hide();
                                $("#msgText").show();
                                $("#msgText").css("color", "red");
                                $("#msgText").html("<b> Video Link Field can not be empty </b>");
                                return;
                            }
                        }
                        var myFormData = new FormData();
                        $.each($("#videoField"),function(j,obj){
                            $.each(obj.files, function(k,file){
                                myFormData.append('fileToUpload[]', file);
                            });
                        });
                        $("#msgImg").show();
                        $("#msgText").hide();
                        res.type = "video";
                        res = encodeURIComponent(JSON.stringify(res));
                        var url = '';
                        if(action === "update"){
                            if (video === "") {
                                url = "dashboardHelpDialog.php?function=editVideoHelper&id="+id+"&file="+file+"&obj="+res + "&csrfMagicToken=" + csrfMagicToken;
                            }else{
                                url = "dashboardHelpDialog.php?function=editVideoHelper&id="+id+"&obj="+res + "&csrfMagicToken=" + csrfMagicToken;
                            }
                        }else if(action === "add"){
                            url = "dashboardHelpDialog.php?function=addVideoHelper&obj="+res + "&csrfMagicToken=" + csrfMagicToken;
                        }
                        $.ajax({
                            url: url,
                            type: 'POST',
                            processData: false, // important
                            contentType: false, // important
                            data: myFormData,
                            success:function(res){
                                //console.log(res);
                                res = res.trim();
                                if(res === "success"){
                                    $("#msgImg").hide();
                                    $("#msgText").show();
                                    $("#msgText").css("color","green");
                                    $("#msgText").html("Video Uploaded Successfully...");
                                    setTimeout(function () {
                                        window.location.href = "dashtour.php";
                                    }, 1000);
                                }else if(res.search("duplicate") > -1){
                                    res = res.replace("duplicate","");
                                    $("#msgImg").hide();
                                    $("#msgText").show();
                                    $("#msgText").css("color","red");
                                    $("#msgText").html(res+" filename already persent...");
                                    return;
                                }else if(res.search("error") > -1){
                                    $("#msgImg").hide();
                                    $("#msgText").show();
                                    $("#msgText").css("color","red");
                                    $("#msgText").html("Db Error Occured.");
                                    return;
                                }
                            },error:function(res){
                                $("#msgImg").hide();
                                $("#msgText").show();
                                $("#msgText").css("color","red");
                                $("#msgText").html("Error Occured...");
                            }
                        });
                    }
                    
                    function submitVideoLink(action,id){
                        var res = verifyFields();
                        if(res.error !== ""){
                            return;
                        }
                        var linkVal = $("#videoLinkField").val();
                        if (typeof linkVal === "undefined") {
                            $("#msgImg").hide();
                            $("#msgText").show();
                            $("#msgText").css("color", "red");
                            $("#msgText").html("<b> Add Video Link Field </b>");
                            return;
                        }
                        if (linkVal === "") {
                            $("#msgImg").hide();
                            $("#msgText").show();
                            $("#msgText").css("color", "red");
                            $("#msgText").html("<b> Video Link Field can not be empty </b>");
                            return;
                        }
                        $("#msgImg").show();
                        $("#msgText").hide();
                        //console.log(res);
                        res.type = "videoLink";
                        res.linkVal = linkVal;
                        res.csrfMagicToken = csrfMagicToken;
                        //console.log(JSON.stringify(res));
                        res = encodeURIComponent(JSON.stringify(res));
                        var url = '';
                        if(action === "update"){
                            url = "dashboardHelpDialog.php?function=saveIntoDb&id="+id+"&obj="+res + "&csrfMagicToken=" + csrfMagicToken;
                        }else if(action === "add"){
                            //url = "dashboardHelpDialog.php?function=saveIntoDb&obj="+res;
                           //url = "dashboardHelpDialog.php?function=savelinkIntoDb&obj="+res;
                           $.ajax({
                                    url: "dashboardHelpDialog.php?function=savelinkIntoDb",
                                    type: "POST",
                                    data: {data:decodeURIComponent(res)},
                                    success: function(data) {
                                                   //alert("success");
                                                   $("#msgImg").hide();
                                                    $("#msgText").show();
                                                    $("#msgText").css("color","green");
                                                    $("#msgText").html("Video Link Added Successfully...");
                                                    setTimeout(function () {
                                                        window.location.href = "dashtour.php";
                                                    }, 1000);
                                                },
                                    error: function(error){
                                                //alert("failure");
                                                $("#msgImg").hide();
                                                $("#msgText").show();
                                                $("#msgText").css("color","red");
                                                $("#msgText").html("Error Occured...");
                                    }
                                    });
                        }
//                        $.ajax({
//                            url: url,
//                            type: 'POST',
//                            //data: res,
//                            success:function(res){
//                                //alert("success");
//                                res = res.trim();
//                                if(res === "success"){
//                                    $("#msgImg").hide();
//                                    $("#msgText").show();
//                                    $("#msgText").css("color","green");
//                                    $("#msgText").html("Video Link Added Successfully...");
//                                    setTimeout(function () {
//                                        window.location.href = "dashtour.php";
//                                    }, 1000);
//                                }
//                            },error:function(res){
//                                console.log(res);
//                                alert("success");
//                                $("#msgImg").hide();
//                                $("#msgText").show();
//                                $("#msgText").css("color","red");
//                                $("#msgText").html("Error Occured...");
//                            }
//                        });
                    }
                    
                    function verifyFields(){
                        var obj = {error:"",name:"",descr:"",searchKey:""};
                        var name = $("#name").val();
                        if (name === "") {
                            $("#msgImg").hide();
                            $("#msgText").show();
                            $("#msgText").css("color", "red");
                            $("#msgText").html("<b> Name can not be empty </b>");
                            obj.error = "error";
                        }
                        var descr = $("#descr").val();
                        if (descr === "") {
                            $("#msgImg").hide();
                            $("#msgText").show();
                            $("#msgText").css("color", "red");
                            $("#msgText").html("<b> Description can not be empty </b>");
                            obj.error = "error";
                        }
                        var searchKey = encodeURIComponent($("#search_key").val());
                        if (searchKey === "") {
                            $("#msgImg").hide();
                            $("#msgText").show();
                            $("#msgText").css("color", "red");
                            $("#msgText").html("<b> searchKey can not be empty </b>");
                            obj.error = "error";
                        }
                        obj.name = name;
                        obj.descr = descr;
                        obj.searchKey = searchKey;
                        return obj;
                    }
                        
                    var boxCount = 0;
                    function getMsgBoxs() {
                        $("#selectType").attr("disabled","");
                        boxCount = 1;
                        $("#msgBoxDiv").show();
                        $("#addMsgBox").hide();
                        getMoreMsgBoxs();
                    }
                    
                    var insertIds = [];
                    function getMsgBoxsForUpdate(count){
                        $("#selectType").attr("disabled","");
                        boxCount = count;
                        $("#msgBoxDiv").show();
                        $("#addMsgBox").hide();
                        getMoreMsgBoxs();
                    }
                    
                    
                    function getMoreMsgBoxs() {
                        if(parseInt(isUpdate) === 1){
                            insertIds.push(boxCount);
                        }
                        var formData = '<form name="tourEdit' + boxCount + '" id="tourEdit' + boxCount + '" method="post" style="border: 1px solid #E8E3E3; margin-top:2%; margin-left: 2%; width: 95%;">'
                                + '<label class="textCss addCnt" style="margin-left: -2.5%;float: left;"></label>'
                                + '<i id="crs-btn" class="tim-icons icon-simple-remove" onclick="removeDiv(\'' + boxCount + '\',\'\')"></i>'
                                + '<div class="form-group" style="margin: 1%;">'
                                + '<label class="textCss">Window Name : </label>'
                                + '<input type="text"  class="form-control" id="windowName' + boxCount + '" name="windowName' + boxCount + '" placeholder="Enter Window Name">'
                                + '</div>'
                                + '<div class="form-group" style="margin: 1%;">'
                                + '<label class="textCss">Element Id : &nbsp;</label>'
                                + '<input type="text"  class="form-control" id="elementId' + boxCount + '" name="elementId' + boxCount + '" placeholder="Enter Element\'s Id" >'
                                + '</div>'
                                + '<div class="form-group" style="margin: 1%;">'
                                + '<label class="textCss">Enable Title : &nbsp;</label>'
                                + '<input type="checkbox" style="margin-left: 16px;margin-top: 3px;" onclick="toggle(\'hideTitle' + boxCount + '\')" class="form-check-input" id="enableTitle' + boxCount + '" name="enableTitle' + boxCount + '" >'
                                + '</div>'
                                + '<div class="form-group" style="margin: 1%;display:none;" id="hideTitle' + boxCount + '">'
                                + '<label class="textCss">Title : </label>'
                                + '<input type="text" name="title' + boxCount + '"  id="title' + boxCount + '" class="form-control" placeholder="Enter Title for Element" />'
                                + '</div>'
                                + '<div class="form-group" style="margin: 1%;">'
                                + '<label class="textCss">Message : </label>'
                                + '<textarea style="border:solid 1px;border-color: #c7c6c6;" name="message' + boxCount + '" id="message' + boxCount + '" class="form-control" placeholder="Enter Message for Element" ></textarea>'
                                + '</div>'
                                + '<div class="form-group" style="margin: 1%;">'
                                + '<label class="textCss">Position : &nbsp;</label>'
                                + '<select class ="selectpicker" style="display:visible" name="position' + boxCount + '" id="position' + boxCount + '" >'
                                + '<option value="right">right</option>'
                                + '<option value="left">left</option>'
                                + '<option value="top">top</option>'
                                + '<option value="bottom">bottom</option>'
                                + '</select>'
                                + '</div>'
                                + '<div class="form-group" style="margin: 1%;">'
                                + '<label class="textCss">Clickable Element : </label>'
                                + '<input type="checkbox" style="margin-left: 21px;margin-top: 3px;" onclick="toggle(\'hidePath' + boxCount + '\')" class="form-check-input" id="nyroPopup' + boxCount + '" name="nyroPopup' + boxCount + '" >'
                                + '</div>'
                                + '<div class="form-group" style="margin: 1%;" id="hidePath' + boxCount + '">'
                                + '<label class="textCss">Path To Next Page : </label>'
                                + '<input type="text"  class="form-control" id="path' + boxCount + '" name="path' + boxCount + '" placeholder="Give Path for page " >'
                                + '</div>'
                                + '</form>';

                        var addMore = '<span id="addMoreBox"><a onclick="getMoreMsgBoxs()" style="cursor: pointer;">Click Here </a> To Add More Message Boxes..</span>';

                        $("#addMoreBox").remove();
                        $("#msgBoxDiv").append(formData);
                        $("#msgBoxDiv").append(addMore);
                        changeNums();
                        boxCount++;
                    }
                    
                   function getMoreMsgBoxs_edit() {
                       console.log("inside");
                        var formData = '<form name="tourEdit' + boxCount + '" id="tourEdit' + boxCount + '" method="post" style="border: 1px solid #E8E3E3; margin-top:2%; margin-left: 2%; width: 95%;">'
                                + '<label class="textCss addCnt" style="margin-left: -2.5%;float: left;"></label>'
                                + '<i id="crs-btn" class="tim-icons icon-simple-remove" onclick="removeDiv(\'' + boxCount + '\',\'\')"></i>'
                                + '<div class="form-group" style="margin: 1%;">'
                                + '<label class="textCss">Window Name : </label>'
                                + '<input type="text"  class="form-control" id="windowName' + boxCount + '" name="windowName' + boxCount + '" placeholder="Enter Window Name">'
                                + '</div>'
                                + '<div class="form-group" style="margin: 1%;">'
                                + '<label class="textCss">Element Id : &nbsp;</label>'
                                + '<input type="text"  class="form-control" id="elementId' + boxCount + '" name="elementId' + boxCount + '" placeholder="Enter Element\'s Id" >'
                                + '</div>'
                                + '<div class="form-group" style="margin: 1%;">'
                                + '<label class="textCss">Enable Title : &nbsp;</label>'
                                + '<input type="checkbox" style="margin-left: 16px;margin-top: 3px;" onclick="toggle(\'hideTitle' + boxCount + '\')" class="form-check-input" id="enableTitle' + boxCount + '" name="enableTitle' + boxCount + '" >'
                                + '</div>'
                                + '<div class="form-group" style="margin: 1%;display:none;" id="hideTitle' + boxCount + '">'
                                + '<label class="textCss">Title : </label>'
                                + '<input type="text" name="title' + boxCount + '"  id="title' + boxCount + '" class="form-control" placeholder="Enter Title for Element" />'
                                + '</div>'
                                + '<div class="form-group" style="margin: 1%;">'
                                + '<label class="textCss">Message : </label>'
                                + '<textarea style="border:solid 1px;border-color: #c7c6c6;" name="message' + boxCount + '" id="message' + boxCount + '" class="form-control" placeholder="Enter Message for Element" ></textarea>'
                                + '</div>'
                                + '<div class="form-group" style="margin: 1%;">'
                                + '<label class="textCss">Position : &nbsp;</label>'
                                + '<select class ="selectpicker" style="display:visible" name="position' + boxCount + '" id="position' + boxCount + '" >'
                                + '<option value="right">right</option>'
                                + '<option value="left">left</option>'
                                + '<option value="top">top</option>'
                                + '<option value="bottom">bottom</option>'
                                + '</select>'
                                + '</div>'
                                + '<div class="form-group" style="margin: 1%;">'
                                + '<label class="textCss">Clickable Element : </label>'
                                + '<input type="checkbox" style="margin-left: 21px;margin-top: 3px;" onclick="toggle(\'hidePath' + boxCount + '\')" class="form-check-input" id="nyroPopup' + boxCount + '" name="nyroPopup' + boxCount + '" >'
                                + '</div>'
                                + '<div class="form-group" style="margin: 1%;" id="hidePath' + boxCount + '">'
                                + '<label class="textCss">Path To Next Page : </label>'
                                + '<input type="text"  class="form-control" id="path' + boxCount + '" name="path' + boxCount + '" placeholder="Give Path for page " >'
                                + '</div>'
                                + '</form>';
                        console.log(formData);
                        //var addMore = '<span id="addMoreBox"><a onclick="getMoreMsgBoxs_edit()" style="cursor: pointer;">Click Here </a> To Add More Message Boxes..</span>';

                        //$("#addMoreBox").remove();
                        $("#msgBoxDiv-result").append(formData);
                        //$("#msgBoxDiv-result").append(addMore);
                        changeNums();
                        boxCount++;
                    }
                    
                    var deleteFromDb = [];
                    function removeDiv(id,action){
                        var isTrue = confirm("Are you sure to delete message box?\nClick OK to delete..\n\n");
                        if(isTrue){
                            if (action === 'update') {
                                var deleteId = $("#hiddenF"+id).val();
                                deleteFromDb.push(deleteId);
                            }
                            $("#tourEdit"+id).slideUp('slow', function(){
                                $("#tourEdit"+id).remove();
                                changeNums();
                            });
                        }
                    }
                    
                    function removeDivEdit(id){
                        var isTrue = confirm("Are you sure to delete message box?\nClick OK to delete..\n\n");
                        if(isTrue){
                                var deleteId = id;
                                console.log(deleteId);
                                $.ajax({
                                    url:"dashboardHelpDialog.php?function=deletewindow&id=" + id + "&csrfMagicToken=" + csrfMagicToken,
                                    success:function(data){
                                        alert("success");
                                        $("#tour-Edit"+id).slideUp('slow', function(){
                                            $("#tour-Edit"+id).remove();
                                            console.log(id);
                                            changeNums();
                                        });
                                    },
                                    error:function(data){
                                        alert("error");
                                    }
                                });
                             
                             
                            
                        }
                    }
                    
                    function changeNums(){
                        var i=0;
                        $(".addCnt").each(function(){
                            i++;
                            $(this).html(i);
                        });
                    }
                    
                    

function addhelper(){
    $('#name').val('');
    $('#descr').val('');
    $('#selectType').val('');
    $('#path').val('');
    $('#search_key').val('');
    $('#msg').val('');
    //$('#').html('');
    
}

function edithelper(){
    $('#msgBoxDiv-result').html('');
    var id=$("#touridhidden").val();
    //console.log(id);
    if($(".odd").hasClass("selected")||$(".even").hasClass("selected")){
        $("#edit-tour").show();
        var url = "dashboardHelpDialog.php?function=fetchdataedit&id=" + id + "&csrfMagicToken=" + csrfMagicToken;
                        setTimeout(function(){
                        $.ajax({
                            url: url,
                            dataType: 'json',
                            success: function (data) {
                                $('#name2').val(data.name);
                                $('#descr2').val(data.descr);
                                $('#path2').val(data.path);
                                $('#searchKey2').val(data.searchKeyWord);
                                var name = data.name;
                                //console.log(data.name);
                                $.ajax({
                                    url: "dashboardHelpDialog.php?function=fetchaddresult&id=" + id + "&csrfMagicToken=" + csrfMagicToken,
                                    dataType: "JSON",
                                    success: function(data) {
                                        $.ajax({
                                            url:"dashboardHelpDialog.php?function=checkguideType&id="+id + "&csrfMagicToken=" + csrfMagicToken,
                                            dataType:'json',
                                            success:function(data){
                                                //console.log(data);
                                               if(data.guideType=='link'){
                                                   $.ajax({
                                                       url:"dashboardHelpDialog.php?function=getDetailsType&id="+id + "&csrfMagicToken=" + csrfMagicToken,
                                                       success:function(data){
                                                          // alert("success");
                                                        //console.log(data);
                                                        var addMore = '<span id="addMoreBox"><a onclick="getMoreMsgBoxs_edit()" style="cursor: pointer;">Click Here </a> To Add More Message Boxes..</span>';  
                                                        $('#msgBoxDiv-result').append(data);
                                                        $('#msgBoxDiv-result').append(addMore);
                                                       },
                                                       error:function(error){
                                                           //alert("error") ;
                                                       }
                                                   });
                                                   
                                               }
                                               if(data.guideType=='image'){
                                                   //alert("image");
                                                   $('#selectTypeField2').hide();
                                                  var check='<div style="padding: 1%;border: 1px solid #E8E3E3; margin-top:2%; margin-left: 2%; width: 95%;" id="imgId2">'
                                                            +'<label class="textCss addCnt" style="margin-left: -3.5%;float: left;"></label>'
                                                            +'<i id="crs-btn" class="tim-icons icon-simple-remove" onclick="removeImgDiv(\'' + id + '\',\'edit\')"></i>'
                                                            +'<img src="../imgUploads/'+data.filenames+'" style="height: 200px; width: 200px;">'
                                                            +'<div id="lable1" style="color: green;" filename='+data.filenames+'><h2>'+data.filenames+'</h2></div>'
                                                            +'</div>'
                                                            +'<div id="imgForm">'
                                                            +'<span id="addMoreImgBox"><a onclick="getImageBoxsForUpdate()" style="cursor: pointer;">Click Here </a> To Add Images</span>'
                                                            +'</div>';
                                                  $('#msgBoxDiv-result').append(check);
                                                  $("#editproduct").attr('onclick','submitImages(\'edit\',\'\');');
                                               }
                                               if(data.guideType=='video'){
                                                   //alert("video");
                                                   $('#selectTypeField2').hide();
                                                   //console.log(data);
                                                   //console.log(data.filenames);
                                                    var str = '<div class="form-group" id="msgBoxDiv" style="margin-top: 4%;">'
                                                                +'<video style="padding: 1%;width: 50%;height: 200px;" controls="">'
                                                                +'<source src="'+data.filenames+'" type="video/mp4">'
                                                                +'Your browser does not support the video tag.'
                                                                +'</video>'
                                                                +'<div style="float: right;margin-top: -30%;margin-right: 2%;"><h3>Upload New video.</h3><h4><span style="color: red;">Note: *Current video will be replaced <br> with new Video.</span></h4></div>'
                                                                +'<div class="form-group" style="margin: 1%;">'
                                                                +'<label class="textCss">VideoLink : </label>'
                                                                +'<input type="file" style="width:70%;height:191%; margin-left: 10%;opacity:1;" class="form-control" id="videoField" name="videoField" placeholder="Select video" accept="video/mp4">'
                                                                + '</div>'
                                                                +'</div>';
                                                   $("#msgBoxDiv-result").append(str);
                                                   $("#editproduct").attr('onclick','submitVideo(\'update\',\'\');');
                                                   //$('#msgBoxDiv-result').append(formData);
                                               }
                                               if(data.guideType=='videoLink'){
                                                   //alert("video link");
                                                   $('#selectTypeField2').hide();
                                                   var str = '<label class="textCss" style="margin-left: 4%;">Add Video Link : </label>'
                                                            +'<input type="text" style="width:70%; margin-left: 4%;" class="form-control" id="videoLinkField2" name="videoLinkField2" value='+decodeURIComponent(data.path)+' >';
                                                   $('#msgBoxDiv-result').append(str);
                                                   $("#editproduct").attr('onclick','submitVideoLink(\'update\',\'\');');
                                               }
                                            },
                                            error:function(data){
                                                //alert("error");
                                            }
                                        });
//                                                   
//                                                   //alert("success"); 
                                                },
                                    error: function(error){
                                                //alert("failure");
                                    }
                                    });
                            }, error: function (error) {
                                alert("error");
                            }
                        });
                        },1000);
    }
    else
    {
        $.notify('Please select a record');
        $("#edit-tour").hide();
        closePopUp();
    }
}

function deletehelper(){
    var id=$("#touridhidden").val();
    //console.log(id);
    if($(".odd").hasClass("selected")||$(".even").hasClass("selected")){
        $("#delete-tour").show();
        var url = "dashboardHelpDialog.php?function=fetchdatadelete&id=" + id + "&csrfMagicToken=" + csrfMagicToken;
                        setTimeout(function(){
                        $.ajax({
                            url: url,
                            dataType: 'json',
                            success: function (data) {
                                $('#delete-name').val(data.name);
                                $('#delete-desc').val(data.descr);
                                $('#delete-path').val(data.path);
                                $('#delete-keywords').val(data.searchKeyWord);
                                //alert("success");
                                
                            }, error: function (error) {
                                //alert("error");
                            }
                        });
                        },1000);
    }
    else
    {
        $.notify('Please select a record');
        $("#delete-tour").hide();
        closePopUp();
    }
}

function deleteForm(id) {
                        var id=$("#touridhidden").val();
                        //console.log(id);
                        var url = "dashboardHelpDialog.php?function=deleteHelper&id=" + id + "&csrfMagicToken=" + csrfMagicToken;
                        $("#msgImg").show();
                        $("#msgText").hide();
                        setTimeout(function(){
                        $.ajax({url: url,
                            success: function (data) {
                                //console.log(data);
                                    $("#msgImg").hide();
                                    $("#msgText").show();
                                    $("#msgText").css("color", "green");
                                    $("#msgText").html("<b>Successfully Deleted....</b>");
                                    setTimeout(function () {
                                        window.location.href = "dashtour.php";
                                    }, 1000);
                            }, error: function (error) {
                               // console.log("error");
                                $("#msgImg").hide();
                                $("#msgText").show();
                                $("#msgText").css("color", "red");
                                $("#msgText").html("<b>Error Occured.....</b>");
                            }
                        });
                        },1000);
                    }
