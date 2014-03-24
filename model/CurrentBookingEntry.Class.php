
<?php

/**
 * A class that represents a current reservation entry
 * @author Sharad
 *
 */
class CurrentBookingEntry
{
	//private $dbcon=NULL;
	private $DBOO;

	public $HRID;//varchar that represents hotel-restaurant id
	//public $CBID;//id of this current booking
	//public $status;//int for denoting status of this booking, MOST imp
	//public $starttime;
	//public $endtime;
	//public $Exp_Duration;

	public $UserID;//user id of the user who is entering thi crr res
	//public $TBIDs;//array of TBIDs for this booking
	//public $WaitID;//if this was a waiting then waiting id, else -1
	//public $AdvID;//if this was a advance booking then adv id, else -1
	//public $GuestNum;
	//public $GuestName;
	//public $GuestContact;
	//public $GuestEmail;
	//public $GuestComment;

	/**
	 * Default constructor for this class.
	 * Holds the handle to access database if successfully created (ie if dbcon initialised then successfull) else throws exception
	 * @throws Exception("Exception_DBerr11::Unable to connect to database , please try again later")
	 */
	public function __construct($mHRID,$mUserID,$mDBOO){
				//TODO: check if given HRID is valid and then proceed
		$this->HRID=$mHRID;
		$this->UserID = $mUserID;
		if(is_null($mDBOO) || empty($mDBOO)){
			$this->DBOO=new DBOperations();
			$this->DBOO->SelectDatabase(DB_TRS1);
		}
		else{
			$this->DBOO=$mDBOO;
		}

		
	}

	/**
	 * Returns CBID if it is not empty and a proper timestamp and exist in database, else throws exception
	 * @param string $supCBID
	 * @throws Exception
	 * @return string
	 */
	public function SetCBID($mCBID){
		$CBID=trim($mCBID);$result=array();
		if(empty($CBID) or !CommonFunctions::isValidTimeStamp($CBID)){
			throw new Exception(USER_CB_INVALIDCBID);
		}
		//check wether it exists or not in db
		$status =CB_CHECKCBID_FORCBID_RES;
		$dummytime = date('Y-m-d');
		$sqlquery_str = $this->GetDBQueryString_CurrentBookings($dummytime,$dummytime,$status,array("CBID"=>$CBID));
		$out = $this->DBOO->SelectCount($sqlquery_str);
		if($out!=1){
			throw new Exception(USER_CB_INVALIDCBID);
		}
		return $CBID;
	}
	
	public function SetUserID($supUserID){
		$mUserID=trim($supUserID);
		if(empty($mUserID)){
			MyErrorHandeler::UserError(EXCP_CBERR168, debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID));
			throw new Exception(EXCP_CBERR168);
		}
		return $mUserID;
	}
	

	/**
	 * Sets waitid. If blank or given id not found in the waitdb then NOWAITID otherwise supplied id
	 * @param an integer as string $supWaitID
	 * @throws Exception
	 */
	public function SetWaitID($mWaitID){
		$mWaitID=trim($mWaitID);
		if (empty($mWaitID))
			return CB_NO_WAITID;
		$wlentry = new WaitingListEntry($this->HRID,$this->DBOO);
		$WaitID = $wlentry->SetWaitID($mWaitID);
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


	/**
	 * Checkks whether the supplied TBIDs are present in Restaurant's active list or not
	 * @param comma delimited string $supTBID, not empty !!
	 * @throws Exception
	 * @return string array
	 */
	public function SetTBID($supTBNames,$mRoomName){
		//assumes HRID has been set
		$mTBNames=explode(',',$supTBNames);
		$mTBNames=array_map("trim", $mTBNames);

		if (count($mTBNames)==0){
			throw new Exception(USER_EMPTYTABLELIST);
		}
		$mTBID=array();
		//$mTBID=$this->GetTBID_FromTableNames($mTBNames);
		$mTBID=$this->GetTBID_FromTableNames($supTBNames,$mRoomName);
		if(count($mTBID)==count($mTBNames)){
			return $mTBID;
		}
		else{
			MyErrorHandeler::UserError(EXCP_INVALIDTABLEID, debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID));
			throw new Exception(USER_INVALIDTABLEID);
		}
			
	}

	public function GetLiveBookings(){
		//returns the live bookings as of now
		$dummytime=date('Y-m-d');
		//$starttime_end=date('Y-m-d H:i:s');
		$status=CB_LIVE_STATUS;
		$out= $this->GetBookings($dummytime,$dummytime,$status,array());
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
		
	}

	public function GetCurrentClosedBookings($mdate){
		//returns all the closed bookings for the given date 
		//$mdate any date time string
		$starttime_start=CommonFunctionsDateTime::GetDT_YMD_DayStart($mdate);
		$starttime_end=CommonFunctionsDateTime::GetDT_YMD_DayEnd($mdate);
		$status=CB_CLOSED_RES;
		$out=  $this->GetBookings($starttime_start,$starttime_end,$status,array());
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function GetCurrentLiveClosedBookings($mdate){
		//returns all the closed (by user or closed when theyt were live) bookings today uptill now		
		$starttime_start=CommonFunctionsDateTime::GetDT_YMD_DayStart($mdate);
		$starttime_end=CommonFunctionsDateTime::GetDT_YMD_DayEnd($mdate);
		$status=CB_CLOSEDLIVE_STATUS;
		$out=  $this->GetBookings($starttime_start,$starttime_end,$status,array());
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	public function GetCurrentAutoClosedBookings($mdate){
		//returns all the auto closed bookings today uptill now
		$starttime_start=CommonFunctionsDateTime::GetDT_YMD_DayStart($mdate);
		$starttime_end=CommonFunctionsDateTime::GetDT_YMD_DayEnd($mdate);
		$status=CB_CLOSEDAUTO_STATUS;
		$out= $this->GetBookings($starttime_start,$starttime_end,$status,array());
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}	
	
	public function GetAllBookings($starttime_start,$starttime_end){
		$starttime_start=CommonFunctionsDateTime::GetDT_YMD_DayStart($starttime_start);
		$starttime_end=CommonFunctionsDateTime::GetDT_YMD_DayEnd($starttime_end);
		$status=CB_ALL_RES;
		$out=  $this->GetBookings($starttime_start,$starttime_end,$status,array());
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function GetAllDetails_ForCBID($mCBID,$CheckCBID){
		$extra_array=array();
		if($CheckCBID){
			$extra_array['CBID']=$this->SetCBID($mCBID);
		}
		else{
			$extra_array['CBID']=$mCBID;
		}
		
		$mstatus=CB_FORCBID_RES;
		$dummytime=date('Y-m-d');
		$out=  $this->GetBookings($dummytime,$dummytime,$mstatus,$extra_array);
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	/*
	public function UpdateExpectedDuration_ForCBID($mCBID,$mExpectedDuration){
		$result = array();
		$CBID=$this->SetCBID($mCBID);
		$ExpectedDuration=CommonFunctions::SetExpectedDuration($mExpectedDuration);
		//$mstatus=CB_FORCBID_UPDATE_EXPECTEDDURATION;
		$status = (INT)$this->GetStatus_CBID_NoChecks($CBID);
		if($status!=CB_LIVE_STATUS){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_CB_UPDATE_EXPTECTEDDURATION_FAIL_BOOKINGCLOSED);
			return $result;
		}
		
		$update_set_array = array(CB_EXPDURATION);
		$update_set_QString=$this->DBOO->Update_Set_QString($update_set_array);
		$update_where_array = array(CB_HRID,CB_CBID);
		$update_where_QString = $this->DBOO->Update_WhereEQAnd_QString($update_where_array);
		$sqlquery_str="UPDATE ".DBT_CURRENTBOOKINGS." ".$update_set_QString." WHERE ".$update_where_QString;
		$bindvalarr=array($ExpectedDuration,$this->HRID,$CBID);
		$affectedrows = $this->DBOO->Update($sqlquery_str, $bindvalarr);
		
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=array("CBID"=>$CBID,"ExpectedDuration"=>$ExpectedDuration);
		return $result;
	}
	*/
	public function UpdateBookingDetails_ForCBID($mCBID,$mRemoveFrom_RoomName,$mOccupiedTableNamesToRemove,$AddFrom_RoomName,$mFreeTableNamesToAdd,$mExpectedDuration,$mGuestNum,$mNotes){
		$result = array();
		$CBID=$this->SetCBID($mCBID);//Proceeds only if CBID exist
		$status = (INT)$this->GetStatus_CBID_NoChecks($CBID);
		if($status!=CB_LIVE_STATUS){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_CBUPDATE_FAIL_BOOKINGCLOSED);
			return $result;
		}
		$ExpectedDuration=CommonFunctions::SetExpectedDuration($mExpectedDuration);
		$GuestNum = CommonFunctions::SetGuestNum($mGuestNum);
		$Notes = $mNotes;
		//if there are no tables to remove/add do nothing else we do
		if(is_null($mOccupiedTableNamesToRemove) or empty($mOccupiedTableNamesToRemove)){
			$OccupiedTBID_ToRemove=null;
		}
		else{
			$OccupiedTBID_ToRemove = $this->SetTBID($mOccupiedTableNamesToRemove,$mRemoveFrom_RoomName);
		}
		
		if(is_null($mFreeTableNamesToAdd) or empty($mFreeTableNamesToAdd)){
			$FreeTBID_ToAdd=NULL;
		}
		else{
			$FreeTBID_ToAdd=$this->SetTBID($mFreeTableNamesToAdd,$AddFrom_RoomName);
		}
		
		//check if in transaction mode else enable
		$Already_InTransaction = $this->DBOO->GetTransactionMode();
		if(!$Already_InTransaction){
			$TransactionStarted = $this->DBOO->StartTransaction();
			if(!$TransactionStarted){
				//throw error..
				$result[RESULT_STATUS]=FALSE;
				$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_CBUPDATE_FAIL_DBTRANSACT_ERROR);
				MyErrorHandeler::UserError(EXCP_CB_TRANSACTIONSTART_FAIL, debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID,"CBID"=>$CBID));
				return $result;
			}
		}
		
		//REMOVE TABLES IF YOU HAVE TOO, ADD TABLES IF ANY ..
		if(!(is_null($OccupiedTBID_ToRemove))){
			$status=CB_CLOSETABLES_FORCBID_RES;
			$extra_array=array();
			$extra_array['CBID']=$CBID;
			$extra_array['TBID']=$OccupiedTBID_ToRemove;
			$endtime=CommonFunctionsDateTime::GetDT_YMDHIS(NULL);
			$NumTablesRemoved=$this->Close_CBT($endtime, $status, $extra_array);
			if(count($extra_array['TBID'])!=$NumTablesRemoved){
				$this->DBOO->Rollback();
				$result[RESULT_STATUS]=FALSE;
				$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_CB_REMOVETABLE_FAIL);
				return $result;
			}
		}
		
		//ADD TABLES IF ANY 
		if(!is_null($FreeTBID_ToAdd)){
			$starttime=CommonFunctionsDateTime::GetDT_YMDHIS(NULL);
		
			if (!$this->CurrentTableAvailability($FreeTBID_ToAdd)){
				$result[RESULT_STATUS]=FALSE;
				$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_CBADDTABLEFAIL);
				return $result;
			}
			$out=$this->AddCBTable_NoChecks($CBID, $FreeTBID_ToAdd, $starttime);
			if(!$out){
				$this->DBOO->Rollback();
				$result[RESULT_STATUS]=FALSE;
				$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_CBADDTABLEFAIL);
				return $result;
			}
		}
		
		//now update the duration, guest num, and notes
		$update_set_array = array(CB_EXPDURATION,CB_GUESTNUM,CB_NOTES);
		$update_set_QString=$this->DBOO->Update_Set_QString($update_set_array);
		$update_where_array = array(CB_HRID,CB_CBID);
		$update_where_QString = $this->DBOO->Update_WhereEQAnd_QString($update_where_array);
		$sqlquery_str="UPDATE ".DBT_CURRENTBOOKINGS." ".$update_set_QString." WHERE ".$update_where_QString;
		$bindvalarr=array($ExpectedDuration,$GuestNum,$Notes,$this->HRID,$CBID);
		$affectedrows = $this->DBOO->Update($sqlquery_str, $bindvalarr);
		
		//TODO::CHECK IF UPDATE HAPPENED, VALUES MIGHT NOT HAVE CHANGED BUT IF THE ACTION WAS PERFORMED SMOOTHLY
		
		if(!$Already_InTransaction){
			$this->DBOO->Commit();
		}
		$result = $this->GetAllDetails_ForCBID($CBID,FALSE);
		return $result;
	}
	
	private function GetAllActiveTablesInHR(){
		//$mrestable=new ResTables($this->HRID,$this->DBOO);
		//$AllTables = $mrestable->GetAllAvaialableTables();
		//return $AllTables;
		$rrt = new ResRoomsTablesEntry($this->HRID,$this->UserID,$this->DBOO);
		$result = $rrt->GetRoomTableDetails_TBID_Included_ForActiveRoomsActiveTable();
		return $result[RESULT_PAYLOAD];
	}
	
	private function GetTableDetails_FromTBID($mTBID){
		//$mrestable=new ResTables($this->HRID,$this->DBOO);
		//$mTableNames = $mrestable->GetTableNames_FromTBID($mTBID);
		//return $mTableNames;
		$rrt = new ResRoomsTablesEntry($this->HRID,$this->UserID,$this->DBOO);
		$result = $rrt->GetRoomTableDetails_ForTBID($mTBID, TRUE);
		return $result[RESULT_PAYLOAD];
	}
	
	private function GetTBID_FromTableNames($mTableNames,$mRoomName){
		//$mrestable=new ResTables($this->HRID,$this->DBOO);
		//$mTBID = $mrestable->GetTBID_FromTableNames($mTableNames);
		//return $mTBID;
		$rrt = new ResRoomsTablesEntry($this->HRID,$this->UserID,$this->DBOO);
		$result = $rrt->GetTBID_ForTableName_RoomName($mTableNames, TRUE, $mRoomName);
		return $result[RESULT_PAYLOAD];
	}	
	
	private function GetDBQueryString_CurrentBookings($starttime_start,$starttime_end,$status,$extra_arr){
		$selectarr=array(CB_USERID=>"UserID",CB_CBID=>"CBID",CB_STATUS=>"BookingStatus",CB_STARTTIME=>"BookingStart",CB_ENDTIME=>"BookingEnd",CB_EXPDURATION=>"ExpectedDuration",CB_GUESTNUM=>"GuestNum",CB_NOTES=>"Notes",CB_GUESTUID=>OUT_GUEST_UID
						,CBT_TBID=>"TBID",CBT_ENDTIME=>"TableEndTime"
						,GUEST_CONTACTNUMBER=>OUT_GUEST_CONTACTNUMBER,GUEST_NAME=>OUT_GUEST_NAME,GUEST_EMAIL=>OUT_GUEST_EMAIL,GUEST_ALTERNATECONTACTNUMBER=>OUT_GUEST_ALTERNATECONTACTNUMBER,GUEST_COMMENT=>OUT_GUEST_COMMENT
						,WL_STARTTIME=>"WaitStart",WL_ENDTIME=>"WaitEnd"
						,AB_STATUS=>"AdvanceStatus",AB_BOOKINGMETHOD=>"AdvanceBookingMethod",AB_ONDATETIME=>"AdvanceBookedOn",AB_FORDATETIME=>"AdvanceBookedFor"
				);
		$selectstr = $this->DBOO->GetSelectColumn($selectarr);
		$mHRID=$this->DBOO->RealEscapeString($this->HRID);
		$starttime_start=$this->DBOO->RealEscapeString($starttime_start);
		$starttime_end=$this->DBOO->RealEscapeString($starttime_end);
		$joinstr = 
				"LEFT OUTER JOIN ".DBT_WAITINGLIST
				." ON ".WL_WAITID."=".CB_WAITINGID." AND ".WL_HRID."=".$mHRID." AND ".WL_WAITID." IS NOT NULL "
				." LEFT OUTER JOIN ".DBT_ADVANCEBOOKINGS
				." ON ".AB_ABID."=".CB_ADVANCEID." AND ".AB_HRID."=".$mHRID." AND ".AB_ABID." IS NOT NULL"			
				." LEFT OUTER JOIN ".DBT_CURRENTBOOKINGSTABLE
				." ON ".CBT_CBID."=".CB_CBID." AND ".CBT_HRID."=".$mHRID
				." LEFT OUTER JOIN ".DBT_GUESTS
				." ON ".GUEST_HRID."=".$mHRID." AND ".GUEST_UID."=".CB_GUESTUID;
		
		if($status==CB_LIVE_STATUS){
			$sqlquery_str=$selectstr
			." FROM ".DBT_CURRENTBOOKINGS
			." ".$joinstr
			." WHERE ".CB_HRID."=".$mHRID." AND ".CB_STATUS."=".$status
			." ORDER BY ".CB_CBID;
		}
		elseif($status==CB_CLOSEDLIVE_STATUS){
			$sqlquery_str=$selectstr
			." FROM ".DBT_CURRENTBOOKINGS
			." ".$joinstr
			." WHERE ".CB_HRID."=".$mHRID." AND ".CB_STATUS."=".$status
			." AND ".CB_STARTTIME." BETWEEN $starttime_start AND $starttime_end"
			." ORDER BY ".CB_CBID;	
		}
		elseif($status==CB_CLOSEDAUTO_STATUS){
			$sqlquery_str=$selectstr
			." FROM ".DBT_CURRENTBOOKINGS
			." ".$joinstr
			." WHERE ".CB_HRID."=".$mHRID." AND ".CB_STATUS."=".$status
			." AND ".CB_STARTTIME." BETWEEN $starttime_start AND $starttime_end"
			." ORDER BY ".CB_CBID;
		}
		elseif ($status==CB_CLOSED_RES){
			$sqlquery_str=$selectstr
			." FROM ".DBT_CURRENTBOOKINGS
			." ".$joinstr
			." WHERE ".CB_HRID."=".$mHRID
			." AND ".CB_STARTTIME." BETWEEN $starttime_start AND $starttime_end"
			." AND (".CB_STATUS."=".CB_CLOSEDLIVE_STATUS." or ".CB_STATUS."=".CB_CLOSEDAUTO_STATUS.")"
			." ORDER BY ".CB_CBID;
			//return $sqlquery_str;
		}
		elseif($status==CB_ALL_RES){
			$sqlquery_str=$selectstr
			." FROM ".DBT_CURRENTBOOKINGS
			." ".$joinstr
			." WHERE ".CB_HRID."=".$mHRID
			." AND ".CB_STARTTIME." BETWEEN $starttime_start AND $starttime_end"
			." ORDER BY ".CB_CBID;
		}
		elseif($status==CB_FORCBID_RES){
			$mCBID=$extra_arr['CBID'];
			$mCBID=$this->DBOO->RealEscapeString($mCBID);
			$sqlquery_str=$selectstr
			." FROM ".DBT_CURRENTBOOKINGS
			." ".$joinstr
			." WHERE ".CB_HRID."=".$mHRID." AND ".CB_CBID."=".$mCBID
			." ORDER BY ".CB_CBID;
		}
		elseif ($status==CB_STATUSFORCBID_RES) {
			$mCBID=$extra_arr['CBID'];
			$mCBID=$this->DBOO->RealEscapeString($mCBID);
			$selectarr=array(CB_STATUS=>"BookingStatus");
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$sqlquery_str=$selectstr
			." FROM ".DBT_CURRENTBOOKINGS
			." WHERE ".CB_HRID."=".$mHRID." AND ".CB_CBID."=".$mCBID;
		}
		elseif($status==CB_CHECKCBID_FORCBID_RES){
			$mCBID = $extra_arr['CBID'];
			$mCBID=$this->DBOO->RealEscapeString($mCBID);
			$sqlquery_str="SELECT COUNT(*)"		
			." FROM ".DBT_CURRENTBOOKINGS
			." WHERE ".CB_HRID."=".$mHRID." AND ".CB_CBID."=".$mCBID;
		}
		elseif ($status == CB_WALKIN_STATSCOUNT) {
			$mGUID = $this->DBOO->RealEscapeString($extra_arr[OUT_GUEST_UID]);
			$sqlquery_str="SELECT COUNT(*)"		
			." FROM ".DBT_CURRENTBOOKINGS
			." WHERE ".CB_HRID."=".$mHRID." AND ".CB_GUESTUID."=".$mGUID
			." AND ".CB_ADVANCEID."=".CB_NO_ADVANCEID;
		}
		elseif($status == CB_WALKIN_HISTORY){
			$mGUID = $this->DBOO->RealEscapeString($extra_arr[OUT_GUEST_UID]);
			if(is_null($extra_arr['limitstart']) or is_null($extra_arr['limitend'])){
				$limitstr = "";
			}
			else{
				$start = $this->DBOO->RealEscapeString($extra_arr['limitstart']-1);
				$end = $this->DBOO->RealEscapeString($extra_arr['limitend']-1);
				$limitstr = " LIMIT ".$start.",".$end;
			}
			
			$selectarr=array(CB_USERID=>"UserID",CB_CBID=>"CBID",CB_STATUS=>"BookingStatus",CB_STARTTIME=>"BookingStart",CB_ENDTIME=>"BookingEnd",CB_GUESTNUM=>"GuestNum",CB_NOTES=>"Notes",CB_GUESTUID=>OUT_GUEST_UID
						,CBT_TBID=>"TBID"
			);

			$selectstr = $this->DBOO->GetSelectColumn($selectarr);

			$joinstr = 		
				" LEFT OUTER JOIN ".DBT_CURRENTBOOKINGSTABLE
				." ON ".CBT_CBID."=".CB_CBID." AND ".CBT_HRID."=".$mHRID;

			$sqlquery_str=$selectstr
			." FROM ".DBT_CURRENTBOOKINGS
			." ".$joinstr
			." WHERE ".CB_HRID."=".$mHRID." AND ".CB_GUESTUID."=".$mGUID." AND ".CB_ADVANCEID."=".CB_NO_ADVANCEID
			." ORDER BY ".CB_STARTTIME.",".CB_CBID." DESC"
			.$limitstr;
		}
		elseif($status == CB_RESERVATIONS_HISTORY){
			$mGUID = $this->DBOO->RealEscapeString($extra_arr[OUT_GUEST_UID]);
			if(is_null($extra_arr['limitstart']) or is_null($extra_arr['limitend'])){
				$limitstr = "";
			}
			else{
				$start = $this->DBOO->RealEscapeString($extra_arr['limitstart']-1);
				$end = $this->DBOO->RealEscapeString($extra_arr['limitend']-1);
				$limitstr = " LIMIT ".$start.",".$end;
			}
			$selectarr=array(CB_USERID=>"UserID",CB_CBID=>"CBID",CB_STATUS=>"BookingStatus",CB_STARTTIME=>"BookingStart",CB_ENDTIME=>"BookingEnd",CB_GUESTNUM=>"GuestNum",CB_NOTES=>"Notes",CB_GUESTUID=>OUT_GUEST_UID
						,CBT_TBID=>"TBID"
						,AB_STATUS=>"AdvanceStatus",AB_BOOKINGMETHOD=>"AdvanceBookingMethod",AB_ONDATETIME=>"AdvanceBookedOn",AB_FORDATETIME=>"AdvanceBookedFor"
			);
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$joinstr = 
				 " LEFT OUTER JOIN ".DBT_ADVANCEBOOKINGS
				." ON ".AB_ABID."=".CB_ADVANCEID." AND ".AB_HRID."=".$mHRID." AND ".AB_ABID." IS NOT NULL"			
				." LEFT OUTER JOIN ".DBT_CURRENTBOOKINGSTABLE
				." ON ".CBT_CBID."=".CB_CBID." AND ".CBT_HRID."=".$mHRID;

			$sqlquery_str=$selectstr
			." FROM ".DBT_CURRENTBOOKINGS
			." ".$joinstr
			." WHERE ".CB_HRID."=".$mHRID." AND ".CB_GUESTUID."=".$mGUID." AND ".CB_ADVANCEID."!=".CB_NO_ADVANCEID
			." ORDER BY ".CB_STARTTIME.",".CB_CBID." DESC"
			.$limitstr;
			
		}
		else{
			MyErrorHandeler::UserError(EXCP_CBINVALIDSTATUS, debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID));
			throw new Exception(USER_CBINVALIDSTATUS);
		}
		//return $sqlquery_str;
		return $sqlquery_str;
	}
	
	private function GetCurrentBookingStatusString_FromStatus($status){
		switch ($status) {
			case CB_LIVE_STATUS:
				$out="Live";
				break;
			case CB_CLOSEDLIVE_STATUS:
				$out="Closed";
				break;	
			case CB_CLOSEDAUTO_STATUS:
				$out="Closed";
				break;
			default:
				$out="Unknown";
				break;
		}
		return $out;
	}
	
	private function GetBookings($starttime_start,$starttime_end,$status,$extra_arr){
		//check all inputs before calling this function
		//input validation not done here
		$out=array();
		$sqlquery_str=$this->GetDBQueryString_CurrentBookings($starttime_start, $starttime_end, $status,$extra_arr);
		
		$SelectReturnArr=$this->DBOO->Select($sqlquery_str);
			
		$last_row=array();
		//IMPORTANT : THIS ASSUMES DATA IS SORTED BY CBID, ROWS WITH SAME CBID ARE CONSECUTIVE
		foreach ($SelectReturnArr as $curr_row){
			if(isset($curr_row['TableEndTime'])){
				$curr_row['TableEndTime']=$curr_row['TableEndTime']==NOEND_TIMESTAMP?"Occupied":CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI($curr_row['TableEndTime']);
			}
			if(empty($last_row)){
				$last_row=$curr_row;
				continue;
			}
			if($curr_row['CBID']==$last_row['CBID']){
				$last_row['TBID']=$last_row['TBID'].",".$curr_row['TBID'];
				if(isset($curr_row['TableEndTime'])){
					$last_row['TableEndTime']=$last_row['TableEndTime'].",".$curr_row['TableEndTime'];
				}
				//$last_row['TableEndTime']=$last_row['TableEndTime']==NOEND_TIMESTAMP?"":$last_row['TableEndTime'];
				continue;
			}
			$TableDetails = $this->GetTableDetails_FromTBID(explode(',', $last_row['TBID']));
			$TableName=array();$RoomName = array();
			foreach ($TableDetails as $TableEntry) {
				$RoomName[] = $TableEntry[OUT_RRT_ROOMNAME];
				$TableName[] = $TableEntry[OUT_RRT_TABLENAME];
			}
			//$last_row['TBID']=implode(',', $this->GetTableNames_FromTBID(explode(',', $last_row['TBID'])));
			$last_row['RoomName'] = implode(',',$RoomName);
			$last_row['TBID'] = implode(',',$TableName);
			$last_row['BookingStatus']=$this->GetCurrentBookingStatusString_FromStatus($last_row['BookingStatus']);
			$last_row['BookingStart']=CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI($last_row['BookingStart']);
			$last_row['BookingEnd']=$last_row['BookingEnd']==NOEND_TIMESTAMP?null:CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI($last_row['BookingEnd']);
			
			if(isset($last_row['AdvanceStatus'])){
				$last_row['AdvanceStatus']=empty($last_row['AdvanceStatus'])?NULL:CommonFunctions::GetABStatusString_FromStatus($last_row['AdvanceStatus']);	
			}
			
			if(isset($last_row[OUT_GUEST_CONTACTNUMBER])){
				$last_row[OUT_GUEST_CONTACTNUMBER]=(int)$last_row[OUT_GUEST_CONTACTNUMBER]<0?"":$last_row[OUT_GUEST_CONTACTNUMBER];
			}
			if(isset($last_row['ExpectedDuration'])){
				$mExpectedEnd = CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI(date('Y-m-d H:i',strtotime($last_row['BookingStart'])+60*(int)$last_row['ExpectedDuration']));
				$last_row['ExpectedBookingEnd']=$mExpectedEnd;
			}
						
			$out[]=$last_row;
			$last_row=$curr_row;
		}
		if(!empty($last_row)){
			$TableDetails = $this->GetTableDetails_FromTBID(explode(',', $last_row['TBID']));
			$TableName=array();$RoomName = array();
			foreach ($TableDetails as $TableEntry) {
				$RoomName[] = $TableEntry[OUT_RRT_ROOMNAME];
				$TableName[] = $TableEntry[OUT_RRT_TABLENAME];
			}
			$last_row['RoomName'] = implode(',',$RoomName);
			$last_row['TBID'] = implode(',',$TableName);			
			//$last_row['TBID']=implode(',', $this->GetTableNames_FromTBID(explode(',', $last_row['TBID'])));
			$last_row['BookingStatus']=$this->GetCurrentBookingStatusString_FromStatus($last_row['BookingStatus']);
			$last_row['BookingStart']=CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI($last_row['BookingStart']);
			$last_row['BookingEnd']=$last_row['BookingEnd']==NOEND_TIMESTAMP?"":CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI($last_row['BookingEnd']);
			
			if(isset($last_row['AdvanceStatus'])){
				$last_row['AdvanceStatus']=empty($last_row['AdvanceStatus'])?null:CommonFunctions::GetABStatusString_FromStatus($last_row['AdvanceStatus']);
			}
			
			if(isset($last_row[OUT_GUEST_CONTACTNUMBER])){
				$last_row[OUT_GUEST_CONTACTNUMBER]=(int)$last_row[OUT_GUEST_CONTACTNUMBER]<0?NULL:$last_row[OUT_GUEST_CONTACTNUMBER];
			}
			if(isset($last_row['ExpectedDuration'])){
				$mExpectedEnd = CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI(date('Y-m-d H:i',strtotime($last_row['BookingStart'])+60*(int)$last_row['ExpectedDuration']));
				$last_row['ExpectedBookingEnd']=$mExpectedEnd;
			}
		}	
		$out[]=$last_row;
		return $out;
	}
	

	
	public function GetCurrentlyOccupiedTables($mFields){
		if($mFields=='TBID'){
			$selectarr=array(CBT_TBID=>"TBID");
		}
		else{
			$selectarr=array(CBT_CBID=>"CBID",CBT_STARTTIME=>"BookingStart",CBT_TBID=>"TBID");
		}
		
		$selectstr = $this->DBOO->GetSelectColumn($selectarr);
		$mHRID=$this->DBOO->RealEscapeString($this->HRID);
		$mNOEND_TIMESTAMP=$this->DBOO->RealEscapeString(NOEND_TIMESTAMP);
		$sqlquery_str=$selectstr
		." FROM ".DBT_CURRENTBOOKINGSTABLE
		." WHERE ".CBT_HRID."=".$mHRID
		." AND ".CBT_ENDTIME."=".$mNOEND_TIMESTAMP; 
		
		$SelectReturnArr=$this->DBOO->Select($sqlquery_str);
		
		$OccupiedTables = array();
		foreach ($SelectReturnArr as $row) {
			$OccupiedTables[] = strcmp($mFields,"TBID")==0?$row["TBID"]:$row;
		}
		return $OccupiedTables;
	}
	
	public function GetCurrentAvailableTables(){
		//get list of occupied tables
		$OccTables = $this->GetCurrentlyOccupiedTables('All');
		$OccTBID=array();$AdvanceBookedTables=array();
		foreach ($OccTables as $Table) {
			$OccTBID[]=$Table['TBID'];
		}
		//get list of all tables in the restaurant which are currently in restaurant list
		$AllTables = $this->GetAllActiveTablesInHR();
		$AvailableTables = array();
		
		//get list of Advancebookings on tables..
		$abentry = new AdvanceBookingEntry($this->HRID,$this->UserID,$this->DBOO);
		$starttime = CommonFunctionsDateTime::GetDT_YMDHIS(NULL);
		$endtime = date('Y-m-d H:i:s',strtotime(sprintf("+%d hours", CB_LOOKAHEADTIME_HOUR)));
		$abresult = $abentry->Get_TablesBetween_AB($starttime, $endtime, FALSE,FALSE);
		
		if($abresult[RESULT_STATUS]){
			$AdvanceBookedTables=$abresult[RESULT_PAYLOAD];
		}
		
		foreach($AllTables as $mTable){
			if(!in_array($mTable['TBID'],$OccTBID)){
				$mTable['BookedFor']="";
				//****************now search if this table is present in advance booked tables*****************
				$mTempABTime=array();
				foreach($AdvanceBookedTables as $mABTable){
					if($mTable['TBID']==$mABTable['TBID'] and !empty($mABTable['BookedFor'])){
						$mTempABTime[]=CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI($mABTable['BookedFor']);
					}
				}
				//sort the $mTempABTime array by time if not empty
				if(!empty($mTempABTime)){
					uasort($mTempABTime, 'CommonFunctions::CompareDateTime');
					$mTable['BookedFor']=implode(",", $mTempABTime);
				}
				//*******************************************************************************************				
				unset($mTable['TBID']);
				$AvailableTables[] = $mTable;
			}
		}
		return $AvailableTables;
	}
	
	private function CloseWaitingListEntry_ConvCurrent($WaitID){
		$wlentry = new WaitingListEntry($this->HRID,$this->DBOO);
		$out = $wlentry->CloseWL_ConvCurrent($WaitID);
		return $out;
	}
	
	private function CloseAdvanceBookingEntry_ConvCurrent($AdvID){
		$StatusType=AB_CONFIRMED_CONVERTEDCURRENT_STATUSTYPE;
		$abentry = new AdvanceBookingEntry($this->HRID,$this->UserID,$this->DBOO);
		$result = $abentry->UpdateStatusAB($AdvID, $StatusType);
		return $result;
	}
	

	/**
	 * A function that inserts the CurrentBookingEntry object into database
	 * It assumes you have already checked the user is allowed to perform this action
	 * @return CBID for the reservation if successfully inserted
	 * @throws Exception on failure
	 */
	public function InsertCurrentBooking($mRoomName,$mTableNamesToBook,$mExpDuration,$mgid,$mGuestNum,$mGuestName,$mGuestContact,$mGuestEmail,$mNote,$mWaitID,$AmdvanceID){
		
		$CBID=time();
		$status=CB_LIVE_STATUS;
		$starttime= CommonFunctionsDateTime::GetDT_YMDHIS(NULL);
		$endtime=NOEND_TIMESTAMP;
		$out=array();
		
	 	$Exp_Duration = CommonFunctions::SetExpectedDuration($mExpDuration);
		$TBIDs = $this->SetTBID($mTableNamesToBook,$mRoomName);
		$WaitID = $this->SetWaitID($mWaitID); //will be checked neeche
		$AdvID = $this->SetAdvanceID($AmdvanceID);
		$GuestNum = CommonFunctions::SetGuestNum($mGuestNum);
		$GuestName = $mGuestName;
		$GuestContact=$mGuestContact;
		$GuestEmail=$mGuestEmail ;
	//public $GuestComment;
		
		
		$GuestID = $mgid;
		$Note=$mNote;
		//TODO: what if this was an advance booking got converted to waiting to current??
		
		if (!count($TBIDs)>0){//can remove this later on, this should be handeled on client side
			
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_EMPTYTABLELIST);
			return $result;
		}
		
		if (!$this->CurrentTableAvailability($TBIDs)){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_TABLEAVAILABILITY);
			return $result;
		}
		
		$this->DBOO->StartTransaction();
		
		if(strcmp($WaitID, (string)CB_NO_WAITID)!=0){
			$result=$this->CloseWaitingListEntry_ConvCurrent($WaitID);
			if($result[RESULT_STATUS]==FALSE){
				$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_WLINVALIDID);
				$this->DBOO->Rollback();
				return $result;
			}
		}
		
		if(strcmp($AdvID, (string)CB_NO_ADVANCEID)!=0){
			$result=$this->CloseAdvanceBookingEntry_ConvCurrent($AdvID);
			if($result[RESULT_STATUS]==FALSE){
				$this->DBOO->Rollback();
				return $result;
			}			
			
		}
		
		$this->AddCBTable_NoChecks($CBID, $TBIDs, $starttime);
		
		//insert into guests...
		$guestobj = new GuestsEntry($this->HRID,$this->UserID,$this->DBOO);
		$result = $guestobj->NewGuest($GuestID,$GuestContact, $GuestName, $GuestEmail, NULL, NULL);
		if($result[RESULT_STATUS]){
			$GuestUID = $result[RESULT_PAYLOAD][0][OUT_GUEST_UID];
		}
		else{
			$this->DBOO->Rollback();
			return $result;
		}
		$insertcol = array(CB_HRID,CB_USERID,CB_CBID,CB_STATUS,CB_STARTTIME,CB_ENDTIME,CB_EXPDURATION,CB_GUESTNUM,CB_NOTES,CB_GUESTUID,CB_WAITINGID,CB_ADVANCEID);
		$insertval=array(array($this->HRID,$this->UserID,$CBID,$status,$starttime,$endtime,$Exp_Duration,$GuestNum,$Note,$GuestUID,$WaitID,$AdvID));
		$mtablename=DBT_CURRENTBOOKINGS;
		$this->DBOO->Insert($mtablename, $insertcol, $insertval);
		$this->DBOO->Commit();
		$out = $this->GetAllDetails_ForCBID($CBID,TRUE);
		return $out;
	}

	/**
	 * Closes all live current bookings by status CloseAllCurrentDefault
	 * @throws Exception
	 * @return array carrying fields 'BookingsClosed' and 'TablesClosed' telling number of enteries closed
	 */
	public function CloseAllCurrent(){
		//closes all live current openings
		$status=CB_CLOSEDAUTO_STATUS;
		$endtime=CommonFunctionsDateTime::GetDT_YMDHIS(NULL);
		$out = $this->Close_CB_CBT($status, array(),$endtime);
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}

	/**
	 * To close a current booking. Checks whether the given CBID is valid. Checks whether the given CBID exists
	 * and is Live currently. Thereby preventingf multiple closes.
	 * @throws Exception
	 * @return true if successful
	 */
	public function CloseCurrentBooking($mCBID){
		$extra_array=array();
		$extra_array['CBID']=$this->SetCBID($mCBID);
		$status=CB_CLOSEDLIVE_STATUS;
		
		$currentstatus = (int)$this->GetStatus_CBID_NoChecks($extra_array['CBID']);
		if($currentstatus!=CB_LIVE_STATUS){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_CBCLOSEFAILALREADYCLOSED);
			return $result;
		}
		
		
		$endtime=CommonFunctionsDateTime::GetDT_YMDHIS(NULL);
		$this->DBOO->StartTransaction();
		$out=$this->Close_CB_CBT($status, $extra_array,$endtime);
		if($out['BookingsClosed']==1){
			$this->DBOO->Commit();
			$result=$this->GetAllDetails_ForCBID($mCBID,FALSE);
		}
		else{
			$this->DBOO->Rollback();
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_CBCLOSEFAIL);
		}
		return $result;
	}
	
	public function CloseCurrentBookingTable($mCBID,$mRoomName,$mTBID){
		$status=CB_CLOSETABLES_FORCBID_RES;
		$extra_array=array();
		$extra_array['CBID']=$this->SetCBID($mCBID);//PROCEEDDS IF ONLY CBID EXIST..
		$extra_array['TBID']=$this->SetTBID($mTBID,$mRoomName);
		$endtime=CommonFunctionsDateTime::GetDT_YMDHIS(NULL);
		$this->DBOO->StartTransaction();
		$out=$this->Close_CBT($endtime, $status, $extra_array);
		if(count($extra_array['TBID'])==$out){
			$this->DBOO->Commit();
			$result=$this->GetAllDetails_ForCBID($mCBID,FALSE);
		}
		else{
			$this->DBOO->Rollback();
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_CBCLOSEFAIL);
		}
		return $result;
		
	}
	public function AddCBTable($mCBID,$mRoomName,$mTBNames){
		$result=array();
		$CBID=$this->SetCBID($mCBID);
		$TBIDArr = $this->SetTBID($mTBNames,$mRoomName);
		$starttime=CommonFunctionsDateTime::GetDT_YMDHIS(NULL);
		$status = (INT)$this->GetStatus_CBID_NoChecks($CBID);
		if($status!=CB_LIVE_STATUS){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_CBADDTABLEFAILBOOKINGCLOSED);
			return $result;
		}
		
		if (!$this->CurrentTableAvailability($TBIDArr)){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_TABLEAVAILABILITY);
			return $result;
		}
		$this->DBOO->StartTransaction();
		$out=$this->AddCBTable_InsertUpdate_NoChecks($CBID, $TBIDArr, $starttime);
		if($out==TRUE){
			$this->DBOO->Commit();
			$result = $this->GetAllDetails_ForCBID($CBID,FALSE);
		}else{
			$this->DBOO->Rollback();
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_CBADDTABLEFAIL);
		}
		return $result;
	}
	
	private function GetStatus_CBID_NoChecks($CBID){
		$dummytime =date('Y-m-d');
		$status = CB_STATUSFORCBID_RES;
		$extra_arr['CBID']=$CBID;
		$sqlquery_str = $this->GetDBQueryString_CurrentBookings($dummytime, $dummytime, $status, $extra_arr);
		$SelectReturnArr = $this->DBOO->Select($sqlquery_str);
		foreach ($SelectReturnArr as $value) {
			$out = $value["BookingStatus"];
		}
		return $out;
	}
	
	private function AddCBTable_InsertUpdate_NoChecks($CBID,$TBIDArr,$starttime){
		$endtime=NOEND_TIMESTAMP;
		$HRID=$this->HRID;$UserID=$this->UserID;
		$insertcol = array(CBT_HRID,CBT_TBID,CBT_CBID,CBT_STARTTIME,CBT_ENDTIME);
		$updatecol = array(CBT_ENDTIME);
		$tablename=DBT_CURRENTBOOKINGSTABLE;
		$insertval=array();$updateval = array();
		foreach ($TBIDArr as $TBID) {
			$insertval[]=array($HRID,$TBID,$CBID,$starttime,$endtime);
			$updateval[]=array($endtime);
		}
		$this->DBOO->InsertOnDuplicateUpdate($tablename, $insertcol, $insertval, $updatecol, $updateval,array());
		return true;
	}
	
	private function AddCBTable_NoChecks($CBID,$TBIDArr,$starttime){
		$endtime=NOEND_TIMESTAMP;
		$HRID=$this->HRID;$UserID=$this->UserID;
		$insertcol = array(CBT_HRID,CBT_TBID,CBT_CBID,CBT_STARTTIME,CBT_ENDTIME);
		$mtablename=DBT_CURRENTBOOKINGSTABLE;
		$insertval=array();
		foreach ($TBIDArr as $TBID) {
			$insertval[]=array($HRID,$TBID,$CBID,$starttime,$endtime);
		}
		$this->DBOO->Insert($mtablename, $insertcol, $insertval);
		return TRUE;
	}
	
	/**
	 * Close 1 or more table in a booking.Currently doesnt close the booking.
	 * @throws Exception
	 * @return integer, 
	 */
	private function Close_CBT($endtime,$status,$extra_array){
		$queryparms = $this->GetDBQuery_Close_CBT($endtime, $status, $extra_array);
		$sqlquery_str=$queryparms['sqlquery_str'];
		$bindvalarr=$queryparms['bindvalarr'];
		$affectedrows = $this->DBOO->Update($sqlquery_str, $bindvalarr);
		return $affectedrows;
	}
	
	private function GetDBQuery_Close_CBT($endtime,$status,$extra_array){
		$HRID=$this->HRID;$UserID=$this->UserID;
		$noendtime=NOEND_TIMESTAMP;
		$out=array();
				
		if($status==CB_CLOSEDAUTO_STATUS){
			
			$update_set_array = array(CBT_ENDTIME);
			$update_where_array = array(CBT_HRID,CBT_ENDTIME);
			$update_where_QString = $this->DBOO->Update_WhereEQAnd_QString($update_where_array);
			$bindvalarr=array($endtime,$HRID,$noendtime);
		}
		elseif($status==CB_CLOSEDLIVE_STATUS){
			
			$CBID=$extra_array['CBID'];
			$update_set_array = array(CBT_ENDTIME);
			$update_where_array = array(CBT_HRID,CBT_ENDTIME,CBT_CBID);
			$update_where_QString = $this->DBOO->Update_WhereEQAnd_QString($update_where_array);
			$bindvalarr=array($endtime,$HRID,$noendtime,$CBID);
		}
		elseif($status==CB_CLOSETABLES_FORCBID_RES){
			
			$CBID=$extra_array['CBID'];
			$TBIDArr=$extra_array['TBID'];
			$update_set_array = array(CBT_ENDTIME);
			$update_where_array1 = array(CBT_HRID,CBT_ENDTIME,CBT_CBID);
			$update_where_inString=$this->DBOO->In_QString(CBT_TBID, count($TBIDArr));
				
			$update_where_QString1 = $this->DBOO->Update_WhereEQAnd_QString($update_where_array1);
			$update_where_QString = $update_where_QString1." AND ".$update_where_inString;
			$bindvalarr=array($endtime,$HRID,$noendtime,$CBID);
			foreach ($TBIDArr as $TBID) {
				$bindvalarr[]=$TBID;
			}
		}
		else{
			
			MyErrorHandeler::UserError(EXCP_CBINVALIDSTATUS,debug_backtrace(), array("HRID"=>$HRID,"UserID"=>$UserID));
			throw new Exception(USER_CBINVALIDSTATUS);
		}
		
		$update_set_QString=$this->DBOO->Update_Set_QString($update_set_array);
		$sqlquery_str="UPDATE ".DBT_CURRENTBOOKINGSTABLE." ".$update_set_QString." WHERE ".$update_where_QString;

		$out[RESULT_STATUS]=TRUE;
		$out['sqlquery_str']=$sqlquery_str;
		$out['bindvalarr']=$bindvalarr;
		return $out;
	}
	
	private function GetDBQuery_Close_CB_CBT($endtime,$status,$extra_array){
		$HRID=$this->HRID;
		$UserID=$this->UserID;
		$out=array();		
		//all input validation done oin the calling funct...this generates the string
		if($status==CB_CLOSEDAUTO_STATUS){
			$oldstatus=CB_LIVE_STATUS;
			$update_set_array = array(CB_STATUS,CB_ENDTIME);
			$update_where_array = array(CB_HRID,CB_STATUS);
			$bindvalarr=array($status,$endtime,$HRID,$oldstatus);
		}
		elseif($status==CB_CLOSEDLIVE_STATUS){
			$oldstatus=CB_LIVE_STATUS;
			$CBID=$extra_array['CBID'];
			$update_set_array = array(CB_STATUS,CB_ENDTIME);
			$update_where_array = array(CB_HRID,CB_STATUS,CB_CBID);
			$bindvalarr=array($status,$endtime,$HRID,$oldstatus,$CBID);
		}
		else{
			MyErrorHandeler::UserError(EXCP_CBINVALIDSTATUS,debug_backtrace(), array("HRID"=>$HRID,"UserID"=>$UserID));
			throw new Exception(USER_CBINVALIDSTATUS);
		}
		$update_set_QString=$this->DBOO->Update_Set_QString($update_set_array);
		$update_where_QString = $this->DBOO->Update_WhereEQAnd_QString($update_where_array);
		
		$sqlquery_str="UPDATE ".DBT_CURRENTBOOKINGS." ".$update_set_QString." WHERE ".$update_where_QString;
		
		$out[RESULT_STATUS]=TRUE;
		$out['sqlquery_str']=$sqlquery_str;
		$out['bindvalarr']=$bindvalarr;
		return $out;
	}
	
	public function GetWalkInCount_ForGUID($mguid,$doDBCheck){
		$extra_array = array();
		/*
		if($doDBCheck){
			$guestentry = new GuestsEntry($this->HRID,$this->UserID,$this->DBOO);
			$GUID = $guestentry->SetGUID($mguid);
		}
		else{
			$GUID = $mguid;
		}*/
		$GUID = $mguid;
		$extra_array[OUT_GUEST_UID]=$GUID;
		$status = CB_WALKIN_STATSCOUNT;
		$dummytime=date('Y-m-d');
		$sqlquery_str=$this->GetDBQueryString_CurrentBookings($dummytime, $dummytime, $status,$extra_array);
		$out = $this->DBOO->SelectCount($sqlquery_str);
		return $out;
	}
	
	public function GetBookings_ForGUID($mguid,$doDBCheck,$mstart,$mend,$mType){
		$result=array();
		if($doDBCheck){
			$guestentry = new GuestsEntry($this->HRID,$this->UserID,$this->DBOO);
			$GUID = $guestentry->SetGUID($mguid);
		}
		else{
			$GUID = $mguid;
		}
		if(empty($mstart) or is_null($mstart) or empty($mend) or is_null($mend)){
			$Start=1;$End=10;//basically default returns last 10 rows
		}
		elseif(!ctype_digit($mstart) or !ctype_digit($mend) or ((int)$mstart<0) or ((int)$mend<0) or ((int)$mstart > (int)$mend)){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_INVALIDLIMITS);
			return $result;
		}
		else{
			$Start = $mstart;
			$End = $mend;
			if($Start > MAX_RECORDS_ALLOWED  or $End > MAX_RECORDS_ALLOWED){
				$result[RESULT_STATUS]=FALSE;
				$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_MAXLIMIT_NOMORERECORDS);
				return $result;
			}
		}
		$Type = trim($mType);
		if(strcmp("WalkIn", $Type)==0){
			$status = CB_WALKIN_HISTORY;
		}
		elseif(strcmp("Advance", $Type)==0){
			$status = CB_RESERVATIONS_HISTORY;
		}
		else{
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_INVALIDTYPE_GETBOOKINGS_FORGUID);
			return $result;
		}
		
		$extra_array=array();
		$extra_array[OUT_GUEST_UID]=$GUID;
		$extra_array['limitstart'] = $Start;
		$extra_array['limitend'] = $End;
		$dummytime=date('Y-m-d');
		$out = $this->GetBookings($dummytime, $dummytime, $status, $extra_array);
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	private function Close_CB_CBT($status,$extra_array,$endtime){
		$out=array();
		$queryparms = $this->GetDBQuery_Close_CB_CBT($endtime, $status, $extra_array);
		$sqlquery_str=$queryparms['sqlquery_str'];
		$bindvalarr=$queryparms['bindvalarr'];

		$affectedrows = $this->DBOO->Update($sqlquery_str, $bindvalarr);
		$out['BookingsClosed']=$affectedrows;
		//**************************************CB Closed..Now close the Table....
		$out['TablesClosed']=$this->Close_CBT($endtime, $status, $extra_array);
		return $out;
	}

	/**
	 * Checks whether the table ids to be booked are currently frr or available
	 * @return boolean
	 */
	private function CurrentTableAvailability($mTBID){
		$mHRID=$this->DBOO->RealEscapeString($this->HRID);
		$noend_timestamp=$this->DBOO->RealEscapeString(NOEND_TIMESTAMP);

		$sqlquery_str=
		"SELECT ".CBT_TBID
		." FROM ".DBT_CURRENTBOOKINGSTABLE
		." WHERE ".CBT_HRID."=".$mHRID
		." AND ".CBT_ENDTIME."=".$noend_timestamp;
		$SelectReturnArr=$this->DBOO->Select($sqlquery_str);
		$mOccTBID = array();
		foreach ($SelectReturnArr as $row){
			$mOccTBID[] = $row["TBID"];
		}
		
		if(count(array_intersect($mTBID,$mOccTBID))==0)
		return true;
		else
		return false;
	}
	
	public function UpdateGuestInfo_CB($mcbid,$mguestnum,$mguestnames,$mguestcontactnos,$mguestemail,$mguestcomment){
		$CBID=$this->SetCBID($mcbid);
		$GuestNum=CommonFunctions::SetGuestNum($mguestnum);
		$update_set_array = array(CB_GUESTNUM,CB_GUESTNAMES,CB_GUESTCONTACTNOS,CB_COMMENT,CB_EMAIL);
		$update_where_array = array(CB_HRID,CB_CBID);
		$update_where_QString = CommonFunctions::UpdateWhere_StringPrepareQ($update_where_array);
		$bindvalarr=array(&$GuestNum,&$mguestnames,&$mguestcontactnos,&$mguestemail,&$mguestcomment,&$this->HRID,&$CBID);
		$bindvaltype=CommonFunctions::GetMYSQLI_BindValueTypeString_IorS($bindvalarr);
		$paramarr = array_merge(array($bindvaltype),$bindvalarr);
		$sqlquery_str="UPDATE ".DBT_CURRENTBOOKINGS." SET ".$update_set_QString." WHERE ".$update_where_QString;
		//*********************************************DB Access starts**************************************
		$sqlquery = mysqli_prepare($this->dbcon,$sqlquery_str);
		if (!$sqlquery){
			MyErrorHandeler::SQLError(EXCP_DBERR1073, mysqli_error($this->dbcon),debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID));
			throw new Exception(EXCP_DBERR1073);
		}	
		$rc = (call_user_func_array(array($sqlquery,"bind_param"), $paramarr) and mysqli_stmt_execute($sqlquery));
		if(!$rc){
			MyErrorHandeler::SQLError(EXCP_CBERR1883, mysqli_error($this->dbcon),debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID));
			throw new Exception(EXCP_CBERR1883);
		}
		mysqli_stmt_close($sqlquery);
		$out=$this->GetAllDetails_ForCBID($CBID,FALSE);
	}
}
?>