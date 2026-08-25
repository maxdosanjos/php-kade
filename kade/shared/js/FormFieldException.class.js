var FormFieldException = function(pMessage,pFieldObj,pFieldId){
	var fieldObj = pFieldObj;
	var fieldId = pFieldId;
	var message = pMessage;
	
	this.getMessage = function(){
		return message;
	}
	
	this.getFieldObj = function(){
		return fieldObj;
	}
	
	this.getFieldId = function(){
		return fieldId;
	}
}