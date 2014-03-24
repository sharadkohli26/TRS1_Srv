<?php
class GuestsEntry{
	
	private $HRID;
	private $UserID;
	private $DBOO;
	
	public function __construct($mHRID,$mUserID,$mDBOO){
		if(is_null($mDBOO) || empty($mDBOO)){
			$this->DBOO=new DBOperations();
			$this->DBOO->SelectDatabase(DB_TRS1);
		}
		else{
			$this->DBOO=$mDBOO;
		}
		//TODO: check if given HRID is valid and then proceed
		$this->HRID=$mHRID;
		$this->UserID=$mUserID;
	}
	/**
	 * Basically insert on duplicate key update
	 *
	 * @return a result array, with two keys Status and Payload
	 * @author  
	 */
	 //public function NewGuest($mguid,$extra_array){
	 public function NewGuest($mguid,$mcontactnumber,$mname,$memail,$malternatecontact,$mcomment){
	 	//if all is empty then do nothing..
	 	$result=array();
		//$Name = $extra_array[OUT_GUEST_NAME];
		//$Email=$extra_array[OUT_GUEST_EMAIL];
		//$ContactNumber = $this->SetContactNumber($extra_array[OUT_GUEST_CONTACTNUMBER],FALSE);
		$Name = $mname;
		$Email = $memail;
		$ContactNumber = $this->SetContactNumber($mcontactnumber, FALSE);
		$HRID=$this->HRID;
		$UserID=$this->UserID;
		$AlternateContact = $malternatecontact;
		$Comment=$mcomment;
		
	 	if(is_null($mguid) or empty($mguid)){
	 		//we insert
	 		if(empty($mcontactnumber) AND empty($Name) AND empty($Email)){
				$result[RESULT_STATUS]=TRUE;
				$result[RESULT_PAYLOAD]=array(array(OUT_GUEST_UID=>NULL,OUT_GUEST_CONTACTNUMBER=>NULL,OUT_GUEST_NAME=>NULL,OUT_GUEST_EMAIL=>NULL,OUT_GUEST_ALTERNATECONTACTNUMBER=>NULL,OUT_GUEST_COMMENT=>NULL));
			return $result;
			}
			//else insert
			//$AlternateContact = isset($extra_array[OUT_GUEST_ALTERNATECONTACTNUMBER])?$extra_array[OUT_GUEST_ALTERNATECONTACTNUMBER]:NULL;
			//$Comment = isset($extra_array[OUT_GUEST_COMMENT])?$extra_array[OUT_GUEST_COMMENT]:NULL;
			
			$insertcol = array(GUEST_HRID,GUEST_CONTACTNUMBER,GUEST_NAME,GUEST_ALTERNATECONTACTNUMBER,GUEST_EMAIL,GUEST_COMMENT);
			$tablename = DBT_GUESTS;
			$insertval = array(array($HRID,$ContactNumber,$Name,$AlternateContact,$Email,$Comment));

	 		$result = $this->DBOO->Insert($tablename, $insertcol, $insertval);
			if($result[RESULT_STATUS]){
				$GUID = $result[RESULT_PAYLOAD]["ID"];
			}else{
				$result[RESULT_STATUS]=FALSE;
				$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_GUEST_INSERTFAILED);
				return $result;
			}
	 	}
		else{
			//check uid..
			$GUID = $this->SetGUID($mguid);
			//then we update..
			$update_set_array = array(GUEST_CONTACTNUMBER,GUEST_NAME,GUEST_EMAIL);
			$update_set_QString=$this->DBOO->Update_Set_QString($update_set_array);
			$update_where_array = array(GUEST_UID,GUEST_HRID);
			$update_where_QString = $this->DBOO->Update_WhereEQAnd_QString($update_where_array);
			$sqlquery_str="UPDATE ".DBT_GUESTS." ".$update_set_QString." WHERE ".$update_where_QString;
			$bindvalarr=array($ContactNumber,$Name,$Email,$GUID,$HRID);
			$affectedrows = $this->DBOO->Update($sqlquery_str, $bindvalarr);
			
		}
		$result = $this->GetAllDetails_ForGUID($GUID, FALSE);
		return $result;
	 }
	
	
	public function GetAllDetails_ForGUID($mguid,$dochecks){
		$extra_array=array();
		if($dochecks){
			$extra_array[OUT_GUEST_UID]=$this->SetGUID($mguid);
		}
		else {
			$extra_array[OUT_GUEST_UID]=$mguid;
		}
		$status=GUEST_ALLDEATILS_FORGUID_RES;
		$out= $this->Get_GuestsInfo($status,  $extra_array);
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function GetAllStats_ForGUID($mguid,$dochecks){
		$extra_array=array();
		if($dochecks){
			$GUID=$this->SetGUID($mguid);
		}
		else {
			$GUID=$mguid;
		}
		//get walk in count
		$cbentry = new CurrentBookingEntry($this->HRID,$this->UserID,$this->DBOO);
		$walkin = $cbentry->GetWalkInCount_ForGUID($GUID, FALSE);
		
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=array(OUT_GUID_WALKINCOUNT=>$walkin);
		return $result;
	}
	
	public function GetAllDetails_ForContactNumber($mcontactnumber,$dochecks){
		$extra_array=array();$out=array();
		if($dochecks){
			$extra_array[OUT_GUEST_CONTACTNUMBER]=$this->SetContactNumber($mcontactnumber,FALSE);
			if(is_null($extra_array[OUT_GUEST_CONTACTNUMBER])){
				$result[RESULT_STATUS]=TRUE;
				$result[RESULT_PAYLOAD]=$out;
				return $result;
			}
		}
		else {
			$extra_array[OUT_GUEST_CONTACTNUMBER]=$mcontactnumber;
		}
		
		$status=GUEST_ALLDEATILS_FORCONTACTNUMBER_RES;
		$out= $this->Get_GuestsInfo($status,  $extra_array);
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function GetAllDetails_ForName($mname,$dochecks){
		$extra_array=array();$out=array();
		$extra_array[OUT_GUEST_NAME]=$mname;
		//TODO:: ADD data validation or check
		 
		if(is_null($extra_array[OUT_GUEST_NAME]) or empty($extra_array[OUT_GUEST_NAME])){
			$result[RESULT_STATUS]=TRUE;
			$result[RESULT_PAYLOAD]=$out;
			return $result;
		}
		
		$status=GUEST_ALLDEATILS_FORNAME_RES;
		$out= $this->Get_GuestsInfo($status,  $extra_array);
		$result[RESULT_STATUS]=TRUE;
		$result[RESULT_PAYLOAD]=$out;
		return $result;
	}
	
	public function UpdateGuestInfo($mcontactnumber,$mname,$memail,$malternatecontact,$mcomment,$mguid){
		$result=array();
		$GUID = $this->SetGUID($mguid);
		$ContactNumber = $this->SetContactNumber($mcontactnumber,FALSE);
		$Name = $mname;
		$Email=$memail;
		$AlternateContact = $malternatecontact;
		$Comment = $mcomment;
		
		$affectedrows = $this->UpdateGuestInfo_NoChecks($ContactNumber,$Name,$Email,$AlternateContact,$Comment,$GUID);
		if($affectedrows==1){ 
			return $this->GetAllDetails_ForGUID($GUID,FALSE);
		}
		else{
			$result[RESULT_STATUS]=FALSE;
			$result[RESULT_PAYLOAD]=array(ERRPAYLOAD_MESSAGE=>USER_GUEST_INFOUPDATEFAIL);
			return $result;
		}
	}
	
	private function Get_GuestsInfo($status,$extra_array){
		$sqlquery_str=$this->GetDBQueryStr($status, $extra_array);
		$SelectReturnArr = 	$this->DBOO->Select($sqlquery_str);
		$last_row=array();$out=array();
		foreach ($SelectReturnArr as $curr_row){
			//$curr_row[OUT_GUEST_CONTACTNUMBER] = $curr_row[OUT_GUEST_CONTACTNUMBER]<0?"":$curr_row[OUT_GUEST_CONTACTNUMBER];
			$out[] = $curr_row;
		}
		return $out;
	}
	
	private function GetDBQueryStr($status,$extra_array){
		$HRID=$this->DBOO->RealEscapeString($this->HRID);
		$selectarr = array(GUEST_UID=>OUT_GUEST_UID,GUEST_CONTACTNUMBER=>OUT_GUEST_CONTACTNUMBER,GUEST_NAME=>OUT_GUEST_NAME,GUEST_EMAIL=>OUT_GUEST_EMAIL,GUEST_ALTERNATECONTACTNUMBER=>OUT_GUEST_ALTERNATECONTACTNUMBER,GUEST_COMMENT=>OUT_GUEST_COMMENT);
		if($status==CHECKGUID_FORGUID){
			$GUID=$this->DBOO->RealEscapeString($extra_array[OUT_GUEST_UID]);
			$sqlquery_str="SELECT COUNT(*)"
			." FROM ".DBT_GUESTS
			." WHERE ".GUEST_HRID."=".$HRID
			." AND ".GUEST_UID."=".$GUID;
		}
		elseif ($status==GUEST_ALLDEATILS_FORGUID_RES) {
			$GUID=$this->DBOO->RealEscapeString($extra_array[OUT_GUEST_UID]);
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$sqlquery_str=$selectstr
			." FROM ".DBT_GUESTS
			." WHERE ".GUEST_HRID."=".$HRID
			." AND ".GUEST_UID."=".$GUID;
		}
		elseif($status==GUEST_ALLDEATILS_FORCONTACTNUMBER_RES){
			$ContactNumber = $this->DBOO->RealEscapeString($extra_array[OUT_GUEST_CONTACTNUMBER]);
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$sqlquery_str=$selectstr
			." FROM ".DBT_GUESTS
			." WHERE ".GUEST_HRID."=".$HRID
			." AND ".GUEST_CONTACTNUMBER." LIKE '%".$ContactNumber."%'";
		}
		elseif($status==GUEST_ALLDEATILS_FORNAME_RES){
			/*$Name = $this->DBOO->RealEscapeString($extra_array[OUT_GUEST_NAME]);
			 * $selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$sqlquery_str=$selectstr
			." FROM ".DBT_GUESTS
			." WHERE ".GUEST_HRID."=".$HRID
			." AND ".GUEST_NAME." LIKE '%".$Name."%' ";
			//doing as above gives an error..the escape string function also adds quotes to non numeric input..so your final string becomes'%'$NAME'%'
			*/
			$Name = $this->DBOO->RealEscapeString("%".$extra_array[OUT_GUEST_NAME]."%");
			$selectstr = $this->DBOO->GetSelectColumn($selectarr);
			$sqlquery_str=$selectstr
			." FROM ".DBT_GUESTS
			." WHERE ".GUEST_HRID."=".$HRID
			." AND ".GUEST_NAME." LIKE ".$Name;
		}
		else{
			MyErrorHandeler::UserError(EXCP_GUEST_INVALIDSTATUS, debug_backtrace(), array("HRID"=>$this->HRID,"UserID"=>$this->UserID));
			throw new Exception(EXCP_GUEST_INVALIDSTATUS);
	}
		return $sqlquery_str;
	}

  public function SetGUID($mguid){
  		$extra_array=array();
		$extra_array[OUT_GUEST_UID]=trim($mguid);
		//check wether it exists or not in db
		$status =CHECKGUID_FORGUID;
		$sqlquery_str = $this->GetDBQueryStr($status,$extra_array);
		$out = $this->DBOO->SelectCount($sqlquery_str);
		if($out!=1){
			throw new Exception(USER_GUEST_INVALIDUID);
		}
		return $extra_array[OUT_GUEST_UID];
	}	
	
	public function SetContactNumber($mcontactnumber,$doDBcheck){
		$contactnumber = trim($mcontactnumber);
		if(is_null($contactnumber) or empty($contactnumber)){
			//$out=-time();
			//PRIMARY GUEST NUMBER CAN BE NULL OR EMPTY
			$out=NULL;
		}
		elseif(!ctype_digit($contactnumber)){
			throw new Exception(USER_GUEST_INVALIDCONTACT);
		}
		else{
			$out=$contactnumber;
		}
		
		if($doDBcheck){
			if(is_null($contactnumber) or empty($contactnumber)){
				throw new Exception(USER_GUEST_CONTACT_EMPTY);
			}
  			$extra_array=array();
			$extra_array[OUT_GUEST_CONTACTNUMBER]=$contactnumber;
			//check wether it exists or not in db
			$status =CHECK_CONTACTNUMBER_FORGUID;
			$sqlquery_str = $this->GetDBQueryStr($status,$extra_array);
			$outcount = $this->DBOO->SelectCount($sqlquery_str);
			if($outcount!=1){
				throw new Exception(USER_GUEST_CONTACT_NOTIN_DB);
			}		
		}
		return $out;
	}
	
		
	private function UpdateGuestInfo_NoChecks($ContactNumber,$Name,$Email,$AlternateContact,$Comment,$GUID){
		$update_set_array = array(GUEST_CONTACTNUMBER,GUEST_NAME,GUEST_EMAIL,GUEST_ALTERNATECONTACTNUMBER,GUEST_COMMENT);
		$update_set_QString=$this->DBOO->Update_Set_QString($update_set_array);
		$update_where_array = array(GUEST_UID,GUEST_HRID);
		$update_where_QString = $this->DBOO->Update_WhereEQAnd_QString($update_where_array);
		$sqlquery_str="UPDATE ".DBT_GUESTS." ".$update_set_QString." WHERE ".$update_where_QString;
		$bindvalarr=array($ContactNumber,$Name,$Email,$AlternateContact,$Comment,$GUID,$this->HRID);
		$affectedrows = $this->DBOO->Update($sqlquery_str, $bindvalarr);
		return $affectedrows;
	}	
	
	
}
?>
