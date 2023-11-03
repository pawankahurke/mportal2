/**
 * Profile Wizard module JS History
 * ---------------------------------
 * DATE         WHO     WHAT
 * ==========================
 * 04-Oct-19    JHN     Initial implementation.
 * 09-Oct-19    JHN     Functionality update.
 * 
 */
var switchButton = $('#switchPreview');

$(document).ready(function () {
    renderProfileConfiguration();
    
    $('.review-tiles').css({'font-size': '12px', 'font-weight': 'normal'});
    /*window.switchButton.on('click', function () {
        switchReview();
    });*/
});

function renderProfileConfiguration() {
    $('#render-dash').removeClass('btn-simple').addClass('btn-alert');
    $('#render-clnt').removeClass('btn-alert').addClass('btn-simple');
    var profid = $("#profileID").val();
    $.ajax({
        url: "../lib/l-profilewiz.php",
        type: 'POST',
        data : {
            'function': 'v_render_ProfileDetails',
            'profid' : profid,
            'csrfMagicToken': csrfMagicToken
        },
        dataType: 'json',
        success: function (data) {
            $('#levelOneData').html(data.datalist);
            renderLevelTwoTiles(data.startmid);
        },
        error: function (err) {

        }
    });
}

function renderClientProfileConfiguration() {
    $('#render-clnt').removeClass('btn-simple').addClass('btn-alert');
    $('#render-dash').removeClass('btn-alert').addClass('btn-simple');
    var profid = $("#profileID").val();
    $.ajax({
        url: "../lib/l-profilewiz.php",
        type: 'POST',
        data: {
            'function': 'v_render_ClientProfileDetails',
            'profid' : profid,
            'csrfMagicToken': csrfMagicToken
        },
        dataType: 'json',
        success: function (data) {
            $('#levelOneData').html(data.datalist);
            renderLevelTwoTiles(data.startmid, 'cli');
        },
        error: function (err) {

        }
    });
}

function renderLevelTwoTiles(pmid, type = '') {
    $.ajax({
        url: "../lib/l-profilewiz.php",
        type: 'POST',
        data: {
            'function': 'render_LevelTwoProfile',
            'mid': pmid,
            'showtype': type,
            'csrfMagicToken': csrfMagicToken
        },
        success: function (data) {
            var res = JSON.parse(data);
            $('#tile-header').html(res.heading);
            $('#tile-description').html(res.description);
            $('#child-lvl').html(res.datalist);
        },
        error: function (err) {

        }
    });
}

/*function switchReview() {
    var reviewval = $('#reviewdata').val();
    if (reviewval === 'client') {
        $('#switchPreview').html('Review Dashboard');
        $('#reviewdata').val('dash');
        renderClientProfileConfiguration();
    } else {
        $('#switchPreview').html('Review Client');
        $('#reviewdata').val('client');
        renderProfileConfiguration();
    }
}*/
