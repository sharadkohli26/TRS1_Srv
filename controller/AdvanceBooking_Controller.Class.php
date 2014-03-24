<?php
//called when somebody wants to add, edit or delete a advance booking
class AdvanceBooking
{
	private $mparams;
	//	
	
	public function __construct($params){
		$this->mparams=$params;
	}

	private function ValidateInputs($params,$KeyArr){
		foreach ($KeyArr as $key) {
			if(!isset($params[$key]))
				$params[$key]="";
		}
		return $params;
	}	
	
	public function NewAdvanceBookingAction(){
		$KeyArr=array("HRID","UserID","ForDateTime","BookingMethod","Status","ExpectedDuration","GuestNum","GID","GuestName","GuestContact","GuestEmail","GuestComment","Notes");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$abentry = new AdvanceBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		//($mForDateTime,$mBookingMehtod,$mStatus,$mTabStatus,$mExpectedDuration,$mGuestNum,$mGuestNames,$mGuestContact,$mGuestEmail,$mGuestComment
		$mForDateTime=$this->mparams['ForDateTime'];
		$mBookingMethod=$this->mparams['BookingMethod'];
		$mStatus=$this->mparams['Status'];
		//$mTabStatus=$this->mparams['TabStatus'];
		$mExpectedDuration=$this->mparams['ExpD'];
		$mGuestNum=$this->mparams['GuestNum'];
		$mGUID=$this->mparams['GID'];
		$mGuestNames=$this->mparams['GuestName'];
		$mGuestContact=$this->mparams['GuestContact'];
		$mGuestEmail=$this->mparams['GuestEmail'];
		$mNotes=$this->mparams['Notes'];
		
		return $abentry->NewAdvanceBookingByHRUser($mForDateTime, $mBookingMethod, $mStatus, $mExpectedDuration, $mGuestNum, $mGUID,$mGuestNames, $mGuestContact, $mGuestEmail, $mNotes);
	}
	
	public function GetAB_All_BetweenDateAction(){
		$KeyArr=array("HRID","UserID","FromDate","ToDate");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$abentry = new AdvanceBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$startdate=$this->mparams['FromDate'];
		$enddate=$this->mparams['ToDate'];
		//echo $startdate."<br>".$enddate."<br>";
		return $abentry->Get_All_AB_BetweenDate($startdate, $enddate);
	}
	
	public function GetAB_Tentative_BetweenDateAction(){
		$KeyArr=array("HRID","UserID","FromDate","ToDate");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$abentry = new AdvanceBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$startdate=$this->mparams['FromDate'];
		$enddate=$this->mparams['ToDate'];
		//echo $startdate."<br>".$enddate."<br>";
		return $abentry->Get_Tentative_AB_BetweenDate($startdate, $enddate);
	}
	
	public function GetAB_Confirmed_BetweenDateAction(){
		$KeyArr=array("HRID","UserID","FromDate","ToDate");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$abentry = new AdvanceBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$startdate=$this->mparams['FromDate'];
		$enddate=$this->mparams['ToDate'];
		//echo $startdate."<br>".$enddate."<br>";
		return $abentry->Get_Confirmed_AB_BetweenDate($startdate, $enddate);
	}	

	public function GetAB_UserCancelled_BetweenDateAction(){
		$KeyArr=array("HRID","UserID","FromDate","ToDate");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$abentry = new AdvanceBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$startdate=$this->mparams['FromDate'];
		$enddate=$this->mparams['ToDate'];
		//echo $startdate."<br>".$enddate."<br>";
		return $abentry->Get_UserCancelledAB_BetweenDate($startdate, $enddate);
	}	
	
	public function GetAB_NoShow_BetweenDateAction(){
		$KeyArr=array("HRID","UserID","FromDate","ToDate");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$abentry = new AdvanceBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$startdate=$this->mparams['FromDate'];
		$enddate=$this->mparams['ToDate'];
		//echo $startdate."<br>".$enddate."<br>";
		return $abentry->Get_NoShow_AB_BetweenDate($startdate, $enddate);
	}
	
	public function GetAB_ConvertedCurrent_BetweenDateAction(){
		$KeyArr=array("HRID","UserID","FromDate","ToDate");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$abentry = new AdvanceBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$startdate=$this->mparams['FromDate'];
		$enddate=$this->mparams['ToDate'];
		//echo $startdate."<br>".$enddate."<br>";
		return $abentry->Get_ConvertedCurrent_AB_BetweenDate($startdate, $enddate);
	}		
	
	public function GetAB_AllFailed_BetweenDateAction(){
		$KeyArr=array("HRID","UserID","FromDate","ToDate");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$abentry = new AdvanceBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$startdate=$this->mparams['FromDate'];
		$enddate=$this->mparams['ToDate'];
		//echo $startdate."<br>".$enddate."<br>";
		return $abentry->Get_AllFailed_AB_BetweenDate($startdate, $enddate);
	}	
	
	public function GetDetails_ForABIDAction(){
		$KeyArr=array("HRID","UserID","ABID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$abentry = new AdvanceBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$mABID=$this->mparams['ABID'];
		return $abentry->GetAllDetails_ForABID($mABID);
	}

	public function TablesAvailabilityForAction(){
		$KeyArr=array("HRID","UserID","ForDateTime");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$abentry = new AdvanceBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$mForDateTime=$this->mparams['ForDateTime'];
		return $abentry->Get_TablesFor_AB($mForDateTime,TRUE);
	}
	
	public function AssignTableToABAction(){
		$KeyArr=array("HRID","UserID","ABID","TBIDs","RoomName");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		//this is also add tables to advance booking
		$abentry = new AdvanceBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$mABID=$this->mparams['ABID'];
		$mTBID=$this->mparams['TBIDs'];
		$mRoomName=$this->mparams['RoomName'];
		return $abentry->AssignTableToAB($mABID,$mTBID,$mRoomName);
	}
	
	public function RemoveTableFromABAction(){
		$KeyArr=array("HRID","UserID","ABID","TBIDs","RoomName");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		//mode is Assign or Remove
		$abentry = new AdvanceBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$mABID=$this->mparams['ABID'];
		$mTBID=$this->mparams['TBIDs'];
		$mRoomName=$this->mparams['RoomName'];
		return $abentry->RemoveTableFromAB($mABID,$mTBID,$mRoomName);
	}	
	
	public function UpdateStatusABAction(){
		$KeyArr=array("HRID","UserID","ABID","Status");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$abentry = new AdvanceBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$mABID=$this->mparams['ABID'];
		$mstatus=$this->mparams['Status'];
		return $abentry->UpdateStatusAB($mABID, $mstatus);	
	}
	
}
?>
