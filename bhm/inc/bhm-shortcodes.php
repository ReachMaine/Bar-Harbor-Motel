<?php 
if (!function_exists('nitelink_show_specs') ) {
	/* used in conjuction with page-temlate rates to display the check availibity form & results */		
	/* this part is used to display the check Availability box.  */
	function nitelink_show_specs ($atts) {
		$html_return = "";
		$html_return .= '<div class="bhm-avail_title">';
		$html_return .= $_SESSION['nitelink_arrive'].' to ' . $_SESSION['nitelink_depart'];
		$html_return .= '</br>';
		$html_return .= 'for'. $_SESSION['nitelink_adults'].' Adult(s) and '.$_SESSION['nitelink_kids'].' children.';
		$html_return .= '</div>';
		return $html_return;
	}/* end of function display_avail_function */
}
add_shortcode ('nitelink_show_avail_specs','nitelink_show_specs');

if (!function_exists('bhm_StartSession') ) {
	function bhm_StartSession() {
	    if(!session_id()) {
	        session_start();
	    }
	}
}
add_action('init', 'bhm_StartSession', 1);

?>