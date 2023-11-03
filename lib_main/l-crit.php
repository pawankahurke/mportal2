<?php






      
    
function outputJavascriptCritBldr($num_ANDs, $num_ORs, $openblockrows) {      global $QUERY_STRING; 
    global $comparison_options;  
    
        $action  = trim(get_argument('action',0,'none'));
    if ( $action == "edit" || $action == "duplicate")  
    {
        $editmode = 1;
    } else {
        $editmode = 0;
    }       
?>   

    <script type="text/javascript">
    
        function changeDiv(the_div,the_change)
        {
          var the_style = getStyleObject(the_div);
          // alert ("before:" + the_div + ":" + the_style.display)      
          if (the_style != false)
          {
            the_style.display = the_change;
          }
          // alert ("after:" + the_div + ":" + the_style.display) 
        }
        
        function hideAllArrows()
        { 
<?php
    for ($i=1; $i<=$num_ORs; $i++) {
        for ($j=1; $j<=$num_ANDs; $j++) { 
?>
          changeDiv("Block<?php echo $i ?>Row<?php echo $j ?>arrow","none");
<?php
        }
    }
?>            
        }

        function getStyleObject(objectId) {
          if (document.getElementById && document.getElementById(objectId)) {
            return document.getElementById(objectId).style;
          } else if (document.all && document.all(objectId)) {
            return document.all(objectId).style;
          } else {
            return false;
          }
        }
 
 /*
        function saveState(AND_or_OR, i, j)
        { 
          var clickedFolder = 0 
          var state = 0 
          var currentOpen
         
          state = clickedFolder.isOpen 
         
          clickedFolder.setState(!state) //open<->close  
        
          if (folderId!=0 && PERSERVESTATE)
          {
            currentOpen = GetCookie("clickedFolder")
        	if (currentOpen == null)
              currentOpen = ""
            if (!clickedFolder.isOpen) //closing
        	{
        	  currentOpen = currentOpen.replace(folderId+"-", "")
        	  SetCookie("clickedFolder", currentOpen)
            }
        	else
        	  SetCookie("clickedFolder", currentOpen+folderId+"-")
          }
        }
 */   
      
        function onClickActions (AND_or_OR, i, j) {
          hideAllArrows(); 
          
          if (AND_or_OR == "AND") {
            changeDiv("Block" + i + "Row" + j + "ANDbutton", "none"); 
            changeDiv("Block" + i + "Row" + j + "ANDtext", "block");               
            //if (j>1) {  
              //changeDiv("Block" + i + "Row" + j + "GROUP", "none"); 
              //changeDiv("Block" + i + "Row" + j + "GROUPA", "none"); 
              //changeDiv("Block" + i + "Row" + j + "GROUPB", "none"); 
            //}
          } else {
            changeDiv("Block" + i + "ORbutton", "none"); 
            changeDiv("Block" + i + "ORtext", "block"); 
          }
          
          // do for both AND Rows and OR Blocks
          var z;
          if (AND_or_OR == "AND") {
            z = j+1;   // apply to the next row
          } else {     
            z = 1;       // apply to the first row of this new clock
            i++;
          }
          
          changeDiv("Block" + i + "Row" + z,"block"); 
          changeDiv("Block" + i + "Row" + z + "A","block"); 
          changeDiv("Block" + i + "Row" + z + "arrow","block"); 
          changeDiv("Block" + i + "Row" + z + "B","block"); 
          changeDiv("Block" + i + "Row" + z + "C","block"); 
          changeDiv("Block" + i + "Row" + z + "D","block"); 
          //changeDiv("Block" + i + "Row" + z + "E","block");
          //changeDiv("Block" + i + "Row" + z + "group","block");
          //if (AND_or_OR == "AND") {
            //changeDiv("Block" + i + "Row" + z + "GROUP","block"); 
            //changeDiv("Block" + i + "Row" + z + "GROUPA","block"); 
            //changeDiv("Block" + i + "Row" + z + "GROUPB","block"); 
          //}
          changeDiv("Block" + i + "Row" + z + "AND","block"); 
          changeDiv("Block" + i + "Row" + z + "ANDA","block"); 
          changeDiv("Block" + i + "Row" + z + "ANDB","block"); 
          changeDiv("Block" + i + "Row" + z + "ANDbutton","block");
        
          if (AND_or_OR == "OR") { 
            changeDiv("Block" + i + "OR","block"); 
            changeDiv("Block" + i + "ORA","block"); 
            changeDiv("Block" + i + "ORbutton", "block"); 
          }  
        } 
        
       function changeFocusArrow(arrowElement) {
            var currentFocusArrow = getFocusBlockRow() + "arrow";
            changeDiv(currentFocusArrow,"none"); 
            changeDiv(arrowElement,"block");              
        }
       
       function getFocusBlockRow() {
            var num_ORs  = "<?php echo $num_ORs ?>";        
            var num_ANDs = "<?php echo $num_ANDs ?>"; 
                        
            for (i=1; i <= num_ORs; i++) {
                for (j=1; j <= num_ANDs; j++) {
                    var arrow = "Block" + i + "Row" +  j + "arrow";       
                    var element = getStyleObject(arrow);
                    var displayStatus = element.display;
                    if (displayStatus == "block") {
                        // alert(arrow + " is " + displayStatus);
                        focusBlockRow = "Block" + i + "Row" +  j;  
                    }
                }
            } 
            return focusBlockRow;    
        }
       
       function autoEnterField(dataname) {
          //  var focusField = "Block1Row1field";
           var focusField = getFocusBlockRow() + "field";
           var element = document.getElementById(focusField)
           element.value = dataname;
           //alert(focusField + " is " + element.value);
        }
       
        
    </script>
    
<?php
    $comp = component_installed();
    $arrowgif = "/" . $comp['odir'] . "/pub/closed.gif";

    for ($i=1; $i<=$num_ORs; $i++) {    
        if ($i==1) {
                        $display = "block";
        } else {
            $display = "none";
        }    
              
        for ($j=1; $j<=$num_ANDs; $j++) 
        { 
            if ($i==1 && $j==1) {
                $display = "block";
            } else {
                $display = "none";
            }   
            
            $comp_value = "Block${i}Row${j}comparison"; 
            global $$comp_value; 
            if (!isset($$comp_value)) $$comp_value="";
            
            $field_value =  "Block${i}Row${j}field";
            global $$field_value; 
            if (!isset($$field_value)) $$field_value="";
    
            $value_value =  "Block${i}Row${j}value";
            global $$value_value; 
            if (!isset($$value_value)) $$value_value="";                        
?>        
            <tr id="Block<?php echo $i ?>Row<?php echo $j ?>" 
                style="position:block;display:<?php echo $display ?>;">
    
                <td id="Block<?php echo $i ?>Row<?php echo $j ?>A" 
                    style="position:block;display:<?php echo $display ?>;"
                    width=7>
                    <img src="<?php echo $arrowgif ?>" 
                    id="Block<?php echo $i ?>Row<?php echo $j ?>arrow" 
                    style="position:block;display:<?php echo $display ?>;">
                </td>
                <td id="Block<?php echo $i ?>Row<?php echo $j ?>B" 
                    style="position:block;display:<?php echo $display ?>;">
                    <input type=text size=20
                            id="Block<?php echo $i ?>Row<?php echo $j ?>field"
                            name="Block<?php echo $i ?>Row<?php echo $j ?>field"
                            value="<?php echo $$field_value ?>"
                            onFocus="changeFocusArrow('Block<?php echo $i ?>Row<?php echo $j ?>arrow')">
                </td>
                <td id="Block<?php echo $i ?>Row<?php echo $j ?>C" 
                    style="position:block;display:<?php echo $display ?>;">
                    <?php echo html_select("Block" . $i ."Row" . $j . "comparison",
                          $comparison_options, $$comp_value, 1) ?>                     
                </td>
                <td id="Block<?php echo $i ?>Row<?php echo $j ?>D" 
                    style="position:block;display:<?php echo $display ?>;">
                    <input type="text" size=18
                            name="Block<?php echo $i ?>Row<?php echo $j ?>value" 
                            value="<?php echo stripslashes($$value_value) ?>"
                            onFocus="changeFocusArrow('Block<?php echo $i ?>Row<?php echo $j ?>arrow')">
                </td>
                <!-- <td align="center"
                    id="Block<?php echo $i ?>Row<?php echo $j ?>E" 
                    style="position:block;display:<?php echo $display ?>;">
                    <input type="text" size=18
                            name="Block<?php echo $i ?>Row<?php echo $j ?>group" 
                            value="">
                </td>-->                    
            </tr>
<?php            
            if ($j!=1) {
?>          
            <!--
            <tr id="Block<?php echo $i ?>Row<?php echo $j ?>GROUP" 
                style="position:block;display:none;">
                <td id="Block<?php echo $i ?>Row<?php echo $j ?>GROUPA"
                    style="position:block;display:none;"></td>
                <td colspan="4" id="Block<?php echo $i ?>Row<?php echo $j ?>GROUPB"
                    style="position:block;display:none;"><INPUT 
                    type="checkbox" 
                    name="" 
                    value="1">The <?php echo $j ?> fields above should be grouped</td>
            </tr>
            -->
<?php
            }
            
            if ($j != $num_ANDs) {    
?>            
            <tr id="Block<?php echo $i ?>Row<?php echo $j ?>AND" 
                style="position:block;display:<?php echo $display ?>;">
                <td id="Block<?php echo $i ?>Row<?php echo $j ?>ANDA" 
                    style="position:block;display:<?php echo $display ?>;">
                </td>                  
                <td colspan="4" id="Block<?php echo $i ?>Row<?php echo $j ?>ANDB" 
                    style="position:block;display:<?php echo $display ?>;">
                    <INPUT type="button" name="AND" value="AND"
                        id="Block<?php echo $i ?>Row<?php echo $j ?>ANDbutton" 
                        style="position:block;display:<?php echo $display ?>"
                        onClick="onClickActions('AND', <?php echo $i ?>, <?php echo $j ?>)">
                    <span id="Block<?php echo $i ?>Row<?php echo $j ?>ANDtext"
                        style="position:block;display:none;">AND</span>
                </td>
            </tr>
<?php      
            }
        }
            
        if ($i==1) {
            $display = "block";
        }
        
        if ($i != $num_ORs) {    
?>                
            <tr id="Block<?php echo $i ?>OR" style="position:block;display:<?php echo $display ?>">
                <td colspan="5" id="Block<?php echo $i ?>ORA" 
                    style="position:block;display:<?php echo $display ?>">
                    <INPUT type="button" name="OR" value="OR" 
                        id="Block<?php echo $i ?>ORbutton" 
                        style="position:block;display:<?php echo $display ?>;"
                        onClick="onClickActions('OR', <?php echo $i ?>, <?php echo $j ?>)">
                    <span id="Block<?php echo $i ?>ORtext"
                        style="position:block;display:none;">OR</span>
                </td>
            </tr>                      
<?php     
        }                                         
    }
    
        if ($editmode) 
    {
?>    

    <script type="text/javascript">

        /* FUTURE: use cookies?
        //set a cookie called openblock:row and enter the list, eg 1:1-1:2  
        SetCookie("openblock:row", "<?php echo $openblockrows ?>");                
        cookie = GetCookie("openblock:row"); 
        openus = cookie.replace(/-$/,"");
        */        
        
        string = "<?php echo $openblockrows ?>";
        openus = string.replace(/-$/,"");
        openus_array = openus.split("-");
        
        prevblock = 0; 
        for (i=0; i < openus_array.length; i++) 
        {
            openme       = openus_array[i];
            openme_array = openme.split(":");
            block        = openme_array[0];
            row          = openme_array[1];
              
            if (block == prevblock) 
            {
                changeDiv("Block" + prevblock + "Row" + prevrow + "ANDbutton","none");
                changeDiv("Block" + prevblock + "Row" + prevrow + "ANDtext","block");
            } 
            else 
            {
                if (prevblock != 0) 
                {
                    changeDiv("Block" + prevblock + "Row" + prevrow + "AND","block"); 
                    changeDiv("Block" + prevblock + "Row" + prevrow + "ANDA","block"); 
                    changeDiv("Block" + prevblock + "Row" + prevrow + "ANDB","block"); 
                    changeDiv("Block" + prevblock + "Row" + prevrow + "ANDbutton","block");
                    changeDiv("Block" + prevblock + "Row" + prevrow + "ANDtext","none");
                    changeDiv("Block" + prevblock + "OR","block");
                    changeDiv("Block" + prevblock + "ORA","block");
                    changeDiv("Block" + prevblock + "ORbutton","none"); 
                    changeDiv("Block" + prevblock + "ORtext","block"); 
                }
            } 
            
            changeDiv("Block" + block + "Row" + row,"block");
            changeDiv("Block" + block + "Row" + row + "A","block");
            changeDiv("Block" + block + "Row" + row + "B","block");
            changeDiv("Block" + block + "Row" + row + "field","block");
            changeDiv("Block" + block + "Row" + row + "C","block");
            changeDiv("Block" + block + "Row" + row + "comparison","block");
            changeDiv("Block" + block + "Row" + row + "D","block");    
            changeDiv("Block" + block + "Row" + row + "value","block");     
            //changeDiv("Block" + block + "Row" + row + "E","block"); 
            //changeDiv("Block" + block + "Row" + row + "group","block"); 
            changeDiv("Block" + block + "Row" + row + "AND","block"); 
            changeDiv("Block" + block + "Row" + row + "ANDA","block"); 
            changeDiv("Block" + block + "Row" + row + "ANDB","block");  
            changeDiv("Block" + block + "Row" + row + "ANDbutton","block");           
            
            prevblock = block;     
            prevrow = row;        
  		}  
        
        changeDiv("Block" + block + "OR","block");
        changeDiv("Block" + block + "ORA","block");
        changeDiv("Block" + block + "ORbutton","block"); 
    </script>
    
<?php          
    }
}


?>
