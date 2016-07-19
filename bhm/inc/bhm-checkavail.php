<?php 
/* bhm-checkavail.php 
*  hooks for ninja forms processing
* 
*/
define( "CHECK_FORM_ID", 5);
define( "RESERVE_FORM_ID", 9);
define("RESET_FORM_ID", 10);
define("BACK_FORM_ID", 12);
define ("NITELINK_ID", "1204");
define ("NITELINK_PWD", "je398bgf");

	/* put sucess messages at bottom of form instead of top */
	function prefix_move_ninja_forms_messages() {
	    remove_action( 'ninja_forms_display_before_form', 'ninja_forms_display_response_message', 10 );
	    add_action( 'ninja_forms_display_after_form', 'ninja_forms_display_response_message', 10 );
	}
	add_action( 'wp_head', 'prefix_move_ninja_forms_messages' );


	/* register some stuff */
	function bhm_ninja_forms_register(){
	  	add_action( 'ninja_forms_pre_process', 'bhm_ninja_forms_pre_process' );
	  	add_action( 'ninja_forms_process', 'bhm_ninja_forms_process' );
	  	add_action( 'ninja_forms_post_process', 'bhm_ninja_forms_post' );
	}
	add_action( 'init', 'bhm_ninja_forms_register' );

	/********** display actions ***************/
	function bhm_div_open_wrap( $field_id, $data ) {
		$classes = $data['class'];
		$arr_classes = explode(" ", $classes);
		if( in_array('open-div', $arr_classes ) ) {
			echo '<div class="bhm-form-group">';
		}
		if (in_array('open-div-inline', $arr_classes )) {
			echo '<div class="bhm-form-group inline">';
		}
		if( $data['class'] == 'nitelink-avail' ) { // this is where we put the night link table
			$html_result = bhm_nitelink_build_roomtable();
			echo $html_result;

		}
	}
	function bhm_div_close_wrap( $field_id, $data ) {
		if( $data['class'] == 'close-div' ) {
			echo '</div>';
		}
	}
	add_action ( 'ninja_forms_display_before_field', 'bhm_div_open_wrap', 1, 2 );
	add_action ( 'ninja_forms_display_after_field', 'bhm_div_close_wrap', 1, 2 );


	// modify defaults if coming back here.
	function bhm_prefill_from_session($data, $field_id){
		global $ninja_forms_processing;

		switch ($field_id) {
			case 6: // arrival date.
				if ($_SESSION['nitelink_arrive']) {
					$data['default_value'] = $_SESSION['nitelink_arrive'];
				}
				break;
			case 8: // number of nights
				if ($_SESSION['nitelink_arrive'] && $_SESSION['nitelink_depart']) {
					$dt_arrival = new DateTime($_SESSION['nitelink_arrive']);
					$dt_depart = new DateTime($_SESSION['nitelink_depart']);
					$nites = $dt_depart->diff($dt_arrival)->format("%a");
					$data['default_value'] = $nites;
				} 
				break;
			case 9: // adults
				if ($_SESSION['nitelink_adults']) {
					$data['default_value'] = $_SESSION['nitelink_adults'];
				} 
				break;
			case 10: // kids 
				if ($_SESSION['nitelink_kids']) {
					$data['default_value'] = $_SESSION['nitelink_kids'];
				} 
				break;
			case 67:
				if ($_SESSION['nitelink_roomtypes']) {
					$arr_roomtypes = explode(",", $_SESSION['nitelink_roomtypes']);
					$data['default_value'] = $arr_roomtypes[0];
				}
				
				break;
			case 70: 
				$data['default_value'] = $_SESSION['nitelink_depart'];
			break;
		} 
		return $data;
	}
	add_filter('ninja_forms_field', 'bhm_prefill_from_session', 10, 2);

	/********** processing actions **********/
	function bhm_ninja_forms_pre_process(){
		global $ninja_forms_processing;

		$fid = $ninja_forms_processing->get_form_ID();
		$availaility_over_7days = "For availability for stays longer than 7 days, please call us at 800-388-3453.";
		$all_fields = $ninja_forms_processing->get_all_fields();
		switch ($fid) {
			case CHECK_FORM_ID: // check avail form
				$numnites = $ninja_forms_processing->get_field_value(8);
			  	if ($numnites >= 7) {
					//$ninja_forms_processing->add_error('ninja_forms_field_82',$availaility_over_7days , "general");	  		
			  	} 
			  	// clear out session vars when hit process again....
			  	$_SESSION['nitelink_arrive'] = '';
			  	$_SESSION['nitelink_depart'] = '';
			  	$_SESSION['nitelink_adults'] = '';
			  	$_SESSION['nitelink_kids'] = '';
			  	$_SESSION['nitelink_roomtypes'] = '';
			  	$_SESSION['nitelink_roomdesc'] = '';
			  	$_SESSION['nitelink_prices'] = '';
			  	$_SESSION['nitelink_seasonnum'] = '';
			break;

			case RESERVE_FORM_ID: // reservations form.
				if (!$_SESSION['nitelink_roomtypes'] ) {
					$ninja_forms_processing->add_error('bhm_nitelink_bookit_err0',"Please check availability before making a reservation." , "general");
				}
			break;
		}
	  
	}

	function bhm_ninja_forms_process(){
		global $ninja_forms_processing;

		//Get all the user submitted values
		$all_fields = $ninja_forms_processing->get_all_fields();
		$fid = $ninja_forms_processing->get_form_ID();
		switch ($fid) {
			case CHECK_FORM_ID: // check availability
				$arrival = $ninja_forms_processing->get_field_value(6);
			  	$numnites = $ninja_forms_processing->get_field_value(8);
			  	$numadults = $ninja_forms_processing->get_field_value(9);
			  	$numkids = $ninja_forms_processing->get_field_value(10);
			  	$soap_results = bhm_nitelink_check_avail(NITELINK_ID, NITELINK_PWD, $arrival, $numnites, $numadults, $numkids);
			  	break;
			case RESERVE_FORM_ID: // make reservation form.
				//bhm_nitelink_make_reservation();
				bhm_nitelink_make_reservation_http();
				break;
			case RESET_FORM_ID: 
				$_SESSION['nitelink_arrive'] = '';
			  	$_SESSION['nitelink_depart'] = '';
			  	$_SESSION['nitelink_adults'] = '';
			  	$_SESSION['nitelink_kids'] = '';
			  	$_SESSION['nitelink_roomtypes'] = '';
			  	$_SESSION['nitelink_roomdesc'] = '';
			  	$_SESSION['nitelink_prices'] = '';
			  	$_SESSION['nitelink_seasonnum'] = '';
				break;
		}
	} // end function bhm_ninja_forms_process 

	function bhm_ninja_forms_post() {
	  global $ninja_forms_processing;
		$fid = $ninja_forms_processing->get_form_ID();
		switch ($fid) {
			case CHECK_FORM_ID: // check availability form
			  	$_SESSION['nitelink_arrive'] = $ninja_forms_processing->get_field_value(6);
			  	$_SESSION['nitelink_depart'] = $ninja_forms_processing->get_field_value(66);
			  	$_SESSION['nitelink_adults'] = $ninja_forms_processing->get_field_value(9);
			  	$_SESSION['nitelink_kids'] = $ninja_forms_processing->get_field_value(10);
			  	$_SESSION['nitelink_roomtypes'] = $ninja_forms_processing->get_field_value(13);
			 	$_SESSION['nitelink_roomdesc'] = $ninja_forms_processing->get_field_value(14);
			  	$_SESSION['nitelink_prices'] = $ninja_forms_processing->get_field_value(15);
			  	$_SESSION['nitelink_seasonnum'] = $ninja_forms_processing->get_field_value(80);
			  	break;

			case RESERVE_FORM_ID: // make reservation form.
				
				break;
		}
	} // end function bhm_ninja_forms_post


	function bhm_disable_saving_subs( $save, $form_id ) {
	  // Set $save = false based on condition
		switch ($form_id) {
			case CHECK_FORM_ID:  // check availability
			case RESET_FORM_ID: // start over
			case BACK_FORM_ID:
				$save = false; 
				break;
		}
		return $save;
	} // end function bhm_disable_saving_subs
	add_filter( 'ninja_forms_save_submission', 'bhm_disable_saving_subs', 2, 10 );


/********support functions ************/
	function bhm_nitelink_check_avail($in_nitelinkID, $in_nitelinkPWD, $in_arrival, $in_numnites, $in_numadults, $in_numkids) {
		/* function to call the soap call  for check availibility....*/
		global $ninja_forms_processing;

		$dt_arrival = new DateTime($in_arrival);
		$str_arrival = date_format($dt_arrival,"m/d/Y"); // put in correct format for call

		$dt_departure = new DateTime($in_arrival);
		date_add($dt_departure, date_interval_create_from_date_string($in_numnites."days"));
		$str_departure = date_format($dt_departure,"m/d/Y");
		$ninja_forms_processing->update_field_value(66, $str_departure);

		$soap_args = array('userid' => $in_nitelinkID, 'userpwd' => $in_nitelinkPWD, 'Arrival' => $str_arrival, 'Departure' => $str_departure, 'Adults' => $in_numadults, 'Children' => $in_numkids );
		/* $ninja_forms_processing->add_error('bhm_nitelink_check_avail1',"in bhm_nitelink_check_avail1: w arrival:".$in_arrival , "general");	  */
		$ok_to_post = true;
		
		try { 
			$client = new SoapClient("https://www.nitelink.com/nitelinkws/nitelink.asmx?WSDL", array('exceptions' => 0));
			/* $client = new SoapClient("https://www.nitelink.com/nitelinkws/nitelink.asmx?WSDL"); */
			$result = $client->GetAvailableRoomtypes($soap_args);  
		} catch (SOAPFault $exception) {
			$ok_to_post = false;
			$form_error_msg = "Soap Execption: Unable to Check Availibility at this time.";
			$ninja_forms_processing->add_error('bhm_nitelink_check_avail2',"in bhm_nitelink_check_avail: ".$form_error_msg , "general");	 
		}

		if (is_soap_fault($result) || !$ok_to_post) {
			$form_error_msg = "Unable to Check Availibility at this time.";
			$ninja_forms_processing->add_error('bhm_nitelink_check_avail3',"in bhm_nitelink_check_avail1: ".$form_error_msg , "general");	 
			$ok_to_post = false;
		} else {

			if ($result) {
				/* we have some results - build the html */
				
				if ($result->GetAvailableRoomtypesResult-> StatusCode > 0)  {
					$form_error_msg = $result->GetAvailableRoomtypesResult->Status;
					$ninja_forms_processing->add_error('bhm_nitelink_check_avail4',"in bhm_nitelink_check_avail1: ".$form_error_msg , "general");	 

				} else {
					$got_results = true;
					/* $ninja_forms_processing->add_error('bhm_nitelink_check_avail5',"in bhm_nitelink_check_avail1: OMG! got results." , "general");	  */
					/* build results */
					$str_avail_results = "";
					if ( $result->GetAvailableRoomtypesResult->AvailableRoomTypes == new stdClass() ) {
						$form_error_msg = "No rooms available.  Please call.";
						$ninja_forms_processing->add_error('bhm_nitelink_check_availnorooms',$form_error_msg , "general");	 
					} else {						
						$ninja_forms_processing->add_success_msg('bhm_nitelink_check_avails1', "in bhm_nitelink_check_avail1: OMG! have rooms results.");
						/* build the results as two strings roomtype & cost */
						$avail_rooms = $result->GetAvailableRoomtypesResult->AvailableRoomTypes->nlRoomType;
						$roomtypeName = "";
						$roomtype = "";
						$cost =""; 
						$seasonnum = "";
						if (false) {
							echo "<p> check avail results:<pre> ";
							var_dump($avail_rooms);
							echo "</pre></p>";
						}
						if (gettype($avail_rooms) == 'array') {
							$cnt = 0;
							foreach ($avail_rooms as $room) {
								$cnt = $cnt + 1; 
								if ($cnt == 1) {
									if (false) {
										echo "<p> room[0].RatesByDate.Rate[0].Rate.Seasonnumber: <pre>";
										var_dump($room->RatesByDate->Rate->SeasonNumber);
										echo "</pre></p>";
									}
									$seasonnum = $room->RatesByDate->Rate->SeasonNumber;
								}
								$roomtype .= $room->RoomType.",";
								$roomtypeName .= $room->Description.",";
								$cost .= $room->TotalEstimatedStay.",";
							}
							if ($cnt > 1 ) {
								// strip off final commas 
								$roomtype = substr($roomtype,0,strrpos($roomtype,","));
								$roomtypeName = substr($roomtypeName,0,strrpos($roomtypeName,","));
								$cost = substr($cost,0,strrpos($cost,","));
							}
							
						} else {
							// only one result returns object not array
							$roomtype .= $avail_rooms->RoomType;
							$roomtypeName .= $avail_rooms->Description;
							$cost .= $avail_rooms->TotalEstimatedStay;
							$seasonnum = $avail_rooms->RatesByDate->Rate->SeasonNumber;
						}
						// save these as fields on form

						$ninja_forms_processing->update_field_value(13, $roomtype);
						$ninja_forms_processing->update_field_value(14, $roomtypeName);
						$ninja_forms_processing->update_field_value(15, $cost);
						$ninja_forms_processing->update_field_value(80, $seasonnum);
						/* get rooms not available */
						$other_rooms = $result->GetAvailableRoomtypesResult->OtherRoomTypes->nlRoomType;
						$otr_roomtypeName = "";
						$otr_roomtype = "";
						$otr_cost =""; 
						if (gettype($other_rooms) == 'array') {
							$cnt = 0;
							foreach ($other_rooms as $room) {
								$cnt = $cnt + 1; 
								$otr_roomtype .= $room->RoomType.",";
								$otr_roomtypeName .= $room->Description.",";
								$otr_cost .= $room->TotalEstimatedStay.",";
							}
							if ($cnt > 1 ) {
								// strip off final commas 
								$otr_roomtype = substr($otr_roomtype,0,strrpos($otr_roomtype,","));
								$otr_roomtypeName = substr($otr_roomtypeName,0,strrpos($otr_roomtypeName,","));
								$otr_cost = substr($otr_cost,0,strrpos($otr_cost,","));
							}
						} else {
							// only one result returns object
							$otr_roomtype .= $room->RoomType;
							$otr_roomtype .= $room->Description;
							$otr_cost .= $room->TotalEstimatedStay;
						}
						// save these as fields on form - maybe

						//$ninja_forms_processing->add_error('bhm_nitelink_check_avail10',"in bhm_nitelink_check_avail1: ".$form_error_msg , "general");	 
					} 
				}
			} else { // no results
				$form_error_msg = " no results ";
				$ninja_forms_processing->add_error('bhm_nitelink_check_avail3',"in bhm_nitelink_check_avail1: ".$form_error_msg , "general");	 

			}
		}
	} // end bhm_nitelink_check_avail

	function bhm_nitelink_build_roomtable() {

		$html_result = '';

		/* $html_result .= '<p>';
		$html_result .= 'types:'.$_SESSION['nitelink_roomtypes'].'</br>';
		$html_result .= 'desc:'.$_SESSION['nitelink_roomdesc'].'</br>';
		$html_result .= 'total:'.$_SESSION['nitelink_prices'].'</br>';
		$html_result .= '</p>'; */
		$arr_roomtypes = explode(",", $_SESSION['nitelink_roomtypes']);
		$arr_roomdesc = explode(",", $_SESSION['nitelink_roomdesc']); 
		$arr_roomprices = explode(",", $_SESSION['nitelink_prices']);
		$seasonnum = $_SESSION['nitelink_seasonnum'];
		$html_result .= '<div class="bh_avail_tab_wrap">';
		$html_result .= '<table id="bh_avail_tab" class="bhm-table bhm_avail_tab"><tbody>';
		$html_result .= '<tr class="bhm_avail_tabhead">';
		$html_result .= 	'<td></td>'; 
		$html_result .= 	'<td>Room Type</td>';
		$html_result .= 	'<td class="bhm_estimate" >Estimated Stay</td>';
		$html_result .= '</tr>';
		$cnt = 0;
		foreach ($arr_roomtypes as $room) {
			if ($cnt == 0) { 
				$checked = "checked";
			} else { 
				$checked = ""; 
			}
			$html_result .= '<tr>';
			$html_result .= '<td><input type="radio" name="_RoomType" '.$checked.'  value="'.$room.'" onclick="set_roomtype(this)"> </td>';
			$html_result .= '<td>'.$arr_roomdesc[$cnt].'</td>';
			$html_result .= '<td class="price">'.$arr_roomprices[$cnt].'</td>';
			//$html_result .= '<input type="hidden" name="Season'.$room->RoomType.'" value="SeasonNumber'.$room->SeasonNumber.'">';
			$html_result .= '</tr>';
			$cnt = $cnt + 1; 
		}

		$html_result .= '</tbody></table></div>'; 
		$js_result = "";
		$js_result .= '<script>function set_roomtype(element) {';
		$js_result .= 		'document.getElementById("ninja_forms_field_67").value = element.value;';
		$js_result .= "}</script>";

		return $html_result.$js_result;
	} // end function bhm_nitelink_build_roomtable

	function bhm_nitelink_make_reservation() {
		/* function to call the soap call to make reservation.....*/
		global $ninja_forms_processing;

		$form_fields = $ninja_forms_processing->get_all_fields();
				
		$str_arrival = $_SESSION['nitelink_arrive'];
		$str_departure = $_SESSION['nitelink_depart'];
		$dt_arrival = new DateTime($str_arrival);
		$dt_departure = new DateTime($str_departure);

		//$ninja_forms_processing->add_error('bhm_ninja_bookit_dbg1',"Process+: here on form: ".$fid , "general");	
		$addons_for_requests = "";
		$packages_arr = $form_fields['75'];
		if (is_array($packages_arr) && (count($packages_arr) > 0)) {
			$addons_for_requests .= implode("-", $packages_arr);
		}
		$discounts_arr = $form_fields['74'];
		if (is_array($discounts_arr) && (count($discounts_arr) > 0)) {
			if (strlen($addons_for_requests) > 0)  {
				$addons_for_requests .= " : ";
			}
			$addons_for_requests .= implode("-", $discounts_arr);
		}

		$soap_args = array( 'userid' =>  intval(NITELINK_ID), 
							'userpwd' => NITELINK_PWD, 
							'Arrival' => $dt_arrival, 
							'Departure' => $dt_departure, 
							'Adults' =>  intval($_SESSION['nitelink_adults']),
							'Children' => intval($_SESSION['nitelink_kids']),
							'roomtype' => intval($form_fields['67']),
							'seasonnumber' => intval($_SESSION['nitelink_seasonnum']),
							'firstname' => $form_fields['31'],
							'lastname' => $form_fields['32'],
							'address' => $form_fields['33'],
							'city' => $form_fields['34'],
							'state' => $form_fields['35'],
							'postalcode' => $form_fields['36'],
							'phone' => $form_fields['38'],
							'email' => $form_fields['37'],
							'specialrequests' => $addons_for_requests." ".$form_fields['40'],
							'creditcardnumber' => $form_fields['42'],
							'expdate' => $form_fields['81']."/".$form_fields['82'],
							'cardholder' => $form_fields['44'],
							'CompanyNumber' => intval($form_fields['41']),// ?? dont know what this is???
						);
		if (false) {
			echo "<p>Args:</p><pre>";
			var_dump($soap_args); 
			echo "</pre>";	
			$ninja_forms_processing->add_error('bhm_nitelink_bookit_err1',"Soap Args: ", "general");	 	
		}

		//$ninja_forms_processing->add_success_msg('bhm_ninja_bookit_dbg2',"in bhm_nitelink_make_reserv: args");	 
		$ok_to_post = true;
		/* $client = new SoapClient("https://www.nitelink.com/nitelinkws/nitelink.asmx?WSDL", array('exceptions' => 0)); */
		try { 
			//$ninja_forms_processing->add_success_msg('bhm_ninja_bookit_dbg3',"Trying...");	 
			$client = new SoapClient("https://www.nitelink.com/nitelinkws/nitelink.asmx?WSDL");
			$result = $client->SubmitReservation($soap_args);  
		} catch (SOAPFault $exception) {
			$ok_to_post = false;
			$form_error_msg = "SOAPFault Exception: ".$exception->getMessage();
			$ninja_forms_processing->add_error('bhm_nitelink_bookit_err2',$form_error_msg , "general");	 
			if (false) {
				echo "<p>SOAPFault Exception:<pre>";
				var_dump($exception);
				echo "</pre></p>";
			}
		} catch (Exception $exception) {
			$ok_to_post = false;
			$form_error_msg = "Std Exception: ".$exception->getMessage();
			$ninja_forms_processing->add_error('bhm_nitelink_bookit_err4',$form_error_msg , "general");	 
			if (false) {
				echo "<p>STD Exception:";
				var_dump($exception);
				echo "</p>";
			}
		}// of try/
		if (is_soap_fault($result) ) {
			$form_error_msg = "SoapFault";
			$ninja_forms_processing->add_error('bhm_nitelink_bookit_err3',"SoapFault ".$form_error_msg , "general");	 
			$ok_to_post = false;
		} 
		if ($ok_to_post)  {
			$ninja_forms_processing->add_success_msg('bhm_nitelink_bookit',"Booked!!!");	 
		} else {
			$form_error_msg = "Unable to Book Room at this time.";
			$ninja_forms_processing->add_error('bhm_nitelink_bookit_err4',$form_error_msg , "general");	 
			$ok_to_post = false;
		}
	} // end function bhm_nitelink_make_reservation
	function bhm_nitelink_make_reservation_http() {
		/* function to call the soap call to make reservation.....*/
		global $ninja_forms_processing;

		$form_fields = $ninja_forms_processing->get_all_fields();
				
	
		$str_departure = $_SESSION['nitelink_depart'];
		$dt_arrival = new DateTime($_SESSION['nitelink_arrive']);
		$str_arrival = date_format($dt_arrival,"m/d/Y");
		$dt_departure = new DateTime($str_departure);

		//$ninja_forms_processing->add_error('bhm_ninja_bookit_dbg1',"Process+: here on form: ".$fid , "general");	
		$addons_for_requests = "";
		$packages_arr = $form_fields['75'];
		if (is_array($packages_arr) && (count($packages_arr) > 0)) {
			$addons_for_requests .= implode("-", $packages_arr);
		}
		$discounts_arr = $form_fields['74'];
		if (is_array($discounts_arr) && (count($discounts_arr) > 0)) {
			if (strlen($addons_for_requests) > 0)  {
				$addons_for_requests .= " : ";
			}
			$addons_for_requests .= implode("-", $discounts_arr);
		}

		$soap_args = array( 'userid' =>  intval(NITELINK_ID), 
							'userpwd' => NITELINK_PWD, 
							'Arrival' => $str_arrival, 
							'Departure' => $str_departure, 
							'Adults' =>  intval($_SESSION['nitelink_adults']),
							'Children' => intval($_SESSION['nitelink_kids']),
							'roomtype' => intval($form_fields['67']),
							'seasonnumber' => intval($_SESSION['nitelink_seasonnum']),
							'firstname' => $form_fields['31'],
							'lastname' => $form_fields['32'],
							'address' => $form_fields['33'],
							'city' => $form_fields['34'],
							'state' => $form_fields['35'],
							'postalcode' => $form_fields['36'],
							'phone' => $form_fields['38'],
							'email' => $form_fields['37'],
							'specialrequests' => $addons_for_requests." ".$form_fields['40'],
							'creditcardnumber' => $form_fields['42'],
							'expdate' => $form_fields['81']."/".$form_fields['82'],
							'cardholder' => $form_fields['44'],
							'CompanyNumber' => 0,// according to nitelink, not needed.
						);
		$http_args = http_build_query($soap_args);
		$o_args = add_query_arg($soap_args, 'https://www.nitelink.com/nitelinkws/nitelink.asmx/SubmitReservation');
		//$ninja_forms_processing->add_success_msg('bhm_nitelink_bookit5',"oArgs:".$o_args);
		$ok_to_post = true;
		if (false) {
			echo "<p>o Args:</p><pre>";
			var_dump($o_args); 
			echo "</pre>";	
			//$ninja_forms_processing->add_error('bhm_nitelink_bookit_dbg4',"https Args: ".$http_args, "general");	 	
			//$ok_to_post = false;
		}

		if ($ok_to_post) {
			$resp = wp_remote_post('https://www.nitelink.com/nitelinkws/nitelink.asmx/SubmitReservation', $soap_args);// internal service error
			//$resp = wp_remote_post('https://www.nitelink.com/nitelinkws/nitelink.asmx/SubmitReservation', $http_args);// internal service error
			//$resp = wp_remote_post('https://www.nitelink.com/nitelinkws/nitelink.asmx/SubmitReservation', $o_args); // internal service error
			//$resp = wp_remote_post($o_args); // bad request.
			if ( is_wp_error($resp) || !isset($resp['body']) ) {
				$ok_to_post = false;
				if (is_wp_error($resp)) {
					$form_error_msg = "htpp error. - wp_error".$resp->get_error_message();
				} else {
					$form_error_msg = "htpp error. - not set body";
				}
				$ninja_forms_processing->add_error('bhm_nitelink_bookit_err3',$form_error_msg , "general");	 
			} else {
				// got a response
				if (false) {
					$ok_to_post = false;
					$form_error_msg = "testing stuff. ";
					$ninja_forms_processing->add_error('bhm_nitelink_bookit_err3',$form_error_msg , "general");	 
					echo "<pre>";
					var_dump($resp);
					echo "</pre>";
				}
			}
		}
		if ($ok_to_post) {
			// check the reponse codes
			$str_resp_code = wp_remote_retrieve_response_code($resp);
			$i_resp_code = intval($str_resp_code);
			if ($i_resp_code <> 200) {
				$ok_to_post = false;
				$form_error_msg = "Response code: ".$str_resp_code;
				$ninja_forms_processing->add_error('bhm_nitelink_bookit_err5',$form_error_msg , "general");	 
				$form_error_msg = "Response message: ".wp_remote_retrieve_response_message($resp);
				$ninja_forms_processing->add_error('bhm_nitelink_bookit_err6',$form_error_msg , "general");	
				
				//$ninja_forms_processing->add_error('bhm_nitelink_bookit_err7',$form_error_msg , "general");	
			}
		}
		if ($ok_to_post)  {
			$ninja_forms_processing->add_success_msg('bhm_nitelink_bookit',"Booked!!!");
			if (false) {
				echo "<pre>";
				var_dump($resp);
				echo "</pre>";
			}
		} else {
			$form_error_msg = "Unable to Book Room at this time.";
			$ninja_forms_processing->add_error('bhm_nitelink_bookit_err4',$form_error_msg , "general");	 
			$ok_to_post = false;
		}
	} // end function bhm_nitelink_make_reservation_http

	function bhm_nitelink_check_avail_http($in_nitelinkID, $in_nitelinkPWD, $in_arrival, $in_numnites, $in_numadults, $in_numkids) {
		/* function to call the soap call  for check availibility....*/
		global $ninja_forms_processing;

		$dt_arrival = new DateTime($in_arrival);
		$str_arrival = date_format($dt_arrival,"m/d/Y"); // put in correct format for call

		$dt_departure = new DateTime($in_arrival);
		date_add($dt_departure, date_interval_create_from_date_string($in_numnites."days"));
		$str_departure = date_format($dt_departure,"m/d/Y");
		$ninja_forms_processing->update_field_value(66, $str_departure);

		$soap_args = array('userid' => $in_nitelinkID, 'userpwd' => $in_nitelinkPWD, 'Arrival' => $str_arrival, 'Departure' => $str_departure, 'Adults' => $in_numadults, 'Children' => $in_numkids );
		/* $ninja_forms_processing->add_error('bhm_nitelink_check_avail1',"in bhm_nitelink_check_avail1: w arrival:".$in_arrival , "general");	  */
		$ok_to_post = true;


		if ($ok_to_post) {
			$resp = wp_remote_post('http://www.nitelink.com/nitelinkws/nitelink.asmx/GetAvailableRoomtypes', $soap_args);
			if ( is_wp_error($resp) || !isset($resp['body']) ) {
				$ok_to_post = false;
				if (is_wp_error($resp)) {
					$form_error_msg = "htpp error. - wp_error".$resp->get_error_message();
				} else {
					$form_error_msg = "htpp error. - not set body";
				}
				$ninja_forms_processing->add_error('bhm_nitelink_checkavail_err3',$form_error_msg , "general");	 
			}
		}
		if ($ok_to_post) {
			// check the reponse codes
			$str_resp_code = wp_remote_retrieve_response_code($resp);
			$i_resp_code = intval($str_resp_code);
			if ($i_resp_code <> 200) {
				$ok_to_post = false;
				$form_error_msg = "Response code: ".$str_resp_code;
				$ninja_forms_processing->add_error('bhm_nitelink_checkavail_err5',$form_error_msg , "general");	 
				$form_error_msg = "Response message: ".wp_remote_retrieve_response_message($resp);
				$ninja_forms_processing->add_error('bhm_nitelink_checkavail_err6',$form_error_msg , "general");	
				
				//$ninja_forms_processing->add_error('bhm_nitelink_bookit_err7',$form_error_msg , "general");	
			}
		}
		$form_error_msg = "testing";
		$ninja_forms_processing->add_error('bhm_nitelink_checkavail_err4',$form_error_msg , "general");	 
		$ok_to_post = false; // for now to debug.
		if (false) {
			echo "<pre>";
			var_dump($resp);
			echo "</pre>";
		}
		if ($ok_to_post) {
			if ($result) {
				/* we have some results - build the html */
				
				if ($result->GetAvailableRoomtypesResult-> StatusCode > 0)  {
					$form_error_msg = $result->GetAvailableRoomtypesResult->Status;
					$ninja_forms_processing->add_error('bhm_nitelink_check_avail4',"in bhm_nitelink_check_avail1: ".$form_error_msg , "general");	 

				} else {
					$got_results = true;
					/* $ninja_forms_processing->add_error('bhm_nitelink_check_avail5',"in bhm_nitelink_check_avail1: OMG! got results." , "general");	  */
					/* build results */
					$str_avail_results = "";
					if ( $result->GetAvailableRoomtypesResult->AvailableRoomTypes == new stdClass() ) {
						$form_error_msg = "No rooms available.  Please call.";
						$ninja_forms_processing->add_error('bhm_nitelink_check_availnorooms',$form_error_msg , "general");	 
					} else {						
						$ninja_forms_processing->add_success_msg('bhm_nitelink_check_avails1', "in bhm_nitelink_check_avail1: OMG! have rooms results.");
						/* build the results as two strings roomtype & cost */
						$avail_rooms = $result->GetAvailableRoomtypesResult->AvailableRoomTypes->nlRoomType;
						$roomtypeName = "";
						$roomtype = "";
						$cost =""; 
						$seasonnum = "";
						if (false) {
							echo "<p> check avail results:<pre> ";
							var_dump($avail_rooms);
							echo "</pre></p>";
						}
						if (gettype($avail_rooms) == 'array') {
							$cnt = 0;
							foreach ($avail_rooms as $room) {
								$cnt = $cnt + 1; 
								if ($cnt == 1) {
									if (false) {
										echo "<p> room[0].RatesByDate.Rate[0].Rate.Seasonnumber: <pre>";
										var_dump($room->RatesByDate->Rate->SeasonNumber);
										echo "</pre></p>";
									}
									$seasonnum = $room->RatesByDate->Rate->SeasonNumber;
								}
								$roomtype .= $room->RoomType.",";
								$roomtypeName .= $room->Description.",";
								$cost .= $room->TotalEstimatedStay.",";
							}
							if ($cnt > 1 ) {
								// strip off final commas 
								$roomtype = substr($roomtype,0,strrpos($roomtype,","));
								$roomtypeName = substr($roomtypeName,0,strrpos($roomtypeName,","));
								$cost = substr($cost,0,strrpos($cost,","));
							}
							
						} else {
							// only one result returns object not array
							$roomtype .= $avail_rooms->RoomType;
							$roomtypeName .= $avail_rooms->Description;
							$cost .= $avail_rooms->TotalEstimatedStay;
							$seasonnum = $avail_rooms->RatesByDate->Rate->SeasonNumber;
						}
						// save these as fields on form

						$ninja_forms_processing->update_field_value(13, $roomtype);
						$ninja_forms_processing->update_field_value(14, $roomtypeName);
						$ninja_forms_processing->update_field_value(15, $cost);
						$ninja_forms_processing->update_field_value(80, $seasonnum);
						/* get rooms not available */
						$other_rooms = $result->GetAvailableRoomtypesResult->OtherRoomTypes->nlRoomType;
						$otr_roomtypeName = "";
						$otr_roomtype = "";
						$otr_cost =""; 
						if (gettype($other_rooms) == 'array') {
							$cnt = 0;
							foreach ($other_rooms as $room) {
								$cnt = $cnt + 1; 
								$otr_roomtype .= $room->RoomType.",";
								$otr_roomtypeName .= $room->Description.",";
								$otr_cost .= $room->TotalEstimatedStay.",";
							}
							if ($cnt > 1 ) {
								// strip off final commas 
								$otr_roomtype = substr($otr_roomtype,0,strrpos($otr_roomtype,","));
								$otr_roomtypeName = substr($otr_roomtypeName,0,strrpos($otr_roomtypeName,","));
								$otr_cost = substr($otr_cost,0,strrpos($otr_cost,","));
							}
						} else {
							// only one result returns object
							$otr_roomtype .= $room->RoomType;
							$otr_roomtype .= $room->Description;
							$otr_cost .= $room->TotalEstimatedStay;
						}
						// save these as fields on form - maybe

						//$ninja_forms_processing->add_error('bhm_nitelink_check_avail10',"in bhm_nitelink_check_avail1: ".$form_error_msg , "general");	 
					} 
				}
			} else { // no results
				$form_error_msg = " no results ";
				$ninja_forms_processing->add_error('bhm_nitelink_check_avail3',"in bhm_nitelink_check_avail1: ".$form_error_msg , "general");	 

			}
		}
	} // end bhm_nitelink_check_avail_http
?>