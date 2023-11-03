/*
 * SimpleModal Confirm Modal Dialog
 * http://simplemodal.com
 *
 * Copyright (c) 2013 Eric Martin - http://ericmmartin.com
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

jQuery(function ($) {
    $('#confirm-dialog input.confirm, #confirm-dialog a.confirm').click(function (e) {
        e.preventDefault();           
        var user=getCookie("prventNext");
        if (user != "" && user != undefined)
        {
            alert("Welcome again " + user);
        }
        else {
            // example of calling the confirm function
            // you must use a callback function to perform the "yes" action
            confirm("Continue to the SimpleModal Project page?", function () {
																		   
                check = $("#prventNext").is(":checked");
                if(check) {
                    alert("Checkbox is checked.");
                    var cname 	= 'prventNext';
                    var cvalue	= $("#prventNext").val();
                    var exdays	= 10;
                    setCookie(cname,cvalue,exdays);
                } else {
                    alert("Checkbox is unchecked.");
                }
            //window.location.href = 'http://simplemodal.com';
            });
        }
    });
});

function confirm(message, callback) {
    $('#confirm').modal({
        closeHTML: "<a href='#' title='Close' class='modal-close'>x</a>",
        position: ["20%",],
        overlayId: 'confirm-overlay',
        containerId: 'confirm-container', 
        onShow: function (dialog) {
            var modal = this;

            $('.message', dialog.data[0]).append(message);

            // if the user clicks "yes"
            $('.yes', dialog.data[0]).click(function () {
                // call the callback
                if ($.isFunction(callback)) {
                    callback.apply();
                }
                // close the dialog
                modal.close(); // or $.modal.close();
            });
            
            // if the user clicks "no"
            $('.no', dialog.data[0]).click(function () {
                $(".loadingStage").css({'display':'none'});
                modal.close(); // or $.modal.close();
            });
        }
    });
}


/* For getting and setting cokkies */

/* for setting cookie */
function setCookie(cname,cvalue,exdays)
{
    var d = new Date();
    d.setTime(d.getTime()+(exdays*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname+"="+cvalue+"; "+expires;
}

/* for getting cookie */
function getCookie(cname)
{
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) 
    {
        var c = ca[i].trim();
        if (c.indexOf(name)==0) return c.substring(name.length,c.length);
    }
    return "";
}

/* for checking cookie */
function checkCookie()
{
    var user=getCookie("username");
    if (user!="")
    {
        alert("Welcome again " + user);
    }
    else 
    {
        user = prompt("Please enter your name:","");
        if (user!="" && user!=null)
        {
            setCookie("username",user,30);
        }
    }
}