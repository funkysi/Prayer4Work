/**
 * @author kirill
 */
var TB_monthNumber = {'Jan':1,'Feb':2,'Mar':3,'Apr':4,'May':5,'Jun':6,'Jul':7,'Aug':8,'Sep':9,'Oct':10,'Nov':11,'Dec':12},
TB_timePeriods = new Array("second", "minute", "hour", "day", "week", "month", "year", "decade"),
TB_timePeriodLengths = new Array("60","60","24","7","4.35","12","10"),
ajaxURLs = new Array(),
screenNamesCount = 0;
  
// make tabs
jQuery(document).ready(function(){
    var tabsElement = jQuery("#tabs").tabs({
	    show:function(event, ui) {
			
			// find out index
			var tabsEl = jQuery('#tabs').tabs();
			var selectedTabIndex = tabsEl.tabs('option', 'selected');

	        jQuery('#tb_tab_index').val(selectedTabIndex);
	        return true;
	    }
	});
	
	// reopen last used tab
	tabsElement.tabs('select', lastUsedTabId);

	// bind event handler to disable archive checkbox
	jQuery('#archive_is_disabled').click(function() {
		if (jQuery('#archive_is_disabled').is(':checked')) {
			jQuery('#archivesettings tr').slice(1).hide();
		}
		else {
			jQuery('#archivesettings tr').slice(1).show();
		}
	});

	// check limit for admin's PC
	jQuery.ajax({
		url: 'http://twitter.com/account/rate_limit_status.json',
		dataType: 'jsonp',
		success: function(json){
			var hitsLeftHtml = '';
			if (json.remaining_hits > 0) {
				hitsLeftHtml = 	'<span class="pass">' + json.remaining_hits + '</span>';
			}
			else {
				hitsLeftHtml = '<span class="fail">0</span>';
			}
			jQuery('#locallimit').html('Max is ' + json.hourly_limit + '/hour &middot; You have ' + hitsLeftHtml + ' left &middot; Next reset ' + TB_verbalTime(TB_str2date(json.reset_time)));
		},
		error: function(){
			jQuery('#locallimit').html('<span class="fail">Check failed</span>');
		}
	});	

	
	// if there were any problems, highlight the Status tab
	if(jQuery('span.fail').length > 0) {
		jQuery('#statustab a').children('span').addClass('fail');
	}

});


// Twitter oAuth window
function tAuth(url) {
	var tWin = window.open(url,'tWin','width=800,height=410,toolbar=0,location=1,status=0,menubar=0,resizable=1');
}

function TB_str2date(dateString) {

	var dateObj = new Date(),
	dateData = dateString.split(/[\s\:]/);
	
	// if it's a search format
	if (dateString.indexOf(',') >= 0) {
		// $wday,$mday, $mon, $year, $hour,$min,$sec,$offset
		dateObj.setUTCFullYear(dateData[3],TB_monthNumber[""+dateData[2]]-1,dateData[1]);
		dateObj.setUTCHours(dateData[4],dateData[5],dateData[6]);
	}
	// if it's a user feed format
	else {
		// $wday,$mon,$mday,$hour,$min,$sec,$offset,$year
		dateObj.setUTCFullYear(dateData[7],TB_monthNumber[""+dateData[1]]-1,dateData[2]);
		dateObj.setUTCHours(dateData[3],dateData[4],dateData[5]);
	}

	return dateObj;
}

function TB_verbalTime(dateObj) {
   
    var j,
	now = new Date(),
	difference,
	verbalTime,
	prefix = '',
	postfix = '';
	
	if (now.getTime() > dateObj.getTime()) {
		difference = Math.round((now.getTime() - dateObj.getTime()) / 1000);
		postfix = ' ago';
	}
	else {
		difference = Math.round((dateObj.getTime() - now.getTime()) / 1000);
		prefix = 'in ';
	}
		
   
    for(j = 0; difference >= TB_timePeriodLengths[j] && j < TB_timePeriodLengths.length; j++) {
        difference = difference / TB_timePeriodLengths[j];
    }
    difference = Math.round(difference);
   
    verbalTime = TB_timePeriods[j];
    if (difference != 1) {
        verbalTime += 's';
    }
   
    return prefix + difference + ' ' + verbalTime + postfix;
}
