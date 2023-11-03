<?php



    function outputJavascriptShowElement($id_list,$testleft,$testright,$clear_list,$delay_ms=500)
    {
        JS_HideShow($id_list,$testleft,$testright,$clear_list,$delay_ms);
    }


    function JS_HideShow($id_list,$testleft,$testright,$clear_list,$delay_ms=500)
    {
        echo <<< HERE

        <script type="text/javascript">

        function getStyleObject(objectId)
        {
            if (document.getElementById && document.getElementById(objectId))
            {
                return document.getElementById(objectId).style;
            }
            else if (document.all && document.all(objectId))
            {
                return document.all(objectId).style;
            }
            else
            {
                return false;
            }
        }


        function getObject(objectId)
        {
            if (document.getElementById && document.getElementById(objectId))
            {
                return document.getElementById(objectId);
            }
            else if (document.all && document.all(objectId))
            {
                return document.all(objectId);
            }
            else
            {
                return false;
            }
        }


        function clearField(clear_list)
        {
            // unfortunately, there is no getElementByClass
            var ids = clear_list.split(",");
            for (i = 0; i < ids.length; i++)
            {
                var id = ids[i];
                var el = getObject(id);
                el.value = "";
            }
        }


        function showElement(id_list,testleft,testright,clear_list)
        {
            if (testleft == testright)
            {  // show
                var display = "inline";
            }
            else
            {
                clearField(clear_list);     // clear fields
                var display = "none";       // hide
            }

              // unfortunately, there is no getElementByClass
            var ids = id_list.split(",");
            for (i = 0; i < ids.length; i++)
            {
                var id = ids[i];
                var el_style = getStyleObject(id);
                el_style.display = display;
            }
       }


        /*
        Need to run on page load, esp. after backing into page (post-error),
        to display saved state.
        Unfortunately, cached values (esp. for checkboxes) do not "register"
        until after the whole page is loaded (window.onload doesn't even work,
        so have to delay slightly -- for some reason a delay of 0 milliseconds works!!
        */

        window.setTimeout("showElement('$id_list',$testleft,$testright,'$clear_list')",$delay_ms);

    </script>
HERE;

    }

?>
