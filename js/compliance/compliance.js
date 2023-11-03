
var detailsDataTableGlobal;
var selectedComplianceName;
var detailsLastRequest;

$(document).ready(function () {
    $('.dropdown-item').on('click', function (e) {
        //         e.stopPropagation();
    });

    $('#showMenu').on('click', function () {
        $('#compliance_item_category_menu').show();
    });

    $('.compliance_item, .compliance_category').on('click', function () {
        getComplianceFilters();
    });

    $('#type_All').on('click', function () {
        $('#itemType').html('All');
        var isChecked = $(this).is(':checked');
        checkUncheckItems(isChecked, 'type_All');
        if (isChecked) {
            getComplianceFilters();
        }
    });

    $('#status_All').on('click', function () {
        $('#CategoryType').html('All');
        var isChecked = $(this).is(':checked');
        checkUncheckItems(isChecked, 'status_All');
        if (isChecked) {
            getComplianceFilters();
        }
    });

    $('#complianceDetailsList').on("click", "tbody tr td a.resetComplianceGroupHand", function (event) {

        if (window.selectedComplianceName == undefined) {
            errorNotify("Compliance name not found");
            return;
        }

        var trObj = $(this).parents('tr');
        var detailsTab = window.detailsDataTableGlobal;
        var row = detailsTab.row(trObj).data();

        if (row == undefined) {
            errorNotify("Cannot read row data");
            return;
        }

        inActivateAllDetailsGrid();
        trObj.addClass('active');
        resetComplianceItem(encodeURIComponent(window.selectedComplianceName), row);
    });

    $('#exportCompliance').on('click', function () {
        exportComplianceList();
    });

    $('#absoLoader,#complianceDetailsList,.equalHeight').show();
    getComplianceFilters();
});

function inActivateAllDetailsGrid() {
    $.each($('#complianceDetailsList tbody tr'), function () {
        $(this).removeClass('active');
    });
}

function resetComplianceItem(complianceName, row) {
    var rowDataObject = {
        'compliance_name': complianceName,
        'machine': row[0],
        'site': row[1],
        'item': row[2],
        'category': row[3],
        'datetime': row[4]
    };

    var postData = {
        "function": "reset_compliance_item",
        "data": rowDataObject,
        'csrfMagicToken': csrfMagicToken
    };

    $.ajax({
        type: "POST",
        url: "compliance.php",
        data: postData,
        success: function (xhr) {
            xhr = $.parseJSON(xhr);
            if (xhr.success != undefined) {
                if (xhr.success) {
                    successNotify(xhr.message);
                    getComplianceFilters();
                } else {
                    errorNotify(xhr.message);
                }
            }
        },
        error: function (msg) { }
    });

    return true;
}

function checkUncheckItems(check, type) {
    if (type == 'type_All') {
        var items = $('.compliance_item');
    } else {
        var items = $('.compliance_category');
    }

    $.each(items, function () {
        if (check) {
            $(this).prop('checked', true);
        } else {
            $(this).prop('checked', false);
        }
    });

    return true;
}

function getComplianceFilterData() {
    var compliance_item = $('.compliance_item');
    var compliance_category = $('.compliance_category');
    var compliance_item_array = [];
    var compliance_category_array = [];

    for (var i in compliance_item) {
        if (compliance_item.eq(i).is(':checked')) {
            compliance_item_array.push(compliance_item.eq(i).val());
        }
    }

    for (var i in compliance_category) {
        if (compliance_category.eq(i).is(':checked')) {
            compliance_category_array.push(compliance_category.eq(i).val());
        }
    }


    compliance_item = compliance_item_array.join(',');
    compliance_category = compliance_category_array.join(',');

    return { 'compliance_item': compliance_item, 'compliance_category': compliance_category };
}

var notifName;
function getComplianceDetails(name, element = '', nextPage = 1, notifSearch = '') {
    var filterData = getComplianceFilterData(), compliance_item = filterData.compliance_item, compliance_category = filterData.compliance_category, postData;

    $.each($('#filtersHtmlList li'), function () {
        $(this).removeClass('filter-list-active');
    });
    if (element != '')
        element.parent('li').addClass('filter-list-active');
    enableDisableToolbarInputs(false);
    if (name == '') {
        name = notifName;
    }

    var notifSearch = $('#notifSearch').val();
    if (typeof notifSearch === 'undefined') {
        notifSearch = '';
    }
    $("#complianceDetailsList").dataTable().fnDestroy();
    postData = {
        name: name,
        compliance_items: encodeURIComponent(compliance_item),
        compliance_categories: encodeURIComponent(compliance_category),
        function: 'get_compliance_details',
        'limitCount': $('#notifyDtl_length :selected').val(),
        'nextPage': nextPage,
        'notifSearch': notifSearch,
        csrfMagicToken: csrfMagicToken
    };
    console.log(postData)
    window.detailsLastRequest = postData;
    console.log("-----------------------in getComplianceDetails------------------------")
    var dT = $('#complianceDetailsList').DataTable({
        scrollY: 'calc(100vh - 240px)', 
        scrollCollapse: true,
        paging: false,
        bFilter: false,
        ajax: {
            url: "compliance.php",
            type: "POST",
            data: postData,
            "dataSrc": function (json) {
               
                notifName = name;
                $('#largeDataPagination').html(json.largeDataPaginationHtml);
                $(".dataTables_filter:first").replaceWith('<div id="notifyDtl_filter" class="dataTables_filter"><label><input type="text" class="form-control form-control-sm" placeholder="Search records" value="' + notifSearch + '" id="notifSearch" aria-controls="notifyDtl"></label></div>');
                return json.data;
            }
        },
        dom: '<"top"f>rt<"bottom"lp><"clear">',
        fnInitComplete: function (oSettings, json) {
            enableDisableToolbarInputs(true);
        }
    });

    window.detailsDataTableGlobal = dT;
    window.selectedComplianceName = name;

    return true;

}
$('body').on('click', '.page-link', function () {
    var nextPage = $(this).data('pgno');
    notifName = $(this).data('name');
    //   alert(nextPage + notifName)
    getComplianceDetails(notifName, '', nextPage);
})
$('body').on('change', '#notifyDtl_lengthSel', function () {
 
    getComplianceDetails(notifName);
});
 

function getComplianceFilters() {

    var data, filtersHtml = '', filterData = getComplianceFilterData(), compliance_item = filterData.compliance_item, compliance_category = filterData.compliance_category;
    var li, table = $('#complianceDetailsList').DataTable();
    if (compliance_item == 'Availability,Maintenance,Events,Security,Resource') {
        $('#itemType').html('All');
    } else {
        $('#itemType').html(compliance_item);
    }

    if (compliance_category == 'Ok,Warning,Alert') {
        $('#CategoryType').html('All');
    } else {
        $('#CategoryType').html(compliance_category);
    }

    $('#absoLoader').show();
    enableDisableToolbarInputs(false);
    table.clear().draw();

    var postData = {
        'function': 'get_compliance_filters',
        'compliance_items': encodeURIComponent(compliance_item),
        'compliance_categories': encodeURIComponent(compliance_category),
        'csrfMagicToken': csrfMagicToken
    };

    $.ajax({
        type: "POST",
        url: "compliance.php",
        data: postData,
        success: function (xhr) {
            enableDisableToolbarInputs(true);
            $('#absoLoader').hide();
            xhr = $.parseJSON(xhr);
            if (xhr.success != undefined) {
                if (xhr.success) {
                    data = xhr.data;
                    for (var i in data) {
                        li = (i == 0) ? '<li class="filter-list-active">' : '<li>';
                        filtersHtml += li + '<a href="javascript:void(0)"  title="' + data[i].name + '" onclick="getComplianceDetails(\'' + data[i].name + '\', $(this))">' + data[i].name + '</a></li>';
                    }

                    if (data[0].name != undefined) {
                        window.selectedComplianceName = data[0].name;
                        getComplianceDetails(encodeURIComponent(data[0].name), $('#filtersHtmlList li:first-child a'));
                    }

                    $('#filtersHtmlList').html(filtersHtml);
                } else {
                    $('#filtersHtmlList').html('No data available');
                    $('#complianceDetailsList tbody tr:first-child').addClass("selected").find('td').css('border-bottom', '1px solid rgba(1, 6, 21, 0.2)');
                    $('#complianceDetailsList_paginate,#complianceDetailsList_length').hide();
                    if (xhr.message != 'No data available') {
                        $.notify(xhr.message);
                    }
                }
            }
        },
        error: function (msg) {
            enableDisableToolbarInputs(true);
            $('#absoLoader').hide();
        }
    });

    return true;
}


function enableDisableToolbarInputs(enable) {
    var inputs = $('#right-toolbar input:checkbox');
    var cursor = enable ? 'pointer' : 'wait';

    $.each(inputs, function () {
        $(this).css('cursor', cursor);
        if (enable) {
            $(this).prop('disabled', false);
        } else {
            $(this).prop('disabled', true);
        }
    });

    return true;
}


function exportComplianceList() {
    var postData = window.detailsLastRequest, form = $('form[data-id=exportForm]');

    if (undefined == postData) {
        errorNotify("Nothing to export");
        return false;
    }

    form.find('input[name=name]').val(postData.name);
    form.find('input[name=compliance_categories]').val(postData.compliance_categories);
    form.find('input[name=compliance_items]').val(postData.compliance_items);
    form.submit();
}

function showFilterOptions() {
    rightContainerSlideOn('rsc-add-container34');
}

function saveComplianceFilters() {
    getComplianceFilters();
    rightContainerSlideClose('rsc-add-container34');
}
