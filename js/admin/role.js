var isSelectedEditable;

$(document).ready(function () {
  get_role_list();
  $('#back_home').hide();
});

function controlCollapse(ob) {
  var iconElement = ob.find('i.tim-icons');
  if (iconElement.hasClass('icon-simple-add')) {
    iconElement.removeClass('icon-simple-add').addClass('icon-simple-delete');
  } else {
    iconElement.removeClass('icon-simple-delete').addClass('icon-simple-add');
  }
}

function get_role_list() {
  $('#back_home').hide();
  $.ajax({
    url: '../lib/l-role.php',
    type: 'POST',
    dataType: 'json',
    data: { function: 'get_RoleList', csrfMagicToken: csrfMagicToken },
    success: function (gridData) {
      $('.loader').hide();
      $('#roleGrid').DataTable().destroy();
      roleTable = $('#roleGrid').DataTable({
        scrollY: jQuery('#roleGrid').data('height'),
        scrollCollapse: true,
        paging: true,
        searching: true,
        ordering: true,
        aaData: gridData,
        bAutoWidth: false,
        select: false,
        bInfo: false,
        responsive: true,
        lengthMenu: [
          [10, 25, 50, 100],
          [10, 25, 50, 100],
        ],
        language: {
          info: 'Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>',
          search: '_INPUT_',
          searchPlaceholder: 'Search records',
        },
        columnDefs: [{ type: 'date', targets: [1] }],
        dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function (settings, json) {
          $('#roleGrid tbody tr:eq(0)').addClass('selected');

          if (
            $('#roleGrid tbody tr:eq(0) p').attr('data-is-editable') != undefined &&
            $('#roleGrid tbody tr:eq(0) p').attr('data-is-editable') == 'true'
          ) {
            $('a#deleteRole').show();
          } else {
            $('a#deleteRole').hide();
          }

          var qid = $('#roleGrid tbody tr:eq(0) p')[0].id;
          $('#selected').val(qid);
        },
        drawCallback: function (settings) {},
      });
      $('.dataTables_filter input').addClass('form-control');
      $('.tableloader').hide();
    },
    error: function (msg) {},
  });
  $('#roleGrid').on('click', 'tr', function () {
    var rowID = roleTable.row(this).data();
    var selected = rowID[2];
    $('#selected').val(rowID[2]);
    roleTable.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
    $('a#deleteRole').hide();

    if ($(this).find('p').attr('data-is-editable') != undefined && $(this).find('p').attr('data-is-editable') == 'true') {
      $('a#deleteRole').show();
    }
  });
  $('#roleGrid').on('dblclick', 'tr', function () {
    $('#editOption').hide();
    rightContainerSlideOn('edit-role');
    rolesEditData();
    $('#toggleButton').hide();
  });
}

function addNewRole() {
  $('#back_home').show();
  $.ajax({
    url: '../lib/l-role.php?',
    dataType: 'json',
    type: 'POST',
    data: { function: 'ROLEgetRoleValueData', csrfMagicToken: csrfMagicToken },
    success: function (data) {
      var res = data.reduce(
        (x, y) => {
          if (y.level === 0) {
            y['child'] = {};
            x.lvl0[y.id] = y;
          } else if (y.level === 1) {
            x.lvl0[y.parent_id].child[y.id] = y;
            y['child'] = {};
            x.lvl1[y.id] = y;
          } else if (y.level === 2) {
            x.lvl1[y.parent_id].child[y.id] = y;
            y['child'] = {};
            x.lvl1[y.id] = y;
          } else if (y.level === 3) {
            x.lvl1[y.parent_id].child[y.id] = y;
          }
          return x;
        },
        { lvl0: {}, lvl1: {} },
      );

      let strnew = '';
      var hasChild, topCollapseIconVisibility;

      for (let i in res.lvl0) {
        hasChild = res.lvl0[i]['child'] != undefined && Object.keys(res.lvl0[i]['child']).length > 0 ? true : false;
        topCollapseIconVisibility = hasChild ? '' : ' style="visibility:hidden"';

        strnew +=
          '<div class="accordion"><div class="accordion-group"><div data-qa="' +
          res.lvl0[i].moduleName +
          '" class="area"><div class="parentDiv">' +
          '<a class="accordion-toggle" data-bs-toggle="collapse" onclick="controlCollapse($(this)); return true" href="#' +
          res.lvl0[i].moduleName +
          '">' +
          '<i class="tim-icons icon-simple-add" aria-hidden="true" ' +
          topCollapseIconVisibility +
          '></i><span class="spacePlus" id="parntName1">' +
          res.lvl0[i].moduleName +
          '</span></a>' +
          '</div><div style="display: inline-block;">' +
          '<select class="roleSelect btn-group bootstrap-select" name="' +
          res.lvl0[i].roleName +
          '" id="' +
          res.lvl0[i].roleName +
          '">' +
          '<option value="0">Disable</option>' +
          '<option value="2">Enable</option>' +
          '</select></div></div>';
        strnew +=
          '<div class="accordion-body collapse" id="' +
          res.lvl0[i].moduleName +
          '"><div class="accordion-inner">' +
          '<div class="accordion" id="equipamento1">';

        for (let j in res.lvl0[i]['child']) {
          strnew +=
            '<div class="accordion-group"><div class="accordion-heading equipamento">' +
            '<div class="parentChildDiv"><a class="accordion-toggle" data-bs-toggle="collapse" onclick="controlCollapse($(this)); return true" href="#view' +
            j +
            '">' +
            '<i class="tim-icons icon-simple-add" aria-hidden="true"></i><span class="spacePlus" id="view-1">' +
            res.lvl0[i]['child'][j].moduleName +
            '</span></a>' +
            '</div>' +
            '<div style="display: inline-block;">' +
            '<select class="roleSelect btn-group bootstrap-select" name="' +
            res.lvl0[i]['child'][j].roleName +
            '" id="' +
            res.lvl0[i]['child'][j].roleName +
            '">' +
            '<option value="0">Disable</option>' +
            '<option value="2">Enable</option>' +
            '</select></div></div></div>';
          strnew += '<div class="accordion-body collapse" id="view' + j + '"><div class="accordion-inner inner-child">';

          let child = res.lvl0[i]['child'][j];

          if (res.lvl1[child.id]) {
            let lvl2 = res.lvl1[child.id].child;
            for (let k in lvl2) {
              //strnew += '<div class="service-main-1" style="display: block;"><div class="childChildDiv">'

              strnew +=
                '<div class="accordion-group"><div class="accordion-heading equipamento">' +
                '<div class="parentChildDiv level3"><a class="accordion-toggle" data-bs-toggle="collapse" onclick="controlCollapse($(this)); return true" href="#view' +
                k +
                '">' +
                '<i class="tim-icons icon-simple-add" aria-hidden="true"></i><span class="spacePlus" id="view1-0">' +
                lvl2[k].moduleName +
                '</span></a></div>' +
                '<div style="display: inline-block;">' +
                '<select class="roleSelect btn-group bootstrap-select" name="' +
                lvl2[k].roleName +
                '" id="' +
                lvl2[k].roleName +
                '">' +
                '<option data-qa="' +
                lvl2[k].roleName +
                '" value="0">Disable</option>' +
                '<option data-qa="' +
                lvl2[k].roleName +
                '" value="2">Enable</option>' +
                '</select></div></div></div>';

              if (res.lvl1[child.id]) {
                strnew += '<div class="accordion-body collapse" id="view' + k + '"><div class="accordion-inner inner-child">';

                let child = res.lvl1[j]['child'][k];
                let lvl3 = res.lvl1[child.id].child;
                for (let r in lvl3) {
                  strnew +=
                    '<div class="service-main-1" style="display: block;"><div class="childChildDiv">' +
                    '<span class="spacePlus" id="view1-0">' +
                    lvl3[r].moduleName +
                    '</span></div>' +
                    '<div style="display: inline-block;">' +
                    '<select class="roleSelect btn-group bootstrap-select" name="' +
                    lvl3[r].roleName +
                    '" id="' +
                    lvl3[r].roleName +
                    '">' +
                    '<option data-qa="' +
                    lvl3[r].moduleName +
                    '" value="0">Disable</option>' +
                    '<option data-qa="' +
                    lvl3[r].moduleName +
                    '" value="2">Enable</option>' +
                    '</select></div></div>';
                }
                strnew += '</div></div>'; // fourth Level Closing
              }
            }
            strnew += '</div></div>'; // third Level Closing
          }
        }
        strnew += '</div></div></div>'; // Second Level Closing
        strnew += '</div></div>'; // Parent Level Closing
      }
      $('#htmlresp').html(strnew);

      $('#add-role select.roleSelect').selectpicker();
    },
  });
}

$('#roleName').on('click', function () {
  $('.msgDisplay').html('');
});

function rolesDataSubmit(formId) {
  if (formId === 'rolesdataform') {
    var roleName = $('#roleName').val();
    var regVal = /^[a-zA-Z0-9 ]+$/;
    if (roleName === '') {
      $.notify('Please enter the name of the role');
      return false;
    } else if (regVal.test(roleName) === false) {
      $.notify('Please enter alphanumeric values');
      return false;
    } else {
      var rolesData = $('#' + formId).serializeArray(),
        isGlobal = '1';
    }
  }
  else {
    const roleName = $('#editRoleName').val();
    const roleNameReplace = roleName.replace(/[^a-zA-Z0-9_-]+/im, '');
    const regVal = /^[a-zA-Z0-9 ]+$/;
    const checkRoleName = regVal.test(roleNameReplace);

    if (roleNameReplace === '') {
      $.notify('Please enter the name of the role');
      return false;
    } else if (checkRoleName === false) {
      $.notify('Please enter alphanumeric values');
      return false;
    } else {
      var rolesData = $('#' + formId).serializeArray(),
        isGlobal = '1';
    }
  }

  $.ajax({
    url: '../lib/l-role.php',
    type: 'POST',
    data: { function: 'ROLEroleValueStored', jsonRolesData: rolesData, formId: formId, global: isGlobal, csrfMagicToken: csrfMagicToken },
    success: function (data, status, xhr) {
      if ($.trim(data) == 'un-editable') {
        $.notify('This role can not be edited');
        return false;
      }

      if ($.trim(data) == 'no-global') {
        $.notify('The global option cannot be changed as there are active users assigned to the Role');
        return false;
      }

      if ($.trim(data) === 'success') {
        $.notify('A new Role has been successfully created');
        location.reload();
      } else if ($.trim(data) === 'update') {
        $.notify('Role has been updated successfully');
        location.reload();
      } else if ($.trim(data) === 'exist') {
        $.notify('Please use another name for the role. A role with the name you specified already exists.');
      } else if ($.trim(data) === '#33758') {
        $.notify('The user can not update the role assigned to themselves');
      } else {
        $.notify('Error occured. Please try again later');
      }
      console.log('success');
    },
    error: function (xhr, status, error) {
      console.log(error);
    },
  });
}

function rolesEditData() {
  var rightSlider = new RightSlider('#edit-role');
  rightSlider.showLoader();
  $('#editRoleName').val('');
  $('#roleId').val('');
  jsondata = '';
  var selected = $('#selected').val();

  $('#editRoleName').prop('readonly', true);
  $('#editOption').hide();
  disableFields();

  if (selected) {
    var id = selected;
    $.ajax({
      url: '../lib/l-role.php',
      dataType: 'json',
      type: 'POST',
      data: { function: 'ROLEgetRoleValueData', csrfMagicToken: csrfMagicToken },
      success: function (data) {
        if (data.error != undefined && data.error) {
          rightContainerSlideClose('edit-role');
          errorNotify(data.message);
          return false;
        }

        $.ajax({
          url: '../lib/l-role.php',
          type: 'POST',
          data: { function: 'fetch_EditRoleData', id: id, csrfMagicToken: csrfMagicToken },
          dataType: 'json',
          success: function (res) {
            rightSlider.hideLoader();
            //                        var res = JSON.parse(res);
            var jsondata = JSON.parse(res.jsondata);
            $('#editRoleName').val(res.name);
            $('#roleId').val(res.id);
            window.isSelectedEditable = res.editable != undefined && !isNaN(res.editable) && parseInt(res.editable) == 0 ? false : true;

            $.ajax({
              type: 'GET',
              dataType: 'text',
              url: '../lib/l-role.php?function=get_Userrole&id=' + selected + '&csrfMagicToken=' + csrfMagicToken,
              success: function (result) {
                result = $.trim(result);
                $('#editOption').show();
              },
              error: function (result) {
                console.log('error');
              },
            });
            if ($.trim(res.global) === 1 || $.trim(res.global) === '1') {
              $('input[name=editglobalRole]').prop('checked', true);
            } else {
              $('input[name=editglobalRole]').prop('checked', false);
            }
            var res = data.reduce(
              (x, y) => {
                if (y.level === 0) {
                  y['child'] = {};
                  x.lvl0[y.id] = y;
                } else if (y.level === 1) {
                  x.lvl0[y.parent_id].child[y.id] = y;
                  y['child'] = {};
                  x.lvl1[y.id] = y;
                } else if (y.level === 2) {
                  x.lvl1[y.parent_id].child[y.id] = y;
                  y['child'] = {};
                  x.lvl1[y.id] = y;
                } else if (y.level === 3) {
                  x.lvl1[y.parent_id].child[y.id] = y;
                }
                return x;
              },
              { lvl0: {}, lvl1: {} },
            );

            //let str = "";
            let strnew = '';
            var hasChild, topCollapseIconVisibility;

            for (let i in res.lvl0) {
              selval0 = '';
              selval1 = '';
              selval2 = '';
              if (jsondata[res.lvl0[i].roleName] == 0) {
                selval0 = 'selected';
              } else if (jsondata[res.lvl0[i].roleName] == 1) {
                selval1 = 'selected';
              } else if (jsondata[res.lvl0[i].roleName] == 2) {
                selval2 = 'selected';
              }

              hasChild = res.lvl0[i]['child'] != undefined && Object.keys(res.lvl0[i]['child']).length > 0 ? true : false;
              topCollapseIconVisibility = hasChild ? '' : ' style="visibility:hidden"';

              strnew +=
                '<div class="accordion"><div class="accordion-group"><div class="area"><div class="parentDiv">' +
                '<a class="accordion-toggle" onclick="controlCollapse($(this)); return true" data-bs-toggle="collapse" href="#' +
                res.lvl0[i].moduleName +
                '">' +
                '<i class="tim-icons icon-simple-add" aria-hidden="true" ' +
                topCollapseIconVisibility +
                '></i><span class="spacePlus" id="parntName1">' +
                res.lvl0[i].moduleName +
                '</span></div><div style="display: inline-block;"></a>' +
                '<select class="roleSelect btn-group bootstrap-select selectpicker" onchange="actionNew()" name="' +
                res.lvl0[i].roleName +
                '" id="' +
                res.lvl0[i].roleName +
                '">' +
                '<option value="0" ' +
                selval0 +
                '>Disable</option>' +
                '<option value="2" ' +
                selval2 +
                '>Enable</option>' +
                '</select></div></div>';

              strnew +=
                '<div class="accordion-body collapse" id="' +
                res.lvl0[i].moduleName +
                '"><div class="accordion-inner">' +
                '<div class="accordion" id="equipamento1">';

              for (let j in res.lvl0[i]['child']) {
                selval0 = '';
                selval1 = '';
                selval2 = '';
                if (jsondata[res.lvl0[i]['child'][j].roleName] == 0) {
                  selval0 = 'selected';
                } else if (jsondata[res.lvl0[i]['child'][j].roleName] == 1) {
                  selval1 = 'selected';
                } else if (jsondata[res.lvl0[i]['child'][j].roleName] == 2) {
                  selval2 = 'selected';
                }

                strnew +=
                  '<div class="accordion-group"><div class="accordion-heading equipamento">' +
                  '<div class="parentChildDiv"><a class="accordion-toggle" onclick="controlCollapse($(this)); return true" data-bs-toggle="collapse" href="#view' +
                  j +
                  '">' +
                  '<i class="tim-icons icon-simple-add" aria-hidden="true"></i><span class="spacePlus" id="view-1">' +
                  res.lvl0[i]['child'][j].moduleName +
                  '</span></a>' +
                  '</div>' +
                  '<div style="display: inline-block;">' +
                  '<select class="roleSelect btn-group bootstrap-select selectpicker" onchange="actionNew()" name="' +
                  res.lvl0[i]['child'][j].roleName +
                  '" id="' +
                  res.lvl0[i]['child'][j].roleName +
                  '">' +
                  '<option value="0" ' +
                  selval0 +
                  '>Disable</option>' +
                  '<option value="2" ' +
                  selval2 +
                  '>Enable</option>' +
                  '</select></div></div></div>';
                strnew += '<div class="accordion-body collapse" id="view' + j + '"><div class="accordion-inner inner-child">';

                let child = res.lvl0[i]['child'][j];

                if (res.lvl1[child.id]) {
                  let lvl2 = res.lvl1[child.id].child;
                  //str += "<ul>"
                  for (let k in lvl2) {
                    selval0 = '';
                    selval1 = '';
                    selval2 = '';
                    if (jsondata[lvl2[k].roleName] == 0) {
                      selval0 = 'selected';
                    } else if (jsondata[lvl2[k].roleName] == 1) {
                      selval1 = 'selected';
                    } else if (jsondata[lvl2[k].roleName] == 2) {
                      selval2 = 'selected';
                    }

                    //strnew += '<div class="service-main-1" style="display: block;"><div class="childChildDiv">'
                    strnew +=
                      '<div class="accordion-group"><div class="accordion-heading equipamento">' +
                      '<div class="parentChildDiv level3"><a class="accordion-toggle" data-bs-toggle="collapse" onclick="controlCollapse($(this)); return true" href="#view' +
                      k +
                      '">' +
                      '<i class="tim-icons icon-simple-add" aria-hidden="true"></i><span class="spacePlus" id="view1-0">' +
                      lvl2[k].moduleName +
                      '</span></a></div>' +
                      //+ '<span class="spacePlus" id="view1-0">' + lvl2[k].moduleName + '</span></div>'
                      '<div style="display: inline-block;">' +
                      '<select class="roleSelect btn-group bootstrap-select selectpicker" onchange="actionNew()" name="' +
                      lvl2[k].roleName +
                      '" id="' +
                      lvl2[k].roleName +
                      '">' +
                      '<option value="0" ' +
                      selval0 +
                      '>Disable</option>' +
                      '<option value="2" ' +
                      selval2 +
                      '>Enable</option>' +
                      '</select></div></div></div>';

                    if (res.lvl1[child.id]) {
                      strnew += '<div class="accordion-body collapse" id="view' + k + '"><div class="accordion-inner inner-child">';

                      let child = res.lvl1[j]['child'][k];
                      let lvl3 = res.lvl1[child.id].child;
                      for (let r in lvl3) {
                        selval0 = '';
                        selval1 = '';
                        selval2 = '';
                        if (jsondata[lvl3[r].roleName] == 0) {
                          selval0 = 'selected';
                        } else if (jsondata[lvl3[r].roleName] == 1) {
                          selval1 = 'selected';
                        } else if (jsondata[lvl3[r].roleName] == 2) {
                          selval2 = 'selected';
                        }

                        strnew +=
                          '<div class="service-main-1" style="display: block;"><div class="childChildDiv">' +
                          '<span class="spacePlus" id="view1-0">' +
                          lvl3[r].moduleName +
                          '</span></div>' +
                          '<div style="display: inline-block;">' +
                          '<select class="roleSelect btn-group bootstrap-select selectpicker" onchange="actionNew()" name="' +
                          lvl3[r].roleName +
                          '" id="' +
                          lvl3[r].roleName +
                          '">' +
                          '<option value="0" ' +
                          selval0 +
                          '>Disable</option>' +
                          '<option value="2" ' +
                          selval2 +
                          '>Enable</option>' +
                          '</select></div></div>';
                      }
                      strnew += '</div></div>'; // fourth Level Closing
                    }
                  }
                  strnew += '</div></div>'; // third Level Closing
                }
              }
              strnew += '</div></div></div>'; // Second Level Closing
              strnew += '</div></div>'; // Parent Level Closing
            }
            $('#htmlrespEdit').html(strnew);
            if (email === 'admin@nanoheal.com') {
              $('#global_EditText').html('Environment Global');
            } else {
              $('#global_EditText').html('Global');
            }

            $('#edit-role select.selectpicker').selectpicker();
            $('#edit-role select.selectpicker').prop('disabled', true).selectpicker('refresh');
          },
        });
        // console.log(strnew);
      },
    });
  } else {
    $('#warning').modal('show');
    //alert(123);
  }
}

function selectConfirm(data_target_id) {
  if (data_target_id === 'delete') {
    var selected = $('#selected').val();
    if (selected) {
      sweetAlert({
        title: 'Are you sure that you want to continue?',
        text: 'If you delete the role, you will not be able to undo the action.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#050d30',
        cancelButtonColor: '#fa0f4b',
        cancelButtonText: 'No, cancel it!',
        confirmButtonText: 'Yes, delete it!',
      })
        .then(function (result) {
          $.ajax({
            type: 'POST',
            dataType: 'text',
            data: { function: 'deleteRoleValue', id: selected, csrfMagicToken: csrfMagicToken },
            url: '../lib/l-role.php',
            success: function (result) {
              if ($.trim(result) === 'success') {
                $.notify('Role has bee removed successfully ');
                get_role_list();
                location.reload();
                $('.edit,.delete,.add').show();
              } else {
                $.notify($.trim(result));
              }
            },
            error: function (err) {
              console.log('errorssssssss');
            },
          });
        })
        .catch(function (reason) {
          $('.closebtn').trigger('click');
        });
      closePopUp();
    } else {
      $.notify('Please choose at least one record');
      closePopUp();
    }
  }
}

function actionNew() {
  $('.iconTick').removeClass('circleGrey');
}
