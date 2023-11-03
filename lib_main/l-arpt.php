<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();




function asset_null()
{
    return 'Nothing';
}




function asset_order_always($db)
{
    $tmp = array();
    $sql = "select name from DataName where include = 1";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_assoc($res)) {
                $tmp[] = $row['name'];
            }
        }
        mysqli_free_result($res);
    }
    return $tmp;
}




function find_single($sql, $db)
{
    $row = array();
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
            mysqli_free_result($res);
        }
    }
    return $row;
}




function asset_display_fields($id, $db)
{
    $list = array();
    if ($id > 0) {
        $sql = "select * from AssetSearches where id = $id";
        $row = find_single($sql, $db);
        if ($row) {
            $df  = $row['displayfields'];
            $tmp = explode(':', $df);
            foreach ($tmp as $k => $d) {
                if ($d) {
                    $list[] = $d;
                }
            }
        }
    }
    return $list;
}

function asset_order_options($id, $db)
{
    $option = array();
    $names  = array();
    $always = asset_order_always($db);
    $fields = asset_display_fields($id, $db);

    reset($always);
    foreach ($always as $xxx => $name) {
        $names[$name] = true;
    }
    reset($fields);
    foreach ($fields as $xxx => $name) {
        $names[$name] = true;
    }
    reset($names);
    foreach ($names as $name => $xxx) {
        $option[] = $name;
    }
    return $option;
}



function write_JS_order_options($searchids, $order1, $order2, $order3, $order4, $db)
{
    $super = "\n";
    $i = 1;
    reset($searchids);
    foreach ($searchids as $index => $id) {
        if ($i > 1) {
            $super .= ",\n";
        }
        $super .= "      new Array(\n";

        $j = 1;
        $names = asset_order_options($id, $db);
        reset($names);
        foreach ($names as $xxx => $name) {
            if ($j > 1) {
                $super .= ",\n";
            }
            $super .= "        new Array('$name','$name')";
            $j++;
        }
        $super .= "\n      )";
        $i++;
    }
    $nothing = asset_null();
    echo <<< HERE

<script language="JavaScript">

 function get_displayfields_for_search(myForm,selectedIndex)
 {
    super_displayfields = new Array(
        $super
    );

    var order1 = '$order1';
    var order2 = '$order2';
    var order3 = '$order3';
    var order4 = '$order4';
    var order1_index = 0;
    var order2_index = 0;
    var order3_index = 0;
    var order4_index = 0;

    for (i = super_displayfields[selectedIndex].length-1; i >= 0; i--)
    {
        if (super_displayfields[selectedIndex][i][1] != null)
        {
            if (super_displayfields[selectedIndex][i][1] == order1)
            {
                order1_index = i+1;
            }
            if (super_displayfields[selectedIndex][i][1] == order2)
            {
                order2_index = i+1;
            }
            if (super_displayfields[selectedIndex][i][1] == order3)
            {
                order3_index = i+1;
            }
            if (super_displayfields[selectedIndex][i][1] == order4)
            {
                 order4_index = i+1;
            }
        }
    }

    fillSelectFromArray( myForm.order1, super_displayfields[selectedIndex], order1_index );
    fillSelectFromArray( myForm.order2, super_displayfields[selectedIndex], order2_index );
    fillSelectFromArray( myForm.order3, super_displayfields[selectedIndex], order3_index );
    fillSelectFromArray( myForm.order4, super_displayfields[selectedIndex], order4_index );
  }

  // Original:  Jerome Caron (jerome.caron@globetrotter.net)
  // This script and many more are available free online at
  // The JavaScript Source!! http://javascript.internet.com
  function fillSelectFromArray(selectCtrl, itemArray, indexToSelect)
  {
    var i, j;
    var prompt;
    // empty existing items
    for (i = selectCtrl.options.length; i >= 0; i--)
    {
      selectCtrl.options[i] = null;
    }

    // add an entry for the first option
    selectCtrl.options[0] = new Option("$nothing");
    selectCtrl.options[0].value = "";

    j = 1;
    if (itemArray != null)
    {
      // add new items
      for (i = 0; i < itemArray.length; i++)
      {
        selectCtrl.options[j] = new Option(itemArray[i][0]);
        if (itemArray[i][1] != null)
        {
            selectCtrl.options[j].value = itemArray[i][1];
        }
        j++;
      }
      // select first item (prompt) for sub list
      if (selectCtrl.options[indexToSelect] != null)
      {
        selectCtrl.options[indexToSelect].selected = true;
      }
    }
  }


  // run when page loads if selection exists, eg. after backing into page (post-error)
  if (window.document.myform.searchid.selectedIndex != -1)
  {
    get_displayfields_for_search(myform,window.document.myform.searchid.selectedIndex);
  }

</script>

HERE;
}


function search_list($authuser, $db)
{
    $list = array();
    $qu   = safe_addslashes($authuser);
    $sql  = "select * from AssetSearches\n";
    $sql .= " where global = 1 or\n";
    $sql .= " username = '$qu'\n";
    $sql .= " order by name, global";
    $rows = find_many($sql, $db);
    if ($rows) {
        $prev = '';
        reset($rows);
        foreach ($rows as $key => $row) {
            $id   = $row['id'];
            $name = $row['name'];
            if ($name != $prev) {
                $list[$id] = $name;
            }
            $prev = $name;
        }
    }
    return $list;
}


function searchid_list($searches)
{
    $list = array();
    if ($searches) {
        reset($searches);
        foreach ($searches as $id => $name) {
            $list[] = $id;
        }
    }
    return $list;
}



function ARPT_WriteSingleSearchArray($row, $context, $db)
{
    $id = $row['id'];
    $thissrchuniq = $row['asrchuniq'];
    $super = '';

    $super .= $context . "super_displayfields['$thissrchuniq'] = new "
        . "Array(\n";

    $j = 1;
    $names = asset_order_options($id, $db);
    reset($names);
    foreach ($names as $xxx => $name) {
        if ($j > 1) {
            $super .= ",\n";
        }
        $super .= "        new Array('$name','$name')";
        $j++;
    }
    $super .= "\n      );\n";

    return $super;
}
