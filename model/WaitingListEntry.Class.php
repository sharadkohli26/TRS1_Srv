<?php

class WaitingListEntry{
	private $dbcon=NULL;
	private $DBOO;

	public $HRID;
	public $UserID;
	//public $WaitID;
	//public $status;
	//public $starttime;
	//public $endtime;
	//public $Exp_Duration;
	//public $GuestNum;
	//public $AdvID;//if this was a advance booking then adv id, else -1
	//public $GuestName;
	//public $GuestContact;
	//public $GuestEmail;

	/**
	 * Default constructor for this class.
	 * Holds the handle to access database if successfully created (ie if dbcon initialised then successfull) else throws exception
	 * @throws Exception("Exception_DBerr11::Unable to connect to database , please try again later")
	 */
	public function __construct($mHRID,$mDBOO){
		if(is_null($mDBOO) || empty($mDBOO)){
			$this->DBOO=new DBOperations();
			$this->DBOO->SelectDatabase(DB_TRS1);
		}
		else{
			$this->DBOO=$mDBOO;
		}		
		//TODO: check if given HRID is valid and then proceed
		$this->HRID=$mHRID;
	}
	
	/**
	 * Returns WaitID if it is not empty and a proper timestamp
	 * @param string $supWaitID
	 * @throws Exception
	 * @return string
	 */
	public function SetWaitID($mWaitID){
		$WaitID=trim($mWaitID);
		if(empty($WaitID) or !CommonFunctions::isValidTimeStamp($WaitID)){
			throw new Exception(USER_WLINVALIDID);
		}
		//check wether it exists or not in db
		$status =WL_CHECKWAITID_FORWAITID_RES;
		$dummytime = date('Y-m-d');
		$sqlquery_str = $this->GetDBQueryString_WaitingList($dummytime,$dummytime,$status,array("WaitID"=>$WaitID));
		$out = $this->DBOO->SelectCount($sqlquery_str);
		if($out!=1){
			throw new Exception(USER_WLINVALIDID);
		}
		return $WaitID;
	}

	public function SetAdvanceID($mAdvID){
		$mAdvID=trim($mAdvID);
		if(empty($mAdvID))
			return CB_NO_ADVANCEID;
		$abentry = new AdvanceBookingEntry($this->HRID,$this->UserID,$this->DBOO);
		$AdvID = $abentry->SetABID($mABID);
		return $AdvID;
	}

	
	public function AddToWaitingList($mExpD,$mGuestNum,$mAdvID,$mGuestName,$mGuestContact,$mGuestEmail,$mgid,$mNote){

		$WaitID=time();
		$status=WL_LIVE_STATUS;
		$starttime= CommonFunctionsDateTime::GetDT_YMDHIS(NULL);
		$endtime=NOEND_TIMESTAMP;
		$GuestID = $mgid;
		$Note = $mNote;
		$Exp_Duration = CommonFunctions::SetExpectedDuration($mExpD);
		$GuestNum = CommonFunctions::SetGuestNum($mGuestNum);
		$AdvID = $this->SetAdvanceID($mAdvID);//if this was a advance booking then adv id, else -1
		$GuestName=$mGuestName;
		$GuestContact=$mGuestContact;
		$GuestEmail=$mGuestEmail;
		//add to db
		$this->DBOO->StartTransaction();
		
		//insert into guests...
		$guestobj = new GuestsEntry($this->HRID,$this->UserID,$this->DBOO);
		$result = $guestobj->NewGuest($GuestID,$GuestContact, $GuestName,$GuestEmail, NULL, NULL);
		//$guestobj->NewGuest($mguid, $mcontactnumber, $mname, $memail, $malternatecontact, $mcomment)
		if($result[RESULT_STATUS]){
			$GuestUID = $result[RESULT_PAYLOAD][0][OUT_GUEST_UID];
		}
		else{
			$this->DBOO->Rollback();
			return $result;
		}
		
		$colnames=array(WL_HRID,WL_USERID,WL_WAITID,WL_STATUS,WL_STARTTIME,WL_ENDTIME,WL_EXPDURATION,WL_GUESTNUM,WL_NOTES,WL_GUESTUID,WL_ADVANCEID);
		$tablename=DBT_WAITINGLIST;
		$colvalues=array(array($this->HRID,$this->UserID,$WaitID,$status,$starttime,$endtime,$Exp_Duration,$GuestNum,$Note,$GuestUID,$AdvID));
		$this->DBOO->Insert($tablename, $colnames, $colvalues);	
		$this->DBOO->Commit();
		$out = $this->GetAllDetails_ForWaitID($WaitID);
		return $out;
	}
		
	public function GetCurrentLiveWaitingList(){
		$dummytime=date('Y-m-d');
		$status=WL_LIVE_STATUS;
		$result =array();
		$out= $this->GetWaitingList($dummytime,$dummytime,$status,array());
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function GetCurrentClosedWaitingList(){
		$mdatetime=date('Y-m-d');
		$starttime_start=CommonFunctionsDateTime::GetDT_YMD_DayStart($mdatetime);
		$starttime_end=CommonFunctionsDateTime::GetDT_YMD_DayEnd($mdatetime);
		$status=WL_CLOSED_LIST;
		$result =array();
		$out= $this->GetWaitingList($starttime_start,$starttime_end,$status,array());
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function GetAllWaitingList($starttime_start,$starttime_end){
		//$starttime_start=date('Y-m-d');
		//echo $starttime_start."<br>".$starttime_end."<br>";
		$starttime_start=CommonFunctionsDateTime::GetDT_YMD_DayStart($starttime_start);
		$starttime_end=CommonFunctionsDateTime::GetDT_YMD_DayEnd($starttime_end); 
		$status=WL_ALL_LIST;
		$out=$this->GetWaitingList($starttime_start,$starttime_end,$status,array());
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function GetAllDetails_ForWaitID($mWaitID){
		$extra_array = array();
		$extra_array['WaitID']=$this->SetWaitID($mWaitID);
		$mstatus=WL_FORWAITID_RES;
		$dummytime=date('Y-m-d');
		$out= $this->GetWaitingList($dummytime, $dummytime, $mstatus, $extra_array);
		$result=array();
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function UpdateBookingDetails_ForWaitID($mWaitID,$mGuestNum,$mExpectedWaitingTime,$mNotes){
		$WaitID = $this->SetWaitID($mWaitID);
		$currstatus = (int)$this->GetStatus_WaitID_NoChecks($WaitID);
		if($currstatus!=WL_LIVE_STATUS){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_WLUPDATE_FAIL_CLOSED);
			return $result;
		}
		
		$ExpectedWaitingTime=CommonFunctions::SetExpectedDuration($mExpectedWaitingTime);
		$GuestNum = CommonFunctions::SetGuestNum($mGuestNum);
		$Notes = $mNotes;
		
		//now update the duration, guest num, and notes
		$update_set_array = array(WL_EXPDURATION,WL_GUESTNUM,WL_NOTES);
		$update_set_QString=$this->DBOO->Update_Set_QString($update_set_array);
		$update_where_array = array(WL_HRID,WL_WAITID);
		$update_where_QString = $this->DBOO->Update_WhereEQAnd_QString($update_where_array);
		$sqlquery_str="UPDATE ".DBT_WAITINGLIST." ".$update_set_QString." WHERE ".$update_where_QString;
		$bindvalarr=array($ExpectedWaitingTime,$GuestNum,$Notes,$this->HRID,$WaitID);
		$affectedrows = $this->DBOO->Update($sqlquery_str, $bindvalarr);
		
		$result = $this->GetAllDetails_ForWaitID($WaitID);
		return $result;
	}
	
	
	private function GetDBQueryString_WaitingList($starttime_start,$starttime_end,$status,$extra_array){
		$mHRID=$this->DBOO->RealEscapeString($this->HRID);
		$mstarttime_start=$this->DBOO->RealEscapeString($starttime_start);
		$mstarttime_end=$this->DBOO->RealEscapeString($starttime_end);

		$selectarr=array(WL_WAITID=>"WaitID",WL_STATUS=>"Status",WL_STARTTIME=>"WaitStart",WL_ENDTIME=>"WaitEnd",WL_EXPDURATION=>"ExpectedWaiting",WL_GUESTNUM=>"GuestNum",WL_NOTES=>"Notes",WL_GUESTUID=>OUT_GUEST_UID
					,GUEST_CONTACTNUMBER=>OUT_GUEST_CONTACTNUMBER,GUEST_NAME=>OUT_GUEST_NAME,GUEST_EMAIL=>OUT_GUEST_EMAIL,GUEST_ALTERNATECONTACTNUMBER=>OUT_GUEST_ALTERNATECONTACTNUMBER,GUEST_COMMENT=>OUT_GUEST_COMMENT
					);
		$joinstr = " LEFT OUTER JOIN ".DBT_GUESTS
			." ON ".GUEST_HRID."=".$mHRID." AND ".GUEST_UID."=".WL_GUESTUID;

		$selectstr = $this->DBOO->GetSelectColumn($selectarr);

		if($status==WL_LIVE_STATUS ){
			$sqlquery_str=$selectstr
			." FROM ".DBT_WAITINGLIST
			.$joinstr
			." WHERE ".WL_HRID."=".$mHRID
			." AND ".WL_STATUS."=".$status
			." ORDER BY ".WL_STARTTIME;
		}
		elseif($status==WL_CONVTOCURRENT_STATUS or $status==WL_NOTCONVTOCURRENT_STATUS){
			$sqlquery_str=$selectstr
			." FROM ".DBT_WAITINGLIST
			.$joinstr
			." WHERE ".WL_HRID."=".$mHRID
			." AND ".WL_STARTTIME." BETWEEN ".$mstarttime_start." AND ".$mstarttime_end
			." AND ".WL_STATUS."=".$status
			." ORDER BY ".WL_STARTTIME;
		}
		elseif($status==WL_ALL_LIST) {
			$sqlquery_str=$selectstr
			." FROM ".DBT_WAITINGLIST
			.$joinstr
			." WHERE ".WL_HRID."=".$mHRID
			." AND ".WL_STARTTIME." BETWEEN ".$mstarttime_start." AND ".$mstarttime_end
			." ORDER BY ".WL_STARTTIME;
		}
		elseif ($status==WL_CLOSED_LIST) {
			$sqlquery_str=$selectstr
			." FROM ".DBT_WAITINGLIST
			.$joinstr
			." WHERE ".WL_HRID."=".$mHRID
			." AND ".WL_STARTTIME." BETWEEN ".$mstarttime_start." AND ".$mstarttime_end
			." AND ".WL_STATUS."<>".WL_LIVE_STATUS
			." ORDER BY ".WL_STARTTIME;
		}
		elseif($status==WL_FORWAITID_RES){
			$mwaitid = $this->DBOO->RealEscapeString($extra_array['WaitID']);
			$sqlquery_str=$selectstr
			." FROM ".DBT_WAITINGLIST
			.$joinstr
			." WHERE ".WL_HRID."=".$mHRID." AND ".WL_WAITID."=".$mwaitid;
		}
		elseif($status==WL_STATUSFORWAITID_RES){
			$mwaitid = $this->DBOO->RealEscapeString($extra_array['WaitID']);
			$selectarr=array(WL_STATUS=>"Status");
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$sqlquery_str=$selectstr
			." FROM ".DBT_WAITINGLIST
			." WHERE ".WL_HRID."=".$mHRID." AND ".WL_WAITID."=".$mwaitid;
		}
		elseif($status==WL_CHECKWAITID_FORWAITID_RES){
			$mwaitid = $this->DBOO->RealEscapeString($extra_array['WaitID']);
			$sqlquery_str="SELECT COUNT(*)"
			." FROM ".DBT_WAITINGLIST
			." WHERE ".WL_HRID."=".$mHRID." AND ".WL_WAITID."=".$mwaitid;
		}		
		else{
			MyErrorHandeler::UserError(EXCP_WLINVALIDSTATUS, debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID));
			throw new Exception(USER_WLINVALIDSTATUS);
		}
		return $sqlquery_str;
	}
	
	private function GetWaitingList($starttime_start,$starttime_end,$status,$extra_array){
		$out=array();
		$sqlquery_str=$this->GetDBQueryString_WaitingList($starttime_start, $starttime_end, $status, $extra_array);
		$SelectReturnArr=$this->DBOO->Select($sqlquery_str);

		foreach ($SelectReturnArr as $curr_row) {
			$curr_row['WaitStart']=CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI($curr_row['WaitStart']);
			$mExpectedEnd = CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI(date('Y-m-d H:i',strtotime($curr_row['WaitStart'])+60*(int)$curr_row['ExpectedWaiting']));
			$curr_row['ExpectedWaitingEndTime']=$mExpectedEnd;
			$curr_row[OUT_GUEST_CONTACTNUMBER]=(int)$curr_row[OUT_GUEST_CONTACTNUMBER]<0?"":$curr_row[OUT_GUEST_CONTACTNUMBER];
			if($curr_row['Status']==WL_LIVE_STATUS){
				$curr_row['Status']="Waiting";
				$curr_row['WaitEnd']="";
			}
			elseif($curr_row['Status']==WL_CONVTOCURRENT_STATUS){
				$curr_row['Status']="ClosedToCurrent";
				$curr_row['WaitEnd']=CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI($curr_row['WaitEnd']);
			}
			elseif($curr_row['Status']==WL_NOTCONVTOCURRENT_STATUS or $curr_row['Status']==WL_AUTOCLOSED_STATUS){
				$curr_row['Status']="Closed";
				$curr_row['WaitEnd']=CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI($curr_row['WaitEnd']);
			}
			$out[]=$curr_row;
		}
	return $out;
	}
	
	public function CloseWL_NotConvCurrent($WaitID){
		$extra_array=array();$result=array();
		$extra_array['WaitID']=$this->SetWaitID($WaitID);
		$currstatus = (int)$this->GetStatus_WaitID_NoChecks($extra_array['WaitID']);
		if($currstatus!=WL_LIVE_STATUS){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_WLCLOSEFAILALREADYCLOSED);
			return $result;
		}
		$status=WL_NOTCONVTOCURRENT_STATUS;
		$endtime=CommonFunctionsDateTime::GetDT_YMDHIS(NULL);
		$out=$this->CloseWLEntry($status,$endtime,$extra_array);
		if($out==1){
			$result=$this->GetAllDetails_ForWaitID($WaitID);
		}
		else{
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_WLCLOSEFAIL);
		}
		return $result;
	}
	
	public function CloseWL_ConvCurrent($WaitID){
		$extra_array=array();$result=array();
		$extra_array['WaitID']=$this->SetWaitID($WaitID);
		
		$currstatus = (int)$this->GetStatus_WaitID_NoChecks($extra_array['WaitID']);
		if($currstatus!=WL_LIVE_STATUS){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_WLCLOSEFAILALREADYCLOSED);
			return $result;
		}
		$status=WL_CONVTOCURRENT_STATUS;
		$endtime=CommonFunctionsDateTime::GetDT_YMDHIS(NULL);
		$out=$this->CloseWLEntry($status,$endtime,$extra_array);
		if($out==1){
			$result=$this->GetAllDetails_ForWaitID($WaitID);
		}
		else{
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_WLCLOSEFAIL);
		}
		return $result;
	}
	public function CloseWL_AutoClosed(){
		$status=WL_AUTOCLOSED_STATUS;
		$endtime=CommonFunctionsDateTime::GetDT_YMDHIS(NULL);
		$extra_array=array();
		$out=$this->CloseWLEntry($status,$endtime,$extra_array);
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=array("EnteriesClosed"=>$out);
		return $result;
	}
	
	private function GetStatus_WaitID_NoChecks($WaitID){
		$out=NULL;
		$dummytime =date('Y-m-d H:i:s');
		$status = WL_STATUSFORWAITID_RES;
		$extra_arr['WaitID']=$WaitID;
		$sqlquery_str = $this->GetDBQueryString_WaitingList($dummytime, $dummytime, $status, $extra_arr);
		$SelectReturnArr = $this->DBOO->Select($sqlquery_str);
		foreach ($SelectReturnArr as $value) {
			$out = $value["Status"];
		}
		return $out;
	}
	
	private function CloseWLEntry($status,$endtime,$extra_array){
		//set rest of the variable
		//add to database
		if($status==WL_AUTOCLOSED_STATUS){
			$oldstatus=WL_LIVE_STATUS;
			$update_set_array = array(WL_ENDTIME,WL_STATUS);
			$update_where_array = array(WL_HRID,WL_STATUS);
			$bindvalarr=array($endtime,$status,$this->HRID,$oldstatus);
		}
		elseif ($status==WL_CONVTOCURRENT_STATUS or $status==WL_NOTCONVTOCURRENT_STATUS) {
			//$oldstatus=WL_LIVE_STATUS;
			$WaitID=$extra_array['WaitID'];
			$update_set_array = array(WL_ENDTIME,WL_STATUS);
			$update_where_array = array(WL_HRID,WL_WAITID);
			$bindvalarr=array($endtime,$status,$this->HRID,$WaitID);
		}
		else{
			MyErrorHandeler::UserError(EXCP_WLINVALIDSTATUS, debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID));
			throw new Exception(USER_WLINVALIDSTATUS);
		}

		$update_set_QString=$this->DBOO->Update_Set_QString($update_set_array);
		$update_where_QString = $this->DBOO->Update_WhereEQAnd_QString($update_where_array);
		$sqlquery_str="UPDATE ".DBT_WAITINGLIST." ".$update_set_QString." WHERE ".$update_where_QString;
		$affectedrows = $this->DBOO->Update($sqlquery_str, $bindvalarr);
		$out=$affectedrows;
		return $out;

	}
}

?>
     