/*** Initial implementation by JYO on 30-Aug-2016 ***/

jQuery(document).ready(function($) {
    helperGuide();
});

// This function is to provide URL to getData function
function helperGuide() {
        var lng_faq = $("#temp_lang").val();
        var url = "../home/FAQ_Model.php?function=getAllData&lng="+lng_faq;
        getData(url);
}

// This function gets data from URL provided and appends its HTML content to the provided id 

function getData(url) {
    $.ajax({
        url: url,
        dataType: 'json',
        success: function(data) {
            var divEle = "";
            if (data.rows.length < 1) {
                divEle = "No Records Found ..!";
            } else {
                for (var i = 0; i < data.rows.length; i++) {
                    var obj = data.rows[i];
                    var divStr = '';
                    divStr = '<li class="headerDiv panel">';
                    divStr += '<a href="#panel' + obj.id + '" data-toggle="collapse" data-parent="#accordion-help" class="head_title collapsed" id="icon' + obj.id + '"> ' + obj.name + ' </a>';
                    divStr += '<div class="collapse" id="panel' + obj.id + '">';
                    divStr += '<div id="panel' + obj.id + '" class="how-we-help-desc">';
                    divStr += '<p>' + obj.descr + '</p>';

                    /* Later implementation here */

//                    if (obj.guideType === 'link') {
//                        divStr += '<div class="start_btn"><button type="button" class="btn btn-primary" onclick="startTour(\'' + obj.path + '\',\'' + obj.id + '\')">Start</button></div>';
//                    } else if (obj.guideType === 'image') {
//                        divStr += '<div class="start_btn"><button type="button" class="btn btn-primary" onclick="startImgTour(\'' + obj.fileNames + '\',\'image\',\'\')">Start</button></div>';
//                    } else if (obj.guideType === 'video') {
//                        divStr += '<div class="start_btn"><button type="button" class="btn btn-primary" onclick="startImgTour(\'' + obj.fileNames + '\',\'video\',\'\')">Start</button></div>';
//                    } else if (obj.guideType === 'videoLink') {
//                        divStr += '<div class="start_btn"><button type="button" class="btn btn-primary" onclick="startImgTour(\'' + obj.path + '\',\'videoLink\',\'' + obj.name + '\')">Start</button></div>';
//                    }

                    /* Later implementation here */

                    divStr += '</div>';
                    divStr += '</div>';
                    divStr += '</li>';
                    divEle += divStr;
                }
            }
            $("#accordion-help").html(divEle);
        }});
}


// Search throught FAQ and will return result based on keywords provided

$("#searchField").keyup(function() {
    var data = $("#searchField").val();
    if (data.length == 0 || data == '') {
        var url = "../home/FAQ_Model.php?function=getAllData";
        getData(url);
    }
    if (data.length > 0) {
        var url = "../home/FAQ_Model.php?function=getSearchData&data=" + data;
        getData(url);
    }
});


function faqfileDownload() {
    var lng_faq = $("#temp_lang").val();
     window.location.href = '../home/FAQ_Model.php?function=FAQ_FileDownload&lng='+lng_faq;
    
}
/* FAQ Export function */
function faqdownloadExport() {
    var lng_faq = $("#temp_lang").val();
    window.location.href = '../home/FAQ_Model.php?function=getallfaqDataExport&lng='+lng_faq;
}