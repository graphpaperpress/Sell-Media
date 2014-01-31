/*!
 * jQuery Validation Plugin 1.11.1
 *
 * http://bassistance.de/jquery-plugins/jquery-plugin-validation/
 * http://docs.jquery.com/Plugins/Validation
 *
 * Copyright 2013 Jörn Zaefferer
 * Released under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 */
(function(t){t.extend(t.fn,{validate:function(e){if(!this.length)return e&&e.debug&&window.console&&console.warn("Nothing selected, can't validate, returning nothing."),void 0;var i=t.data(this[0],"validator");return i?i:(this.attr("novalidate","novalidate"),i=new t.validator(e,this[0]),t.data(this[0],"validator",i),i.settings.onsubmit&&(this.validateDelegate(":submit","click",function(e){i.settings.submitHandler&&(i.submitButton=e.target),t(e.target).hasClass("cancel")&&(i.cancelSubmit=!0),void 0!==t(e.target).attr("formnovalidate")&&(i.cancelSubmit=!0)}),this.submit(function(e){function s(){var s;return i.settings.submitHandler?(i.submitButton&&(s=t("<input type='hidden'/>").attr("name",i.submitButton.name).val(t(i.submitButton).val()).appendTo(i.currentForm)),i.settings.submitHandler.call(i,i.currentForm,e),i.submitButton&&s.remove(),!1):!0}return i.settings.debug&&e.preventDefault(),i.cancelSubmit?(i.cancelSubmit=!1,s()):i.form()?i.pendingRequest?(i.formSubmitted=!0,!1):s():(i.focusInvalid(),!1)})),i)},valid:function(){if(t(this[0]).is("form"))return this.validate().form();var e=!0,i=t(this[0].form).validate();return this.each(function(){e=e&&i.element(this)}),e},removeAttrs:function(e){var i={},s=this;return t.each(e.split(/\s/),function(t,e){i[e]=s.attr(e),s.removeAttr(e)}),i},rules:function(e,i){var s=this[0];if(e){var r=t.data(s.form,"validator").settings,n=r.rules,a=t.validator.staticRules(s);switch(e){case"add":t.extend(a,t.validator.normalizeRule(i)),delete a.messages,n[s.name]=a,i.messages&&(r.messages[s.name]=t.extend(r.messages[s.name],i.messages));break;case"remove":if(!i)return delete n[s.name],a;var u={};return t.each(i.split(/\s/),function(t,e){u[e]=a[e],delete a[e]}),u}}var o=t.validator.normalizeRules(t.extend({},t.validator.classRules(s),t.validator.attributeRules(s),t.validator.dataRules(s),t.validator.staticRules(s)),s);if(o.required){var l=o.required;delete o.required,o=t.extend({required:l},o)}return o}}),t.extend(t.expr[":"],{blank:function(e){return!t.trim(""+t(e).val())},filled:function(e){return!!t.trim(""+t(e).val())},unchecked:function(e){return!t(e).prop("checked")}}),t.validator=function(e,i){this.settings=t.extend(!0,{},t.validator.defaults,e),this.currentForm=i,this.init()},t.validator.format=function(e,i){return 1===arguments.length?function(){var i=t.makeArray(arguments);return i.unshift(e),t.validator.format.apply(this,i)}:(arguments.length>2&&i.constructor!==Array&&(i=t.makeArray(arguments).slice(1)),i.constructor!==Array&&(i=[i]),t.each(i,function(t,i){e=e.replace(RegExp("\\{"+t+"\\}","g"),function(){return i})}),e)},t.extend(t.validator,{defaults:{messages:{},groups:{},rules:{},errorClass:"error",validClass:"valid",errorElement:"label",focusInvalid:!0,errorContainer:t([]),errorLabelContainer:t([]),onsubmit:!0,ignore:":hidden",ignoreTitle:!1,onfocusin:function(t){this.lastActive=t,this.settings.focusCleanup&&!this.blockFocusCleanup&&(this.settings.unhighlight&&this.settings.unhighlight.call(this,t,this.settings.errorClass,this.settings.validClass),this.addWrapper(this.errorsFor(t)).hide())},onfocusout:function(t){this.checkable(t)||!(t.name in this.submitted)&&this.optional(t)||this.element(t)},onkeyup:function(t,e){(9!==e.which||""!==this.elementValue(t))&&(t.name in this.submitted||t===this.lastElement)&&this.element(t)},onclick:function(t){t.name in this.submitted?this.element(t):t.parentNode.name in this.submitted&&this.element(t.parentNode)},highlight:function(e,i,s){"radio"===e.type?this.findByName(e.name).addClass(i).removeClass(s):t(e).addClass(i).removeClass(s)},unhighlight:function(e,i,s){"radio"===e.type?this.findByName(e.name).removeClass(i).addClass(s):t(e).removeClass(i).addClass(s)}},setDefaults:function(e){t.extend(t.validator.defaults,e)},messages:{required:"This field is required.",remote:"Please fix this field.",email:"Please enter a valid email address.",url:"Please enter a valid URL.",date:"Please enter a valid date.",dateISO:"Please enter a valid date (ISO).",number:"Please enter a valid number.",digits:"Please enter only digits.",creditcard:"Please enter a valid credit card number.",equalTo:"Please enter the same value again.",maxlength:t.validator.format("Please enter no more than {0} characters."),minlength:t.validator.format("Please enter at least {0} characters."),rangelength:t.validator.format("Please enter a value between {0} and {1} characters long."),range:t.validator.format("Please enter a value between {0} and {1}."),max:t.validator.format("Please enter a value less than or equal to {0}."),min:t.validator.format("Please enter a value greater than or equal to {0}.")},autoCreateRanges:!1,prototype:{init:function(){function e(e){var i=t.data(this[0].form,"validator"),s="on"+e.type.replace(/^validate/,"");i.settings[s]&&i.settings[s].call(i,this[0],e)}this.labelContainer=t(this.settings.errorLabelContainer),this.errorContext=this.labelContainer.length&&this.labelContainer||t(this.currentForm),this.containers=t(this.settings.errorContainer).add(this.settings.errorLabelContainer),this.submitted={},this.valueCache={},this.pendingRequest=0,this.pending={},this.invalid={},this.reset();var i=this.groups={};t.each(this.settings.groups,function(e,s){"string"==typeof s&&(s=s.split(/\s/)),t.each(s,function(t,s){i[s]=e})});var s=this.settings.rules;t.each(s,function(e,i){s[e]=t.validator.normalizeRule(i)}),t(this.currentForm).validateDelegate(":text, [type='password'], [type='file'], select, textarea, [type='number'], [type='search'] ,[type='tel'], [type='url'], [type='email'], [type='datetime'], [type='date'], [type='month'], [type='week'], [type='time'], [type='datetime-local'], [type='range'], [type='color'] ","focusin focusout keyup",e).validateDelegate("[type='radio'], [type='checkbox'], select, option","click",e),this.settings.invalidHandler&&t(this.currentForm).bind("invalid-form.validate",this.settings.invalidHandler)},form:function(){return this.checkForm(),t.extend(this.submitted,this.errorMap),this.invalid=t.extend({},this.errorMap),this.valid()||t(this.currentForm).triggerHandler("invalid-form",[this]),this.showErrors(),this.valid()},checkForm:function(){this.prepareForm();for(var t=0,e=this.currentElements=this.elements();e[t];t++)this.check(e[t]);return this.valid()},element:function(e){e=this.validationTargetFor(this.clean(e)),this.lastElement=e,this.prepareElement(e),this.currentElements=t(e);var i=this.check(e)!==!1;return i?delete this.invalid[e.name]:this.invalid[e.name]=!0,this.numberOfInvalids()||(this.toHide=this.toHide.add(this.containers)),this.showErrors(),i},showErrors:function(e){if(e){t.extend(this.errorMap,e),this.errorList=[];for(var i in e)this.errorList.push({message:e[i],element:this.findByName(i)[0]});this.successList=t.grep(this.successList,function(t){return!(t.name in e)})}this.settings.showErrors?this.settings.showErrors.call(this,this.errorMap,this.errorList):this.defaultShowErrors()},resetForm:function(){t.fn.resetForm&&t(this.currentForm).resetForm(),this.submitted={},this.lastElement=null,this.prepareForm(),this.hideErrors(),this.elements().removeClass(this.settings.errorClass).removeData("previousValue")},numberOfInvalids:function(){return this.objectLength(this.invalid)},objectLength:function(t){var e=0;for(var i in t)e++;return e},hideErrors:function(){this.addWrapper(this.toHide).hide()},valid:function(){return 0===this.size()},size:function(){return this.errorList.length},focusInvalid:function(){if(this.settings.focusInvalid)try{t(this.findLastActive()||this.errorList.length&&this.errorList[0].element||[]).filter(":visible").focus().trigger("focusin")}catch(e){}},findLastActive:function(){var e=this.lastActive;return e&&1===t.grep(this.errorList,function(t){return t.element.name===e.name}).length&&e},elements:function(){var e=this,i={};return t(this.currentForm).find("input, select, textarea").not(":submit, :reset, :image, [disabled]").not(this.settings.ignore).filter(function(){return!this.name&&e.settings.debug&&window.console&&console.error("%o has no name assigned",this),this.name in i||!e.objectLength(t(this).rules())?!1:(i[this.name]=!0,!0)})},clean:function(e){return t(e)[0]},errors:function(){var e=this.settings.errorClass.replace(" ",".");return t(this.settings.errorElement+"."+e,this.errorContext)},reset:function(){this.successList=[],this.errorList=[],this.errorMap={},this.toShow=t([]),this.toHide=t([]),this.currentElements=t([])},prepareForm:function(){this.reset(),this.toHide=this.errors().add(this.containers)},prepareElement:function(t){this.reset(),this.toHide=this.errorsFor(t)},elementValue:function(e){var i=t(e).attr("type"),s=t(e).val();return"radio"===i||"checkbox"===i?t("input[name='"+t(e).attr("name")+"']:checked").val():"string"==typeof s?s.replace(/\r/g,""):s},check:function(e){e=this.validationTargetFor(this.clean(e));var i,s=t(e).rules(),r=!1,n=this.elementValue(e);for(var a in s){var u={method:a,parameters:s[a]};try{if(i=t.validator.methods[a].call(this,n,e,u.parameters),"dependency-mismatch"===i){r=!0;continue}if(r=!1,"pending"===i)return this.toHide=this.toHide.not(this.errorsFor(e)),void 0;if(!i)return this.formatAndAdd(e,u),!1}catch(o){throw this.settings.debug&&window.console&&console.log("Exception occurred when checking element "+e.id+", check the '"+u.method+"' method.",o),o}}return r?void 0:(this.objectLength(s)&&this.successList.push(e),!0)},customDataMessage:function(e,i){return t(e).data("msg-"+i.toLowerCase())||e.attributes&&t(e).attr("data-msg-"+i.toLowerCase())},customMessage:function(t,e){var i=this.settings.messages[t];return i&&(i.constructor===String?i:i[e])},findDefined:function(){for(var t=0;arguments.length>t;t++)if(void 0!==arguments[t])return arguments[t];return void 0},defaultMessage:function(e,i){return this.findDefined(this.customMessage(e.name,i),this.customDataMessage(e,i),!this.settings.ignoreTitle&&e.title||void 0,t.validator.messages[i],"<strong>Warning: No message defined for "+e.name+"</strong>")},formatAndAdd:function(e,i){var s=this.defaultMessage(e,i.method),r=/\$?\{(\d+)\}/g;"function"==typeof s?s=s.call(this,i.parameters,e):r.test(s)&&(s=t.validator.format(s.replace(r,"{$1}"),i.parameters)),this.errorList.push({message:s,element:e}),this.errorMap[e.name]=s,this.submitted[e.name]=s},addWrapper:function(t){return this.settings.wrapper&&(t=t.add(t.parent(this.settings.wrapper))),t},defaultShowErrors:function(){var t,e;for(t=0;this.errorList[t];t++){var i=this.errorList[t];this.settings.highlight&&this.settings.highlight.call(this,i.element,this.settings.errorClass,this.settings.validClass),this.showLabel(i.element,i.message)}if(this.errorList.length&&(this.toShow=this.toShow.add(this.containers)),this.settings.success)for(t=0;this.successList[t];t++)this.showLabel(this.successList[t]);if(this.settings.unhighlight)for(t=0,e=this.validElements();e[t];t++)this.settings.unhighlight.call(this,e[t],this.settings.errorClass,this.settings.validClass);this.toHide=this.toHide.not(this.toShow),this.hideErrors(),this.addWrapper(this.toShow).show()},validElements:function(){return this.currentElements.not(this.invalidElements())},invalidElements:function(){return t(this.errorList).map(function(){return this.element})},showLabel:function(e,i){var s=this.errorsFor(e);s.length?(s.removeClass(this.settings.validClass).addClass(this.settings.errorClass),s.html(i)):(s=t("<"+this.settings.errorElement+">").attr("for",this.idOrName(e)).addClass(this.settings.errorClass).html(i||""),this.settings.wrapper&&(s=s.hide().show().wrap("<"+this.settings.wrapper+"/>").parent()),this.labelContainer.append(s).length||(this.settings.errorPlacement?this.settings.errorPlacement(s,t(e)):s.insertAfter(e))),!i&&this.settings.success&&(s.text(""),"string"==typeof this.settings.success?s.addClass(this.settings.success):this.settings.success(s,e)),this.toShow=this.toShow.add(s)},errorsFor:function(e){var i=this.idOrName(e);return this.errors().filter(function(){return t(this).attr("for")===i})},idOrName:function(t){return this.groups[t.name]||(this.checkable(t)?t.name:t.id||t.name)},validationTargetFor:function(t){return this.checkable(t)&&(t=this.findByName(t.name).not(this.settings.ignore)[0]),t},checkable:function(t){return/radio|checkbox/i.test(t.type)},findByName:function(e){return t(this.currentForm).find("[name='"+e+"']")},getLength:function(e,i){switch(i.nodeName.toLowerCase()){case"select":return t("option:selected",i).length;case"input":if(this.checkable(i))return this.findByName(i.name).filter(":checked").length}return e.length},depend:function(t,e){return this.dependTypes[typeof t]?this.dependTypes[typeof t](t,e):!0},dependTypes:{"boolean":function(t){return t},string:function(e,i){return!!t(e,i.form).length},"function":function(t,e){return t(e)}},optional:function(e){var i=this.elementValue(e);return!t.validator.methods.required.call(this,i,e)&&"dependency-mismatch"},startRequest:function(t){this.pending[t.name]||(this.pendingRequest++,this.pending[t.name]=!0)},stopRequest:function(e,i){this.pendingRequest--,0>this.pendingRequest&&(this.pendingRequest=0),delete this.pending[e.name],i&&0===this.pendingRequest&&this.formSubmitted&&this.form()?(t(this.currentForm).submit(),this.formSubmitted=!1):!i&&0===this.pendingRequest&&this.formSubmitted&&(t(this.currentForm).triggerHandler("invalid-form",[this]),this.formSubmitted=!1)},previousValue:function(e){return t.data(e,"previousValue")||t.data(e,"previousValue",{old:null,valid:!0,message:this.defaultMessage(e,"remote")})}},classRuleSettings:{required:{required:!0},email:{email:!0},url:{url:!0},date:{date:!0},dateISO:{dateISO:!0},number:{number:!0},digits:{digits:!0},creditcard:{creditcard:!0}},addClassRules:function(e,i){e.constructor===String?this.classRuleSettings[e]=i:t.extend(this.classRuleSettings,e)},classRules:function(e){var i={},s=t(e).attr("class");return s&&t.each(s.split(" "),function(){this in t.validator.classRuleSettings&&t.extend(i,t.validator.classRuleSettings[this])}),i},attributeRules:function(e){var i={},s=t(e),r=s[0].getAttribute("type");for(var n in t.validator.methods){var a;"required"===n?(a=s.get(0).getAttribute(n),""===a&&(a=!0),a=!!a):a=s.attr(n),/min|max/.test(n)&&(null===r||/number|range|text/.test(r))&&(a=Number(a)),a?i[n]=a:r===n&&"range"!==r&&(i[n]=!0)}return i.maxlength&&/-1|2147483647|524288/.test(i.maxlength)&&delete i.maxlength,i},dataRules:function(e){var i,s,r={},n=t(e);for(i in t.validator.methods)s=n.data("rule-"+i.toLowerCase()),void 0!==s&&(r[i]=s);return r},staticRules:function(e){var i={},s=t.data(e.form,"validator");return s.settings.rules&&(i=t.validator.normalizeRule(s.settings.rules[e.name])||{}),i},normalizeRules:function(e,i){return t.each(e,function(s,r){if(r===!1)return delete e[s],void 0;if(r.param||r.depends){var n=!0;switch(typeof r.depends){case"string":n=!!t(r.depends,i.form).length;break;case"function":n=r.depends.call(i,i)}n?e[s]=void 0!==r.param?r.param:!0:delete e[s]}}),t.each(e,function(s,r){e[s]=t.isFunction(r)?r(i):r}),t.each(["minlength","maxlength"],function(){e[this]&&(e[this]=Number(e[this]))}),t.each(["rangelength","range"],function(){var i;e[this]&&(t.isArray(e[this])?e[this]=[Number(e[this][0]),Number(e[this][1])]:"string"==typeof e[this]&&(i=e[this].split(/[\s,]+/),e[this]=[Number(i[0]),Number(i[1])]))}),t.validator.autoCreateRanges&&(e.min&&e.max&&(e.range=[e.min,e.max],delete e.min,delete e.max),e.minlength&&e.maxlength&&(e.rangelength=[e.minlength,e.maxlength],delete e.minlength,delete e.maxlength)),e},normalizeRule:function(e){if("string"==typeof e){var i={};t.each(e.split(/\s/),function(){i[this]=!0}),e=i}return e},addMethod:function(e,i,s){t.validator.methods[e]=i,t.validator.messages[e]=void 0!==s?s:t.validator.messages[e],3>i.length&&t.validator.addClassRules(e,t.validator.normalizeRule(e))},methods:{required:function(e,i,s){if(!this.depend(s,i))return"dependency-mismatch";if("select"===i.nodeName.toLowerCase()){var r=t(i).val();return r&&r.length>0}return this.checkable(i)?this.getLength(e,i)>0:t.trim(e).length>0},email:function(t,e){return this.optional(e)||/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i.test(t)},url:function(t,e){return this.optional(e)||/^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(t)},date:function(t,e){return this.optional(e)||!/Invalid|NaN/.test(""+new Date(t))},dateISO:function(t,e){return this.optional(e)||/^\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}$/.test(t)},number:function(t,e){return this.optional(e)||/^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(t)},digits:function(t,e){return this.optional(e)||/^\d+$/.test(t)},creditcard:function(t,e){if(this.optional(e))return"dependency-mismatch";if(/[^0-9 \-]+/.test(t))return!1;var i=0,s=0,r=!1;t=t.replace(/\D/g,"");for(var n=t.length-1;n>=0;n--){var a=t.charAt(n);s=parseInt(a,10),r&&(s*=2)>9&&(s-=9),i+=s,r=!r}return 0===i%10},minlength:function(e,i,s){var r=t.isArray(e)?e.length:this.getLength(t.trim(e),i);return this.optional(i)||r>=s},maxlength:function(e,i,s){var r=t.isArray(e)?e.length:this.getLength(t.trim(e),i);return this.optional(i)||s>=r},rangelength:function(e,i,s){var r=t.isArray(e)?e.length:this.getLength(t.trim(e),i);return this.optional(i)||r>=s[0]&&s[1]>=r},min:function(t,e,i){return this.optional(e)||t>=i},max:function(t,e,i){return this.optional(e)||i>=t},range:function(t,e,i){return this.optional(e)||t>=i[0]&&i[1]>=t},equalTo:function(e,i,s){var r=t(s);return this.settings.onfocusout&&r.unbind(".validate-equalTo").bind("blur.validate-equalTo",function(){t(i).valid()}),e===r.val()},remote:function(e,i,s){if(this.optional(i))return"dependency-mismatch";var r=this.previousValue(i);if(this.settings.messages[i.name]||(this.settings.messages[i.name]={}),r.originalMessage=this.settings.messages[i.name].remote,this.settings.messages[i.name].remote=r.message,s="string"==typeof s&&{url:s}||s,r.old===e)return r.valid;r.old=e;var n=this;this.startRequest(i);var a={};return a[i.name]=e,t.ajax(t.extend(!0,{url:s,mode:"abort",port:"validate"+i.name,dataType:"json",data:a,success:function(s){n.settings.messages[i.name].remote=r.originalMessage;var a=s===!0||"true"===s;if(a){var u=n.formSubmitted;n.prepareElement(i),n.formSubmitted=u,n.successList.push(i),delete n.invalid[i.name],n.showErrors()}else{var o={},l=s||n.defaultMessage(i,"remote");o[i.name]=r.message=t.isFunction(l)?l(e):l,n.invalid[i.name]=!0,n.showErrors(o)}r.valid=a,n.stopRequest(i,a)}},s)),"pending"}}}),t.format=t.validator.format})(jQuery),function(t){var e={};if(t.ajaxPrefilter)t.ajaxPrefilter(function(t,i,s){var r=t.port;"abort"===t.mode&&(e[r]&&e[r].abort(),e[r]=s)});else{var i=t.ajax;t.ajax=function(s){var r=("mode"in s?s:t.ajaxSettings).mode,n=("port"in s?s:t.ajaxSettings).port;return"abort"===r?(e[n]&&e[n].abort(),e[n]=i.apply(this,arguments),e[n]):i.apply(this,arguments)}}}(jQuery),function(t){t.extend(t.fn,{validateDelegate:function(e,i,s){return this.bind(i,function(i){var r=t(i.target);return r.is(e)?s.apply(r,arguments):void 0})}})}(jQuery);

/** */
jQuery( document ).ready(function( $ ){

    /**
     * Set-up our default Ajax options.
     * Please reference http://api.jquery.com/jQuery.ajaxSetup/
     */
    $.ajaxSetup({
        type: "POST",
        url: sell_media.ajaxurl
    });


    /**
     * Our global user object
     */
    var _user = {
        "count": cart_count()
    };


    jQuery.validator.addMethod("email_exists", function(value,element){
        $('.sell-media-error').remove();
        $this = $('#sell_media_checkout_form');
        var response;
        $.ajax({
            async: false,
            url: sell_media.ajax_url,
            data: {
                email: $('#sell_media_email_field').val(),
                action: 'sell_media_check_email',
                security: $('#sell_media_cart_nonce').val()
            },
            success: function( msg ){
                if ( msg.status == 1 ){
                    response = false;
                } else {
                    response = true;
                }
            }
        });
        return response;
    }, sell_media.error.email_exists);

	// Checkout Country Select Fields
	$('#sell_media_country').change(function(){
	    if($(this).val() == 'US'){
	        $('#sell_media_reprints_sf_state_wrap').show();
	        $('#sell_media_reprints_sf_other_provience_wrap').hide();
	    } else {
	        $('#sell_media_reprints_sf_state_wrap').hide();
	        $('#sell_media_reprints_sf_other_provience_wrap').show();
	    }
	});

    // Validation
    $('#sell_media_checkout_form').validate({
        rules: {
            email: {
                required: true,
                email_exists: true
            }
        }
    });


    /**
     * Determine the price of an Item based on the below formula:
     *
     * amount = price + (( license_markup * .01 ) * price ))
     *
     */
    function calculate_total( license_markup, price ){

        if ( typeof( license_markup ) == "undefined" ) license_markup = 0;

        current_total = sell_media.cart.subtotal;

        // Don't use the license_markup on the checkout table
        if ( $('#sell-media-checkout-table').length ){
            finalPrice = ( +price ).toFixed(2);
        } else {
            finalPrice = ( +price + +current_total + ( +license_markup * .01 ) * price ).toFixed(2);
        }

        if ( $('.subtotal-target').length ){
            $('.subtotal-target').html( finalPrice );
            $('.subtotal-target').val( finalPrice );
        }

        if ( $('.sell-media-item-price').length ){
            $('.sell-media-item-price').html( finalPrice );
            $('.sell-media-item-price').val( finalPrice );
        }

        return finalPrice;
    }


    /**
     * Send an Ajax request to our function to update the users cart count
     */
    function cart_count(){
        $.ajax({
            data: "action=sell_media_count_cart",
            success: function( msg ){
                _user.count = msg;
                $('.count-target').html( msg );
                $('.menu-cart-items').html( msg );
            }
        });
    }


    /**
     * Calculate the total for each Item
     */
    function total_items(){
        var current = 0;
        var total = 0;

        if ( $('.item-price-target').length ){
            $( '.item-price-target' ).each(function( index ){
                current = ( +current ) + ( parseFloat( $(this).html() ) );
                total = ( +total ) + ( +current );
                final_total = current.toFixed(2);
            });
        } else {
            final_total = "0.00";
        }

        sell_media.cart.subtotal = final_total;

        $( '.subtotal-target' ).html( final_total );
    }


    /**
     * Retrieves the x, y coordinates of the viewport
     * getPageScroll() by quirksmode.com
     */
    function sell_media_get_page_scroll() {
        var xScroll, yScroll;
        if (self.pageYOffset) {
          yScroll = self.pageYOffset;
          xScroll = self.pageXOffset;
        } else if (document.documentElement && document.documentElement.scrollTop) {
          yScroll = document.documentElement.scrollTop;
          xScroll = document.documentElement.scrollLeft;
        } else if (document.body) {// all other Explorers
          yScroll = document.body.scrollTop;
          xScroll = document.body.scrollLeft;
        }
        return new Array(xScroll,yScroll)
    }


    /**
     * Calculate our total, round it to the nearest hundreds
     * and update the html our price target.
     */
    function sell_media_update_total(){
        var total = 0;
        if ( $('.item-price-target').length ){
            $('.item-price-target').each(function(){
                total = +( $(this).text() ) + +total;
            });

            if ( $('#sell-media-checkout-table').length ){
                total = +total;
            } else {
                total = +sell_media.cart.subtotal + +total;
            }
        } else {
            total = +sell_media.cart.subtotal;
        }

        $('.subtotal-target').html( total.toFixed(2) );
        $('.menu-cart-total').html( total.toFixed(2) );

        $('.sell-media-item-price').html( total.toFixed(2) );
    }


    /**
     * Update our sub-total, if our sub-total is less than 0 we set
     * it to ''. Then update the html of our sub-total target.
     */
    function sell_media_update_sub_total(){
        $('.sell-media-quantity').each(function(){
            item_id = $(this).attr('data-id');

            if ( typeof( $(this).attr('data-markup') ) == "undefined" || $(this).attr('data-markup') == 0 ){
                price = $(this).attr('data-price');
            } else {
                price = calculate_total( $(this).attr('data-markup'), $(this).attr('data-price') );
            }
            qty = +$('#quantity-' + item_id ).val();

            sub_total = price * qty;

            if ( sub_total <= 0 )
                sub_total = 0;

            $( '#sub-total-target-' + item_id ).html( sub_total.toFixed(2) );
        });
    }

    /**
     * Updates a div with the class name called 'menu-cart-items' to have
     * the total number of items.
     */
    function sell_media_quantity_total(){
        var total = 0;
        if ( $('.sell-media-quantity').length ){
            $('.sell-media-quantity').each(function(){
                item_id = $(this).attr('data-id');
                qty = +$('#quantity-' + item_id ).val();
                total = total + qty;
            });
        } else {
            total = sell_media.cart.quantity;
        }

        if ( $('.menu-cart-items').length )
            $('.menu-cart-items').html( total );
    }

    /**
     * Add subtotal and shipping together
     */
    function sell_media_update_final_total(){
        total = +$('.subtotal-target').text() + +$('.shipping-target').text();
        $('.total-target').html( total.toFixed(2) );
    }


    /**
     * Run the following code below the DOM is ready update the cart count
     */
    sell_media_update_total();
    sell_media_update_final_total();
    sell_media_quantity_total();


    /**
     * When the user clicks on our trigger we set-up the overlay,
     * launch our dialog, and send an Ajax request to load our cart form.
     */
    $( document ).on( 'click', '.sell-media-cart-trigger', function( event ){
        event.preventDefault();

        // Overlay set-up
        coordinates = sell_media_get_page_scroll();
        y = coordinates[1] + +100;
        x = ( $(window).width() / 2 ) - ( $( '.sell-media-cart-dialog' ).outerWidth() / 2 );
        $('.sell-media-cart-dialog').css({
            'top': y + 'px',
            'left': x + 'px'
        });


        // Show our dialog with a loading message
        $('.sell-media-cart-dialog').toggle();
        $( ".sell-media-cart-dialog-target" ).html( '<div class="sell-media-ajax-loader">Loading...</div>' );


        // Send Ajax request for Shopping Cart
        $.ajax({
            data: {
                "action": "sell_media_load_template",
                "template": "cart.php",
                "product_id": $( this ).attr( 'data-sell_media-product-id' ),
                "attachment_id": $( this ).attr( 'data-sell_media-thumb-id' )
            },
            success: function( msg ){
                $( ".sell-media-cart-dialog-target" ).fadeIn().html( msg ); // Give a smooth fade in effect
                cart_count();
                if ($('#download #sell_media_size_select').length) {
                    $('#sell_media_license_select').attr('disabled', true);
                };
                if ($('#download #sell_media_size_select').length || $('#download #sell_media_license_select').length) {
                    $('.sell-media-buy-button').attr('disabled', true);
                };
            }
        });


        // Add our overlay to the html if #overlay is present.
        if($('#overlay').length > 0){
            $('#overlay').remove();
        } else {
            $('body').append('<div id="overlay"></div>');
            var doc_height = $(document).height();
            $('#overlay').height(doc_height);
            $('#overlay').click(function(){
                $('.sell-media-cart-dialog').toggle();
                $(this).remove();
            });
        }
    });


    $( document ).on( 'click', '.close', function(){
        $('.sell-media-cart-dialog').hide();
        $('#overlay').remove();
    });


    /**
     * On change run the calculate_total() function
     */
    $( document ).on('change', '#sell_media_license_select', function(){
        var price;
        var size = $('#sell_media_size_select :selected').attr('data-price');

        if ( typeof( size ) === "undefined" )
            size = $('input[name="CalculatedPrice"]').val();

        var license_desc = $('#sell_media_license_select :selected').attr('title');

        $("option:selected", this).each(function(){
            price = $(this).attr('data-price');
            calculate_total( price, size );
        });

        $(this).parent().find(".license_desc").attr('data-tooltip', license_desc);
        if ( license_desc == '' ){
            $(this).parent().find(".license_desc").hide();
        } else {
            $(this).parent().find(".license_desc").show();
        }

        // If we have a value enable the buy button, if we don't disable it
        if ( $("option:selected", this).val() ){
            $('.sell-media-buy-button').removeAttr('disabled');
        } else {
            $('.sell-media-buy-button').attr('disabled', true);
        }
    });


    /**
     * On change make sure the license has a value
     */
    $( document ).on('change', '#sell_media_size_select', function(){

        /**
         * Derive the license from the select
         * or from the single item
         */
        if ( $('#sell_media_single_license_markup').length ){
            license = $('#sell_media_single_license_markup').val();
        } else if( $('#sell_media_license_select').length ){
            license = $('#sell_media_license_select :selected').attr('data-price');
        } else {
            license = null;
        }

        $("option:selected", this).each(function(){
            size = $(this).attr('data-price');
            calculate_total( license, size );
        });

        size = $('#sell_media_size_select :selected').attr('data-price');

        // if no size disable the add to cart button
        // and the license select
        if ( size == 0 && license != null ){
            $('.sell-media-buy-button').attr('disabled', true);
            $('#sell_media_license_select').attr('disabled', true);
        }

        // Check if multiple licenses are in use, else we enable the
        // buy button
        if ( $('#sell_media_license_select').length ) {
            if ( size != 0 && license >= 0 ) {
                $('#sell_media_license_select').removeAttr('disabled');
            }
        } else {
            if ( size != 0 && license >= 0 ) {
                $('.sell-media-buy-button').removeAttr('disabled');
            }
        }

        // user selected a size, but there's no license to select
        if ( size != 0 && license == null ){
            $('.sell-media-buy-button').removeAttr('disabled');
        }

    });


    $( document ).on('submit', '.sell-media-dialog-form', function(){
        $('.sell-media-buy-button').attr('disabled',true);

        var _data = "action=add_items&taxonomy=licenses&" + $( this ).serialize();

        if ( _user.count < 1 ) {
            text = '(<span class="count-container"><span class="count-target"></span></span>)';
            $('.empty').html( text );
            $('.cart-handle').show();
        }

        $.ajax({
            data: _data,
            success: function( msg ) {

                sell_media.cart.subtotal = msg.data.cart.subtotal;

                cart_count();
                // sell_media_update_total();

                total = ( +( $('.menu-cart-total').html() ) + +( $('.sell-media-item-price').html() ) );
                $('.menu-cart-total').html( total.toFixed(2) );
                $('.sell-media-buy-button').removeAttr('disabled');

                if ( $('#sell_media_size_select').length )
                    $('#sell_media_size_select').val('');

                if ( $('#sell_media_license_select').length )
                    $('#sell_media_license_select').val('');

                if ( $('.sell-media-quantity') ){
                    $('.sell-media-quantity').each(function(){
                        $(this).val('');
                    });
                }
            }
        });

        return false;
    });


    $( document ).on('click', '.remove-item-handle', function(){

        $(this).closest('tr').remove();

        count = $(".sell_media-product-list li").size();

        if( count == 1 ) {
            $('.subtotal-target').html('0');
            $('.sell-media-buy-button-checkout').fadeOut();
        }

        data = {
            action: "remove_item",
            item_id: $(this).attr('data-item_id')
        };

        $.ajax({
            data: data,
            success: function( msg ){
                // We have no items in the cart
                if ( msg ){
                    $('#sell-media-checkout').html( msg );
                }

                total_items();
                sell_media_update_final_total();
                sell_media_quantity_total();
                sell_media_update_total();
            }
        });
    });

    $( document ).on('click', '.remove-all-handle', function( e ){
        e.preventDefault();

        var item_ids = [];
        $('.remove-item-handle').each(function(){
            item_ids.push( $( this ).attr('data-item_id') );
        });

        $.ajax({
            data: {
                action: "remove_item",
                item_id: item_ids
            },
            success: function( msg ){
                // We have no items in the cart
                if ( msg ){
                    $('#sell-media-checkout').html( msg );
                }
            }
        });
    });

    $( document ).on('click', '.cart-handle', function(){
        $.ajax({
            data: "action=sell_media_show_cart",
            success: function( msg ){
                $('.product-target-tmp').fadeOut();
                $('.cart-target-tmp').fadeIn().html( msg );
                total_items();
            }
        });
    });

    $( document ).on('click', '#checkout_handle', function(){
        $('.cart-container').hide();
        $('.payment-form-container').show();
    });


    $("#sell-media-checkout table tr:nth-child(odd)").addClass("odd-row");
    $("#sell-media-checkout table td:first-child, #sell-media-checkout table th:first-child").addClass("first");
    $("#sell-media-checkout table td:last-child, #sell-media-checkout table th:last-child").addClass("last");


    /**
     * If the user clicks inside of our input box and manually updates the quantiy
     * we run the sub-total and total functions.
     */
    $(document).on('keyup', '.sell-media-quantity', function(){

        sell_media_update_sub_total();
        sell_media_update_total();
        sell_media_update_final_total();
        sell_media_quantity_total();
        if ( $(this).val() > 0 ){
            $('.sell-media-buy-button').removeAttr('disabled');
        } else {
            $('.sell-media-buy-button').attr('disabled', true);
        }
    });


    $( document ).on( 'submit', '#sell_media_checkout_form', function() {
        var faults = $( 'input' ).filter( function() {
            return $( this ).data( 'required' ) && $( this ).val() === '';
        }).css( 'background-color', 'red');
        if ( faults.length ) return false;
    });


    /**
     * When the user clicks on our trigger we set-up the overlay,
     * launch our dialog, and send an Ajax request to load our cart form.
     */
    $( document ).on( 'click', '#agree_terms_and_conditions', function( event ){
        event.preventDefault();

        // Overlay set-up
        coordinates = sell_media_get_page_scroll();
        y = coordinates[1] + +100;
        x = ( $(window).width() / 2 ) - ( $( '#terms-and-conditions-dialog' ).outerWidth() / 2 );
        $('#terms-and-conditions-dialog').css({
            'top': y + 'px',
            'left': x + 'px'
        });

        // Show our dialog with a loading message
        $('#terms-and-conditions-dialog').toggle();

        // Add our overlay to the html if #overlay is present.
        if($('#overlay').length > 0){
            $('#overlay').remove();
        } else {
            $('body').append('<div id="overlay"></div>');
            var doc_height = $(document).height();
            $('#overlay').height(doc_height);
            $('#overlay').click(function(){
                $('#terms-and-conditions-dialog').toggle();
                $(this).remove();
            });
        }
    });

    $( document ).on( 'click', '.close', function(){
        $('#terms-and-conditions-dialog').hide();
        $('#overlay').remove();
    });


    /**
     * Hide our current search option when the user clicks off the input field
     */
    $( document ).on('blur', '#s', function(){
        $(".sell-media-search-options", this).hide();
    });


    $( document ).on('click', '.sell-media-search-options-trigger', function(e){
        e.preventDefault();
        $(this).closest('.sell-media-search-form').find('.sell-media-search-options:first').toggle();
     });


    $( document ).on('change', '.post_type_selector', function(){

        /**
         * Cache the objects for later use.
         */
        $collection = $('#collection_select');
        $keywords = $('#keywords_select');


        /**
         * We store the field name as an attribute since will toggle it later.
         * For our purposes its easier to just remove the name attribute so it
         * isn't sent to PHP in $_POST
         */
        if ( $('.sell-media-search-taxonomies').css('display') == 'block' ){
            $('.sell-media-search-taxonomies').hide();

            $collection.attr('name','');
            $keywords.attr('name','');
        } else {
            $('.sell-media-search-taxonomies').show();

            $collection.attr('name', $collection.attr( 'data-name') );
            $keywords.attr('name', $keywords.attr( 'data-name' ) );
        }
     });



    $('#sell_media_terms_cb').on('click', function(){
        $this = $(this);
        $this.val() == 'checked' ? $this.val('') : $this.val('checked');
    });

});