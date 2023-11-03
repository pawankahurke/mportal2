$(document).ready(function () {

});

function OnSelectionCust() {

    //var username = $('input[name ="username"]').val('sdsad');
    //alert(username);
    var custId = $('#custlist').val();
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "sites_config/sites-help.php",
        data: "function=get_SKUForCust_ajx&custId=" + custId,
        success: function (result) {
            var optionStr = '';
            for (var i = 0; i < result.sid.length; i++) {
                //console.log(result.sid[i]);
                optionStr += '<option value=' + result.sid[i] + '>' + result.name[i] + '</option>';
            }
            //console.log("optionStr-->"+optionStr);
            $("#skulist").html(optionStr);

        },
        error: function (result) {

        }
    });
}