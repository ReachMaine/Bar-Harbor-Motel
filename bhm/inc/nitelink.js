// nitelink js functions 
	function nitelink_validate_avail() {
		//var ok, adults, kids, nights, arrive;
		//var errmsg;
		//ok = true;
		//errmsg = "";
		//document.getElementById("bhm-check_avail_box").style.color = "red";
		document.getElementById("bhm_check_avail_results").style.display = "none";
		document.getElementById("bhm_avail_errormsg").style.display = "block";
		document.getElementById("bhm_avail_errormsg").innerHTML = "test";
		//nights = document.getElementById("numdays").value;
		//arrive = document.getElementById("arrive").value;
		//adults = document.getElementById("numadults").value;
		//kids = document.getElementById("numkids").value;
		//if (isNaN(adults) || adults < 1 ) {
		//	ok = false;
		//	errmsg = "XXX Please enter number of adults";
		//}
		//if (ok <> true ) {
		// 	document.getElementById("bhm_avail_errormsg").innerHTML = errmsg;
		//}
		document.getElementById("bhm-check_avail_box").style.color = "red";
		return false;
	}
