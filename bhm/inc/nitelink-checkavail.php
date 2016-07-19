<?php 
/* Nitelink stuff 
* 	There are 3 shortcodes:
* 		[nitelink_check_avail] - put this on the page where the check availibility box will show
*
*		[nitelink_show_avail] - put this on the page where the results of the check availability 
*								will display & any error messages from the checking avail
*
* 		[nitelink_bookit] - put this on the page if you want to put the bookit on the page. TBD
*
*/
if (!function_exists('check_avail_function') ) {
	/* used in to display the check availibity form  */		
	function check_avail_function ($atts) {
		global $default_arrive, $default_arriveYMD, $default_days, $default_adults, $default_kids;
		global $form_error_msg, $html_avail_result;

		/* $a = shortcode_atts( array(
	        'title' => 'Room Availability',
	        'unvoted' => true // by default show the unvoted 
	    ), $atts ); 
	    $result_title = $a['title']; */

		$htmlreturn = '';

		$htmlreturn .= '<div id="bhm-check_avail_box" class="nitelink-box">';
		$htmlreturn .= '<h2 class="bhm-check_avail_title">Check Availability</h3>';
		$htmlreturn .= '<form method="post" id="bhm-check-avail-form" action="'.get_the_permalink().'">';
		//$htmlreturn .= 		'<input type='hidden' name='user_id' value='BHM' />';
		//$htmlreturn .= 		'<input type='hidden' name='user_pwd' value='1234' />';
		$htmlreturn .= 		'<table id="form-check-avail"><tdbody>';
		$htmlreturn .=         	'<tr class="form-date bhm-arrival-date">';
	    $htmlreturn .=        		'<th><label for="arrive">Arrival</label></th>';
	    $htmlreturn .=        		'<td>';
	    $htmlreturn .=        			'<input class="text-input" name="arrive" type="text" id="arrive" value="'.$default_arrive.'" />';
/* 	 works differently in IE vs Chrome....   $htmlreturn .=        			'<input name="arrive" type="date" id="arrive" value="'.$default_arriveYMD.'" />'; */
	    $htmlreturn .=        		'</td>';
	    $htmlreturn .=        	'</tr>'; 
	    $htmlreturn .=        	'<tr class="form-numbdays">';
	    $htmlreturn .=        		'<th><label for="numdays">Nights</label></th>';
	    $htmlreturn .=        		'<td><input class="text-input" name="numdays" type="text" id="numdays" value="'.$default_days.'" /></td>';
	    $htmlreturn .=        	'</tr>';
		$htmlreturn .=          '<tr class="form-numadults">';
		$htmlreturn .=        		'<th><label for="numadults">Adults</label></th>';
		$htmlreturn .=        		'<td><input class="text-input" name="numadults" type="text" id="numadults" value="'.$default_adults.'" /></td>';
		$htmlreturn .=        	'</tr><!-- .form-numadults -->';
		$htmlreturn .=        	'<tr class="form-numkids">';
		$htmlreturn .=        		'<th><label for="numkids">Children</label></th>';
		$htmlreturn .=        		'<td><input class="text-input" name="numkids" type="text" id="numkids" value="'.$default_kids.'" /></td>';
		$htmlreturn .=        	'</tr><!-- .form-numkids -->';                    
		$htmlreturn .=        	'<tr class="form-submit">';
		$htmlreturn .=        		'<td colspan="2" class="btn_checknow">';
		$htmlreturn .=        			'<input name="checkavail" type="submit" id="checkavail" class="blue_sidebar_link bhm-blue_sidebar_box" value="Check Now" onclick="return nitelink_validate_avail();" />';
		$htmlreturn .=        			'<input name="action" type="hidden" id="action" value="checkavail" />';
		$htmlreturn .=        		'</td>';
		$htmlreturn .=        	'</tr><!-- .form-submit -->';
		$htmlreturn .=     '</tbody></table>';
		$htmlreturn .= '</form>';
		$htmlreturn .= '<div id="bhm_avail_errormsg" class="bhm_errormsg" style="color:red;">'.$form_error_msg.'</div>';
		$htmlreturn .= '</div>'; // end of box.
		$jsreturn = '';
		$jsreturn .= '<script src="'.get_stylesheet_directory_uri().'/inc/nitelink.js"></script>';
		$jsreturn .= '<script type="text/javascript">';
		$jsreturn .= 	'function validate_avail() {';
		$jsreturn .= 		'document.getElementById("bhm_check_avail_results").style.display = "none";';
		$jsreturn .= 		'document.getElementById("bhm_avail_errormsg").style.display = "none";';
		$jsreturn .= 		'document.getElementById("bhm-check_avail_box").style.color = "red";'; // for testing
		//$jsreturn .= 		'var ok, adults, kids, nights, arrive, errmsg;';
		$jsreturn .= 		'document.getElementById("bhm_avail_errormsg").innerHTML = "test";';	
		$jsreturn .= 		'';	
		$jsreturn .= 		'return false;';
		$jsreturn .= 	'}';
		$jsreturn .= '</script>';
		$htmlreturn .= $jsreturn; 
		/* $htmlreturn .= ' <div id="available_rooms">'.$html_avail_result.'</div>'.$jsreturn; */

		return($htmlreturn);
	} /* end of function check_avail_funtion */
}
add_shortcode ('nitelink_check_avail','check_avail_function');


if (!function_exists('show_avail_function') ) {
	function show_avail_function ($atts) { 
		/* $a = shortcode_atts( array(
	        'title' => 'Room Availability',
	        'unvoted' => true // by default show the unvoted 
	    ), $atts ); 
	    $result_title = $a['title']; */
	    global $html_avail_result; 
	    $html_avail_result;
		$htmlreturn = '';
		$htmlreturn .= '<div id="nitelink_available_rooms">';
		$htmlreturn .= $html_avail_result;
		$htmlreturn .= '</div>'; // available_rooms
		/* $htmlreturn .= '<div id="nitelink_available_rooms">'.$html_avail_result.'</div>'; // starts as an empty box.  Filled later when check the availablility. */
		return ($htmlreturn);
	}
}
add_shortcode ('nitelink_show_avail','show_avail_function');

if (!function_exists('display_avail_function') ) {
	/* used in conjuction with page-temlate rates to display the check availibity form & results */		
	/* this part is used to display the check Availability box.  */
	function display_avail_function ($atts) {
	}/* end of function display_avail_function */
}
add_shortcode ('display_availability','display_avail_function');


?>