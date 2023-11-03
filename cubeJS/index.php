<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">
<title>cubejs index</title>

<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<style>
.loader {
  border: 8px solid #f3f3f3;
  border-radius: 50%;
  border-top: 8px solid #3498db;
  width: 20px;
  height: 20px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}

#saveSchema{
    position: relative;
    top: 32px;
    left: -235px;
}

#JsonSchema{
    padding: 15px;
    width: 500px;
    height: 500px;
}
</style>
</head>

<body>
        <form name="dartform">
            <textarea id="JsonSchema"></textarea>
            <input type="hidden" id="hiddenId">
            <button type="button" id="saveSchema" onclick="saveSchemaFile();">Save</button> 
            <span id="SuccessMsg"></span>
        </form>

    <script>
        $(document).ready(function () {
            var urlParam = getParam('id');
            $('#hiddenId').val(urlParam);
            $.ajax({
                url: 'schemaOut.php?id='+urlParam,
                type: 'GET',
                success: function(res) {
                    $('#JsonSchema').html(res);
		},
                error:function(error){
                    console.log("error");
                }
            });
        });
        
        function getParam( name )
        {
         name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
         var regexS = "[\\?&]"+name+"=([^&#]*)";
         var regex = new RegExp( regexS );
         var results = regex.exec( window.location.href );
         if( results == null )
          return "";
        else
         return results[1];
        }
        
        function saveSchemaFile(){
            var id = $('#hiddenId').val();
            window.location.href = 'schemaOut.php?id='+id+'&type=submit';
            $.ajax({
                url: 'schemaOut.php?id='+id+'&type=submit',
                type: 'POST',
                dataType: 'json',
                success: function(res) {
//                    console.log(res);
                    $('#SuccessMsg').html(res+" Cubes Successfully added to the Schemas Folder");
		},
                error:function(error){
                    console.log("error");
                }
            });
        }
        
    </script>


</body>


</html>
