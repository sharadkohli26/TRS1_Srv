<?php

class CommonFunctions {
	/**
	 * Checks whether a string is a valid unix timestamp
	 * @param string $timestamp
	 * @return boolean
	 */
	public static function isValidTimeStamp($timestamp) {
		return ((string)(int)$timestamp === $timestamp) && ($timestamp <= PHP_INT_MAX) && ($timestamp >= ~PHP_INT_MAX);
	}
	
	/**
	 * Return the date time string in the foormat Y-m-d H:i:s
	 * @param string $mdatetime the date time string
	 * 
	 * @throws exception if not valid date time string!!!
	 * 
	 * @return string the date time string in specified format
	 */
	public static function GetDateTime_YMDHIS($mdatetime){
		//returns the date time string or raises an exception
		if (empty($mdatetime) or !strtotime($mdatetime)) {
			MyErrorHandeler::UserError(EXCP_ERR2588, debug_backtrace(), array());
			throw new Exception(USER_INVALIDTIMEDATE);	
		}
		 return date('Y-m-d H:i:s',strtotime($mdatetime));		
	}
	
	/**
	 * Return the date time string in the foormat Y-m-d 23:59:59
	 * @param string $mdatetime the date time string
	 * 
	 * @throws exception if not valid date time string!!!
	 * 
	 * @return string the date time string in specified format
	 */
	public static function GetDateTime_YMD_DayEnd($mdatetime){
		//returns the date time string or raises an exception
		if (empty($mdatetime) or !strtotime($mdatetime)) {
			MyErrorHandeler::UserError(EXCP_ERR2588, debug_backtrace(), array());			
			throw new Exception(USER_INVALIDTIMEDATE);	
		}
		 return date('Y-m-d 23:59:59',strtotime($mdatetime));
	}
	
	/**
	 * Return the date time string in the foormat Y-m-d 00:00:00
	 * @param string $mdatetime the date time string
	 * 
	 * @throws exception if not valid date time string!!!
	 * 
	 * @return string the date time string in specified format
	 */
	public static function GetDateTime_YMD_DayStart($mdatetime){
		//returns the date time string or raises an exception
		if (empty($mdatetime) or !strtotime($mdatetime)) {
			MyErrorHandeler::UserError(EXCP_ERR2588, debug_backtrace(), array());
			throw new Exception(USER_INVALIDTIMEDATE);	
		}
		 return date('Y-m-d 00:00:00',strtotime($mdatetime));
	}
	
	/**
	 * Check and returns the expected duration
	 * @param the numeric expected duration
	 * @return same
	 * @throws Exception
	 */
	public static function SetExpectedDuration($supExpD){
		/*
		 "" (an empty string)
		 0 (0 as an integer)
		 0.0 (0 as a float)
		 "0" (0 as a string)
		 NULL
		 FALSE
		 array() (an empty array)
		 $var; (a variable declared, but without a value)*/
		$mExpD=trim($supExpD);
		if(empty($mExpD) or !ctype_digit($mExpD) or ($mExpD<0)){
			//MyErrorHandeler::UserError(EXCP_ERR598, debug_backtrace(), array());
			throw new Exception(USER_INVALIDTIMEDURATION);
		}
		
		return $mExpD;
	}
	
	public static function SetGuestNum($supGuestNum){
		/*
		 "" (an empty string)
		 0 (0 as an integer)
		 0.0 (0 as a float)
		 "0" (0 as a string)
		 NULL
		 FALSE
		 array() (an empty array)
		 $var; (a variable declared, but without a value)*/
		$mGuestNum=trim($supGuestNum);
		if(empty($mGuestNum) or !ctype_digit($mGuestNum) or ($mGuestNum<0)){
			//MyErrorHandeler::UserError(EXCP_CBERR158, debug_backtrace(), array());
			throw new Exception(USER_INVALIDGUESTNUM);
		}
		
		return $mGuestNum;
	}
	
	public static function CompareDateTime_BookedFor($a,$b){
		$ad = new DateTime($a['BookedFor']);
		$bd = new DateTime($b['BookedFor']);
		if ($ad == $bd) {
    		return 0;
		}
  	return $ad > $bd ? 1 : -1;
	}
	
	public static function CompareDateTime($a,$b){
		$ad = new DateTime($a);
		$bd = new DateTime($b);
		if ($ad == $bd) {
    		return 0;
		}
  	return $ad > $bd ? 1 : -1;
	}

  public static function GetMYSQLI_BindValueTypeString_IorS($minarr){
  	$outstr="";
  	//$outarr = array();
  	foreach ($minarr as $bindval) {
		if(is_numeric($bindval))
			$outstr=$outstr."i";
			//$outarr[] = "i";
		else {
			$outstr=$outstr."s";
			//$outarr[] = "s";
		}
	  }
	return $outstr;
  }
  
	public static function GetABStatusString_FromStatus($mstatus){
  	switch($mstatus){
		case AB_APPROVED_STATUS:
			$out= AB_APPROVED_STATUSTYPE;
			break;
		case AB_REQUESTED_STATUS:
			$out =AB_REQUESTED_STATUSTYPE;
			break;
		case AB_UNKNOWN_STATUS:
			$out = AB_UNKNOWN_STATUSTYPE;
			break;
		case AB_CALLETC_CONFIRMED_STATUS:
			$out = AB_CALLETC_CONFIRMED_STATUSTYPE;
			break;
		case AB_CALLETC_NOTCONFIRMED_STATUS:
			$out = AB_CALLETC_NOTCONFIRMED_STATUSTYPE;
			break;
		case AB_USERCONFIRMED_USERCALLED_ANDCANCELLED_STATUS:
			$out = AB_USERCONFIRMED_USERCALLED_ANDCANCELLED_STATUSTYPE;
			break;
		case AB_USERCONFIRMED_AND_NOSHOW_STATUS:
			$out=AB_USERCONFIRMED_AND_NOSHOW_STATUSTYPE;
			break;
		case AB_NOTCONFIRMED_CANCELLED_STATUSTYPE:
			$out=AB_NOTCONFIRMED_CANCELLED_STATUSTYPE;
			break;
		case AB_CONFIRMED_CONVERTEDCURRENT_STATUS:
			$out=AB_CONFIRMED_CONVERTEDCURRENT_STATUSTYPE;
			break;
		case AB_NOTCONFIRMED_CONVERTEDCURRENT_STATUS:
			$out=AB_NOTCONFIRMED_CONVERTEDCURRENT_STATUSTYPE;
			break;
		default:
			//MyErrorHandeler::UserError(EXCP_ABERR588, debug_backtrace(), array());
			throw new Exception(USER_ABINVALIDSTATUS);
	}
	return $out;
  }


	public static function GetOnlineStatusCode_FromOnlineStatus($mStatus){
		$Status = trim($mStatus);
		if(strcasecmp($Status, "Yes")==0)
			$Code = RT_OLSTATUS_AVAILABLEONLINE;
		elseif (strcasecmp($Status, "No")==0) 
			$Code = RT_OLSTATUS_UNAVAILABLEONLINE;
		else{
			throw new Exception(USER_RRT_INVALID_ONLINESTATUS);
		}
		return $Code;
	}
	
	public static function GetOnlineStatus_FromOnlineStatusCode($mcode){
		switch($mcode){
			case RT_OLSTATUS_AVAILABLEONLINE:
				$Status = "Yes";
				break;
			case RT_OLSTATUS_UNAVAILABLEONLINE:
				$Status = "No";
				break;
			default:
				throw new Exception(USER_RRT_INVALID_ONLINESTATUSCODE);
		}
		return $Status;
	}
	
	public static function GetTableStatus_FromTableStatusCode($mcode){
		switch ($mcode) {
			case RT_ACTIVESTATUS:
				$Status = "Active";
				break;
			case RT_DISABLEDSTATUS:
				$Status = "InActive";
				break;
			default:	
				throw new Exception(USER_RRT_INVALID_TABLE_STATUSCODE);
		}
		return $Status;
	}

}
?>
