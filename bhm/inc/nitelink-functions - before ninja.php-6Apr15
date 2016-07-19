<?php /* nitelink support functions */
if (!function_exists('nitelink_build_avail_results') ) {
	function nitelink_build_avail_results ($nitelinkID, $in_dt_arrival, $in_dt_depart, $in_adults, $in_kids, $in_obj_results) {
	/* returns a html formatted string of availability result */	

		$got_results = true;
		$html_result = "";
		$html_result .= '<div id="bhm_check_avail_results" class="nitelink-box" >';
		$html_result .= '<div id="bhm_avail_head"><h2 class="bhm-check_avail_title">Room Availibility</h2>';
		$html_result .=	  '<div class="bhm_avail_headdates">'.date_format($in_dt_arrival,"n/j/Y").' to '.date_format($in_dt_depart,"n/j/Y").'</div>';
		$html_result .= 	  '<div class="bhm_avail_headdesc">'.$in_adults.' Adult(s),  '.$in_kids.' Child(ren) </div>';
		$html_result .= '</div>'; // end bhm_avail_head

		if ($in_obj_results->GetAvailableRoomtypesResult->AvailableRoomTypes == new stdClass()) {
			$html_result .= '<div class="bhm-no-rooms">No available rooms.</div>';
		} else {
			/* build the results */
			$avail_rooms = $in_obj_results->GetAvailableRoomtypesResult->AvailableRoomTypes->nlRoomType;
			$html_result .= '<form action="https://www.nitelink.com/nitelink21/step3.asp" method="post">';
			$html_result .= '<input type="hidden" name="Arrival" value="'.date_format($in_dt_arrival,"n/j/Y").'" >';
			$html_result .= '<input type="hidden" name="Departure" value="'.date_format($in_dt_depart,"n/j/Y").'" >';
			$html_result .= '<input type="hidden" name="Adults" value="'.$in_adults.'" >';
			$html_result .= '<input type="hidden" name="Children" value="'.$in_kids.'" >';
			$html_result .= '<input type="hidden" name="UserID" value="'.$nitelinkID.'" >';
			$html_result .= '<table id="bh_avail_tab" class="bhm-table bhm_avail_tab"><tbody>';
			$html_result .= '<tr class="bhm_avail_tabhead">';
			/* $html_result .= 	'<td></td>'; */
			/* $html_result .= 	'<td>Room<br>Type</td>'; */
			$html_result .= 	'<td class="bhm_estimate" colspan="3">Estimated Stay</td>';
			$html_result .= '</tr>';
			if (gettype($avail_rooms) == 'array') {
				$cnt = 0;
				foreach ($avail_rooms as $room) {
					$cnt = $cnt + 1; 
					if ($cnt == 1) { $checked = "checked"; } else { $checked = ""; }
					$html_result .= '<tr>';
					$html_result .= '<td><input type="radio" name="RoomType" '.$checked.'  value="'.$room->RoomType.'"> </td>';
					$html_result .= '<td>'.$room->Description.'</td>';
					$html_result .= '<td class="price">'.$room->TotalEstimatedStay.'</td>';
					$html_result .= '<input type="hidden" name="Season'.$room->RoomType.'" value="SeasonNumber'.$room->SeasonNumber.'">';
					$html_result .= '</tr>';
				}
			} else {
				/* only one result returns object */
				$room = $avail_rooms;
				$html_result .= '<tr>';
				$html_result .= '<td><input type="radio" name="RoomType" checked  value="'.$room->RoomType.'"> </td>';
				$html_result .= '<td>'.$room->Description.'</td>';
				$html_result .= '<td class="price">'.$room->TotalEstimatedStay.'</td>';
				$html_result .= '<input type="hidden" name="Season'.$room->RoomType.'" value="SeasonNumber'.$room->SeasonNumber.'">';
				$html_result .= '</tr>';
			}
			$html_result .= '</tbody></table>';
			/* $html_result .=  '<p>Rates are subject to Maine State Sales Tax.</p>'; */
			$html_result .= '<div id="bookit_box">';
			$html_result .=	 '<input name="bookit" type="submit" id="bookit" class="blue_sidebar_link bhm-blue_sidebar_box" value="Make Reservation" />';
			$html_result .= 	 '<input name="action" type="hidden" id="action" value="bookit" />';
			$html_result .= '</div>';
			/* $html_result .= '<div id="bhm_bookit"><a class="bhm-blue_sidebar_link bhm-arrow_after" href="https://www.nitelink.com/nitelink21/main.asp?p=1204">Reserve Now</a>'; */
			$html_result .= '</form>';		
		}
		$html_result .= '</div>'; // end "bhm_avail_box" 
		return ($html_result);
	} /* end function nitelink_build_avail_results */
}
?>