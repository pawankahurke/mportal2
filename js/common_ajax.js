function AjaxCall(url, data){

    // alert("AjaxCall");
    var returndata=""; 
    var requestStatus = 1;
    //if( requestStatus != 0) {
        try {
            // $.support.cors = true;
            $.ajax({
                url: url + "&csrfMagicToken=" + csrfMagicToken,
                type: "POST",
                data: data,
                async: false,
                success: function(res){
                    // alert("AJAX: "+res);
                    returndata=res;
                    
                },
                error: function(res){
                    console.log("AjaxCall = "+JSON.stringify(res));
                    returndata="error";
                }
            });
        } catch (e) {
            console.log('common.js'+'[func=AjaxCall]'+",Error: "+ e.message);
            returndata="error";
        }
    //}
        return returndata;
    
    }

    function commonAjaxCall(url, data, timeout) {
        
        return new Promise(function(resolve, reject) {
            try {
                $.support.cors = true;
                $.ajax({
                    url: url + "&csrfMagicToken=" + csrfMagicToken,
                    type: "POST",
                    data: data,
                    timeout: timeout,
                    success: function(res) {                    
                        resolve(res);
                    },
                    error: function(res) {
                        //errorLog("commonAjaxCall = " + JSON.stringify(res));
                        resolve(res);
                    }
                });
            } catch (e) {
                //errorLog('generic.js', 'commonAjaxCall', e.message);
                resolve({"error":e.message});
            }
        });
    }