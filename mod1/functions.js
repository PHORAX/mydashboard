function setUpdate(obj){
	if(!TYPO3.jQuery(obj).is('.ajax-update'))
		TYPO3.jQuery(obj).addClass('ajax-update');
}

function unsetUpdate(obj){
	if(!TYPO3.jQuery(obj).is('ajax-update'))
		TYPO3.jQuery(obj).removeClass('ajax-update');
}

/* refresh the Widget */
function ajaxWidgetParms(keyval, actionkey){
	setUpdate('#widget_'+keyval);
	TYPO3.jQuery.ajax(
			{
				url: document.location,
				mathod: 'POST',
				data: { ajax: 1, key: keyval, action: actionkey },
				success: function(data){
					TYPO3.jQuery('#widget_'+keyval+'_content').html(data);
					unsetUpdate('#widget_'+keyval);
				}
			}
	);
} 

function refreshWidget(keyval){
	ajaxWidgetParms(keyval, 'refresh');
	return false;
}

function configWidget(keyval){
	ajaxWidgetParms(keyval, 'config');
	return false;
}

function ajaxWidgetParmsValue(keyval, actionkey, valuekey){
	setUpdate('#widget_'+keyval);
	TYPO3.jQuery.ajax(
			{
				url: document.location,
				mathod: 'POST',
				data: { ajax: 1, key: keyval, action: actionkey , value: valuekey },
				success: function(data){
					TYPO3.jQuery('#widget_'+keyval+'_content').html(data);
					unsetUpdate('#widget_'+keyval);
				}
			}
	);
}

function sendConfForm(dashKey){
	var parms = TYPO3.jQuery('#'+dashKey+'_confform').serialize();
	ajaxWidgetParmsValue('#'+dashKey, 'refresh_config', parms);
}

function deleteWidget(keyval){
	setUpdate('#widget_'+keyval);
	TYPO3.jQuery.ajax(
			{
				url: document.location,
				mathod: 'POST',
				data: { ajax: 1, key: keyval, action: 'delete' },
				success: function(data){
					TYPO3.jQuery('#widget_'+keyval).remove();
				}
			}
	);
	return false;
}

/* Show the Widget Options */
function showOptions(widgetKey){
	if(TYPO3.jQuery('#'+widgetKey+'_config')) TYPO3.jQuery('#'+widgetKey+'_config').show();
	if(TYPO3.jQuery('#'+widgetKey+'_refresh')) TYPO3.jQuery('#'+widgetKey+'_refresh').show();
	if(TYPO3.jQuery('#'+widgetKey+'_delete')) TYPO3.jQuery('#'+widgetKey+'_delete').show();
}

/* Hide the Widget Options */
function hideOptions(widgetKey){
	if(TYPO3.jQuery('#'+widgetKey+'_config')) TYPO3.jQuery('#'+widgetKey+'_config').hide();
	if(TYPO3.jQuery('#'+widgetKey+'_refresh')) TYPO3.jQuery('#'+widgetKey+'_refresh').hide();
	if(TYPO3.jQuery('#'+widgetKey+'_delete')) TYPO3.jQuery('#'+widgetKey+'_delete').hide();
}