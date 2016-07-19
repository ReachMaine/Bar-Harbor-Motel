<?php
// Template Name: Rates2
/* copy of side-navigation for rates page to include post 
 */
$soappost = "";
$form_error_msg = "";
$html_avail_result = "";

$default_arrive = date("n/j/y");
$default_arriveYMD = date("Y-m-d");
$default_days = 1;
$default_adults = "1";
$default_kids = "0";
$nitelinkID = "1204";
$nitelinkPWD = "je398bgf";
$got_results = false;

if ( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == 'checkavail' ) {
	$default_days = $_POST['numdays'];
	$default_adults = $_POST['numadults'];
	$default_kids = $_POST['numkids'];
	$default_arrive = $_POST["arrive"];

	$logit ="in POST - <br />";
	$logit .= " Arrive: ".$_POST['arrive'].'<br />';
	$logit .= " Lenght of stay: ".$_POST['numdays'].'<br />';
	$logit .= " Number of Adults: ".$_POST['numadults'].'<br />';
	$logit .= " Number of kids: ".$_POST['numkids'].'<br />';

	// validation of inputs before call nitelink
	$ok_to_post = true;
	if ($ok_to_post  && empty($_POST['arrive']) ) { // arrival date
		$ok_to_post = false;
		$form_error_msg = "Please fill in arrival date.";
	} 
	if ($ok_to_post ) {
		try {
			$dt_arrive = new DateTime($_POST['arrive']);
		} catch (Exception $e) {
			//$form_error_msg = $e->getMessage();
			$form_error_msg = "Please enter a valid date for your arrival.";
			$ok_to_post = false;
			$logit = $form_error_msg;
		}
	}
	if ($ok_to_post) {
		$logit .= "<br>dt_arrive".date_format($dt_arrive,"m/d/y");
		if (!$dt_arrive) {
			$ok_to_post = false;
			$form_error_msg = "Please give valid date in MM/DD/YY format in arrival date.";
			$default_arrive = date("m/d/y");
		}
	}
	if ($ok_to_post) {
		if ($dt_arrive >= strtotime("today")) {
			$ok_to_post = false;
			$form_error_msg = "Please give a date in the future.";
			$default_arrive = date("m/d/y");
		}
	}

	if ($ok_to_post && empty($_POST['numdays']) ) {// num days
		$ok_to_post = false;
		$form_error_msg = "Please fill in how many nights you would like to stay.";
	}
	if ($ok_to_post) {
		$i_numdays = (int) $_POST['numdays'];
		if ($i_numdays <= 0) {
			$ok_to_post = false;
			$form_error_msg = "Please fill in how many nights you would like to stay.";
		}
	}
	if ($ok_to_post && ($i_numdays >= 7)) {
		$form_error_msg = "Days > 7";
		$ok_to_post = false;
	}

	if ($ok_to_post) { // departure
		$dt_departure = new DateTime($_POST['arrive']);
		date_add($dt_departure, date_interval_create_from_date_string($_POST["numdays"]."days"));
		$departure = date_format($dt_departure,"m/d/y");
		$logit .= "<br>departure:".$departure;
	}

	if ($ok_to_post && ($_POST['numadults']) == "" ) { // number of adults
		$ok_to_post = false;
		$form_error_msg = "Please fill in how many adults.";
	}
	if ($ok_to_post) {
		$i_numadults = (int) $_POST['numadults'];
		if ($i_numadults <= 0) {
			$ok_to_post = false;
			$form_error_msg = "Please fill in how many adults 0.";
		}
	}

	if ($ok_to_post && ($_POST['numkids']) == "" ) { // number of children
		$ok_to_post = false;
		$logit .= 'NO KIDS FIELD';
		$form_error_msg = "Please fill in how many children are traveling with you.";
	}

	/* here we go */
	if ($ok_to_post) {
		$args = array('userid' => $nitelinkID, 'userpwd' => $nitelinkPWD, 'Arrival' => $_POST['arrive'], 'Departure' => $departure, 'Adults' => $_POST['numadults'], 'Children' => $_POST['numkids'] );
		/* $client = new SoapClient("https://www.nitelink.com/nitelinkws/nitelink.asmx?WSDL", array('exceptions' => 0)); */
		try { 
			ini_set("default_socket_timeout", 15);
			$client = new SoapClient("https://www.nitelink.com/nitelinkws/nitelink.asmx?WSDL", array("connection_timeout" => 15));
			$result = $client->GetAvailableRoomtypes($args); 
		} /* catch (SOAPFault $exception) { */
			catch (Exception $exception) {
			$logit = "<p>Execption!</p>";
			$ok_to_post = false;
			$form_error_msg = "Soap Exception";
		}

		if (is_soap_fault($result) || !$ok_to_post) {
			$form_error_msg = "Unable to Check Availibility at this time.";
			$ok_to_post = false;
		} else {

			if ($result) {
				if ($result->GetAvailableRoomtypesResult->StatusCode > 0)  {
					$form_error_msg = $result->GetAvailableRoomtypesResult->Status;
				} else {
					/* we are good to go */
					$got_results = true;
					$html_avail_result = "";
					$html_avail_result .= '<div id="bhm_check_avail_results" class="bhm_avail_box" >';
					$html_avail_result .= '<div id="bhm_avail_head"><h2 class="bhm-check_avail_title">Room Availibility</h2>';
					$html_avail_result .= '<div class="bhm_avail_headdates">'.date_format($dt_arrive,"n/j/Y").' to '.date_format($dt_departure,"n/j/Y").'</div>';
					$html_avail_result .= '<div class="bhm_avail_headdesc">'.$_POST['numadults'].' Adult(s),  '.$_POST['numkids'].' Child(ren) </div>';
					$html_avail_result .= '</div>';

					if ($result->GetAvailableRoomtypesResult->AvailableRoomTypes == new stdClass()) {
						$html_avail_result .= '<div class="bhm-no-rooms">No available rooms.</div>';
					} else {
						/* build the results */
						$avail_rooms = $result->GetAvailableRoomtypesResult->AvailableRoomTypes->nlRoomType;
						$html_avail_result .= '<form action="https://www.nitelink.com/nitelink21/step3.asp" method="post">';
						$html_avail_result .= '<input type="hidden" name="Arrival" value="'.$_POST['arrive'].'" >';
						$html_avail_result .= '<input type="hidden" name="Departure" value="'.$departure.'" >';
						$html_avail_result .= '<input type="hidden" name="Adults" value="'.$_POST['numadults'].'" >';
						$html_avail_result .= '<input type="hidden" name="Children" value="'.$_POST['numkids'].'" >';
						$html_avail_result .= '<input type="hidden" name="UserID" value="'.$nitelinkID.'" >';
						$html_avail_result .= '<table id="bh_avail_tab" class="bhm-table bhm_avail_tab"><tbody>';
						$html_avail_result .= '<tr class="bhm_avail_tabhead">';
						/* $html_avail_result .= 	'<td></td>'; */
						/* $html_avail_result .= 	'<td>Room<br>Type</td>'; */
						$html_avail_result .= 	'<td class="bhm_estimate" colspan="3">Estimated Stay</td>';
						$html_avail_result .= '</tr>';
						if (gettype($avail_rooms) == 'array') {
							$cnt = 0;
							foreach ($avail_rooms as $room) {
								$cnt = $cnt + 1; 
								if ($cnt == 1) { $checked = "checked"; } else { $checked = ""; }
								$html_avail_result .= '<tr>';
								$html_avail_result .= '<td><input type="radio" name="RoomType" '.$checked.'  value="'.$room->RoomType.'"> </td>';
								$html_avail_result .= '<td>'.$room->Description.'</td>';
								$html_avail_result .= '<td class="price">'.$room->TotalEstimatedStay.'</td>';
								$html_avail_result .= '<input type="hidden" name="Season'.$room->RoomType.'" value="SeasonNumber'.$room->SeasonNumber.'">';
								$html_avail_result .= '</tr>';
							}
						} else {
							/* only one result returns object */
							$room = $avail_rooms;
							$html_avail_result .= '<tr>';
							$html_avail_result .= '<td><input type="radio" name="RoomType" checked  value="'.$room->RoomType.'"> </td>';
							$html_avail_result .= '<td>'.$room->Description.'</td>';
							$html_avail_result .= '<td class="price">'.$room->TotalEstimatedStay.'</td>';
							$html_avail_result .= '<input type="hidden" name="Season'.$room->RoomType.'" value="SeasonNumber'.$room->SeasonNumber.'">';
							$html_avail_result .= '</tr>';
						}
						$html_avail_result .= '</tbody></table>';
						$html_avail_result .=  '<p>Rates are subject to Maine State Sales Tax.</p>';
						$html_avail_result .= '<div id="bookit_box">';
						$html_avail_result .=	 '<input name="bookit" type="submit" id="bookit" class="blue_sidebar_link bhm-blue_sidebar_box" value="Make Reservation" />';
						$html_avail_result .= 	 '<input name="action" type="hidden" id="action" value="bookit" />';
						$html_avail_result .= '</div>';
						/* $html_avail_result .= '<div id="bhm_bookit"><a class="bhm-blue_sidebar_link bhm-arrow_after" href="https://www.nitelink.com/nitelink21/main.asp?p=1204">Reserve Now</a>'; */
						$html_avail_result .= '</form>';
						$html_avail_result .= '</div>'; // end "bhm_avail_box"
					}
					
				}
			}

		}
	} else {

	}
	
} else {
	$logit ="no POST - ";

}

?>
<?php get_header(); ?>
	<?php
	$content_css = 'width:100%';
	$sidebar_css = 'display:none';
	$content_class = '';
	$sidebar_exists = true;
	$sidebar_left = '';
	$double_sidebars = false;
	$sidebar_class_left = '';
	$sidebar_class_right = '';

	$sidebar_1 = get_post_meta( $post->ID, 'sbg_selected_sidebar_replacement', true );
	$sidebar_2 = get_post_meta( $post->ID, 'sbg_selected_sidebar_2_replacement', true );
	
	if( $smof_data['pages_global_sidebar'] ) {
		if( $smof_data['pages_sidebar'] != 'None' ) {
			$sidebar_1 = array( $smof_data['pages_sidebar'] );
		} else {
			$sidebar_1 = '';
		}

		if( $smof_data['pages_sidebar_2'] != 'None' ) {
			$sidebar_2 = array( $smof_data['pages_sidebar_2'] );
		} else {
			$sidebar_2 = '';
		}
	}

	if( is_array( $sidebar_2 ) && 
		( $sidebar_2[0] || $sidebar_2[0] === '0' ) ) {
		$double_sidebars = true;
	}

	if(get_post_meta($post->ID, 'pyre_sidebar_position', true) == 'left') {
		$content_css = 'float:right;';
		$sidebar_css = 'float:left;';
		$sidebar_class = 'side-nav-left';
		$content_class = 'portfolio-one-sidebar';
		$sidebar_left = 1;
	} elseif(get_post_meta($post->ID, 'pyre_sidebar_position', true) == 'right') {
		$content_css = 'float:left;';
		$sidebar_css = 'float:right;';
		$sidebar_class = 'side-nav-right';		
		$content_class = 'portfolio-one-sidebar';
	} elseif(get_post_meta($post->ID, 'pyre_sidebar_position', true) == 'default' || ! metadata_exists( 'post', $post->ID, 'pyre_sidebar_position' )) {
		$content_class = 'portfolio-one-sidebar';
		if($smof_data['default_sidebar_pos'] == 'Left') {
			$content_css = 'float:right;';
			$sidebar_css = 'float:left;';
			$sidebar_class = 'side-nav-left';
			$sidebar_left = 1;
		} elseif($smof_data['default_sidebar_pos'] == 'Right') {
			$content_css = 'float:left;';
			$sidebar_css = 'float:right;';
			$sidebar_class = 'side-nav-right';
			$sidebar_left = 2;
		}
	}

	if(get_post_meta($post->ID, 'pyre_sidebar_position', true) == 'right') {
		$sidebar_left = 2;
	}

	if( $smof_data['pages_global_sidebar'] ) {
		if( $smof_data['pages_sidebar'] != 'None' ) {
			$sidebar_1 = $smof_data['pages_sidebar'];
			
			if( $smof_data['default_sidebar_pos'] == 'Right' ) {
				$content_css = 'float:left;';
				$sidebar_css = 'float:right;';	
				$sidebar_left = 2;
			} else {
				$content_css = 'float:right;';
				$sidebar_css = 'float:left;';
				$sidebar_left = 1;
			}			
		}

		if( $smof_data['pages_sidebar_2'] != 'None' ) {
			$sidebar_2 = $smof_data['pages_sidebar_2'];
		}
		
		if( $smof_data['pages_sidebar'] != 'None' && $smof_data['pages_sidebar_2'] != 'None' ) {
			$double_sidebars = true;
		}
	} else {
		if( is_array( $sidebar_1 ) ) {
			$sidebar_1 = $sidebar_1[0];
		}
		if( is_array( $sidebar_2 ) ) {
			$sidebar_2 = $sidebar_2[0];
		}
	}
	
	if($double_sidebars == true) {
		$content_css = 'float:left;';
		$sidebar_css = 'float:left;';
		$sidebar_2_css = 'float:left;';
	} else {
		$sidebar_left = 1;
	}	
	?>
	<div id="content" style="<?php echo $content_css; ?>">
		<?php while(have_posts()): the_post(); 
		$page_id = get_the_ID();
		?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php echo avada_render_rich_snippets_for_pages(); ?>
			<?php echo avada_featured_images_for_pages(); ?>
			<div class="post-content">
				<?php if (true) {echo '<p>'.$logit;'</p>';} ?>
				<?php the_content(); ?>
				<?php if (true) { // for debugging
					var_dump($result);
					/*var_dump($result->GetAvailableRoomtypesResult->AvailableRoomTypes); */
					/* var_dump($avail_rooms); */
				} ?>
				<?php avada_link_pages(); ?>
			</div>
			<?php if ($got_results) {  //make sure results are showing on page.  didnt work
				/* $('html, body').animate({scrollTop: $("#bhm_check_avail_results").offset.top}, 2000) */
			}  ?>
			<?php if( ! post_password_required($post->ID) ): ?>
			<?php if($smof_data['comments_pages']): ?>
				<?php
				wp_reset_query();
				comments_template();
				?>
			<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php endwhile; ?>
	</div>
	<?php wp_reset_query(); ?>
	<div id="sidebar" class="sidebar <?php echo $sidebar_class; ?>" style="<?php echo $sidebar_css; ?>">
		<?php		
		if( $sidebar_exists == true ) {
			if($sidebar_left == 1) {
				/* echo avada_display_sidenav( $page_id ); */
				generated_dynamic_sidebar($sidebar_1);
			}
			if($sidebar_left == 2) {
				generated_dynamic_sidebar_2($sidebar_2);
			}
		}
		?>
	</div>
	<?php if( $sidebar_exists && $double_sidebars ): ?>
	<div id="sidebar-2" class="sidebar <?php echo $sidebar_class; ?>" style="<?php echo $sidebar_2_css; ?>">
		<?php
		if($sidebar_left == 1) {
			generated_dynamic_sidebar_2($sidebar_2);
		}
		if($sidebar_left == 2) {
			echo avada_display_sidenav( $page_id );
			generated_dynamic_sidebar($sidebar_1);
		}
		?>
	</div>
	<?php endif; ?>

<?php get_footer(); ?>
<?php
	 $jsreturn = "";
	if ($got_results) {
		$jsreturn .= '<script type="text/javascript">';
		 $jsreturn .= 	'document.getElementById("bhm-check_avail_box").scrollIntoView();'; 
		$jsreturn .= '</script>';
	} 
	echo $jsreturn;
?>