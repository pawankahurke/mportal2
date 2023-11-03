
function Get_ComplianceCalendarMonthGraph() {
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    $('.se-pre-con').show();
    $("#WeeklyDataDiv,#DailyDataDiv,#EmptyDataDiv").fadeOut();
    $("#MonthDataDiv").fadeIn();
    $("#complianceGraphMonth").html("");
    $.ajax({
        url: "../lib/l-ajax.php?function=AJAX_GetComplianceCalendarMonthGraph",
        type: 'POST',
        data: 'searchType=' + searchType + '&searchValue=' + searchValue,
        dataType: 'json',
        success: function(data) {
            $('.se-pre-con').hide();
            if (data[0] == "") {
                $("#MonthDataDiv,#WeeklyDataDiv,#DailyDataDiv").fadeOut();
                $("#EmptyDataDiv").fadeIn();
                $("#complianceSearch").html("" + data[2]);
            } else {
                if (data[1] != "") {
                    $("#complianceListMonth").html(data[1]);
                }
                $("#complianceGraphMonth").html(data[0]);
                $("#complianceSearch").html("" + data[2]);
                $('.notification-slider-month').slick('unslick');
                $('.notification-slider-month').slick({
                    slidesToShow: 5,
                    slidesToScroll: 1,
                    speed: 500,
                    adaptiveHeight: true,
                    dots: false,
                    swipe: true,
                    arrows: true,
                    autoplay: false,
                    centerMode: false,
                    focusOnSelect: false,
                    fade: false,
                    infinite: false,
                    responsive: [
                        {
                            breakpoint: 1300,
                            settings: {
                                slidesToShow: 3
                            }
                        },
                        {
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: 2
                            }
                        },
                        {
                            breakpoint: 767,
                            settings: {
                                slidesToShow: 2
                            }
                        },
                        {
                            breakpoint: 640,
                            settings: {
                                slidesToShow: 1
                            }
                        }
                    ]
                });
            }

        },
        error: function(err) {
            console.error(err);
        }
    });
}

function Get_ComplianceCalendarWeekGraph(time) {
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    $('.se-pre-con').show();
    $("#DailyDataDiv,#MonthDataDiv,#EmptyDataDiv").fadeOut();
    $("#WeeklyDataDiv").fadeIn();
    $("#complianceGraphWeek").html("");
    $.ajax({
        url: "../lib/l-ajax.php?function=AJAX_GetComplianceCalendarWeekGraph&tstamp=" + time,
        type: 'POST',
        data: 'searchType=' + searchType + '&searchValue=' + searchValue,
        dataType: 'json',
        success: function(data) {
            $('.se-pre-con').hide();
            if (data[0] == "") {
                $("#MonthDataDiv,#WeeklyDataDiv,#DailyDataDiv").fadeOut();
                $("#EmptyDataDiv").fadeIn();
                $("#complianceSearch").html("" + data[2]);
            } else {
                if (data[1] != "") {
                    $("#complianceListWeek").html(data[1]);
                }
                $("#complianceGraphWeek").html(data[0]);
                $("#complianceSearch").html("" + data[2]);
                $('.notification-slider-week').slick('unslick');
                $('.notification-slider-week').slick({
                    slidesToShow: 4,
                    slidesToScroll: 1,
                    speed: 500,
                    adaptiveHeight: true,
                    dots: false,
                    swipe: true,
                    arrows: true,
                    autoplay: false,
                    centerMode: false,
                    focusOnSelect: false,
                    fade: false,
                    infinite: false,
                    responsive: [
                        {
                            breakpoint: 1300,
                            settings: {
                                slidesToShow: 3
                            }
                        },
                        {
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: 2
                            }
                        },
                        {
                            breakpoint: 767,
                            settings: {
                                slidesToShow: 2
                            }
                        },
                        {
                            breakpoint: 640,
                            settings: {
                                slidesToShow: 1
                            }
                        }
                    ]
                });
            }

        },
        error: function(err) {
            console.error(err);
        }
    });
}

function Get_ComplianceCalendarDailyGraph(time) {
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    $('.se-pre-con').show();
    $("#WeeklyDataDiv,#MonthDataDiv,#EmptyDataDiv").fadeOut();
    $("#DailyDataDiv").fadeIn();
    $("#complianceGraphDaily").html("");
    $.ajax({
        url: "../lib/l-ajax.php?function=AJAX_GetComplianceCalendarDailyGraph&tstamp=" + time,
        type: 'POST',
        data: 'searchType=' + searchType + '&searchValue=' + searchValue,
        dataType: 'json',
        success: function(data) {
            $('.se-pre-con').hide();
            if (data[0] == "") {
                $("#MonthDataDiv,#WeeklyDataDiv,#DailyDataDiv").fadeOut();
                $("#EmptyDataDiv").fadeIn();
                $("#complianceSearch").html("" + data[2]);
            } else {
                if (data[1] != "") {
                    $("#complianceListDaily").html(data[1]);
                }
                $("#complianceGraphDaily").html(data[0]);
                $("#complianceSearch").html("" + data[2]);
                $('.notification-slider').slick('unslick');
                $('.notification-slider').slick({
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    speed: 500,
                    adaptiveHeight: true,
                    dots: false,
                    swipe: true,
                    arrows: true,
                    autoplay: false,
                    centerMode: false,
                    focusOnSelect: false,
                    fade: false,
                    infinite: false,
                    responsive: [
                        {
                            breakpoint: 1300,
                            settings: {
                                slidesToShow: 3
                            }
                        },
                        {
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: 2
                            }
                        },
                        {
                            breakpoint: 767,
                            settings: {
                                slidesToShow: 2
                            }
                        },
                        {
                            breakpoint: 640,
                            settings: {
                                slidesToShow: 1
                            }
                        }
                    ]
                });
            }

        },
        error: function(err) {
            console.error(err);
        }
    });
}
