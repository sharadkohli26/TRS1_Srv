<?php

class AdvanceBookingEntry{
  private $dbcon=NULL;
  private $DBOO;
  
  public $HRID;//hotel restaurant id
  public $ABID;//booking id of this advance booking
  public $BookingMethod;//customer called, or online booking or third party
  public $UserID;//waiter user id
  public $Status;//like approved, rejected, confirmed,called etc
  public $TableStatus;//has table been assigned or not
  public $OnDateTime;//date on which this entry was created
  public $ForDateTime;//date time for which reservation is asked
  public $ExpectedDuration;
  
  public $GuestNum;
  public $GuestNames;
  public $GuestContactNos;
  public $GuestEmail;
  public $GuestComment;
  
  /**
  * Default constructor for this class.
  * Holds the handle to access database if successfully created (ie if dbcon initialised then successfull) else throws exception
  * @throws Exception("Exception_DBerr11::Unable to connect to database , please try again later")
  */
  public function __construct($mHRID,$mUserID,$mDBOO){
 	if(is_null($mDBOO) || empty($mDBOO)){
		$this->DBOO=new DBOperations();
		$this->DBOO->SelectDatabase(DB_TRS1);
	}
	else{
		$this->DBOO=$mDBOO;
	}	 	
    //TODO: check if given HRID is valid for this user id and then proceed
    $this->HRID=$mHRID;
    $this->UserID=$mUserID;
  }
  
  //************************************************Main and DB Access Functions ************************//
  public function NewAdvanceBookingByHRUser($mForDateTime,$mBookingMehtod,$mStatusType,$mExpectedDuration,$mGuestNum,$mGUID,$mGuestNames,$mGuestContact,$mGuestEmail,$mNotes){
  //check the params and assign default values
  	$result=array();
  	$ABID=time();
	$OnDateTime= CommonFunctionsDateTime::GetDT_YMDHIS(NULL);
	$ForDateTime=CommonFunctionsDateTime::GetDT_YMDHIS($mForDateTime);
	//for date should be greater than today...
	//var_dump($ForDateTime);echo "--ForDateTime<br>";
	//var_dump($OnDateTime);echo "--OnDateTime<br>";
	if(CommonFunctionsDateTime::CompareDT($ForDateTime, $OnDateTime)!=1){
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_ABFORDATEWRONG);
		return $result;
	}
	
    $BookingMethod=$this->SetBookingMethod($mBookingMehtod);
	$Status=$this->SetStatus($mStatusType);

	//$this->TableStatus=$this->SetTableStatus($mTabStatus);
	$ExpectedDuration=CommonFunctionsDateTime::SetExpectedDuration($mExpectedDuration);
	//$AB_TBID=$this->GetTBIDArray($mTBID);
	$GuestNum=$mGuestNum;
	$GuestID = $mGUID;
	$GuestName=$mGuestNames;
	$GuestContact=$mGuestContact;
	$GuestEmail=$mGuestEmail;
	$Notes = $mNotes;
	//$GuestComment=$mGuestComment;
	
	//all set,so insert
	$this->DBOO->StartTransaction();
	
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
	
	$colnames = array(AB_HRID,AB_ABID,AB_BOOKINGMETHOD,AB_USERID,AB_STATUS,AB_ONDATETIME,AB_FORDATETIME,AB_EXPECTEDDURATION,AB_GUESTNUM,AB_GUESTUID,AB_NOTES);
	$colvalues = array(array($this->HRID,$ABID,$BookingMethod,$this->UserID,$Status,$OnDateTime,$ForDateTime,$ExpectedDuration,$GuestNum,$GuestUID,$Notes));
	$tablename = DBT_ADVANCEBOOKINGS;
	$this->DBOO->Insert($tablename, $colnames, $colvalues);
	$this->DBOO->Commit();
	return $this->GetAllDetails_ForABID($ABID);
  }
    /**
	 * Returns Tables Advance Booking status for a given time. If given time is close to current time and a table is occupied then its CurrentlyOccupied flag will be 1..
	 */
	public function Get_TablesFor_AB($mForDateTime,$DropTBID){
		
		$starttime=CommonFunctionsDateTime::GetDT_YMD_DayStart($mForDateTime);
		$endtime=CommonFunctionsDateTime::GetDT_YMD_DayEnd($mForDateTime);
		$mForDateTime=CommonFunctionsDateTime::GetDT_YMDHIS($mForDateTime);
	//	echo "<br>".$mForDateTime;
		//for date should be greater than now...
		if(CommonFunctionsDateTime::CompareDT($mForDateTime, CommonFunctionsDateTime::GetDT_YMDHIS(NULL))!=1){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_ABFORDATEWRONG);
			return $result;
		}		
		$check_lookahead=TRUE;
		$extra_array=array();
		$extra_array['ForDateTime']=$mForDateTime;
		return $this->Get_Tables_AB($starttime, $endtime, $check_lookahead, $DropTBID,$extra_array);
	}
	
	/**
	 * Returns table advance booking status between starttime and endtime
	 */
	public function Get_TablesBetween_AB($starttime,$endtime,$check_lookahead,$DropTBID){
		$starttime=CommonFunctionsDateTime::GetDT_YMDHIS($starttime);
		$endtime=CommonFunctionsDateTime::GetDT_YMDHIS($endtime);
		$extra_array=array();
		if($check_lookahead){
		$extra_array['ForDateTime']=$starttime;
		}
		return $this->Get_Tables_AB($starttime, $endtime, $check_lookahead, $DropTBID,$extra_array);
	}
	
	private function Get_Tables_AB($starttime,$endtime,$check_lookahead,$DropTBID,$extra_array){

		$mstatus=AB_ASSIGNED_TABLE_RES;$To_lookahead = FALSE;$CurrentlyOccupiedTables=array();
		
		$sqlquery_str=$this->GetDBQueryStr_AdvanceBookings_BetweenDate($starttime, $endtime, $mstatus,array());
		$SelectReturnArr = $this->DBOO->Select($sqlquery_str);

		$AdvanceBookedTables=$SelectReturnArr;
		//get currently occupied tables if lookahead is true...
		//if ForTime is for today +(say) 2hour then get this query else no..
		if($check_lookahead){
			$mLookAheadStr= date('Y-m-d H:i:s',strtotime(sprintf("+%d hours", AB_LOOKAHEADTIME)));
			$mLookAheadObj=new DateTime($mLookAheadStr);
			$mForDateTimeObj = new DateTime($extra_array['ForDateTime']);
			$To_lookahead = ($mForDateTimeObj<=$mLookAheadObj);
			if($To_lookahead){
				$cbentry = new CurrentBookingEntry($this->HRID,$this->DBOO);
				$CurrentlyOccupiedTables =$cbentry->GetCurrentlyOccupiedTables('TBID'); 
			}
		}
		//get all tables now
		$AllTables = $this->GetAllActiveTablesInHR();

		$out=array();
		foreach ($AllTables as $mTable) {
			$mTable['BookedFor']="";
			$mTable['CurrentlyOccupied']=0;
			//****************now search if this table is present in advance booked tables*****************
			$mTempABTime=array();
			foreach($AdvanceBookedTables as $mABTable){
				if($mTable['TBID']==$mABTable['TBID']){
					$mTempABTime[]=CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI($mABTable['BookedFor']);
				}
			}
			//sort the $mTempABTime array by time if not empty
			if(!empty($mTempABTime)){
				uasort($mTempABTime, 'CommonFunctions::CompareDateTime');
				$mTable['BookedFor']=implode(",", $mTempABTime);
			}
			//*******************************************************************************************
			
			//now search if this table is currently occupied..but only if To_Lookahead is true
			if($To_lookahead AND in_array($mTable['TBID'], $CurrentlyOccupiedTables)){
				$mTable['CurrentlyOccupied']=1;
			}
			if($DropTBID){
				unset($mTable['TBID']);
			}
			$out[]=$mTable;
		}
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	 
	public function Get_All_AB_BetweenDate($mdatestr1,$mdatestr2){
		$out= $this->Get_AdvanceBookings_BetweenDate($mdatestr1,$mdatestr2,AB_ALL_RES,array());
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function Get_Tentative_AB_BetweenDate($mdatestr1,$mdatestr2){
		$out= $this->Get_AdvanceBookings_BetweenDate($mdatestr1,$mdatestr2,AB_TENTATIVE_RES,array());
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function Get_Confirmed_AB_BetweenDate($mdatestr1,$mdatestr2){
		$out= $this->Get_AdvanceBookings_BetweenDate($mdatestr1,$mdatestr2,AB_CONFIRMED_RES,array());
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}	
	
	public function Get_UserCancelledAB_BetweenDate($mdatestr1,$mdatestr2){
		$out= $this->Get_AdvanceBookings_BetweenDate($mdatestr1,$mdatestr2,AB_USERCANCELLED_RES,array());
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}

	public function Get_NoShow_AB_BetweenDate($mdatestr1,$mdatestr2){
		$out= $this->Get_AdvanceBookings_BetweenDate($mdatestr1,$mdatestr2,AB_NOSHOW_RES,array());
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}  
	
	public function Get_ConvertedCurrent_AB_BetweenDate($mdatestr1,$mdatestr2){
		//TODO:: write this function 
		//return Get_AdvanceBookings_BetweenDate($mdatestr1,$mdatestr2,AB_CONVERTEDCURRENT_RES);
	} 
	
	public function Get_AllFailed_AB_BetweenDate($mdatestr1,$mdatestr2){
		$out= $this->Get_AdvanceBookings_BetweenDate($mdatestr1,$mdatestr2,AB_ALLCANCELLEDORNOSHOW_RES,array());
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	} 
	
	public function GetAllDetails_ForABID($mABID){
		$extra_array=array();
		$extra_array['ABID']=$this->SetABID($mABID);
		$mstatus=AB_FORABID_RES;
		$dummytime=date('Y-m-d');
		
		$out= $this->Get_AdvanceBookings_BetweenDate($dummytime, $dummytime, $mstatus, $extra_array);
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function UpdateStatusAB($mABID,$mstatusType){
		$out=array();
		$ABID = $this->SetABID($mABID);
		$status=$this->SetStatus($mstatusType);

		$affectedrows = $this->UpdateStatusAB_NoChecksFinal($ABID,$status);
		if($affectedrows==1){ 
			return $this->GetAllDetails_ForABID($ABID);
		}
		else{
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_ABSTATUSUPDATEFAIL);
			return $result;
		}
	}
	
	private function UpdateStatusAB_NoChecksFinal($ABID,$status){
		$update_set_array = array(AB_STATUS);
		$update_set_QString=$this->DBOO->Update_Set_QString($update_set_array);
		$update_where_array = array(AB_HRID,AB_ABID);
		$update_where_QString = $this->DBOO->Update_WhereEQAnd_QString($update_where_array);
		$sqlquery_str="UPDATE ".DBT_ADVANCEBOOKINGS." ".$update_set_QString." WHERE ".$update_where_QString;
		
		$bindvalarr=array($status,$this->HRID,$ABID);
		$affectedrows = $this->DBOO->Update($sqlquery_str, $bindvalarr);
		return $affectedrows;
	}
	
	
	public function RemoveTableFromAB($mABID,$mTBNames,$mRoomName){
		$result=array();
		$ABID = $this->SetABID($mABID);
		$TBIDArr = $this->GetTBIDArray($mTBNames,$mRoomName);
		
		$this->DBOO->StartTransaction();
		$affectedrows = $this->RemoveTableFromAB_NoChecksFinal($ABID, $TBIDArr);
		if($affectedrows==count($TBIDArr)) {
			$this->DBOO->Commit();
			return $this->GetAllDetails_ForABID($ABID);;
		}
		else{
			$this->DBOO->Rollback();
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_ABREMOVETABLEFAIL);
			return $result;
		}
		
	}
	
	public function RemoveTableFromAB_NoChecksFinal($ABID,$TBIDArr){
		$mAliveBit=0;$mAliveBitIs=1;
		$update_set_array = array(ABT_ALIVE);
		$update_set_QString=$this->DBOO->Update_Set_QString($update_set_array);
		$update_where_array = array(ABT_HRID,ABT_ABID,ABT_ALIVE);
		$update_where_QString = $this->DBOO->Update_WhereEQAnd_QString($update_where_array);
		$update_whereIn_QString = $this->DBOO->In_QString(ABT_TBID, count($TBIDArr));
		$sqlquery_str="UPDATE ".DBT_ABTABLES." ".$update_set_QString." WHERE ".$update_where_QString." AND ".$update_whereIn_QString;
		$bindvalarr=array_merge(array($mAliveBit,$this->HRID,$ABID,$mAliveBitIs),$TBIDArr);
		$affectedrows = $this->DBOO->Update($sqlquery_str, $bindvalarr);
		return $affectedrows;
	}
	
	public function AssignTableToAB($mABID,$mTBNames,$mRoomName){
		$ABID = $this->SetABID($mABID);
		$result=array();

		$currForDateTime = $this->GetForDateTime_ABID_NoChecks($ABID);
		//for date should be greater than today...
		
		if(CommonFunctionsDateTime::CompareDT($currForDateTime, CommonFunctionsDateTime::GetDT_YMDHIS(NULL))!=1){
			$result[RESULT_STATUS]=	FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_ABASSIGNTABLEFAILFORDATEPAST);
			return $result;
		}		
		//proceed if only status is in tentative or confirmed...
		$currstatus = (int)$this->GetStatus_ABID_NoChecks($ABID);
		$tent_conf_arr = array_merge(unserialize(AB_TENTATIVE_RESARRAY),unserialize(AB_GUESTCONFIRMED_RESARRAY));
		if(!in_array($currstatus, $tent_conf_arr)){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_ABASSIGNTABLEINVALIDSTATUS);
			return $result;
		}
		$TBIDArr = $this->GetTBIDArray($mTBNames,$mRoomName);
		$mAliveBit=1;		
		
		//check if status in Tentative ...dont update...this depends on your implementation...
		//currently no status update !!!!THIS WILL HAVE TO CHANGE
		$status=AB_APPROVED_STATUS;
		$this->DBOO->StartTransaction();
		//close any existing such tables
		$outRemoveTable=$this->RemoveTableFromAB_NoChecksFinal($ABID, $TBIDArr);
		//update status to Approved and add tables
		//$outStatus=$this->UpdateStatusAB_NoChecksFinal($ABID, $status);
		//insert into database.
		//add these TBIDS to abt table
		$colnames = array(ABT_HRID,ABT_ABID,ABT_TBID,ABT_ALIVE);
		$tablename = DBT_ABTABLES;
		$colvalues=array();
		foreach ($TBIDArr as $TBID) {
			$colvalues[] = array($this->HRID,$ABID,$TBID,$mAliveBit);
		}
		$this->DBOO->Insert($tablename, $colnames, $colvalues);
		
		$this->DBOO->Commit();
		return $this->GetAllDetails_ForABID($ABID);
	}
  
  //********************************hrow exception or return true********************//
  /**
   * Compares two date time, returns true if $datetimestr1<=$datetimestr2  
   */
  
  //********************************GetMethods****************************************// 	
  
    private function Get_AdvanceBookings_BetweenDate($mdatestr1,$mdatestr2,$mstatus,$extra_array){
  	$starttime=CommonFunctionsDateTime::GetDT_YMD_DayStart($mdatestr1);
	$endtime=CommonFunctionsDateTime::GetDT_YMD_DayEnd($mdatestr2);
	$sqlquery_str=$this->GetDBQueryStr_AdvanceBookings_BetweenDate($starttime, $endtime, $mstatus,$extra_array);
	$SelectReturnArr = 	$this->DBOO->Select($sqlquery_str);
	//prepare the results
	$last_row=array();

	foreach ($SelectReturnArr as $curr_row){
		if(empty($last_row)){
			$last_row=$curr_row;
			continue;
		}
		if(array_key_exists("TBID", $last_row) and $last_row['ABID']==$curr_row['ABID']){
			$last_row['TBID']=$last_row['TBID'].",".$curr_row['TBID'];
		}
		else{
			//$last_row['TBID']=array_key_exists("TBID", $last_row)?implode(',', $this->GetTableNames_FromTBID(explode(',', $last_row['TBID']))):"";
			if(array_key_exists("TBID", $last_row) and !empty($last_row['TBID'])){
				$TableDetails = $this->GetTableDetails_FromTBID(explode(',', $last_row['TBID']));
				$TableName=array();$RoomName = array();
				foreach ($TableDetails as $TableEntry) {
					$RoomName[] = $TableEntry[OUT_RRT_ROOMNAME];
					$TableName[] = $TableEntry[OUT_RRT_TABLENAME];
				}	
				$last_row['RoomName'] = implode(',',$RoomName);
				$last_row['TBID'] = implode(',',$TableName);			
			}
			$last_row['Status']=CommonFunctions::GetABStatusString_FromStatus($last_row['Status']);
			$last_row['BookedOn'] = CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI($last_row['BookedOn']);
			$last_row['BookedFor'] = CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI($last_row['BookedFor']);
			$last_row[OUT_GUEST_CONTACTNUMBER]=(int)$last_row[OUT_GUEST_CONTACTNUMBER]<0?"":$last_row[OUT_GUEST_CONTACTNUMBER];
			$mExpectedEnd = CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI(date('Y-m-d H:i',strtotime($last_row['BookedFor'])+60*(int)$last_row['ExpectedDuration']));
			$last_row['ExpectedEndTime'] = $mExpectedEnd;
			$out[]=$last_row;
			$last_row=$curr_row;
		}
	}
	if(!empty($last_row)){
		//$last_row['TBID']=(array_key_exists("TBID", $last_row))?implode(',', $this->GetTableNames_FromTBID(explode(',', $last_row['TBID']))):"";
			if(array_key_exists("TBID", $last_row) and !empty($last_row['TBID'])){
				$TableDetails = $this->GetTableDetails_FromTBID(explode(',', $last_row['TBID']));
				$TableName=array();$RoomName = array();
				foreach ($TableDetails as $TableEntry) {
					$RoomName[] = $TableEntry[OUT_RRT_ROOMNAME];
					$TableName[] = $TableEntry[OUT_RRT_TABLENAME];
				}	
				$last_row['RoomName'] = implode(',',$RoomName);
				$last_row['TBID'] = implode(',',$TableName);
			}		
		$last_row['Status']=CommonFunctions::GetABStatusString_FromStatus($last_row['Status']);
		$last_row['BookedOn'] = CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI($last_row['BookedOn']);
		$last_row['BookedFor'] = CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI($last_row['BookedFor']);
		$last_row[OUT_GUEST_CONTACTNUMBER]=(int)$last_row[OUT_GUEST_CONTACTNUMBER]<0?"":$last_row[OUT_GUEST_CONTACTNUMBER];
		$mExpectedEnd = CommonFunctionsDateTime::GetDT_NearestQuarter_YMDHI(date('Y-m-d H:i',strtotime($last_row['BookedFor'])+60*(int)$last_row['ExpectedDuration']));
		$last_row['ExpectedEndTime'] = $mExpectedEnd;	
	}
	$out[]=$last_row;
	//sort by BookingFor field
	uasort($out, 'CommonFunctionsDateTime::CompareDT_BookedFor');
	return $out;
  }
  
  private function GetDBQueryStr_AdvanceBookings_BetweenDate($starttime,$endtime,$mstatus,$extra_array){
  	$mHRID=$this->DBOO->RealEscapeString($this->HRID);
	$mstarttime=$this->DBOO->RealEscapeString($starttime);
	$mendtime=$this->DBOO->RealEscapeString($endtime);
  	
  	$selectarr = array(AB_ABID=>"ABID",AB_BOOKINGMETHOD=>"BookingMethod",AB_USERID=>"UserID",AB_STATUS=>"Status",AB_ONDATETIME=>"BookedOn",AB_FORDATETIME=>"BookedFor",AB_EXPECTEDDURATION=>"ExpectedDuration",AB_GUESTNUM=>"GuestNum",AB_GUESTUID=>OUT_GUEST_UID,AB_NOTES=>"Notes"
	  				,GUEST_CONTACTNUMBER=>OUT_GUEST_CONTACTNUMBER,GUEST_NAME=>OUT_GUEST_NAME,GUEST_EMAIL=>OUT_GUEST_EMAIL,GUEST_ALTERNATECONTACTNUMBER=>OUT_GUEST_ALTERNATECONTACTNUMBER,GUEST_COMMENT=>OUT_GUEST_COMMENT
	  				);
	$joinstr = " LEFT OUTER JOIN ".DBT_ABTABLES
			." ON ".ABT_HRID."=".$mHRID." AND ".ABT_ABID."=".AB_ABID." AND ".ABT_TBID." IS NOT NULL AND ".ABT_ALIVE."=1"
			." LEFT OUTER JOIN ".DBT_GUESTS
			." ON ".GUEST_HRID."=".$mHRID." AND ".GUEST_UID."=".AB_GUESTUID;

  	if($mstatus==AB_TENTATIVE_RES){
  		$selectarr=array_merge($selectarr,array(ABT_TBID=>"TBID"));
		$selectstr = $this->DBOO->GetSelectColumn($selectarr);
		
		$statarr = unserialize(AB_TENTATIVE_RESARRAY);
  		$abstatus_EqOrstr=$this->DBOO->EqualOR_String(array_fill(0, count($statarr), AB_STATUS), $statarr);
		
		$sqlquery_str=$selectstr
		." FROM ".DBT_ADVANCEBOOKINGS
		.$joinstr
		." WHERE ".AB_HRID."=".$mHRID
		." AND ".AB_FORDATETIME." BETWEEN ".$mstarttime." AND ".$mendtime
		." AND ".$abstatus_EqOrstr
		." ORDER BY ".AB_ABID;
	}
	elseif($mstatus==AB_CONFIRMED_RES){
  		$selectarr=array_merge($selectarr,array(ABT_TBID=>"TBID"));
		$selectstr = $this->DBOO->GetSelectColumn($selectarr);
		
		$statarr = unserialize(AB_GUESTCONFIRMED_RESARRAY);
  		$abstatus_EqOrstr=$this->DBOO->EqualOR_String(array_fill(0, count($statarr), AB_STATUS), $statarr);
		
		$sqlquery_str=$selectstr
		." FROM ".DBT_ADVANCEBOOKINGS
		.$joinstr
		." WHERE ".AB_HRID."=".$mHRID
		." AND ".AB_FORDATETIME." BETWEEN ".$mstarttime." AND ".$mendtime
		." AND ".$abstatus_EqOrstr
		." ORDER BY ".AB_ABID;
	}	
	elseif ($mstatus==AB_USERCANCELLED_RES) {
		$selectstr = $this->DBOO->GetSelectColumn($selectarr);
		$statarr = unserialize(AB_USERCANCELLED_RESARRAY);
  		$abstatus_EqOrstr=$this->DBOO->EqualOR_String(array_fill(0, count($statarr), AB_STATUS), $statarr);
  		
		$sqlquery_str=$selectstr
		." FROM ".DBT_ADVANCEBOOKINGS
		.$joinstr		
		." WHERE ".AB_HRID."=".$mHRID
		." AND ".AB_FORDATETIME." BETWEEN ".$mstarttime." AND ".$mendtime
		." AND ".$abstatus_EqOrstr
		." ORDER BY ".AB_ABID;
	}
	elseif ($mstatus==AB_NOSHOW_RES) {
		$selectstr = $this->DBOO->GetSelectColumn($selectarr);
		$statarr = unserialize(AB_NOSHOW_RESARRAY);
  		$abstatus_EqOrstr=$this->DBOO->EqualOR_String(array_fill(0, count($statarr), AB_STATUS), $statarr);
		
		$sqlquery_str=$selectstr
		." FROM ".DBT_ADVANCEBOOKINGS
		.$joinstr		
		." WHERE ".AB_HRID."=".$mHRID
		." AND ".AB_FORDATETIME." BETWEEN ".$mstarttime." AND ".$mendtime
		." AND ".$abstatus_EqOrstr
		." ORDER BY ".AB_ABID;
	}
	elseif ($mstatus==AB_CONVERTEDCURRENT_RES) {
		//TODO:: also return the CBID
	}
	elseif ($mstatus==AB_ALLCANCELLEDORNOSHOW_RES){
		$selectstr = $this->DBOO->GetSelectColumn($selectarr);
		$cancelarr = unserialize(AB_USERCANCELLED_RESARRAY);
		$noshowarr = unserialize(AB_NOSHOW_RESARRAY);
		$statarr = array_merge($cancelarr,$noshowarr);
  		$abstatus_EqOrstr=$this->DBOO->EqualOR_String(array_fill(0, count($statarr), AB_STATUS), $statarr);
		
		$sqlquery_str=$selectstr
		." FROM ".DBT_ADVANCEBOOKINGS
		.$joinstr		
		." WHERE ".AB_HRID."=".$mHRID
		." AND ".AB_FORDATETIME." BETWEEN ".$mstarttime." AND ".$mendtime
		." AND ".$abstatus_EqOrstr
		." ORDER BY ".AB_ABID;
	}
	elseif ($mstatus==AB_ALL_RES) {
		$selectarr=array_merge($selectarr,array(ABT_TBID=>"TBID"));
		$selectstr = $this->DBOO->GetSelectColumn($selectarr);
		
		$sqlquery_str=$selectstr
		." FROM ".DBT_ADVANCEBOOKINGS
		.$joinstr
		." WHERE ".AB_HRID."=".$mHRID
		." AND ".AB_FORDATETIME." BETWEEN ".$mstarttime." AND ".$mendtime
		." ORDER BY ".AB_ABID;
	}
	elseif($mstatus==AB_ASSIGNED_TABLE_RES){
		//will return only those records where tables have been assigned
		//so in essence returns the assigned tables
		$selectarr=array_merge($selectarr,array(ABT_TBID=>"TBID"));
		$selectstr = $this->DBOO->GetSelectColumn($selectarr);
		$tentarr = unserialize(AB_TENTATIVE_RESARRAY);
		$confarr = unserialize(AB_GUESTCONFIRMED_RESARRAY);
		$statarr = array_merge($tentarr,$confarr);
  		$abstatus_EqOrstr=$this->DBOO->EqualOR_String(array_fill(0, count($statarr), AB_STATUS), $statarr);
		
		$sqlquery_str=$selectstr
		." FROM ".DBT_ADVANCEBOOKINGS
		." LEFT OUTER JOIN ".DBT_GUESTS
		." ON ".GUEST_HRID."=".$mHRID." AND ".GUEST_UID."=".AB_GUESTUID
		." INNER JOIN ".DBT_ABTABLES
		." ON ".ABT_HRID."=".$mHRID." AND ".ABT_ABID."=".AB_ABID." AND ".ABT_ALIVE."=1"
		." WHERE ".AB_HRID."=".$mHRID
		." AND ".AB_FORDATETIME." BETWEEN ".$mstarttime." AND ".$mendtime
		." AND ".$abstatus_EqOrstr
		." ORDER BY ".AB_ABID;
	}
	elseif($mstatus==AB_FORABID_RES){
		$mABID=$this->DBOO->RealEscapeString($extra_array['ABID']);
		
		$selectarr=array_merge($selectarr,array(ABT_TBID=>"TBID"));
		$selectstr = $this->DBOO->GetSelectColumn($selectarr);
  
		$sqlquery_str=$selectstr
		." FROM ".DBT_ADVANCEBOOKINGS
		.$joinstr
		." WHERE ".AB_HRID."=".$mHRID
		." AND ".AB_ABID."=".$mABID;
	}
	elseif($mstatus==AB_FORDATETIME_FORABID_RES){
		$mABID=$this->DBOO->RealEscapeString($extra_array['ABID']);
		$selectarr = array(AB_FORDATETIME=>"BookedFor");
		$selectstr = $this->DBOO->GetSelectColumn($selectarr);
		$sqlquery_str=$selectstr
		." FROM ".DBT_ADVANCEBOOKINGS
		." WHERE ".AB_HRID."=".$mHRID
		." AND ".AB_ABID."=".$mABID;
	}
	elseif($mstatus==AB_STATUS_FORABID_RES){
		$mABID=$this->DBOO->RealEscapeString($extra_array['ABID']);
		$selectarr = array(AB_STATUS=>"Status");
		$selectstr = $this->DBOO->GetSelectColumn($selectarr);
		$sqlquery_str=$selectstr
		." FROM ".DBT_ADVANCEBOOKINGS
		." WHERE ".AB_HRID."=".$mHRID
		." AND ".AB_ABID."=".$mABID;
	}
	elseif($mstatus==AB_CHECKABID_FORABID_RES){
		$mABID=$this->DBOO->RealEscapeString($extra_array['ABID']);
		$sqlquery_str="SELECT COUNT(*)"
		." FROM ".DBT_ADVANCEBOOKINGS
		." WHERE ".AB_HRID."=".$mHRID
		." AND ".AB_ABID."=".$mABID;
	}	
	else{
		MyErrorHandeler::UserError(EXCP_ABINVALIDSTATUS, debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID));
		throw new Exception(USER_ABINVALIDSTATUS);
	}
	return $sqlquery_str;
  } 
  

	public function GetTBIDArray($supTableNames,$mRoomName){
		$out =array();
		$mTableNames=explode(',',$supTableNames);
		$mTableNames=array_map("trim", $mTableNames);
		
		if(empty($mTableNames)){
			throw new Exception(USER_EMPTYTABLELIST);
		}
		$mTBID=array();
		$mTBID=$this->GetTBID_FromTableNames($supTableNames,$mRoomName);
		if(count($mTBID)==count($mTableNames)){
			return $mTBID;
		}
		else{
			MyErrorHandeler::UserError(EXCP_INVALIDTABLEID,debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID));
			throw new Exception(USER_INVALIDTABLEID);
		}
			
  }
	
	private function GetForDateTime_ABID_NoChecks($ABID){
		$dummytime =date('Y-m-d');
		$status = AB_FORDATETIME_FORABID_RES;
		$extra_arr['ABID']=$ABID;
		$sqlquery_str = $this->GetDBQueryStr_AdvanceBookings_BetweenDate($dummytime, $dummytime, $status, $extra_arr);
		$SelectReturnArr = $this->DBOO->Select($sqlquery_str);
		foreach ($SelectReturnArr as $value) {
			$out = $value["BookedFor"];
		}
		return $out;
	}
	
	private function GetStatus_ABID_NoChecks($ABID){
		$dummytime =date('Y-m-d');
		$status = AB_STATUS_FORABID_RES;
		$extra_arr['ABID']=$ABID;
		$sqlquery_str = $this->GetDBQueryStr_AdvanceBookings_BetweenDate($dummytime, $dummytime, $status, $extra_arr);
		$SelectReturnArr = $this->DBOO->Select($sqlquery_str);
		foreach ($SelectReturnArr as $value) {
			$out = $value["Status"];
		}
		return $out;
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
		
  
  //*********************************SetMethods******************//
  
  public function SetABID($mABID){
		$ABID=trim($mABID);
		if(empty($ABID) or !CommonFunctions::isValidTimeStamp($ABID)){
			throw new Exception(USER_ABINVALIDSTATUS);
		}
		//check wether it exists or not in db
		$status =AB_CHECKABID_FORABID_RES;
		$dummytime = date('Y-m-d H:i:s');
		$sqlquery_str = $this->GetDBQueryStr_AdvanceBookings_BetweenDate($dummytime,$dummytime,$status,array("ABID"=>$ABID));
		$out = $this->DBOO->SelectCount($sqlquery_str);
		if($out!=1){
			throw new Exception(USER_ABINVALIDSTATUS);
		}
		return $ABID;		
	}
  
  
 public function SetTableStatus($mTabStatus){
 	if(empty($mTabStatus) or strcasecmp($mTabStatus, AB_PENDING_TABLESTATUSTYPE)==0){
 		return AB_PENDING_TABLESTATUS;
 	}
	elseif(strcasecmp($mTabStatus, AB_ASSIGNED_TABLESTATUSTYPE)==0){
 		return AB_ASSIGNED_TABLESTATUS;
 	}
	elseif(strcasecmp($mTabStatus, AB_CONVERTEDCURRENT_TABLESTATUSTYPE)==0){
 		return AB_CONVERTEDCURRENT_TABLESTATUS;
 	}	
	elseif(strcasecmp($mTabStatus, AB_CANCELLORCLOSE_TABLESTATUSTYPE)==0){
 		return AB_CANCELLORCLOSE_TABLESTATUS;
 	}	
	else{
		//MyErrorHandeler::UserError(EXCP_ABERR131,debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID));
		throw new Exception(USER_ABINVALIDTABLESTATUS);
	}
 } 
  
  public function SetStatus($mStatusType){
  	$mStatusType=trim($mStatusType);
	if(empty($mStatusType)){
  		return AB_UNKNOWN_STATUS;
  	}
	elseif (strcasecmp($mStatusType, AB_REQUESTED_STATUSTYPE)==0) {
		return AB_REQUESTED_STATUS;
	}
	elseif (strcasecmp($mStatusType, AB_APPROVED_STATUSTYPE)==0) {
		return AB_APPROVED_STATUS;
	}
	elseif (strcasecmp($mStatusType, AB_CALLETC_CONFIRMED_STATUSTYPE)==0) {
		return AB_CALLETC_CONFIRMED_STATUS;
	}
	elseif (strcasecmp($mStatusType, AB_CALLETC_NOTCONFIRMED_STATUSTYPE)==0) {
		return AB_CALLETC_NOTCONFIRMED_STATUS;
	}	
	elseif (strcasecmp($mStatusType, AB_USERCONFIRMED_USERCALLED_ANDCANCELLED_STATUSTYPE)==0) {
		return AB_USERCONFIRMED_USERCALLED_ANDCANCELLED_STATUS;
	}	
	elseif (strcasecmp($mStatusType, AB_USERCONFIRMED_AND_NOSHOW_STATUSTYPE)==0) {
		return AB_USERCONFIRMED_AND_NOSHOW_STATUS;
	}
	elseif (strcasecmp($mStatusType, AB_NOTCONFIRMED_CANCELLED_STATUSTYPE)==0) {
		return AB_NOTCONFIRMED_CANCELLED_STATUS;
	}	
	elseif (strcasecmp($mStatusType, AB_CONFIRMED_CONVERTEDCURRENT_STATUS)==0) {
		return AB_CONFIRMED_CONVERTEDCURRENT_STATUS;
	}
	elseif (strcasecmp($mStatusType, AB_NOTCONFIRMED_CONVERTEDCURRENT_STATUSTYPE)==0) {
		return AB_NOTCONFIRMED_CONVERTEDCURRENT_STATUS;
	}
	else{
		MyErrorHandeler::UserError(EXCP_ABINVALIDSTATUS,debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID));
		throw new Exception(USER_ABINVALIDSTATUS);
	}

  }
  
  public function SetBookingMethod($mBookingMethod){
  	if(empty($mBookingMethod)){
  		return AB_UNKNOWN_BOOKINGMETHODTYPE;
  	}
	elseif(strcasecmp($mBookingMethod, AB_CALL_BOOKINGMETHODTYPE)==0){
		return AB_CALL_BOOKINGMETHODTYPE;
	}
	elseif(strcasecmp($mBookingMethod, AB_INPERSON_BOOKINGMETHODTYPE)==0){
		return AB_INPERSON_BOOKINGMETHODTYPE;
	}
	elseif(strcasecmp($mBookingMethod, AB_TRSONLINE_BOOKINGMETHODTYPE)==0){
		return AB_TRSONLINE_BOOKINGMETHODTYPE;
	}
	elseif(strcasecmp($mBookingMethod, AB_OWNONLINE_BOOKINGMETHODTYPE)==0){
		return AB_OWNONLINE_BOOKINGMETHODTYPE;
	}
  	elseif(strcasecmp($mBookingMethod, AB_THIRDPARTY_BOOKINGMETHODTYPE)==0){
		return AB_THIRDPARTY_BOOKINGMETHODTYPE;
	}
	else{
		MyErrorHandeler::UserError(EXCP_ABINVALIDBOOKINGMETHOD,debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID));
		throw new Exception(USER_ABINVALIDBOOKINGMETHOD);
	}
  }
  
}


?>
