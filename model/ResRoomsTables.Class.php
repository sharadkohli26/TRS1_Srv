<?php

/**
 * Default class for ResRooms and ResRooms_Tables
 */
class ResRoomsTablesEntry {
	private $DBOO;
	public $HRID;
	public $UserID;
	
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
	
	//Set up functions
	public function CreateNewRoom($mRoomName){
		$result = array();
		$mRoomNameArray = array($mRoomName);
		if($this->ValidateRoomName($mRoomNameArray,FALSE)){
			$RoomName = trim($mRoomName);
		}
		else{
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_INVALIDROOMNAME);
			return $result;
		}
		
		//check if room name already exists ..
		if($this->CheckRoomNameInDB($mRoomNameArray)){
			//choose another room name
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_ROOMNAME_ALREADYEXISTS);
			return $result;	
		}
		//now create room
		$createtime = CommonFunctionsDateTime::GetDT_YMDHIS(NULL);
		$isalive = 1;
		$insertcol = array(RESROOMS_HRID,RESROOMS_ROOMNAME,RESROOMS_CREATEDTIME,RESROOMS_ISALIVE);
		$insertval = array(array($this->HRID,$RoomName,$createtime,$isalive));
		$mtablename=DBT_RESROOMS;
		
		$result = $this->DBOO->Insert($mtablename, $insertcol, $insertval);
		if($result[RESULT_STATUS]){
			$idarr = $result[RESULT_PAYLOAD];
			return $this->GetRoomDetails_ForRoomID($idarr["ID"],FALSE);
		}
		else{
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_ROOMCREATE_FAILED);
			return $result;
		}
	}
	
	public function ChangeRoomName($mNewRoomName,$mOldRoomName){
		$result=array();
		$NewRoomName = trim($mNewRoomName);
		$OldRoomName = trim($mOldRoomName);
		//$mRoomNameArray = array($NewRoomName,$OldRoomName);
		if($this->ValidateRoomName($NewRoomName, FALSE) and $this->ValidateRoomName($OldRoomName,TRUE)){
			if(strcmp($NewRoomName, $OldRoomName)==0){
				return $this->GetRoomDetails_ForRoomNames($OldRoomName, FALSE);
			}
		}
		else{
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_INVALIDROOMNAME);
			return $result;
		}
		//check if room name already exists ..
		if($this->CheckRoomNameInDB(array($NewRoomName))){
			//choose another room name
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_ROOMNAME_ALREADYEXISTS);
			return $result;	
		}		
		//now update roomname
		$update_set_array = array(RESROOMS_ROOMNAME);
		$update_set_QString=$this->DBOO->Update_Set_QString($update_set_array);
		$update_where_array = array(RESROOMS_HRID,RESROOMS_ROOMNAME);
		$update_where_QString = $this->DBOO->Update_WhereEQAnd_QString($update_where_array);
		$sqlquery_str="UPDATE ".DBT_RESROOMS." ".$update_set_QString." WHERE ".$update_where_QString;
		$bindvalarr=array($NewRoomName,$this->HRID,$OldRoomName);
		$affectedrows = $this->DBOO->Update($sqlquery_str, $bindvalarr);
		
		if($affectedrows==0){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_ROOMNAMEUPDATE_FAILED);
		}
		else{
			$result = $this->GetRoomDetails_ForRoomNames($NewRoomName, FALSE);
		}
		return $result;
	}
	
	public function ActivateDeactivateRoom_ForRoomName($mRoomName,$mCurrentStatus,$dovalidate){
		$RoomName = trim($mRoomName);
		$CurrentStatus = trim($mCurrentStatus);
		$this->CheckAliveDeadStatus($CurrentStatus);
		if($dovalidate){
			if(! $this->ValidateRoomName($RoomName, TRUE)){
				$result[RESULT_STATUS]=FALSE;
				$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_INVALIDROOMNAME);
				return $result;
			}
			$CurrStatusInDB = $this->GetRoomAliveDeadStatus_ForRoomNames($RoomName, FALSE);
			if($CurrentStatus!=$CurrStatusInDB[0]){
				$result[RESULT_STATUS]=FALSE;
				$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_STATUSINVALID);
				return $result;	
			}
		}
		$newisalive=$CurrentStatus==0?1:0;
		$update_set_array = array(RESROOMS_ISALIVE);
		$update_set_QString=$this->DBOO->Update_Set_QString($update_set_array);
		$update_where_array = array(RESROOMS_HRID,RESROOMS_ROOMNAME,RESROOMS_ISALIVE);
		$update_where_QString = $this->DBOO->Update_WhereEQAnd_QString($update_where_array);
		$sqlquery_str="UPDATE ".DBT_RESROOMS." ".$update_set_QString." WHERE ".$update_where_QString;
		$bindvalarr=array($newisalive,$this->HRID,$RoomName,$CurrentStatus);
		$affectedrows = $this->DBOO->Update($sqlquery_str, $bindvalarr);
		
		return $this->GetRoomDetails_ForRoomNames($RoomName, FALSE);
	}
	
	public function GetRoomDetails_ForGivenStatus($mCurrentStatus){
		$RoomIDArr = $this->GetRoomIDArr_ForGivenStatus($mCurrentStatus);
		$result=array();
		if(empty($RoomIDArr)){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_NOROOMS_FORSTATUS);
			return $result;
		}
		return $this->GetRoomDetails_ForRoomID($RoomIDArr, FALSE);
	}
	
	public function GetRoomDetails_ForRoomNames($mRoomNamesStr,$dodbcheck){
		$mRoomNamesArr=explode(',',$mRoomNamesStr);
		$RoomNamesArr=array_map("trim", $mRoomNamesArr);
		//VALIDATION already takes place in GetRoomID_ForRoomNamesArr
		$RoomIDArr = $this->GetRoomID_ForRoomNamesArr($RoomNamesArr, FALSE);
		return $this->GetRoomDetails_ForRoomID($RoomIDArr, FALSE);
	}
	
	private function GetRoomID_ForRoomNamesArr($mRoomNamesArr,$dovalidate){
		//returns the room id or throws exception
		$result = array();
		$RoomNamesArr = $mRoomNamesArr;
		$extra_array = array();
		$status = RR_ROOMID_FORROOMNAMES;
		$extra_array['RoomNames'] = $RoomNamesArr;
		$sqlquery_str = $this->GetDBQueryString($status, $extra_array);
		$SelectReturnArr=$this->DBOO->Select($sqlquery_str);
		$out=array();
		foreach ($SelectReturnArr as $row) {
			$out[]=$row[OUT_RRT_ROOMID];
		}
		if(count($out)!=count($RoomNamesArr)){
			throw new Exception(USER_RRT_ROOMNAME_NOTFOUND);
		}
		return $out;
	}
	
	private function GetRoomIDArr_ForGivenStatus($mCurrentStatus){
		$CurrentStatus = trim($mCurrentStatus);
		$this->CheckAliveDeadStatus($CurrentStatus);
		$extra_array = array();
		$status = RR_ROOMID_FOR_GIVENSTATUS;
		$extra_array['AliveDeadStatus']=$CurrentStatus;
		$sqlquery_str = $this->GetDBQueryString($status, $extra_array);
		$SqlRetArr=$this->DBOO->Select($sqlquery_str);
		$RoomIDArr=array();
		foreach ($SqlRetArr as $value) {
			$RoomIDArr[] = $value[OUT_RRT_ROOMID];
		}
		return $RoomIDArr;
	}
	
	public function GetRoomDetails_ForRoomID($mRoomID,$dodbcheck){
		$result=array();
		//TODO Implement dbcheck
		if(! is_array($mRoomID)){
			$RoomIDArr = array($mRoomID);
		}
		else{
			$RoomIDArr = $mRoomID;
		}
		
		$status = RR_ROOMDETAILS_FOR_ROOMID;
		$extra_array['RoomID'] = $RoomIDArr;
		$sqlquery_str = $this->GetDBQueryString($status, $extra_array);
		$SqlRetArr1=$this->DBOO->Select($sqlquery_str);
		if((count($SqlRetArr1)!=count($RoomIDArr))){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_ROOMDETAILS_FAILED);
			MyErrorHandeler::UserError(USER_RRT_ROOMDETAILS_FAILED,debug_backtrace(), array());
		}
		$status = RR_NUM_ACTIVE_TABLES_FOR_ROOMID;
		$extra_array['RoomID'] = $RoomIDArr;
		$sqlquery_str = $this->GetDBQueryString($status, $extra_array);
		$SqlRetArr2=$this->DBOO->Select($sqlquery_str);
		$out = array();
		foreach ($SqlRetArr1 as $curr_row) {
			//find roomid in the SqlRetArr2..and append NumTableActive
			$curr_row[OUT_RRT_CREATEDTIME] = date('Y-m-d',strtotime($curr_row[OUT_RRT_CREATEDTIME]));
			$curr_row[OUT_RRT_NUM_ACTIVE_TABLES]=0;
			foreach ($SqlRetArr2 as $var) {
				if($curr_row[OUT_RRT_ROOMID]==$var[OUT_RRT_ROOMID]){
					$curr_row[OUT_RRT_NUM_ACTIVE_TABLES]=$var[OUT_RRT_NUM_ACTIVE_TABLES];
					break;
				}
			}
			unset($curr_row[OUT_RRT_ROOMID]);
			$out[] = $curr_row;
		}
		
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	private function GetRoomAliveDeadStatus_ForRoomNames($mRoomName,$dodbcheck){
		if(is_array($mRoomName)){
			$RoomNamesArr = $mRoomName;
		}
		else{
			$RoomNamesArr = array($mRoomName);
		}
		if($dodbcheck){
			if(! $this->ValidateRoomName($RoomNamesArr, TRUE)){
				throw new Exception(USER_RRT_ROOMNAME_NOTFOUND);
			}
		}
		$status = RR_GET_ALIVEDEADSTATUS_FORROOMNAMES;
		$extra_array = array();$extra_array['RoomNames']=$RoomNamesArr;
		$sqlquery_str = $this->GetDBQueryString($status, $extra_array);
		$SelectReturnArr=$this->DBOO->Select($sqlquery_str);
		$out=array();
		foreach ($SelectReturnArr as $row) {
			$out[]=$row[OUT_RRT_ISALIVE];
		}
		return $out;
	}
	
	public function GetRoomID_ForRoomNames($mRoomNames,$dovalidate){
		//supplied inputs is a string csv
		//basically just explode the sring with comma..and pass it to the arr function 
		$RoomNamesArr = explode(",", $mRoomNames);
		return $this->GetRoomID_ForRoomNamesArr($RoomNamesArr, $dovalidate);
	}
	
	private function CheckRoomIDInDB($mRoomIDArr){
		$status = RR_CHECK_ROOMID_INDB;
		$extra_array['RoomID']=$mRoomIDArr;
		$sqlquery_str = $this->GetDBQueryString($status, $extra_array);
		$out = $this->DBOO->SelectCount($sqlquery_str);
		if($out == count($mRoomIDArr)){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	
	public function CheckAliveDeadStatus($mCurrentStatus){
		$CurrentStatus = (int)trim($mCurrentStatus);
		if($CurrentStatus != 0 and $CurrentStatus != 1 ){
			throw new Exception(USER_RRT_ALIVEDEAD_STATUSINVALID);
		}
		
	}
		
	private function GetDBQueryString($status,$extra_array){
		$selectarr=array(RESROOMS_HRID=>OUT_RRT_HRID,RESROOMS_ROOMNAME=>OUT_RRT_ROOMNAME,RESROOMS_ROOMID=>OUT_RRT_ROOMID,RESROOMS_CREATEDTIME=>OUT_RRT_CREATEDTIME,RESROOMS_ISALIVE=>OUT_RRT_ISALIVE);
		$HRID = $this->DBOO->RealEscapeString($this->HRID);

		if($status == RR_ROOMID_FORROOMNAMES){
			$RoomNamesArr = $extra_array['RoomNames'];
			$where_in=$this->DBOO->SelectWhere_In_QString(RESROOMS_ROOMNAME, $RoomNamesArr);
			$selectarr=array(RESROOMS_ROOMID=>OUT_RRT_ROOMID);
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$sqlquery_str=$selectstr
			." FROM ".DBT_RESROOMS
			." WHERE ".RESROOMS_HRID."=".$HRID
			." AND ".$where_in;
		}
		elseif ($status==RR_ROOMID_FOR_GIVENSTATUS) {
			$CurrentStatus =$this->DBOO->RealEscapeString($extra_array['AliveDeadStatus']);
			$selectarr=array(RESROOMS_ROOMID=>OUT_RRT_ROOMID);
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$sqlquery_str=$selectstr
			." FROM ".DBT_RESROOMS
			." WHERE ".RESROOMS_HRID."=".$HRID
			." AND ".RESROOMS_ISALIVE."=".$CurrentStatus;
		}
		elseif($status == RR_CHECK_ROOMID_INDB){
			$RoomIDArr = $extra_array['RoomID'];
			$where_in=$this->DBOO->SelectWhere_In_QString(RESROOMS_ROOMID, $RoomIDArr);
			$sqlquery_str="SELECT COUNT(*)"		
				." FROM ".DBT_RESROOMS
				." WHERE ".RESROOMS_HRID."=".$HRID
				." AND ".$where_in;
		}
		elseif($status == RR_ROOMDETAILS_FOR_ROOMID){
			$RoomIDArr = $extra_array['RoomID'];
			$where_in=$this->DBOO->SelectWhere_In_QString(RESROOMS_ROOMID, $RoomIDArr);
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$sqlquery_str=$selectstr
				." FROM ".DBT_RESROOMS
				." WHERE ".RESROOMS_HRID."=".$HRID
				." AND ".$where_in
				." ORDER BY ".RESROOMS_ROOMID;
		}
		elseif ($status == RR_NUM_ACTIVE_TABLES_FOR_ROOMID) {
			$RoomIDArr = $extra_array['RoomID'];
			$where_in=$this->DBOO->SelectWhere_In_QString(RESROOMS_ROOMID, $RoomIDArr);
			$selectarr = array(RESROOMS_ROOMID=>OUT_RRT_ROOMID,"COUNT(".RRT_TBID.")"=>OUT_RRT_NUM_ACTIVE_TABLES);
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$sqlquery_str=$selectstr
				." FROM ".DBT_RESROOMS
				." LEFT OUTER JOIN ".DBT_RESROOMSTABLES
				." ON ".RRT_HRID."=".$HRID." AND ".RRT_ROOMID."=".RESROOMS_ROOMID." AND ".RRT_STATUS."=".RT_ACTIVESTATUS
				." AND ".RESROOMS_ISALIVE."= 1"
				." WHERE ".RESROOMS_HRID."=".$HRID
				." AND ".$where_in
				." GROUP BY ".RESROOMS_ROOMID
				." ORDER BY ".RESROOMS_ROOMID;
		}
		elseif($status == RR_CHECK_ROOMNAME_INDB){
			$RoomNamesArr = $extra_array['RoomNames'];
			$where_in=$this->DBOO->SelectWhere_In_QString(RESROOMS_ROOMNAME, $RoomNamesArr);
			$sqlquery_str="SELECT COUNT(*)"		
				." FROM ".DBT_RESROOMS
				." WHERE ".RESROOMS_HRID."=".$HRID
				." AND ".$where_in;
		}
		elseif ($status==RR_GET_ALIVEDEADSTATUS_FORROOMNAMES) {
			$RoomNamesArr = $extra_array['RoomNames'];
			$where_in=$this->DBOO->SelectWhere_In_QString(RESROOMS_ROOMNAME, $RoomNamesArr);
			$selectarr = array(RESROOMS_ISALIVE=>OUT_RRT_ISALIVE);
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$sqlquery_str=$selectstr
				." FROM ".DBT_RESROOMS
				." WHERE ".RESROOMS_HRID."=".$HRID
				." AND ".$where_in;
		}
		elseif ($status == RR_ROOMDETAILS_FOR_GIVENSTATUS) {
			$CurrentStatus =$this->DBOO->RealEscapeString($extra_array['AliveDeadStatus']);
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$sqlquery_str = $selectstr
			." FROM ".DBT_RESROOMS
			." WHERE ".RESROOMS_HRID."=".$HRID
			." AND ".RESROOMS_ISALIVE."=".$CurrentStatus;
		}
		elseif($status == RRT_CHECK_TABLENAME_INDB){
			$TableNamesArr = $extra_array['TableNames'];
			$RoomID = $this->DBOO->RealEscapeString($extra_array['RoomID']);
			$where_in=$this->DBOO->SelectWhere_In_QString(RRT_DISPLAYNAME, $TableNamesArr);
			$sqlquery_str="SELECT COUNT(*)"		
				." FROM ".DBT_RESROOMSTABLES
				." WHERE ".RRT_HRID."=".$HRID
				." AND ".RRT_ROOMID."=".$RoomID
				." AND ".$where_in;
		}
		elseif($status == RRT_TABLEDETAILS_FOR_TBIDS){
			$TBIDArr = $extra_array['TBIDArr'];
			$selectarr=array(RRT_HRID=>OUT_RRT_HRID,RESROOMS_ROOMNAME=>OUT_RRT_ROOMNAME,RESROOMS_ISALIVE=>OUT_RRT_ISALIVE,
			RRT_DISPLAYNAME=>OUT_RRT_TABLENAME,RRT_MINCAPACITY=>OUT_RRT_MINCAPACITY,RRT_MAXCAPACITY=>OUT_RRT_MAXCAPACITY,
			RRT_STATUS=>OUT_RRT_TABLESTATUS,RRT_ONLINESTATUS=>OUT_RRT_TABLESTATUS_ONLINEAVAILABILITY);
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$joinstr = 
				 " LEFT OUTER JOIN ".DBT_RESROOMS
				." ON ".RESROOMS_HRID."=".$HRID." AND ".RESROOMS_ROOMID."=".RRT_ROOMID;
			$where_in=$this->DBOO->SelectWhere_In_QString(RRT_TBID, $TBIDArr);
			$sqlquery_str = $selectstr
			." FROM ".DBT_RESROOMSTABLES
			." ".$joinstr
			." WHERE ".RRT_HRID."=".$HRID
			." AND ".$where_in;
		}
		elseif ($status == RRT_TBID_FOR_TABLENAMES) {
			$TableNamesArr=$extra_array['TableNamesArr'] ;
			$RoomID=$this->DBOO->RealEscapeString($extra_array['RoomID']);
			$selectarr = array(RRT_TBID=>OUT_RRT_TABLEID);
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$where_in=$this->DBOO->SelectWhere_In_QString(RRT_DISPLAYNAME, $TableNamesArr);
			$sqlquery_str = $selectstr
			." FROM ".DBT_RESROOMSTABLES
			." WHERE ".RRT_HRID."=".$HRID." AND ".RRT_ROOMID."=".$RoomID
			." AND ".$where_in;
		}
		elseif($status == RRT_TABLENAMES_FOR_TBID){
			$TBIDArr = $extra_array['TBIDARR'];
			$selectarr = array(RRT_DISPLAYNAME=>OUT_RRT_TABLENAME);
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$where_in=$this->DBOO->SelectWhere_In_QString(RRT_TBID, $TBIDArr);
			$sqlquery_str = $selectstr
			." FROM ".DBT_RESROOMSTABLES
			." WHERE ".RRT_HRID."=".$HRID
			." AND ".$where_in;
		}
		elseif ($status == ROOMTABLEDETAILS_FOR_ROOMIDARRAY) {
			$RoomIDArr=$extra_array['RoomIDArr'];
			$where_in=$this->DBOO->SelectWhere_In_QString(RESROOMS_ROOMID, $RoomIDArr);
			$selectarr = array(RESROOMS_HRID=>OUT_RRT_HRID,RESROOMS_ROOMID=>OUT_RRT_ROOMID,RESROOMS_ROOMNAME=>OUT_RRT_ROOMNAME,RESROOMS_CREATEDTIME=>OUT_RRT_CREATEDTIME,RESROOMS_ISALIVE=>OUT_RRT_ISALIVE
			,RRT_TBID=>OUT_RRT_TABLEID,RRT_DISPLAYNAME=>OUT_RRT_TABLENAME,RRT_MINCAPACITY=>OUT_RRT_MINCAPACITY,RRT_MAXCAPACITY=>OUT_RRT_MAXCAPACITY,RRT_STATUS=>OUT_RRT_TABLESTATUS,RRT_ONLINESTATUS=>OUT_RRT_TABLESTATUS_ONLINEAVAILABILITY
			);
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$sqlquery_str = $selectstr
			." FROM ".DBT_RESROOMS
			." LEFT OUTER JOIN ".DBT_RESROOMSTABLES
			." ON ".RRT_HRID."=".$HRID." AND ".RRT_ROOMID."=".RESROOMS_ROOMID
			." WHERE ".RESROOMS_HRID."=".$HRID." AND ".$where_in
			." ORDER BY ".RESROOMS_ROOMNAME.",".RRT_DISPLAYNAME;
		}
		elseif($status == ACTIVETABLE_ROOM_DETAILS_FOR_ROOMIDARRAY or $status == ACTIVETABLE_ROOM_DETAILS_TBID_INCLUDED_FOR_ROOMIDARRAY){
			$selectarr = array(RESROOMS_HRID=>OUT_RRT_HRID,RESROOMS_ROOMID=>OUT_RRT_ROOMID,RESROOMS_ROOMNAME=>OUT_RRT_ROOMNAME,RESROOMS_CREATEDTIME=>OUT_RRT_CREATEDTIME,RESROOMS_ISALIVE=>OUT_RRT_ISALIVE
			,RRT_TBID=>OUT_RRT_TABLEID,RRT_DISPLAYNAME=>OUT_RRT_TABLENAME,RRT_MINCAPACITY=>OUT_RRT_MINCAPACITY,RRT_MAXCAPACITY=>OUT_RRT_MAXCAPACITY,RRT_STATUS=>OUT_RRT_TABLESTATUS,RRT_ONLINESTATUS=>OUT_RRT_TABLESTATUS_ONLINEAVAILABILITY
			);
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$sqlquery_str = $selectstr
			." FROM ".DBT_RESROOMS
			." LEFT OUTER JOIN ".DBT_RESROOMSTABLES
			." ON ".RRT_HRID."=".$HRID." AND ".RRT_ROOMID."=".RESROOMS_ROOMID." AND ".RRT_STATUS."=".RT_ACTIVESTATUS
			." WHERE ".RESROOMS_HRID."=".$HRID." AND ".RESROOMS_ISALIVE."=1"
			." ORDER BY ".RESROOMS_ROOMNAME.",".RRT_DISPLAYNAME;
			//." AND ".$where_in;
		}
		
		else{
			MyErrorHandeler::UserError(EXCP_RRT_INVALIDSTATUS, debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID,"Status"=>$status));
			throw new Exception(USER_RRT_STATUSINVALID);
		}
		//var_dump($sqlquery_str);
		//echo "<br>";
		return $sqlquery_str;
	}
	
	private function CheckRoomNameInDB($mRoomNamesArr){
		$status = RR_CHECK_ROOMNAME_INDB;
		$extra_array['RoomNames'] = $mRoomNamesArr;
		$sqlquery_str = $this->GetDBQueryString($status, $extra_array);
		$out = $this->DBOO->SelectCount($sqlquery_str);
		if($out == count($mRoomNamesArr)){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	
	private function ValidateRoomName($mRoomNames,$dodbcheck){
		if(is_array($mRoomNames)){
			$RoomNamesArr = $mRoomNames;
		}
		else{
			$RoomNamesArr = array($mRoomNames);
		}
		
		foreach ($RoomNamesArr as $name) {
			$name = trim($name);
			if(empty($name) or preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $name)){
				return FALSE;
			}
		}
		//do db check ..the names should exist in database
		if($dodbcheck){
			return $this->CheckRoomNameInDB($RoomNamesArr);
		}
		else{
			return TRUE;
		}
	} 
	//*************************************************************************
	public function AddNewTable($mTableName,$mRoomName,$mMinCapacity,$mMaxCapacity,$mOnlineAvailability,$dovalidate){
		$extra_array=array();$reult = array();
		$RoomIDArr = $this->GetRoomID_ForRoomNamesArr(array($mRoomName), $dovalidate);
		$RoomID = $RoomIDArr[0];
		if($dovalidate){
			if(!$this->ValidateTableName($mTableName, FALSE, $RoomID)){	
				$result[RESULT_STATUS]=FALSE;
				$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_INVALIDTABLENAME);
				return $result;
			}
			if(is_null($mMinCapacity) or empty($mMinCapacity)){
				$mMinCapacity=RRT_DEFAULT_MINCAPACITY;
			}
			if(is_null($mMaxCapacity) or empty($mMaxCapacity)){
				$mMaxCapacity = RRT_DEFAULT_MAXCAPACITY;
			}
			
			if(!((int)$mMinCapacity==$mMinCapacity) or !((int)$mMaxCapacity==$mMaxCapacity) or $mMinCapacity<0 or $mMaxCapacity<0 or $mMinCapacity>$mMaxCapacity){
				$result[RESULT_STATUS]=FALSE;
				$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_INVALIDCAPACITY);
				return $result;	
			}
			if(is_null($mOnlineAvailability) or empty($mOnlineAvailability)){
				$mOnlineAvailability="No";
			}
		}
		//check table name in DB ....
		if($this->CheckTableNameInDB(array($mTableName), $RoomID)){
			//if exists then choose another name
				$result[RESULT_STATUS]=FALSE;
				$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_TABLENAME_ALREADYEXISTS);
				return $result;
		}
		$TableName = $mTableName;
		//now create room
		$MinCapacity = empty($mMinCapacity)?RRT_DEFAULT_MINCAPACITY:$mMinCapacity;
		$MaxCapacity = empty($mMaxCapacity)?RRT_DEFAULT_MAXCAPACITY:$mMaxCapacity;
		$OnlineStatus = CommonFunctions::GetOnlineStatusCode_FromOnlineStatus($mOnlineAvailability);
		$Status = RT_ACTIVESTATUS;
		
		$insertcol = array(RRT_HRID,RRT_DISPLAYNAME,RRT_ROOMID,RRT_MAXCAPACITY,RRT_MINCAPACITY,RRT_STATUS,RRT_ONLINESTATUS);
		$insertval = array(array($this->HRID,$TableName,$RoomID,$MaxCapacity,$MinCapacity,$Status,$OnlineStatus));
		$mtablename=DBT_RESROOMSTABLES;
		$result = $this->DBOO->Insert($mtablename, $insertcol, $insertval);
		if($result[RESULT_STATUS]){
			$idarr = $result[RESULT_PAYLOAD];
			return $this->GetTableDetails_ForTableIDArr(array($idarr["ID"]),FALSE);
		}
		else{
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_ROOMCREATE_FAILED);
			return $result;
		}		
	}	
	
	public function ChangeTableName($mRoomName,$mOldTableName,$mNewTableName){
		$result=array();
		$NewTableName = trim($mNewTableName);
		$OldTableName = trim($mOldTableName);
		
		$RoomIDArr = $this->GetRoomID_ForRoomNamesArr(array($mRoomName), FALSE);
		$RoomID = $RoomIDArr[0];
		
		$TBIDArr = $this->GetTBID_ForTableNames($OldTableName, FALSE, $RoomID);
		$TBID = $TBIDArr[0];
		
		if($this->ValidateTableName($NewTableName, FALSE,$RoomID)){
			if(strcmp($NewTableName, $OldTableName)==0){
				return $this->GetTableDetails_ForTableIDArr($TBIDArr,FALSE);
			}
		}
		else{
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_INVALIDTABLENAME);
			return $result;
		}
		//check if table name already exists ..
		if($this->CheckTableNameInDB(array($NewTableName), $RoomID)){
			//choose another table name
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_TABLENAME_ALREADYEXISTS);
			return $result;	
		}		
		//now update tablename
		$update_set_array = array(RRT_DISPLAYNAME);
		$update_set_QString=$this->DBOO->Update_Set_QString($update_set_array);
		$update_where_array = array(RRT_HRID,RRT_TBID);
		$update_where_QString = $this->DBOO->Update_WhereEQAnd_QString($update_where_array);
		$sqlquery_str="UPDATE ".DBT_RESROOMSTABLES." ".$update_set_QString." WHERE ".$update_where_QString;
		$bindvalarr=array($NewTableName,$this->HRID,$TBID);
		$affectedrows = $this->DBOO->Update($sqlquery_str, $bindvalarr);
		
		if($affectedrows==0){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_TABLE_NAMEUPDATE_FAILED);
		}
		else{
			$result = $this->GetTableDetails_ForTableIDArr($TBIDArr, FALSE);
		}
		return $result;
	}
	
	public function GetTableDetails_ForTableNames($mTableNames,$dodbcheck,$mRoomName){
		$result = $this->GetTBID_ForTableName_RoomName($mTableNames, FALSE, $mRoomName);
		return $this->GetTableDetails_ForTableIDArr($result[RESULT_PAYLOAD], FALSE);
	}
	
	public function GetTBID_ForTableName_RoomName($mTableNames,$dodbcheck,$mRoomName){
		$RoomIDArr = $this->GetRoomID_ForRoomNamesArr(array($mRoomName), FALSE);
		$RoomID = $RoomIDArr[0];
		$TBIDArr = $this->GetTBID_ForTableNames($mTableNames, FALSE, $RoomID);
		$result=array();
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$TBIDArr;
		return $result;
	}
	
	private function GetTBID_ForTableNames($mTableNames,$dodbcheck,$mRoomID){
		//returns an array of TBID or throws exception
		$mTableNamesArr=explode(',',$mTableNames);
		$TableNamesArr=array_map("trim", $mTableNamesArr);
		$extra_array = array();
		$status = RRT_TBID_FOR_TABLENAMES;
		$extra_array['TableNamesArr'] = $TableNamesArr;
		$extra_array['RoomID'] = $mRoomID;
		$sqlquery_str = $this->GetDBQueryString($status, $extra_array);
		$SelArr=$this->DBOO->Select($sqlquery_str);
		if((count($SelArr)!=count($TableNamesArr))){
			MyErrorHandeler::UserError(USER_RRT_TABLEDETAILS_FAILED,debug_backtrace(), array());
			throw new Exception(USER_RRT_TABLEDETAILS_FAILED);
		}
		$TBIDArr = array();
		foreach ($SelArr as  $value) {
			$TBIDArr[] = $value[OUT_RRT_TABLEID];
		}		
		return $TBIDArr;
	}
	
	public function GetRoomTableDetails_ForTBID($mTBIDArr,$dodbcheck){
		//returns an array of TBID or throws exception
		$result = $this->GetTableDetails_ForTableIDArr($mTBIDArr, $dodbcheck);
		$TBDetailsArr = $result[RESULT_PAYLOAD];
		if((count($TBDetailsArr)!=count($mTBIDArr))){
			MyErrorHandeler::UserError(USER_RRT_TABLEDETAILS_FAILED,debug_backtrace(), array());
			throw new Exception(USER_RRT_TABLEDETAILS_FAILED);
		}
		return $result;
	}		
	
	public function GetRoomTableDetails_ForRoomNames($mRoomNamesStr,$dovalidate){
		$RoomIDArr = $this->GetRoomID_ForRoomNames($mRoomNamesStr, $dovalidate);
		$extra_array = array();
		$extra_array['RoomIDArr'] = $RoomIDArr;
		$status = ROOMTABLEDETAILS_FOR_ROOMIDARRAY;
		$out= $this->GetRoomTableDetails($status,$extra_array);
		$result = array();
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function GetRoomTableDetails_ForRoomStatus($mCurrentStatus,$validate){
		$RoomIDArr = $this->GetRoomIDArr_ForGivenStatus($mCurrentStatus);
		$result = array();
		if(empty($RoomIDArr)){
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_RRT_NOROOMS_FORSTATUS);
			return $result;
		}
		
		$extra_array = array();
		$extra_array['RoomIDArr'] = $RoomIDArr;
		$status = ROOMTABLEDETAILS_FOR_ROOMIDARRAY;
		$out= $this->GetRoomTableDetails($status,$extra_array);
		
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function GetRoomTableDetails_ForActiveRoomsActiveTable(){
		$extra_array = array();
		//$RoomIDArr = $this->GetRoomIDArr_ForGivenStatus(1);
		//$extra_array['RoomIDArr'] = $RoomIDArr;
		$status = ACTIVETABLE_ROOM_DETAILS_FOR_ROOMIDARRAY;
		$out= $this->GetRoomTableDetails($status,$extra_array);
		$result = array();
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function GetRoomTableDetails_TBID_Included_ForActiveRoomsActiveTable(){
		$extra_array = array();
		$status = ACTIVETABLE_ROOM_DETAILS_TBID_INCLUDED_FOR_ROOMIDARRAY;
		$out= $this->GetRoomTableDetails($status,$extra_array);
		$result = array();
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	private function GetRoomTableDetails($status,$extra_array){
		$sqlquery_str = $this->GetDBQueryString($status, $extra_array);
		$SelArr=$this->DBOO->Select($sqlquery_str);
		$out = array();
		foreach ($SelArr as $curr_row) {
			if(isset($curr_row[OUT_RRT_CREATEDTIME])){
				$curr_row[OUT_RRT_CREATEDTIME] = date('Y-m-d',strtotime($curr_row[OUT_RRT_CREATEDTIME]));	
			}
			if(! is_null($curr_row[OUT_RRT_TABLEID])){
				$curr_row[OUT_RRT_TABLESTATUS] = CommonFunctions::GetTableStatus_FromTableStatusCode($curr_row[OUT_RRT_TABLESTATUS]);
				$curr_row[OUT_RRT_TABLESTATUS_ONLINEAVAILABILITY]=CommonFunctions::GetOnlineStatus_FromOnlineStatusCode($curr_row[OUT_RRT_TABLESTATUS_ONLINEAVAILABILITY]);
			}
			
			unset($curr_row[OUT_RRT_ROOMID]);
			if($status!=ACTIVETABLE_ROOM_DETAILS_TBID_INCLUDED_FOR_ROOMIDARRAY){
				unset($curr_row[OUT_RRT_TABLEID]);
			}
			$out[] = $curr_row;
		}
		return $out;
	} 
	
	private function GetTableDetails_ForTableIDArr($TBIDArr,$validate){
		//TODO::Implement $validate
		$extra_array=array();
		$status = RRT_TABLEDETAILS_FOR_TBIDS;
		$extra_array['TBIDArr'] = $TBIDArr;
		$sqlquery_str = $this->GetDBQueryString($status, $extra_array);
		$SelArr=$this->DBOO->Select($sqlquery_str);
		//prepare output
		$out=array();
		foreach ($SelArr as $curr_row) {
			$curr_row[OUT_RRT_TABLESTATUS] = CommonFunctions::GetTableStatus_FromTableStatusCode($curr_row[OUT_RRT_TABLESTATUS]);
			$curr_row[OUT_RRT_TABLESTATUS_ONLINEAVAILABILITY]=CommonFunctions::GetOnlineStatus_FromOnlineStatusCode($curr_row[OUT_RRT_TABLESTATUS_ONLINEAVAILABILITY]);
			$out[] = $curr_row;
		}
		
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	private function ValidateTableName($mTableNames,$dodbcheck,$mRoomID){
		if(is_array($mTableNames)){
			$TableNamesArr = $mTableNames;
		}
		else{
			$TableNamesArr = array($mTableNames);
		}
		
		foreach ($TableNamesArr as $name) {
			$name = trim($name);
			if(empty($name) or preg_match('/[\'^£$%&*}{@#~?><>,|=+¬-]/', $name)){
				return FALSE;
			}
		}
		//do db check ..the names should exist in database
		if($dodbcheck){
			return $this->CheckTableNameInDB($TableNamesArr,$mRoomID);
		}
		else{
			return TRUE;
		}
	}
	
	private function CheckTableNameInDB($mTableNamesArr,$mRoomID){
		$status = RRT_CHECK_TABLENAME_INDB;
		$extra_array['TableNames'] = $mTableNamesArr;
		$extra_array['RoomID'] = $mRoomID;
		$sqlquery_str = $this->GetDBQueryString($status, $extra_array);
		$out = $this->DBOO->SelectCount($sqlquery_str);
		if($out == count($mTableNamesArr)){
			return TRUE;
		}
		else{
			return FALSE;
		}
	} 
}



?>
