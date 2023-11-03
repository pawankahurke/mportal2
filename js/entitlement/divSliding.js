// JavaScript Document
var SlideWidth = 708;
var SlideSpeed = 800;

$(document).ready(function() {
    // set the prev and next buttons display
    SetNavigationDisplay();
});

function CurrentMargin() {
    // get current margin of slider
    var currentMargin = $(".slider-wrapperChg").css("margin-left");

    // first page load, margin will be auto, we need to change this to 0
    if (currentMargin == "auto") {
        currentMargin = 0;
    }

    // return the current margin to the function as an integer
    return parseInt(currentMargin);
}

function SetNavigationDisplay() {
    // get current margin
    var currentMargin = CurrentMargin();

    // if current margin is at 0, then we are at the beginning, hide previous
    if (currentMargin == 0) {
        $("#PreviousButton").hide();
    }
    else {
        $("#PreviousButton").show();
    }

    // get wrapperChg width
    var wrapperWidth = $(".slider-wrapperChg").width();

    // turn current margin into postive number and calculate if we are at last slide, if so, hide next button
    if ((currentMargin * -1) == (wrapperWidth - SlideWidth)) {
        $("#NextButton").hide();
    }
    else {
        $("#NextButton").show();
    }
}
var dynamicPage = 0;
function NextSlide(slideDivId) {
    // get the current margin and subtract the slide width
    dynamicPage = slideDivId;
    var newMargin = CurrentMargin() - SlideWidth;
    // slide the wrapper to the left to show the next panel at the set speed. Then set the nav display on completion of animation.

    var headerData = $("#slideDiv_" + slideDivId + "_link").val().split('_');
    $("#toolNav").html('<span class="breadcrumbs" onclick="setHomePage()">toolbox</span><span class="breadval"> >' + unescape(headerData[1]) + '</span>');

    $(".slider-wrapperChg").animate({marginLeft: newMargin}, SlideSpeed, function() {
    });
    $("#slideDiv_" + slideDivId).html('<span onclick="PreviousSlide(' + slideDivId + ')"><img src="images/Previous.png"></span>');
}

function setHomePage() {

    $(".slider-wrapperChg").animate({marginLeft: 0}, SlideSpeed, function() { });

    $('#toolNav > span').each(function() {
        var classTxt = $(this).attr('class');
        classTxt = classTxt.replace(/[a-z]/g, '');
        classTxt = classTxt.replace(/[A-Z]/g, '');
        classTxt = classTxt.replace(/^\s+|\s+$/g, '');
        if (parseInt(classTxt) === 0) {
            $('.' + classTxt).removeClass('breadcrumbs');
        } else {
            $("." + classTxt).remove();
        }
    });

}

function breadCumLink(newMargin, page) {
    $(".slider-wrapperChg").animate({marginLeft: newMargin}, SlideSpeed, function() {  });

    var conditionMatched = false;
    $('#toolNav > span').each(function() {
        var classTxt = $(this).attr('class');
        classTxt = classTxt.replace(/[a-z]/g, '');
        classTxt = classTxt.replace(/[A-Z]/g, '');
        classTxt = classTxt.replace(/^\s+|\s+$/g, '');
        if (conditionMatched === false) {
            if (parseInt(classTxt) === parseInt(page)) {
                $('.' + classTxt).removeClass('breadcrumbs');
                conditionMatched = true;
            }
        } else {
            $('.' + classTxt).addClass('breadcrumbs');
            $('.' + classTxt).remove();
        }

    });

}

function NextSlideJump(slideDivId, slideDiv, menu, id, page)
{
    //console.log("NextSlideJump-->slideDivId:"+slideDivId+",slideDiv:"+slideDiv+",menu:"+menu+",id:"+id+",page:"+page);

    dynamicPage = slideDivId;
    var newMargin = CurrentMargin() - (SlideWidth * slideDivId);
    $(".slider-wrapperChg").animate({marginLeft: newMargin}, SlideSpeed, function() {
    });

    var classBreadcum = 'breadval';

    $("#toolNav").append('<span class="' + classBreadcum + ' ' + page + '" onclick="breadCumLink(' + newMargin + ',' + page + ')"> > ' + unescape(menu) + '</span>');
  
    
    //breadval 0 breadcrumbs --->with hand pointer
    //breadval 0  --->no hand pointer

    var conditionMatched = false;
    $('#toolNav > span').each(function() {
        var classTxt = $(this).attr('class');
        //console.log("classTxt:"+classTxt)
        classTxt = classTxt.replace(/[a-z]/g, '');
        classTxt = classTxt.replace(/[A-Z]/g, '');
        classTxt = classTxt.replace(/^\s+|\s+$/g, '');
        //console.log("classTxt:"+classTxt+",page:"+page)
        
        if (conditionMatched === false) {
            if (parseInt(classTxt) === parseInt(page)) {
                $('.' + classTxt).removeClass('breadcrumbs');
                conditionMatched = true;
            } else {
                $('.' + classTxt).addClass('breadcrumbs');
            }
        } else {
            $('.' + classTxt).addClass('breadcrumbs');
        }

    });


    // ## breadcum link end

    for (var i = 2; i <= slideDiv; i++)
    {
        if (i == 2) {  //by anil
            $("#slideDiv_" + i).html('<span style="margin-left:30px;" onclick="PreviousSlide(' + i + ')"><img src="images/Previous.png"></span>');
        } else {
            $("#slideDiv_" + i).html('<span onclick="PreviousSlide(' + i + ')"><img src="images/Previous.png"></span>');
        }
    }
}

function PreviousSlide(slideDivId) {
    // get the current margin and subtract the slide width
    var newMargin = CurrentMargin() + SlideWidth;

    var headerData = $("#slideDiv_" + (slideDivId - 1) + "_link").val().split('_');
    $("#toolNav").html('<span class="breadcrumbs" onclick="setHomePage()">toolbox</span><span class="breadval"> >' + unescape(headerData[1]) + '</span>');
    // slide the wrapperChg to the right to show the previous panel at the set speed. Then set the nav display on completion of animation.
    $(".slider-wrapperChg").animate({marginLeft: newMargin}, SlideSpeed, function() {
    });

    if (slideDivId == 2) {
        $("#slideDiv_" + slideDivId).html('<span style="margin-left:31px;" onclick="PreviousSlide(' + parseInt(slideDivId - 1) + ')"><img src="images/Previous.png"></span>');// by anil
    } else {
        $("#slideDiv_" + slideDivId).html('<span onclick="NextSlide(' + slideDivId + ')"><img src="images/Next.png"></span>');
    }
} 