/********************************************************************
 Revision history:

 Date        Who     What
 ---------   ---     ----
 27-Oct-16   AVI     Created. added functions validateEmailAddr, validateName, validateAlphaNumeric, validateZipCode

 **********************************************************************/
/*
 This file will have all validation functions for javascript. All functions have return type boolean value
 */


function validate_Email(email) {
    var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
    if (filter.test(email)) {
        return true;
    }
    else {
        return false;
    }
}

function validate_Name(name) {
    var nameFilter = /^[a-zA-Z]+$/;
    ;
    if (nameFilter.test(name)) {
        return true;
    } else {
        return false;
    }
}

function validate_AlphaNumeric(name) {
    var filter = /^[a-z\d\ \s]+$/i;
    if (filter.test(name)) {
        return true;
    }
    else {
        return false;
    }
}

function validate_SiteName(name) {
    var filter = /^[a-z\d\_\s]+$/i;
    if (filter.test(name)) {
        return true;
    }
    else {
        return false;
    }
}

function validate_ZipCode(zipcode) {
//  var regExp = /^\+?([0-9]{2})\)?[-. ]?([0-9]{4})[-. ]?([0-9]{4})$/;
    var regExp = /^[0-9]+$/;
    if (regExp.test(zipcode)) {
        return true;
    } else {
        return false;
    }
}

function validate_OrderNumber(orderNum) {
//  var regExp = /^\+?([0-9]{2})\)?[-. ]?([0-9]{4})[-. ]?([0-9]{4})$/;
    var regExp = /^[0-9]{8,16}$/;
    if (regExp.test(orderNum)) {
        return true;
    } else {
        return false;
    }
}

function validate_Number(number) {
    var regExp = /^[0-9]*(?:\.\d{1,2})?$/;    // allow only numbers [0-9]
    if (regExp.test(number)) {
        return true;
    } else {
        return false;
    }
}

function validate_AlphaNumericLenght(orderNum) {
//  var regExp = /^\+?([0-9]{2})\)?[-. ]?([0-9]{4})[-. ]?([0-9]{4})$/;
    var regExp = /^[a-zA-Z0-9]{8,16}$/;
    if (regExp.test(orderNum)) {
        return true;
    } else {
        return false;
    }
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function checks given value should only contains Letters, Numbers, And Hypen
 *----------------------------------------------------------------------------------------------------------------------
 */
function validate_Alphanumeric(value)
{
    var regExp = /^[a-zA-Z0-9\-\s]+$/;
    if (value.match(regExp)) {

        return true;
    }
    else
    {
        return false;
    }
}

function validate_alphanumeric_underscore(value) {
    var regExp = /^[a-zA-Z0-9-_\s]+$/;
    if (value.match(regExp)) {

        return true;
    }
    else
    {
        return false;
    }


}

function validate_alphanumeric_nounderscore(value) {
    var regExp = /^[a-zA-Z0-9-\s]+$/;
    if (value.match(regExp)) {

        return true;
    }
    else
    {
        return false;
    }


}

function validate_alphanumeric_noSpecial(value) {
    var regExp = /^[a-zA-Z0-9\s]+$/;
    if (value.match(regExp)) {

        return true;
    }
    else
    {
        return false;
    }


}

function validate_Alphanumeric_speciAL(value) {
    var regExp = /^[a-zA-Z0-9#/-\s,]+$/;
    if (value.match(regExp)) {

        return true;
    }
    else
    {
        return false;
    }
}

function validate_AlphaSlash(value) {
    var regExp = /^[a-zA-Z\/\\]+$/;
    if (value.match(regExp)) {

        return true;
    }
    else
    {
        return false;
    }
}

function validate_numeric_dot(value) {
    var regExp = /^[0-9.]+$/;
    if (value.match(regExp)) {

        return true;
    }
    else
    {
        return false;
    }
}

function validateUrl(s) {
    //var regexp = /[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/;
    var regexp = /[a-z0-9-\.]+\.[a-z0-9]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/;
    if (regexp.test(s)) {
        return true;
    }
    else {
        return false;
    }
}

function validate_AlphaSlashSpace(value) {
    var regExp = /^[a-zA-Z\/\\\s]+$/;
    if (value.match(regExp)) {

        return true;
    }
    else
    {
        return false;
    }
}
