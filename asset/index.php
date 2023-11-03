<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'eventInfo';
$_SESSION['currentwindow'] = 'eventInfo';

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'asset_html.php';
require_once '../layout/rightmenu.php';
require_once '../layout/footer.php';
?>
<script type="text/javascript">
    $('#pageName').html('Ad-hoc Queries');
</script>
<script type="text/javascript" src="../js/rightmenu/rightMenu.js"></script>
<style>
    .ip-r-g label {
        position: relative;
        top: -2px;
        left: 10px;
    }

    .select.bs-select-hidden,
    select.selectpicker {
        display: block !important;
        width: 173px;
        border-radius: 5px;
        height: 27px
    }

    .r-ic-plain:first-child {
        margin-top: 26px;
    }

    .icon-simple-remove.r-ic-plain {
        margin-top: 6px !important;
    }

    #add-aq-filter-container label {
        width: 100%
    }
</style>
<script>
    var filterList, isCreateProgress = false;

    $(document).ready(function() {

        fetchAdhocQList();
        getFilterList();

        $('#asset_query').change(function() {
            $('#event_query').prop("checked", false);
            $('#showasset').show();
            $('#showevent').hide();
        });

        $('#event_query').change(function() {
            $('#asset_query').prop("checked", false);
            $('#showevent').show();
            $('#showasset').hide();
            $.ajax({
                url: "asset.php",
                type: "POST",
                data: {
                    'function': 'geteventDartList'
                },
                success: function(data) {
                    $('#Dartnumbers_event').html('');
                    $('#Dartnumbers_event').html(data);
                    $('.selectpicker').selectpicker('refresh');

                },
                error: function(error) {
                    console.log("error");
                }
            });
        });


        $('#export-asset').on('click', function() {
            var types = $('[name=types]'),
                queryType = $('input[name=query-type]:checked'),
                filterName = $('input[name=filter-name]');

            if (types.val() == undefined || types.val() == '') {
                errorNotify("Please select a type");
                return false;
            }

            var filterQ = '';

            if (queryType.val() == 'filter') {
                if (filterName.val() == '') {
                    errorNotify("Please mention the filter to query");
                    filterName.focus();
                    return false;
                }

                filterQ = '&filter=' + filterName.val()
            }

            document.location.href = "../asset/asset.php?function=export-asset&type=" + types.val() + filterQ;
        });

        $('input[name=query-type]').on('click', function() {
            var typeValue = $(this).val(),
                wrap = $('#filter-wrap');

            if (typeValue == 'filter') {
                wrap.show();
            } else {
                wrap.hide();
            }
        });

        $('#add-more-filter').on('click', function() {
            var maxGrids = 10;
            var grid = $(this).parents('.filter-rows'),
                clone = grid.clone();
            var grids = function() {
                    return $('#add-aq-filter-container .filter-rows')
                },
                grLength = grids().length;

            clone.find('.icon-simple-add').remove();
            clone.find('label').remove();
            clone.find('.icon-simple-remove').show();
            var htm = '<div class="row filter-rows">' + clone.html() + '</div>';

            if (grLength >= maxGrids) {
                errorNotify('You can add maximum of ' + maxGrids + ' grids');
                return false;
            }

            grids().eq(grLength - 1).after(htm);
            grid = grids().eq((grids().length - 1));
            refreshCsSelectpicker("[name='filter-name[]']", grids);
            refreshCsSelectpicker("[name='filter-operator[]']", grids);

            return true;
        });

        $('#add-more-filter2').on('click', function() {
            var maxGrids = 10;
            var grid = $(this).parents('.filter-rows2'),
                clone = grid.clone();
            var grids = function() {
                    return $('#add-aq-filter-container .filter-rows2')
                },
                grLength = grids().length;

            clone.find('.icon-simple-add').remove();
            clone.find('label').remove();
            clone.find('.icon-simple-remove').show();
            var htm = '<div class="row filter-rows2">' + clone.html() + '</div>';

            if (grLength >= maxGrids) {
                errorNotify('You can add maximum of ' + maxGrids + ' grids');
                return false;
            }

            grids().eq(grLength - 1).after(htm);
            grid = grids().eq((grids().length - 1));
            refreshCsSelectpicker("[name='filter-name2[]']", grids);
            refreshCsSelectpicker("[name='filter-operator2[]']", grids);

            return true;
        });

        $('#add-more-filteredit').on('click', function() {
            var maxGrids = 10;
            var grid = $(this).parents('.edit-filter-rows'),
                clone = grid.clone();
            var grids = function() {
                    return $('#edit-aq-filter-container .edit-filter-rows')
                },
                grLength = grids().length;

            clone.find('.icon-simple-add').remove();
            clone.find('label').remove();
            clone.find('.icon-simple-remove').show();
            var htm = '<div class="row edit-filter-rows">' + clone.html() + '</div>';

            if (grLength >= maxGrids) {
                errorNotify('You can add maximum of ' + maxGrids + ' grids');
                return false;
            }

            grids().eq(grLength - 1).after(htm);
            grid = grids().eq((grids().length - 1));
            refreshCsSelectpicker("[name='filter-nameedit[]']", grids);
            refreshCsSelectpicker("[name='filter-operatoredit[]']", grids);

            return true;
        });
    });

    $('#Dartnumbers_event').on('change', function() {
        var selectedDart = $('#Dartnumbers_event').val();
        $.ajax({
            url: "asset.php",
            type: "GET",
            data: {
                'function': 'checkEventTitle',
                'selectedDart': selectedDart
            },
            success: function(data) {
                data = $.trim(data);
                if (data != 'NA') {
                    $('#Events_filterlist').show();
                    getEventFilterList(selectedDart);
                } else {
                    $('#Events_filterlist').hide();
                }
            },
            error: function(error) {
                console.log("error");
            }
        });

    });

    function getEventFilterList(selectedDart) {
        $.ajax({
            url: "asset.php",
            type: "GET",
            data: {
                function: 'getEventFilterList',
                selectedDart: selectedDart
            },
            success: function(data) {
                window.filterList = data;
                $('#source-field2').html('');
                $('#source-field2').html(data);
                $("[name='filter-name2[]']").html(data);
                $('.selectpicker').selectpicker('refresh');
            },
            error: function(error) {
                console.log("error");
            }
        });
    }

    function refreshCsSelectpicker(fieldName, grids) {
        grid = grids().eq((grids().length - 1));
        var field = grid.find(fieldName);
        field.parents('.bootstrap-select').find('button, div.dropdown-menu').remove();
        var si = field.parents('.bootstrap-select').html();
        var g = field.parents('.col-sm-4');
        field.parents('.bootstrap-select').remove();
        g.append(si);
        var grid = grids().eq((grids().length - 1));
        grid.find(fieldName).selectpicker();

        return true;
    }

    function removeFilterGrid(t) {
        t.parents('.filter-rows').remove();
    }

    function removeFilterGrid2(t) {
        t.parents('.filter-rows2').remove();
    }

    function removeFilterGridedit(t) {
        t.parents('.edit-filter-rows').remove();
    }

    function getFilterList() {
        $.ajax({
            url: "../admin/groupfunctions.php",
            type: "GET",
            data: {
                function: 'getFilterList'
            },
            dataType: "json",
            success: function(data) {
                window.filterList = data.asset;
                $("[name='filter-name[]']").html(data.asset);
                $("[name=source-field]").html(data.asset);
                $('.selectpicker').selectpicker('refresh');
            }
        });
    }

    function createFilter(form, event) {
        if (event.preventDefault) {
            event.preventDefault();
        } else {
            event.returnValue = false;
        }
        if (window.isCreateProgress) {
            errorNotify("A Filter create request is alredy in progress, please wait while we process your request.");
            return false;
        }
        window.isCreateProgress = true;

        if ($('#asset_query').is(':checked')) {
            var sourceFields = $('[name=source-field]').val();
            var formData = form.serialize();
            formData += "&function=create-filter";
            if (sourceFields != undefined && sourceFields != '')
                formData += '&source-field=' + sourceFields.join(',');
            AssetAjaxCall(form, formData);
        } else {

            var selectedDart = $('#Dartnumbers_event').val();
            $.ajax({
                url: "asset.php",
                type: "GET",
                data: {
                    function: 'checkEventTitle',
                    selectedDart: selectedDart
                },
                success: function(data) {
                    data = $.trim(data);
                    if (data != 'NA') {
                        var sourceFields = $('[name=source-field]').val();
                        if (sourceFields != undefined && sourceFields != '')
                            formData += '&source-field=' + sourceFields.join(',');
                    } else {
                        var sourceFields = '-';
                    }
                },
                error: function(error) {
                    console.log("error");
                }
            });
            var formData = form.serialize();
            formData += "&function=createeventfilter";
            EventAjaxCall(formData);
        }
        return false;
    }

    function EventAjaxCall(formData) {
        $.ajax({
            url: "asset.php",
            data: formData,
            type: "POST",
            dataType: "JSON",
            success: function(data) {
                window.isCreateProgress = false;
                if (data.success != undefined) {
                    if (!data.success) {
                        if (data.validator != undefined && data.validator) {
                            for (var i in data.message) {
                                errorNotify(data.message[i][0]);
                                break;
                            }
                        } else {
                            errorNotify(data.message);
                        }
                    } else {
                        successNotify(data.message);
                        fetchAdhocQList();
                        rightContainerSlideClose('add-aq-filter-container');
                        //location.reload();
                    }
                }
            },
            error: function(data) {
                window.isCreateProgress = false;
                errorNotify("Something went wrong, please retry again later");
            }
        });
    }

    function AssetAjaxCall(form, formData) {
        $.ajax({
            url: "asset.php",
            data: formData,
            type: 'POST',
            dataType: "JSON",
            success: function(data) {
                window.isCreateProgress = false;
                if (data.success != undefined) {
                    if (!data.success) {
                        if (data.validator != undefined && data.validator) {
                            for (var i in data.message) {
                                errorNotify(data.message[i][0]);
                                break;
                            }
                        } else {
                            errorNotify(data.message);
                        }
                    } else {
                        successNotify(data.message);
                        fetchAdhocQList();
                        rightContainerSlideClose('add-aq-filter-container');
                        form.find("[name=fname], [name='filter-value[]']").val('');
                        form.find('.icon-simple-remove:visible').click();
                    }
                }
            },
            error: function() {
                window.isCreateProgress = false;
                errorNotify("Something went wrong, please retry again later");
            }
        });
    }

    function AssetUpdateAjaxCall(form, formData) {
        $.ajax({
            url: 'asset.php',
            data: formData,
            type: 'POST',
            dataType: "JSON",
            success: function(data) {
                window.isCreateProgress = false;
                if (data.success != undefined) {
                    if (!data.success) {
                        if (data.validator != undefined && data.validator) {
                            for (var i in data.message) {
                                errorNotify(data.message[i][0]);
                                break;
                            }
                        } else {
                            errorNotify(data.message);
                        }
                    } else {
                        successNotify(data.message);
                        fetchAdhocQList();
                        rightContainerSlideClose('edit-aq-filter-container');
                        form.find("[name=fnameedit], [name='filter-valueedit[]']").val('');
                        form.find('.icon-simple-remove:visible').click();
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                }
            },
            error: function() {
                window.isCreateProgress = false;
                errorNotify("Something went wrong, please retry again later");
            }
        });
    }

    function EventUpdateAjaxCall(formData) {
        $.ajax({
            url: "asset.php",
            data: formData,
            type: "POST",
            dataType: "JSON",
            success: function(data) {
                window.isCreateProgress = false;
                if (data.success != undefined) {
                    if (!data.success) {
                        if (data.validator != undefined && data.validator) {
                            for (var i in data.message) {
                                errorNotify(data.message[i][0]);
                                break;
                            }
                        } else {
                            errorNotify(data.message);
                        }
                    } else {
                        successNotify(data.message);
                        fetchAdhocQList();
                        rightContainerSlideClose('edit-aq-filter-container');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                }
            },
            error: function(data) {
                window.isCreateProgress = false;
                errorNotify("Something went wrong, please retry again later");
            }
        });
    }

    function fetchAdhocQList() {

        $("#adhoc-q-list").dataTable().fnDestroy();

        var repoTable = $('#adhoc-q-list').DataTable({
            scrollY: 'calc(100vh - 240px)',
            "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
            ajax: {
                url: "asset.php",
                type: "GET",
                data: {
                    function: 'datatable-list'
                }
            },
            columns: [{
                    "data": "id"
                },
                {
                    "data": "name"
                },
                {
                    "data": "source"
                },
                {
                    "data": "created"
                },
                {
                    "data": "type"
                },
                {
                    "data": "export"
                }
            ],
            order: [
                [0, "desc"]
            ],
            "aoColumnDefs": [{
                "bVisible": false,
                "aTargets": [0]
            }],
            ordering: true,
            select: false,
            bInfo: false,
            responsive: true,
            columnDefs: [{
                "type": "date",
                "targets": [3]
            }],
            fnInitComplete: function(oSettings, json) {},
            language: {
                "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                search: "",
                searchPlaceholder: "Search Records"
            }
        });

        $('#adhoc-q-list tbody').on('click', 'tr', function() {
            repoTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var rowID = repoTable.row(this).data();
            if (rowID != 'undefined' && rowID !== undefined) {
                $('#selectedValue').val(rowID.id);
                $('#idselected').val(rowID.id);
                $('#typeselected').val(rowID.type);
            }
        });

        $('.dataTables_filter input').addClass('form-control');
    }

    function editfilter() {
        $("#edit_asset_query").removeClass('disabled');
        $("#edit_event_query").removeClass("not-allowed");
        var selected = $('#idselected').val();
        var type = $('#typeselected').val();
        if (selected == '') {
            $.notify("Please select a record to Edit");
            closePopUp();
        } else {
            rightContainerSlideOn('edit-aq-filter-container');
            if (type == 'Event') {
                $('#edit_event_query').attr("checked", true);
                $('#edit_asset_query').attr("checked", false);
                $('#edit_asset_query').attr("disabled", true);
                $("#edit_asset_query").addClass("not-allowed");
                $('#showeassetedit').hide();
                $('#showeventedit').show();
            } else {
                $('#edit_asset_query').attr("checked", true);
                $('#edit_event_query').attr("checked", false);
                $('#edit_event_query').prop("disabled", true);
                $("#edit_event_query").addClass("not-allowed");
                $('#showeventedit').hide();
                $('#showeassetedit').show();
            }
            fetchFilterDetails(selected, type);
        }
    }

    function fetchFilterDetails(id, type) {
        if (type == 'Asset') {
            $('[name=fnameedit]').val('');
            $('[name=source-fieldedit]').html('');
            $('#editFilterData').html('');
            $.ajax({
                url: "asset.php",
                type: "GET",
                data: {
                    'function': 'fetchFilterDetails',
                    'id': id,
                    'type': type
                },
                dataType: "JSON",
                success: function(data) {
                    $('[name=fnameedit]').val(data.name);
                    $('[name=source-fieldedit]').html(data.asset_options);
                    $('.selectpicker').selectpicker('refresh');
                    $('#editFilterData').html(data.filter_options);
                    $('.selectpicker').selectpicker('refresh');
                },
                error: function(error) {
                    console.log("error");
                }
            });
        } else {
            $.ajax({
                url: "asset.php",
                type: "GET",
                data: {
                    'function': 'fetcheventFilterDetails',
                    'id': id,
                    'type': type
                },
                dataType: "JSON",
                success: function(data) {
                    //                console.log(data.dartno);
                    var selectedDart = data.dartno;
                    $.ajax({
                        url: "asset.php",
                        type: "GET",
                        data: {
                            'function': 'checkEventTitle',
                            'selectedDart': selectedDart
                        },
                        success: function(msg) {
                            msg = $.trim(msg);
                            if (msg != 'NA') {
                                $('#Events_filterlistedit').show();
                                $('[name=fname2edit]').val(data.name);
                                $('[name=source-field2edit]').html(data.asset_options);
                                $('.selectpicker').selectpicker('refresh');
                                $('#Dartnumbers_eventedit').html(data.dart_list);
                                $('.selectpicker').selectpicker('refresh');
                                $('#editFilterData2').html(data.filter_options);
                                $('.selectpicker').selectpicker('refresh');
                            } else {
                                $('#Events_filterlistedit').hide();
                                $('[name=fname2edit]').val(data.name);
                                $('[name=source-field2edit]').html(data.asset_options);
                                $('.selectpicker').selectpicker('refresh');
                                $('#Dartnumbers_eventedit').html(data.dart_list);
                                $('.selectpicker').selectpicker('refresh');
                                $('#editFilterData2').html(data.filter_options);
                                $('.selectpicker').selectpicker('refresh');
                            }
                        },
                        error: function(error) {
                            console.log("error");
                        }
                    });


                },
                error: function(error) {
                    console.log("error");
                }
            });
        }

    }


    function deletefilter() {
        //closePopUp();
        var selected = $('#idselected').val();
        if (selected == '') {
            $.notify("Please select a record to delete");
            return false;
        } else {
            sweetAlert({
                title: 'Are you sure to delete this filter?',
                text: "You will not be able to recover this filter!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#050d30',
                cancelButtonColor: '#fa0f4b',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                $.ajax({
                    url: "asset.php?function=delete-filter&id=" + selected;
                    type: 'DELETE',
                    success: function(data) {
                        if ($.trim(data) == 'success') {
                            $.notify("Filter Deleted Successfully");
                            fetchAdhocQList();
                        } else {
                            var res = JSON.parse(data);
                            $.notify($.trim(res['message']));
                        }
                    },
                    error: function(error) {
                        console.log("error");
                    }
                });
            });
        }
    }

    function updateFilter(form, event) {
        if (event.preventDefault) {
            event.preventDefault();
        } else {
            event.returnValue = false;
        }
        if (window.isCreateProgress) {
            errorNotify("A Filter create request is alredy in progress, please wait while we process your request.");
            return false;
        }
        window.isCreateProgress = true;

        if ($('#edit_asset_query').is(':checked')) {
            var sourceFields = $('[name=source-fieldedit]').val();
            var formData = form.serialize();
            formData += "&function=update-filter";
            if (sourceFields != undefined && sourceFields != '')
                formData += '&source-field=' + sourceFields.join(',');
            AssetUpdateAjaxCall(form, formData);
        } else {
            var selectedDart = $('#Dartnumbers_eventedit').val();
            $.ajax({
                url: "asset.php",
                type: "GET",
                data: {
                    'function': 'checkEventTitle',
                    'selectedDart': selectedDart
                },
                success: function(data) {
                    data = $.trim(data);
                    if (data != 'NA') {
                        var sourceFields = $('[name=source-field2edit]').val();
                        if (sourceFields != undefined && sourceFields != '')
                            formData += '&source-field2edit=' + sourceFields.join(',');
                    } else {
                        var sourceFields = '-';
                    }
                },
                error: function(error) {
                    console.log("error");
                }
            });
            var formData = form.serialize();
            formData += "&function=updateeventfilter";
            EventUpdateAjaxCall(formData);
        }

        return false;

    }

    function export_Event(fid) {
        rightContainerSlideOn('event-export-range');
        $('#hiddenfid').val(fid);
    }

    function export_Asset(fid) {
        //    rightContainerSlideOn('event-export-range');
        $('#hiddenfid').val(fid);

        addInfoPortalReport('asset', fid);
        //exportAssetDetails();
    }

    function exportEventDetails() {

        var from = $('#datefrom').val();
        var to = $('#dateto').val();
        var fid = $('#hiddenfid').val();

        addInfoPortalReport('event', fid, from, to);
        //window.location.href = "../asset/asset.php?function=export-event&from=" + from + "&to=" + to + "&fid=" + fid;
        //$.notify('Event Details Exported Successfully');
        //closePopUp();
        //setTimeout(function () {
        //    location.reload();
        //}, 3200);
    }

    function exportAssetDetails() {
        var fid = $('#hiddenfid').val();
        window.location.href = "../asset/asset.php?function=export-asset&fid=" + fid;
        closePopUp();
    }

    function loadInformationPortalData() {

        $("#adhoc-info-portal-dt").dataTable().fnDestroy();
        var repoTable = $('#adhoc-info-portal-dt').DataTable({
            scrollY: 'calc(100vh - 240px)',
            "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
            ajax: {
                url: "asset.php",
                type: "GET",
                data: {
                    function: 'adhocInfoPortal'
                }
            },
            columns: [{
                    "data": "qid"
                },
                {
                    "data": "qname"
                },
                {
                    "data": "scope"
                },
                {
                    "data": "status"
                },
                {
                    "data": "time"
                },
                {
                    "data": "downloadfile"
                }
            ],
            order: [
                [0, "desc"]
            ],
            "aoColumnDefs": [{
                "bVisible": false,
                "aTargets": [0]
            }],
            ordering: true,
            select: false,
            bInfo: false,
            responsive: true,
            fnInitComplete: function(oSettings, json) {},
            language: {
                "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                search: "",
                searchPlaceholder: "Search Records"
            }
        });

        $('#adhoc-info-portal-dt tbody').on('click', 'tr', function() {
            repoTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var rowID = repoTable.row(this).data();
            if (rowID != 'undefined' && rowID !== undefined) {
                //$('#selectedValue').val(rowID.id);
                //$('#idselected').val(rowID.id);
                //$('#typeselected').val(rowID.type);
            }
        });

        $('.dataTables_filter input').addClass('form-control');
    }

    function adhocInfoPortal() {
        $('#adhoc-main').hide();
        $('#adhoc-info-portal').show();

        loadInformationPortalData();
    }

    function adhocMain() {
        $('#adhoc-main').show();
        $('#adhoc-info-portal').hide();
    }

    function addInfoPortalReport(type, fid, from = '', to = '') {

        var datastring = '';
        if (type === 'asset') {
            datastring = {
                function: 'addInfoPortal',
                'type': type,
                'qid': fid
            };
        } else {
            datastring = {
                function: 'addInfoPortal',
                'type': type,
                'qid': fid,
                'from': from,
                'to': to
            };
        }

        $.ajax({
            url: 'asset.php',
            type: 'POST',
            data: datastring,
            success: function(data) {
                var res = $.trim(data)
                if (type === 'event') {
                    $('.closebtn').click();
                }
                if (res === 'success') {
                    $.notify('Report will published to the information portal shortly!');
                } else {
                    $.notify('Error adding report to information portal!');
                }
            },
            error: function(err) {
                $.notify('Some error occured ' + err);
            }
        });
    }
</script>