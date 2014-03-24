<?php

class CommonFunctionsDateTime {
	
	/**
	 * Return the date time string in the foormat Y-m-d H:i rounded to nearest quarter
	 * @param string $mdatetime the date time string, if empty you get current time in the specified format
	 * 
	 * @throws exception if not valid date time string!!!
	 * 
	 * @return string;the date time string in specified format
	 */	
	public static function GetDT_NearestQuarter_YMDHI($mdatetime){
		$seconds=0;
		if(is_null($mdatetime) or empty($mdatetime)){
			$seconds = time();
		}
		elseif (!strtotime($mdatetime)) {
			MyErrorHandeler::UserError(USER_INVALIDTIMEDATE,debug_backtrace(), array());
			throw new Exception(USER_INVALIDTIMEDATE);	
		}
		else{
			$seconds = strtotime($mdatetime);
		}
		$rounded_seconds = round($seconds / (15 * 60)) * (15 * 60);
		return date('Y-m-d H:i',$rounded_seconds);
	}
	
	/**
	 * Return the date time string in the foormat Y-m-d H:i:s
	 * @param string $mdatetime the date time string, if empty you get current time in the specified format
	 * 
	 * @throws exception if not valid date time string!!!
	 * 
	 * @return string the date time string in specified format
	 */
	public static function GetDT_YMDHIS($mdatetime){
		$seconds=0;
		if(is_null($mdatetime) or empty($mdatetime)){
			$seconds = time();
		}
		elseif (!strtotime($mdatetime)) {
			throw new Exception(USER_INVALIDTIMEDATE);	
		}
		else{
			$seconds = strtotime($mdatetime);
		}
		 return date('Y-m-d H:i:s',$seconds);
	}
	
	/**
	 * Return the date time string in the foormat Y-m-d 23:59:59
	 * @param string $mdatetime the date time string, if empty you get current time in the specified format
	 * 
	 * @throws exception if not valid date time string!!!
	 * 
	 * @return string the date time string in specified format
	 */
	public static function GetDT_YMD_DayEnd($mdatetime){
		$seconds=0;
		if(is_null($mdatetime) or empty($mdatetime)){
			$seconds = time();
		}
		elseif (!strtotime($mdatetime)) {
			throw new Exception(USER_INVALIDTIMEDATE);	
		}
		else{
			$seconds = strtotime($mdatetime);
		}
		 return date('Y-m-d 23:59:59',$seconds);
	}
	
	/**
	 * Return the date time string in the foormat Y-m-d 00:00:00
	 * @param string $mdatetime the date time string, if empty you get current time in the specified format
	 * 
	 * @throws exception if not valid date time string!!!
	 * 
	 * @return string the date time string in specified format
	 */
	public static function GetDT_YMD_DayStart($mdatetime){
		$seconds=0;
		if(is_null($mdatetime) or empty($mdatetime)){
			$seconds = time();
		}
		elseif (!strtotime($mdatetime)) {
			throw new Exception(USER_INVALIDTIMEDATE);	
		}
		else{
			$seconds = strtotime($mdatetime);
		}
		 return date('Y-m-d 00:00:00',$seconds);
	}
	
	/**
	 * Check and returns the expected duration, Minutes
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
		if(empty($mExpD) or !ctype_digit($mExpD)){
			//MyErrorHandeler::UserError(EXCP_ERR598, debug_backtrace(), array());
			throw new Exception(USER_INVALIDTIMEDURATION);
		}
		
		return $mExpD;
	}
	/**
	 * Compares DateTime for two single array elements with key BookedFor
	 *TODO:: make the key a parameter
	 * @return 1 if $a>$b, 0 if equal -1 negative
	 * @author  
	 */
	public static function CompareDT_BookedFor($a,$b){
		$ad = new DateTime($a['BookedFor']);
		$bd = new DateTime($b['BookedFor']);
		if ($ad == $bd) {
    		return 0;
		}
  	return $ad > $bd ? 1 : -1;
	}
	/**
	 * Compares DateTime for two single elements,returns 1 if $a>$b
	 * @return int
	 * @author  
	 */
	public static function CompareDT($a,$b){
		$ad = new DateTime($a);
		$bd = new DateTime($b);
		if ($ad == $bd) {
    		return 0;
		}
  	return $ad > $bd ? 1 : -1;
	}

}
?>
