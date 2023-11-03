$(document).ready(function(){
    getWeightDetails(1,'','','');
});

$(document).ready(function(){

    // Add new element
    $(".add").click(function(){

     // Finding total number of elements added
     var total_element = $(".element").length;

     // last <div> with element class id
     var lastid = $(".element:last").attr("id");
     var split_id = lastid.split("_");
     var nextindex = Number(split_id[1]) + 1;

     var max = 5;
     // Check total number elements
     if(total_element < max ){
      // Adding new div container after last occurance of element class
      $(".element:last").after("<div class='element' id='div_"+ nextindex +"'></div>");

      // Adding element to <div>
      $("#div_" + nextindex).append(
      "<span id='remove_" + nextindex + "' class='remove'><i class='tim-icons icon-simple-delete iconAdd'></span>"+
    //   "<span id='remove_" + nextindex + "' class='remove'>X</span>"+
      '<table>'+
          '<tr>'+
              '<th>From</th>'+
              '<th>To</th>'+
              '<th>Rank</th>'+
              '<th>Score</th>'+
              '<th>Metric Weight</th>'+
              '<th>Category Weight</th>'+
              '<th>Sub Category Weight</th>'+
          '</tr>'+
          '<tr>'+
              '<td>'+
                "<input id='from_" + nextindex + "' type = 'text'/>"+
              '</td>'+
              '<td>'+
                "<input id='to_" + nextindex + "' type = 'text'/>"+
              '</td>'+
              '<td>'+
                "<input id='rank_" + nextindex + "' type = 'text'/>"+
              '</td>'+
              '<td>'+
                "<input id='score_" + nextindex + "' type = 'text'/>"+
              '</td>'+
              '<td>'+
                "<input id='mw_" + nextindex + "' type = 'text'/>"+
              '</td>'+
              '<td>'+
                "<input id='lcw_" + nextindex + "' type = 'text'/>"+
              '</td>'+
              '<td>'+
                "<input id='scw_" + nextindex + "' type = 'text'/>"+
              '</td>'+
          '</tr>'+
      '</table>'
      );

     }

    });

    // Remove element
    $('.container').on('click','.remove',function(){

     var id = this.id;
     var split_id = id.split("_");
     var deleteindex = split_id[1];

     // Remove <div> with id
     $("#div_" + deleteindex).remove();

    });

    $(document).on('click', ".editadd", function () {
        var total_element = $(".editelement").length;
        // last <div> with element class id
        var lastid = $(".editelement:last").attr("id");
        var split_id = lastid.split("_");
        var nextindex = Number(split_id[1]) + 1;

        var max = 5;
        // Check total number elements
        if(total_element < max ){
         // Adding new div container after last occurance of element class
         $(".editelement:last").after("<div class='form-group editelement' id='editdiv_"+ nextindex +"'></div>");

         // Adding element to <div>
         $("#editdiv_" + nextindex).append(
         "<span id='editremove_" + nextindex + "' class='editremove'><i class='tim-icons icon-simple-delete iconAdd'></span>"+
         '<table>'+
             '<tr>'+
                 '<th>From</th>'+
                 '<th>To</th>'+
                 '<th>Rank</th>'+
                 '<th>Score</th>'+
                 '<th>Metric Weight</th>'+
                 '<th>Category Weight</th>'+
                 '<th>Sub Category Weight</th>'+
             '</tr>'+
             '<tr>'+
                 '<td>'+
                   "<input id='editfrom_" + nextindex + "' type = 'text'/>"+
                 '</td>'+
                 '<td>'+
                   "<input id='editto_" + nextindex + "' type = 'text'/>"+
                 '</td>'+
                 '<td>'+
                   "<input id='editrank_" + nextindex + "' type = 'text'/>"+
                 '</td>'+
                 '<td>'+
                   "<input id='editscore_" + nextindex + "' type = 'text'/>"+
                 '</td>'+
                 '<td>'+
                   "<input id='editmw_" + nextindex + "' type = 'text'/>"+
                 '</td>'+
                 '<td>'+
                   "<input id='editlcw_" + nextindex + "' type = 'text'/>"+
                 '</td>'+
                 '<td>'+
                   "<input id='editscw_" + nextindex + "' type = 'text'/>"+
                 '</td>'+
             '</tr>'+
         '</table>'
         );

        }
    });

    //Remove edit element
    $('.editcontainer').on('click','.editremove',function(){
        var id = this.id;
        var split_id = id.split("_");
        var deleteindex = split_id[1];
        $("#editdiv_" + deleteindex).remove();

    });

   });

function getWeightDetails(nextPage = 1, notifSearch = '', key = '', sort = ''){
    $('#loader').show();

    notifSearch = $('#notifSearch').val();

    if (typeof notifSearch === 'undefined') {
        notifSearch = '';
    }

  checkAndUpdateActiveSortElement(key, sort);

  var dat = {
        "function": 'getWeightDetails',
        'csrfMagicToken': csrfMagicToken,
        'limitCount': $('#notifyDtl_length :selected').val(),
        'nextPage': nextPage,
        'notifSearch': notifSearch,
        'order' : key,
        'sort' :sort
    };
    var gridData = {};
    $.ajax({
        url: "weightsFunction.php",
        type: "POST",
        dataType: "json",
        data: dat,
        success: function (gridData) {
            $('#absoLoader').hide();
            $(".se-pre-con").hide();
            $('#weightsTable').DataTable().destroy();
            $('#weightsTable tbody').empty();
            table3 = $('#weightsTable').DataTable({
                scrollY: 'calc(100vh - 240px)',
                scrollCollapse: true,
                paging: false,
                searching: false,
                bFilter: false,
                ordering: false,
                aaData: gridData.html,
                bAutoWidth: true,
                select: false,
                bInfo: false,
                responsive: true,
                stateSave: true,
                processing: true,
                "pagingType": "full_numbers",
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                order: [[2, "asc"]],
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    search: "_INPUT_",
                    searchPlaceholder: "Search records"
                },
                "columnDefs": [
                    {
                        "targets": 0,
                        "orderable": false
                    }
                ],
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function (settings, json) {
                    $('.equalHeight').show();
                    $('.loader').hide();
                    $("th").removeClass('sorting_desc');
                    $("th").removeClass('sorting_asc');
                    },
                "drawCallback": function (settings) {
                    $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
                }
            });
            $('.dataTables_filter input').addClass('form-control');
            $('.tableloader').hide();
        }
    });

    $('#weightsTable').on('dblclick', 'tr', function () {
        var rowID = table3.row(this).data();
        table3.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        $('#selectedweight').val(rowID[5]);
        $('#editOption').show();
        $('#toggleButton').hide();
        rightContainerSlideOn('editweight');
        getType(rowID[5],'edit');
    });

}

$('body').on('click', '.page-link', function () {
    var nextPage = $(this).data('pgno');
    notifName = $(this).data('name');
    const activeElement = window.currentActiveSortElement;
    const key = (activeElement) ? activeElement.sort : '';
    const sort = (activeElement) ? activeElement.type : '';
    getWeightDetails(nextPage,'', key, sort);
});

$('body').on('change', '#notifyDtl_lengthSel', function () {
    getWeightDetails(1,'','','');
});

function showAddnew(){
    rightContainerSlideOn('addNew');
    getType('','add');
}

function getType(id,type){
    $('#editweight').find('.form-control').attr('readonly', true);
    $('#editweight').find('.selectpicker').attr('disabled', true);
    $('#editweight').find(".selectpicker").selectpicker("refresh");
    $.ajax({
        url: "weightsFunction.php",
        type: "POST",
        dataType: "json",
        data: {'function': 'getTypeDetails','type':type,'id':id,'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            if(type == 'add'){
                $('#weightType').html(data.MerticName);
                $(".selectpicker").selectpicker("refresh");
                $('#weightCategory').html(data.Category);
                $(".selectpicker").selectpicker("refresh");
                $('#weightSubCat').html(data.subcategory);
                $(".selectpicker").selectpicker("refresh");
                // $('#weightAttr').val(data.SpecificInfo);
            }else{
                $('#editweightType').html(data.MerticName);
                $(".selectpicker").selectpicker("refresh");
                $('#editweightCategory').html(data.Category);
                $(".selectpicker").selectpicker("refresh");
                $('#editweightSubCat').html(data.subcategory);
                $(".selectpicker").selectpicker("refresh");
                $('#editweightAttr').val(data.SpecificInfo);
                $('#editweightDescription').val(data.MetricDesc);
                var ScoresVal = JSON.parse(data.Scores);

                // var scoreCount = ScoresVal.length;
                // for(var i = 1;i <= scoreCount; i++){
                //     $('#editdiv_'+i).show();
                // }
                var strHTML = '';
                ScoresVal.map((a,b) => {
                    var check = JSON.stringify(a);
                    var iconStr = '';
                    if(b == 0){
                        iconStr += "<span class='editadd' id='editadd'><i class='tim-icons icon-simple-add iconAdd' ></i></span>";
                    }else{
                        iconStr += "<span id='editremove_" + b + "' class='editremove'><i class='tim-icons icon-simple-delete iconAdd' ></i></span>";
                    }

                    check = JSON.parse(check);
                    var from = check['from'];
                    strHTML += "<div class='form-group has-label editelement' id='editdiv_" + b + "'>"+
                    iconStr +
                    "<table>"+
                        "<tr>"+
                            "<th>From</th>"+
                            "<th>To</th>"+
                            "<th>Rank</th>"+
                            "<th>Score</th>"+
                            "<th>Metric Weight</th>"+
                            "<th>Category Weight</th>"+
                            "<th>Sub Category Weight</th>"+
                        "</tr>"+
                        "<tr>"+
                            "<td>"+
                                "<input id='editfrom_" + b + "' type = 'text'  value='" + check['from'] + "' />  "+
                            "</td>"+
                            "<td>"+
                                "<input id='editto_" + b + "' type = 'text' value='" + check['to'] + "'/> "+
                            "</td>"+
                            "<td>"+
                                "<input id='editrank_" + b + "' type = 'text' value='" + check['rank'] + "'/> "+
                            "</td>"+
                            "<td>"+
                                "<input id='editscore_" + b + "' type = 'text' value='" + check['score'] + "'/> "+
                            "</td>"+
                            "<td>"+
                                "<input id='editmw_" + b + "' type = 'text' value='" + check['mw'] + "'/>  "+
                            "</td>"+
                            "<td>"+
                                "<input id='editlcw_" + b + "' type = 'text' value='" + check['cw'] + "'/>    "+
                            "</td>"+
                            "<td>"+
                                "<input id='editscw_" + b + "' type = 'text' value='" + check['scw'] + "'/>   "+
                            "</td>"+
                        "</tr>"+
                    "</table>"+
                    "</div>";
                });
                $('#editData').html(strHTML);

            }

        },
        error: function(error){
            console.log("error");
        }
    });
}

function addInputClass(id){
    $('#err_Scores').html('');
    $("[id*='"+ id +"']").each(function() {
        if(isNaN($(this).val())){
            $(this).addClass('red');
            $.notify('Please enter a numeric From value');
            return false;
        }
    });
}


$('#addWeight').click(function(){
    $('#err_weight_Type,#err_weight_Category,#err_weight_scat,#err_weight_desc,#err_sInfo,#err_Scores').html('');
    var toArr = [];
    var fromArr = [];
    var rankArr = [];
    var scoreArr = [];
    var mwArr = [];
    var cwArr = [];
    var scwArr = [];



    $("[id*=to_]").each(function() {
        toArr.push($(this).val());
    });

    $("[id*=from_]").each(function() {
        fromArr.push($(this).val());
    });

    $("[id*=rank_]").each(function() {
        rankArr.push($(this).val());
    });

    $("[id*=score_]").each(function() {
        scoreArr.push($(this).val());
    });

    $("[id*=mw_]").each(function() {
        mwArr.push($(this).val());
    });

    $("[id*=lcw_]").each(function() {
        cwArr.push($(this).val());
    });

    $("[id*=scw_]").each(function() {
        scwArr.push($(this).val());
    });

    var Type = $('#weightType').val();
    var Category = $('#weightCategory').val();
    var SubCat = $('#weightSubCat').val();
    var Description = $('#weightDescription').val();
    var Attr = $('#weightAttr').val();

    if(Type == ""){
        $('#err_weight_Type').css("color", "red").html('Please select the type');
        return false;
    }

    if(Category == ""){
        $('#err_weight_Category').css("color", "red").html('Please select the category');
        return false;
    }

    if(SubCat == ""){
        $('#err_weight_scat').css("color", "red").html('Please select the sub category');
        return false;
    }

    if(Description == ""){
        $('#err_weight_desc').css("color", "red").html('Please add a description');
        return false;
    }

    if(Attr == ""){
        $('#err_sInfo').css("color", "red").html('Please select an attribute');
        return false;
    }

    if(fromArr[0] == ""){
        alert("if");
        $('#err_Scores').css("color", "red").html('From cannot be empty');
        return false;
    }else{
        $('#err_Scores').html('');
        var val1 = $('#from_1').val();
        var val2 = $('#from_2').val();
        var val3 = $('#from_3').val();
        var val4 = $('#from_4').val();
        var val5 = $('#from_5').val();

        if(isNaN(val1) && isNaN(val2) && isNaN(val3) && isNaN(val4) && isNaN(val5)){
            $.notify('Please enter a numeric From value');
            return false;
        }
    }

    if(toArr[0] == ""){
        $('#err_Scores').css("color", "red").html('To cannot be empty');
        return false;
    }else{
        $('#err_Scores').html('');
        var val1 = $('#to_1').val();
        var val2 = $('#to_2').val();
        var val3 = $('#to_3').val();
        var val4 = $('#to_4').val();
        var val5 = $('#to_5').val();

        if(isNaN(val1) && isNaN(val2) && isNaN(val3) && isNaN(val4) && isNaN(val5)){
            $.notify('Please enter a numeric To value');
            return false;
        }
    }

    if(rankArr[0] == ""){
        $('#err_Scores').css("color", "red").html('Rank cannot be empty');
        return false;
    }else{
        $('#err_Scores').html('');
        var val1 = $('#rank_1').val();
        var val2 = $('#rank_2').val();
        var val3 = $('#rank_3').val();
        var val4 = $('#rank_4').val();
        var val5 = $('#rank_5').val();

        if(isNaN(val1) && isNaN(val2) && isNaN(val3) && isNaN(val4) && isNaN(val5)){
            $.notify('Please enter a numeric Rank value');
            return false;
        }
    }


    if(scoreArr[0] == ""){
        $('#err_Scores').css("color", "red").html('Score cannot be empty');
        return false;
    }else{
        $('#err_Scores').html('');
        var val1 = $('#score_1').val();
        var val2 = $('#score_2').val();
        var val3 = $('#score_3').val();
        var val4 = $('#score_4').val();
        var val5 = $('#score_5').val();

        if(isNaN(val1) && isNaN(val2) && isNaN(val3) && isNaN(val4) && isNaN(val5)){
            $.notify('Please enter a numeric From value');
            return false;
        }
    }

    if(mwArr[0] == ""){
        $('#err_Scores').css("color", "red").html('Metric Score cannot be empty');
        return false;
    }else{
        $('#err_Scores').html('');
        var val1 = $('#mw_1').val();
        var val2 = $('#mw_2').val();
        var val3 = $('#mw_3').val();
        var val4 = $('#mw_4').val();
        var val5 = $('#mw_5').val();

        if(isNaN(val1) && isNaN(val2) && isNaN(val3) && isNaN(val4) && isNaN(val5)){
            $.notify('Please enter a numeric Metric Score value');
            return false;
        }
    }

    if(cwArr[0] == ""){
        $('#err_Scores').css("color", "red").html('Category score cannot be empty');
        return false;
    }else{
        $('#err_Scores').html('');
        var val1 = $('#cw_1').val();
        var val2 = $('#cw_2').val();
        var val3 = $('#cw_3').val();
        var val4 = $('#cw_4').val();
        var val5 = $('#cw_5').val();

        if(isNaN(val1) && isNaN(val2) && isNaN(val3) && isNaN(val4) && isNaN(val5)){
            $.notify('Please enter a numeric Category score value');
            return false;
        }
    }

    if(scwArr[0] == ""){
        $('#err_Scores').css("color", "red").html('Sub Category score cannot be empty');
        return false;
    }else{
        $('#err_Scores').html('');
        var val1 = $('#scw_1').val();
        var val2 = $('#scw_2').val();
        var val3 = $('#scw_3').val();
        var val4 = $('#scw_4').val();
        var val5 = $('#scw_5').val();

        if(isNaN(val1) && isNaN(val2) && isNaN(val3) && isNaN(val4) && isNaN(val5)){
            $.notify('Please enter a numeric Sub Category score value');
            return false;
        }
    }

    var dat = {
        'function': 'addNewWeight',
        'csrfMagicToken': csrfMagicToken,
        'Type': Type,
        'updateType':'add',
        'Category': Category,
        'SubCat': SubCat,
        'Description': Description,
        'Attr': Attr,
        'to': toArr.join(","),
        'from': fromArr.join(","),
        'rank':rankArr.join(","),
        'score': scoreArr.join(","),
        'mw': mwArr.join(","),
        'cw': cwArr.join(","),
        'scw': scwArr.join(",")
    }

    $.ajax({
        url: "weightsFunction.php",
        type: "POST",
        // dataType: "json",
        data: dat,
        success: function (data) {
            if($.trim(data) == "success"){
                $.notify("Visualization Weight Added Successfully");
                rightContainerSlideClose('addNew');
                getWeightDetails(1,'','','');
            }else{
                $.notify("Error in adding the Visualization Weight");
                rightContainerSlideClose('addNew');
            }
        },
        error: function(error){
            $.notify("Error in adding the Visualization Weight");
            rightContainerSlideClose('addNew');
        }
    });
});

$('#updateWeight').click(function(){

    $('#err_editweight_Type,#err_editweight_Category,#err_editweight_scat,#err_editweight_desc,#err_editsInfo,#err_editScores').html('');
    var toArr = [];
    var fromArr = [];
    var rankArr = [];
    var scoreArr = [];
    var mwArr = [];
    var cwArr = [];
    var scwArr = [];

    $("[id*=editto_]").each(function() {
        toArr.push($(this).val());
    });

    $("[id*=editfrom_]").each(function() {
        fromArr.push($(this).val());
    });

    $("[id*=editrank_]").each(function() {
        rankArr.push($(this).val());
    });

    $("[id*=editscore_]").each(function() {
        scoreArr.push($(this).val());
    });

    $("[id*=editmw_]").each(function() {
        mwArr.push($(this).val());
    });

    $("[id*=editlcw_]").each(function() {
        cwArr.push($(this).val());
    });

    $("[id*=editscw_]").each(function() {
        scwArr.push($(this).val());
    });

    var Type = $('#editweightType').val();
    var Category = $('#editweightCategory').val();
    var SubCat = $('#editweightSubCat').val();
    var Description = $('#editweightDescription').val();
    var Attr = $('#editweightAttr').val();
    var selected = $('#selectedweight').val();

    if(Type == ""){
        $('#err_editweight_Type').css("color", "red").html('Please select the type');
        return false;
    }

    if(Category == ""){
        $('#err_editweight_Category').css("color", "red").html('Please select the category');
        return false;
    }

    if(SubCat == ""){
        $('#err_editweight_scat').css("color", "red").html('Please select the sub category');
        return false;
    }

    if(Description == ""){
        $('#err_editweight_desc').css("color", "red").html('Please add a description');
        return false;
    }

    if(Attr == ""){
        $('#err_editsInfo').css("color", "red").html('Please select an attribute');
        return false;
    }

    if(fromArr[0] == ""){
        $('#err_editScores').css("color", "red").html('Please add atleast one from value');
        return false;
    }

    if(toArr[0] == ""){
        $('#err_editScores').css("color", "red").html('Please add atleast one to value');
        return false;
    }

    if(rankArr[0] == ""){
        $('#err_editScores').css("color", "red").html('Please add atleast one rank');
        return false;
    }

    if(scoreArr[0] == ""){
        $('#err_editScores').css("color", "red").html('Please add atleast one score');
        return false;
    }

    if(mwArr[0] == ""){
        $('#err_editScores').css("color", "red").html('Please add atleast one mw');
        return false;
    }

    if(cwArr[0] == ""){
        $('#err_editScores').css("color", "red").html('Please add atleast one cw');
        return false;
    }

    if(scwArr[0] == ""){
        $('#err_editScores').css("color", "red").html('Please add atleast one scw');
        return false;
    }


    var dat = {
        'function': 'addNewWeight',
        'csrfMagicToken': csrfMagicToken,
        'updateType':'edit',
        'Type': Type,
        'Category': Category,
        'SubCat': SubCat,
        'Description': Description,
        'Attr': Attr,
        'to': toArr.join(","),
        'from': fromArr.join(","),
        'rank':rankArr.join(","),
        'score': scoreArr.join(","),
        'mw': mwArr.join(","),
        'cw': cwArr.join(","),
        'scw': scwArr.join(","),
        'selected': selected
    }

    $.ajax({
        url: "weightsFunction.php",
        type: "POST",
        data: dat,
        success: function (data) {
            if($.trim(data) == "success"){
                $.notify("Visualization Weight Updated Successfully");
                rightContainerSlideClose('editweight');
                getWeightDetails(1,'','','');
            }else{
                $.notify("Error in updating the Visualization Weight");
                rightContainerSlideClose('editweight');
            }
        },
        error: function(error){
            $.notify("Error in updating the Visualization Weight");
            rightContainerSlideClose('editweight');
        }
    });
});

function exportWeights(){
    window.location.href = "weightsFunction.php?function=exportWeightDetails&csrfMagicToken=" + csrfMagicToken;
    $.notify('Please check the Download Bar or the Downloads folder');
}

$('.closebtn').click(function(){
    $('#err_weight_Type,#err_weight_Category,#err_weight_scat,#err_weight_desc,#err_sInfo,#err_Scores').html('');
    $('#weightDescription').val('');
    $('#weightAttr').val('');
    $('#from_1,#from_2,#from_3,#from_4,#from_5').val('');
    $('#to_1,#to_2,#to_3,#to_4,#to_5').val('');
    $('#rank_1,#rank_2,#rank_3,#rank_4,#rank_4').val('');
    $('#score_1,#score_2,#score_3,#score_4,#score_5').val('');
    $('#mw_1,#mw_2,#mw_3,#mw_4,#mw_5').val('');
    $('#lcw_1,#lcw_2,#lcw_3,#lcw_4,#lcw_5').val('');
    $('#scw_1,#scw_2,#scw_3,#scw_4,#scw_5').val('');
});
