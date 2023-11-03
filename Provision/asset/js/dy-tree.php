<?php
function outputJavascriptBegin($checkboxes, $hyperlinks, $textboxes)
{
    global $REMOTE_ADDR;
?>
    <script language="JavaScript">
        // You can find instructions for this file here:
        // http://www.treeview.net

        // Decide if the names are links or just the icons
        USETEXTLINKS = <?php echo $hyperlinks  ?> //use 1 for hyperlinks

        // Decide if the tree is to start all open or just showing the root folders
        STARTALLOPEN = 0 //replace 0 with 1 to show the whole tree

        // Decide if the tree is to to be shown on a separate frame of its own
        USEFRAMES = 0

        // Remove the folder and link icons
        USEICONS = 0

        // add checkboxes  // ADDED BY NINA
        USECHECKBOXES = <?php echo $checkboxes . "\n"  ?>

        // add textboxes  // ADDED BY NINA
        USETEXTBOXES = <?php echo $textboxes . "\n"  ?>

        // Make the folder and link labels wrap into multiple lines
        WRAPTEXT = 0

        // Folders reopen to previous state across page loads
        PERSERVESTATE = 1

        // If images are stored in a different directory, specify path
        ICONPATH = "js/"

        //ADDED BY NINA: used for setting a clickedFolder cookie for new queries
        IPADDRESS = '<?php echo $REMOTE_ADDR ?>'


        <?php
    }

    /* 
   ADDED BY  NINA
   call this function when in edit mode so that the list
   will expand to show checked data fields
*/
    function getOpenFolders($displayfields)
    {
        global $db;
        $openfolders_array = array();

        $displayfields_array = explode(":", $displayfields);
        foreach ($displayfields_array as $k => $v) {
            if (strlen($v)) {
                $name = $v;
                # find all the parent folders for checked items
                $openfolders_array = getParentIds($openfolders_array, $name, "");
            }
        }
        $openfolders = implode(":", $openfolders_array);
        if (strlen($openfolders)) {
            $openfolders = ":" . $openfolders . ":";
        }
        return $openfolders;
    }

    function getParentIds($parents, $name, $id)
    {
        global $db;

        if (strlen($name)) {
            $sql = "SELECT parent FROM DataName where name = '" . $name . "'";
        } else {
            $sql = "SELECT parent FROM DataName where dataid = " . $id;
        }

        $result = mysqli_query($db, $sql);
        if ($result) {

            $row = mysqli_fetch_array($result);
            $parent = $row['parent'];

            if ($parent > 0) {
                // use parent as both key and value ot avoid duplicates    
                $parents[$parent] = $parent;
                $parents = getParentIds($parents, "", $parent);
            }

            return $parents;
        }
    }

    function outputJavascript($parentId, $parentObject, $displayfields, $indent)
    {
        /* The original script is based on a database table that
    includes a binary field called "nodeIsFolder".  Since our 
    table doesn't have such a field, QUERY1 must include criteria 
    that the item have children and QUERY2 must exclude those items.       
    */

        global $db;
        $categories_string = "";
        $categories_array = array();
        $dataitems_array = array();
        $indent .= "  ";

        if ($parentId == -1) {
            echo "foldersTree = gFld('<span class=faded>SYSTEM SURVEY</span>')\n";
            outputJavascript(0, "foldersTree", $displayfields, $indent);
        } else {
            $sql1 = "SELECT distinct T1.dataid, T1.name, T1.parent " .
                " FROM DataName AS T1, DataName as T2" .
                " WHERE T1.parent = " . $parentId .
                " AND T1.dataid = T2.parent" .
                " ORDER BY T1.ordinal";

            $result1 = mysqli_query($db, $sql1);
            if ($result1) {
                while ($row = mysqli_fetch_array($result1)) {
                    $dataid = $row['dataid'];
                    $name = $row['name'];
                    $parent = $row['parent'];

                    // is this a folder that should be opened (to display a checked item)?
                    $openfolders = getOpenFolders($displayfields);
                    $this_open = strstr($openfolders, ":" . $dataid . ":");
                    $open = ($this_open) ? "1" : "0";

                    // modification from original ftiens.js allows me to pass
                    // $open as new 3rd param
                    $gFldStr = "gFld('<span class=faded>" . $name . "</span>', '', '" . $open . "')";
                    echo $indent . $parentObject . "Sub = insFld(" . $parentObject . "," . $gFldStr . ")\n";
                    outputJavascript($dataid, $parentObject . "Sub", $displayfields, $indent);

                    // Create a list of dataids retrieved (to exclude in next query)   
                    // Also for use in ftiens.js form field names
                    $categories_array[] = $dataid;
                }
            }

            if (safe_count($categories_array)) {
                $categories_string = implode(",", $categories_array);
            }
        }

        // QUERY2: select only data fields (items w/o children) // 
        $sql2 = "SELECT dataid, name FROM DataName WHERE parent=" . $parentId;
        if (isset($categories_string) && strlen($categories_string)) {
            $sql2 .= " AND dataid NOT IN (" . $categories_string . ")";
        }
        $sql2 .= " ORDER BY ordinal";

        $result2 = mysqli_query($db, $sql2);
        if ($result2) {
            while ($row = mysqli_fetch_array($result2)) {
                $name = $row['name'];
                $dataid = $row['dataid'];

                // is this one of the checked items?
                $this_checked = strstr($displayfields, ":" . $name . ":");
                $checked = ($this_checked) ? "checked" : "";

                // modification from original ftiens.js allows me to pass
                // $row['dataid'], $checked as new 4th & 5th params
                $gLnkStr = "gLnk('0','$name','','$dataid','$checked')";
                echo $indent . "insDoc(" . $parentObject . "," . $gLnkStr . ")\n";

                // For use in ftiens.js form fieled names   
                $dataitems_array[] = $dataid;
            }
        }

        if (safe_count($dataitems_array)) {
            $dataitems_string = implode(",", $dataitems_array);
        }
    }

    function outputJavascriptEnd()
    {
        echo "</script>";
    }



    outputJavascriptBegin($checkboxes, $hyperlinks, $textboxes);
    outputJavascript("-1", "", $displayfields, "");
    outputJavascriptEnd();
        ?>