/*
 * Copyright 2015 brutusin.org
 *
 * Licensed under the Apache License, Version 2.0 (the "SuperLicense");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @author Ignacio del Valle Alles idelvall@brutusin.org
 */

if (typeof brutusin === "undefined") {
    window.brutusin = new Object();
} else if (typeof brutusin !== "object") {
    throw ("brutusin global variable already exists");
}

(function () {
    if (!String.prototype.startsWith) {
        String.prototype.startsWith = function (searchString, position) {
            position = position || 0;
            return this.indexOf(searchString, position) === position;
        };
    }
    if (!String.prototype.endsWith) {
        String.prototype.endsWith = function (searchString, position) {
            var subjectString = this.toString();
            if (position === undefined || position > subjectString.length) {
                position = subjectString.length;
            }
            position -= searchString.length;
            var lastIndex = subjectString.indexOf(searchString, position);
            return lastIndex !== -1 && lastIndex === position;
        };
    }
    if (!String.prototype.includes) {
        String.prototype.includes = function () {
            'use strict';
            return String.prototype.indexOf.apply(this, arguments) !== -1;
        };
    }
    if (!String.prototype.format) {
        String.prototype.format = function () {
            var formatted = this;
            for (var i = 0; i < arguments.length; i++) {
                var regexp = new RegExp('\\{' + i + '\\}', 'gi');
                formatted = formatted.replace(regexp, arguments[i]);
            }
            return formatted;
        };
    }

    var BrutusinForms = new Object();
    BrutusinForms.messages = {
        "validationError": "Validation error",
        "required": "This field is **required**",
        "invalidValue": "Invalid field value",
        "addpropNameExistent": "This property is already present in the object",
        "addpropNameRequired": "A name is required",
        "minItems": "At least `{0}` items are required",
        "maxItems": "At most `{0}` items are allowed",
        "pattern": "Value does not match pattern: `{0}`",
        "minLength": "Value must be **at least** `{0}` characters long",
        "maxLength": "Value must be **at most** `{0}` characters long",
        "multipleOf": "Value must be **multiple of** `{0}`",
        "minimum": "Value must be **greater or equal than** `{0}`",
        "exclusiveMinimum": "Value must be **greater than** `{0}`",
        "maximum": "Value must be **lower or equal than** `{0}`",
        "exclusiveMaximum": "Value must be **lower than** `{0}`",
        "minProperties": "At least `{0}` properties are required",
        "maxProperties": "At most `{0}` properties are allowed",
        "uniqueItems": "Array items must be unique",
        "addItem": "Add item",
        "true": "True",
        "false": "False"
    };

    
    /**
     * Callback functions to be notified after an HTML element has been rendered (passed as parameter).
     * @type type
     */
    BrutusinForms.decorators = new Array();

    BrutusinForms.addDecorator = function (f) {
        BrutusinForms.decorators[BrutusinForms.decorators.length] = f;
    };

    BrutusinForms.onResolutionStarted = function (element) {
    };

    BrutusinForms.onResolutionFinished = function (element) {
    };

    BrutusinForms.onValidationError = function (element, message) {
        element.focus();
        if (!element.className.includes(" error")) {
            element.className += " error";
        }
        alert(message);
    };

    BrutusinForms.onValidationSuccess = function (element) {
        element.className = element.className.replace(" error", "");
    };

    /**
     * Callback function to be notified after a form has been rendered (passed as parameter).
     * @type type
     */
    BrutusinForms.postRender = null;
    /**
     * BrutusinForms instances created in the document
     * @type Array
     */
    BrutusinForms.instances = new Array();
    /**
     * BrutusinForms factory method
     * @param {type} schema schema object
     * @returns {BrutusinForms.create.obj|Object|Object.create.obj}
     */
    BrutusinForms.create = function (schema) {
        var SCHEMA_ANY = {"type": "any"};
        var obj = new Object();

        var schemaMap = new Object();
        var dependencyMap = new Object();
        var renderInfoMap = new Object();
        var container;
        var data;
        var error;
        var initialValue;
        var inputCounter = 0;
        var root = schema;
        var formId = "BrutusinForms#" + BrutusinForms.instances.length;

        renameRequiredPropeties(schema); // required v4 (array) -> requiredProperties
        populateSchemaMap("$", schema);

        validateDepencyMapIsAcyclic();

        var renderers = new Object();

        renderers["integer"] = function (container, id, parentObject, propertyProvider, value) {
            renderers["string"](container, id, parentObject, propertyProvider, value);
        };

        renderers["number"] = function (container, id, parentObject, propertyProvider, value) {
            renderers["string"](container, id, parentObject, propertyProvider, value);
        };

        renderers["any"] = function (container, id, parentObject, propertyProvider, value) {
            renderers["string"](container, id, parentObject, propertyProvider, value);
        };

        renderers["string"] = function (container, id, parentObject, propertyProvider, value) {
            /// TODO change the handler for when there is a 'media'
            /// specifier so it becomes a file element. 
            var schemaId = getSchemaId(id);
            var parentId = getParentSchemaId(schemaId);
            var s = getSchema(schemaId);
            var parentSchema = getSchema(parentId);
            var input;

            if (s.type === "any") {
                input = document.createElement("textarea");
                if (value) {
                    input.value = JSON.stringify(value, null, 4);
                    if (s.readOnly)
                        input.disabled = true;
                }
            } else if (s.media) {
                input = document.createElement("input");
                input.type = "file";
                appendChild(input, option, s);
                // XXX TODO, encode the SOB properly.
            } else if (s.enum) { 
               
                  
                   
                    var selectedIndex = 0;
                    var optionvalueData=s.enum;

                    if(s.format=="checkbox-group" && s.multiple){
                        var div = document.createElement("div");   
                        div.setAttribute("class","checkbox-group");
                        div.setAttribute("id","checkboxes");
                        // Create the list element:
                        var list = document.createElement('ul');
                        if($.isArray(optionvalueData)) { 
                            for (var i = 0; i < optionvalueData.length; i++) {
                                // Create the list item:
                                var item = document.createElement('li');
                                var inputli =document.createElement('input');
                                inputli.type="checkbox";
                                inputli.id=optionvalueData[i];

                                var label = document.createElement("label");
                                label.setAttribute("for",optionvalueData[i]);
                                var labeltxt= document.createTextNode(optionvalueData[i]);
                                label.appendChild(labeltxt);
                                
                                item.appendChild(inputli);
                                item.appendChild(label);
                        
                                // Add it to the list:
                                list.appendChild(item);
                            }
                        }
                        
                        div.appendChild(list);                        
                        input=div;
                        // input+=span;

                        

                    }else{
                        input = document.createElement("select");
                        if(s.multiple){
                            input.setAttribute("multiple",true);
                            input.setAttribute("id","multiselect");                        
                        }
                        if (!s.required) {
                            var option = document.createElement("option");
                            var textNode = document.createTextNode("Select");                        
                            option.value = "";
                            appendChild(option, textNode, s);
                            appendChild(input, option, s);
                        }
                    

                            //***********************If enum is an Array***********************
                            if($.isArray(optionvalueData)) { 
                                
                                for (var i = 0; i < s.enum.length; i++) {
                                    var option = document.createElement("option");                        
                                    var textNode = document.createTextNode(s.enum[i]);                                                
                                    option.value = s.enum[i];                        
                                    appendChild(option, textNode, s);
                                    appendChild(input, option, s);
                                    if (value && s.enum[i] === value) {
                                        selectedIndex = i;
                                        if (!s.required) {
                                            selectedIndex++;
                                        }
                                        if (s.readOnly)
                                            input.disabled = true;
                                    }
                                }
                            } else {  
                                
                                    //***********************If enum is an object with key-value pair***********************             
                                    Object.keys(optionvalueData).forEach(function(key) {
                                        var option = document.createElement("option");
                                        var textNode = document.createTextNode("");
                                        // console.log('Key : ' + key + ', Value : ' + optionvalueData[key]);
                                        option.value = key;
                                        option.text = optionvalueData[key];

                                        appendChild(option, textNode, s);
                                        appendChild(input, option, s);
                                        if (value && key === value) {
                                            selectedIndex = i;
                                            if (!s.required) {
                                                selectedIndex++;
                                            }
                                            if (s.readOnly)
                                                input.disabled = true;
                                        }
                                        })                                            
                            }


                    
                        if (s.enum.length === 1)
                            input.selectedIndex = 1;
                        else
                            input.selectedIndex = selectedIndex;
                }
            } else {
                input = document.createElement("input");
                if (s.type === "integer" || s.type === "number") {
                    input.type = "number";
                    input.step = s.step?""+s.step:"any";
                    if (typeof value !== "number") {
                        value = null;
                    }
                }else if (s.format === "password") {
                    input = document.createElement("input");
                    input.type="password";
                }else if (s.format === "date-time") {
                    try {
                        input.type = "datetime-local";
                    } catch (err) {
                        // #46, problem in IE11. TODO polyfill?
                        input.type = "text";
                    }
                } else if (s.format === "button") {                                       
                    input = document.createElement("label");
                    input.setAttribute("class","switch");
                    input.value=0;

                    var btnInput=document.createElement("input");
                    btnInput.type="checkbox";

                    if (value === true) {
                        btnInput.checked = true;
                        input.value=2;
                    }
                    
                    btnInput.addEventListener("click", function() {     
                        if(btnInput.checked===true){
                            input.value=2;
                        }else{
                            input.value=0;
                        }                        
                    }); 

                    var cssSpan=document.createElement("span");
                    cssSpan.setAttribute("class","slider round");
                    appendChild(input, btnInput, s);
                    appendChild(input, cssSpan, s);

                                    

                }else if (s.format === "email") {
                    input.type = "email";
                } else if (s.format === "text") {
                    input = document.createElement("textarea");
                }  else {
                    input.type = "text";
                }
                if (value !== null && typeof value !== "undefined") {
                    // readOnly?
                    input.value = value;
                    if (s.readOnly)
                        input.disabled = true;

                }
            }
            input.schema = schemaId;
            input.setAttribute("autocorrect", "off");
            
            input.getValidationError = function () {
                try {
                    var value = getValue(s, input);
                    if (value === null) {
                        if (s.required) {
                            if (parentSchema && parentSchema.type === "object") {
                                if (parentSchema.required) {
                                    return BrutusinForms.messages["required"];
                                } else {
                                    for (var prop in parentObject) {
                                        if (parentObject[prop] !== null) {
                                            return BrutusinForms.messages["required"];
                                        }
                                    }
                                }
                            } else {
                                return BrutusinForms.messages["required"];
                            }
                        }
                    } else {
                        if (s.pattern && !s.pattern.test(value)) {
                            return BrutusinForms.messages["pattern"].format(s.pattern.source);
                        }
                        if (s.minLength) {
                            if (!value || s.minLength > value.length) {
                                return BrutusinForms.messages["minLength"].format(s.minLength);
                            }
                        }
                        if (s.maxLength) {
                            if (value && s.maxLength < value.length) {
                                return BrutusinForms.messages["maxLength"].format(s.maxLength);
                            }
                        }
                    }
                    if (value !== null && !isNaN(value)) {
                        if (s.multipleOf && value % s.multipleOf !== 0) {
                            return BrutusinForms.messages["multipleOf"].format(s.multipleOf);
                        }
                        if (s.hasOwnProperty("maximum")) {
                            if (s.exclusiveMaximum && value >= s.maximum) {
                                return BrutusinForms.messages["exclusiveMaximum"].format(s.maximum);
                            } else if (!s.exclusiveMaximum && value > s.maximum) {
                                return BrutusinForms.messages["maximum"].format(s.maximum);
                            }
                        }
                        if (s.hasOwnProperty("minimum")) {
                            if (s.exclusiveMinimum && value <= s.minimum) {
                                return BrutusinForms.messages["exclusiveMinimum"].format(s.minimum);
                            } else if (!s.exclusiveMinimum && value < s.minimum) {
                                return BrutusinForms.messages["minimum"].format(s.minimum);
                            }
                        }
                    }
                } catch (error) {
                    return BrutusinForms.messages["invalidValue"];
                }
            };

           

            input.onchange = function () {
                var value;
                try {
                    value = getValue(s, input);
                } catch (error) {
                    value = null;
                }
                if (parentObject) {
                    parentObject[propertyProvider.getValue()] = value;
                } else {
                    data = value;
                }
                onDependencyChanged(schemaId, input);
            };

            if (s.description) {
                input.title = s.description;                
            }

            

            if (s.placeholder) {                          
                input.placeholder = s.placeholder;
            }

//        if (s.pattern) {
//            input.pattern = s.pattern;
//        }
//        if (s.required) {
//            input.required = true;
//        }
//       
//        if (s.minimum) {
//            input.min = s.minimum;
//        }
//        if (s.maximum) {
//            input.max = s.maximum;
//        }
            
            input.onchange();
            input.id = getInputId();
            inputCounter++;
            appendChild(container, input, s);
            return parentObject;
        };

        

        renderers["boolean"] = function (container, id, parentObject, propertyProvider, value) {
            var schemaId = getSchemaId(id);
            var s = getSchema(schemaId);
            var input;
            if (s.required) {
                input = document.createElement("input");
                input.type = "checkbox";
                if (value === true) {
                    input.checked = true;
                }
            } else {
                input = document.createElement("select");
                var emptyOption = document.createElement("option");
                var textEmpty = document.createTextNode("Select");
                textEmpty.value = "";
                appendChild(emptyOption, textEmpty, s);
                appendChild(input, emptyOption, s);

                var optionTrue = document.createElement("option");
                var textTrue;
                optionTrue.value = "true";
                if(s.truetext){
                    textTrue = document.createTextNode("");
                    optionTrue.text = s.truetext;                    
                }else{
                    textTrue = document.createTextNode(BrutusinForms.messages["true"]);
                }
                appendChild(optionTrue, textTrue, s);
                appendChild(input, optionTrue, s);

                var optionFalse = document.createElement("option");
                var textFalse;
                optionFalse.value = "false";
                if(s.falsetext){
                    textFalse = document.createTextNode("");
                    optionFalse.text = s.falsetext;
                }else{
                    textFalse = document.createTextNode(BrutusinForms.messages["false"]);
                }
                appendChild(optionFalse, textFalse, s);
                appendChild(input, optionFalse, s);

                if (value === true) {
                    input.selectedIndex = 1;
                } else if (value === false) {
                    input.selectedIndex = 2;
                }
            }
            input.onchange = function () {
                if (parentObject) {
                    parentObject[propertyProvider.getValue()] = getValue(s, input);
                } else {
                    data = getValue(s, input);
                }
                onDependencyChanged(schemaId, input);
            };
            input.schema = schemaId;
            input.id = getInputId();
            inputCounter++;            
            input.onchange();            
            
            if (s.description) {                
                input.title = s.description;                
            }
            appendChild(container, input, s);
        };

        renderers["boolean-int"] = function (container, id, parentObject, propertyProvider, value) {
            var schemaId = getSchemaId(id);
            var s = getSchema(schemaId);
            var input;
            if (s.required) {
                input = document.createElement("input");
                input.type = "checkbox";
                if (value === true) {
                    input.checked = true;
                }
            } else {
                input = document.createElement("select");
                var emptyOption = document.createElement("option");
                var textEmpty = document.createTextNode("Select");
                textEmpty.value = "";
                // appendChild(emptyOption, textEmpty, s);
                // appendChild(input, emptyOption, s);

                var optionTrue = document.createElement("option");
                var textTrue;
                optionTrue.value = 1;
                if(s.truetext){
                    textTrue = document.createTextNode("");
                    optionTrue.text = s.truetext;                    
                }else{
                    textTrue = document.createTextNode(BrutusinForms.messages["true"]);
                }
                appendChild(optionTrue, textTrue, s);
                appendChild(input, optionTrue, s);

                var optionFalse = document.createElement("option");
                var textFalse;
                optionFalse.value = 0;
                if(s.falsetext){
                    textFalse = document.createTextNode("");
                    optionFalse.text = s.falsetext;
                }else{
                    textFalse = document.createTextNode(BrutusinForms.messages["false"]);
                }
                appendChild(optionFalse, textFalse, s);
                appendChild(input, optionFalse, s);

                if (value === true) {
                    input.selectedIndex = 1;
                } else if (value === false) {
                    input.selectedIndex = 2;
                }
            }
            input.onchange = function () {
                if (parentObject) {
                    parentObject[propertyProvider.getValue()] = getValue(s, input);
                } else {
                    data = getValue(s, input);
                }
                onDependencyChanged(schemaId, input);
            };
            input.schema = schemaId;
            input.id = getInputId();
            inputCounter++;            
            input.onchange();            
            
            if (s.description) {                
                input.title = s.description;                
            }
            appendChild(container, input, s);
        };

        renderers["oneOf"] = function (container, id, parentObject, propertyProvider, value) {
            var schemaId = getSchemaId(id);
            var s = getSchema(schemaId);
            var input = document.createElement("select");
            var display = document.createElement("div");
            display.innerHTML = "";
            input.type = "select";
            input.schema = schemaId;
            var noption = document.createElement("option");
            noption.value = null;
            appendChild(input, noption, s);
            for (var i = 0; i < s.oneOf.length; i++) {
                var option = document.createElement("option");
                var propId = schemaId + "." + i;
                var ss = getSchema(propId);
                var textNode = document.createTextNode(ss.title);
                option.value = s.oneOf[i];
                appendChild(option, textNode, s);
                appendChild(input, option, s);
                if (value === undefined || value === null)
                    continue;
                if (s.readOnly)
                    input.disabled = true;
                if (value.hasOwnProperty("type")) {
                    if (ss.hasOwnProperty("properties")) {
                        if (ss.properties.hasOwnProperty("type")) {
                            var tryit = getSchema(ss.properties.type);
                            if (value.type === tryit.enum[0]) {
                                input.selectedIndex = i + 1;
                                render(null, display, id + "." + (input.selectedIndex - 1), parentObject, propertyProvider, value);
                            }
                        }
                    }
                }
            }
            input.onchange = function () {
                render(null, display, id + "." + (input.selectedIndex - 1), parentObject, propertyProvider, value);
            };
            appendChild(container, input, s);
            appendChild(container, display, s);

        };

        function walk(obj) {
            for (var key in obj) {
              if (obj.hasOwnProperty(key)) {
                var val = obj[key];
                console.log(val);
                walk(val);
              }
            }
          }
        renderers["object"] = function (container, id, parentObject, propertyProvider, value) {
          
            function createStaticPropertyProvider(propname) {
                var ret = new Object();
                ret.getValue = function () {
                    return propname;
                };
                ret.onchange = function (oldName) {
                };
                return ret;
            }

            function addAdditionalProperty(current, table, id, name, value, pattern) {
                
                var schemaId = getSchemaId(id);
                var s = getSchema(schemaId);
                var tbody = table.tBodies[0];
                var tr = document.createElement("tr");
                var td1 = document.createElement("td");
                td1.className = "add-prop-name";
                var innerTab = document.createElement("table");
                var innerTr = document.createElement("tr");
                var innerTd1 = document.createElement("td");
                var innerTd2 = document.createElement("td");
                var keyForBlank = "$" + Object.keys(current).length + "$";
                var td2 = document.createElement("td");
                td2.className = "prop-value";
                var nameInput = document.createElement("input");
                nameInput.type = "text";
                var regExp;
                if (pattern) {
                    regExp = RegExp(pattern);
                }
                nameInput.getValidationError = function () {
                    if (nameInput.previousValue !== nameInput.value) {
                        if (current.hasOwnProperty(nameInput.value)) {
                            return BrutusinForms.messages["addpropNameExistent"];
                        }
                    }
                    if (!nameInput.value) {
                        return BrutusinForms.messages["addpropNameRequired"];
                    }
                };
                var pp = createPropertyProvider(
                        function () {
                            if (nameInput.value) {
                                if (regExp) {
                                    if (nameInput.value.search(regExp) !== -1) {
                                        return nameInput.value;
                                    }
                                } else {
                                    return nameInput.value;
                                }
                            }
                            return keyForBlank;
                        },
                        function (oldPropertyName) {
                            if (pp.getValue() === oldPropertyName) {
                                return;
                            }
                            if (!oldPropertyName || !current.hasOwnProperty(oldPropertyName)) {
                                oldPropertyName = keyForBlank;
                            }
                            if (current.hasOwnProperty(oldPropertyName) || regExp && pp.getValue().search(regExp) === -1) {
                                current[pp.getValue()] = current[oldPropertyName];
                                delete current[oldPropertyName];
                            }
                        });

                nameInput.onblur = function () {
                    if (nameInput.previousValue !== nameInput.value) {
                        var name = nameInput.value;
                        var i = 1;
                        while (nameInput.previousValue !== name && current.hasOwnProperty(name)) {
                            name = nameInput.value + "(" + i + ")";
                            i++;
                        }
                        nameInput.value = name;
                        pp.onchange(nameInput.previousValue);
                        nameInput.previousValue = nameInput.value;
                        return;
                    }
                };
                var removeButton = document.createElement("button");
                removeButton.setAttribute('type', 'button');
                removeButton.className = "remove";
                appendChild(removeButton, document.createTextNode("x"), s);
                removeButton.onclick = function () {
                    delete current[nameInput.value];
                    table.deleteRow(tr.rowIndex);
                    nameInput.value = null;
                    pp.onchange(nameInput.previousValue);
                };
                appendChild(innerTd1, nameInput, s);
                appendChild(innerTd2, removeButton, s);
                appendChild(innerTr, innerTd1, s);
                appendChild(innerTr, innerTd2, s);
                appendChild(innerTab, innerTr, s);
                appendChild(td1, innerTab, s);

                if (pattern !== undefined) {
                    nameInput.placeholder = pattern;
                }

                appendChild(tr, td1, s);
                appendChild(tr, td2, s);
                appendChild(tbody, tr, s);
                appendChild(table, tbody, s);
                render(null, td2, id, current, pp, value);

                if (name) {
                    nameInput.value = name;
                    nameInput.onblur();
                }
            }

            var schemaId = getSchemaId(id);
            var s = getSchema(schemaId);
            var current = new Object();
            
            if (!parentObject) {
                data = current;                
            } else {
                if (propertyP