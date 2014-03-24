<?php
//called when somebody wants to add, edit or delete a booking
class CurrentBooking
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
	
	/**
	 * Reads the values from $mparam and initialises a new CurrentBookingEntry
	 * [Requiured]HRID is needed, function wont work without it
	 * [Required]UserID will also be needed
	 * [Required] TBIDs, the TBIDs are a comma separated string. each TBID should exist in the Hotels database, and further each TBID should be available for booking now. Otherwise exception thrown.
	 * [Required]ExpD, ia the expected duration. 
	 * [Required] GuestNum
	 *
	 * [Optional]WaitID, if blank then default value is automatically assigned otherwise the value is checked first (if its a timestamp) and then assigned. This will automatically try to first close the waiting and  then proceed. If waiting already closed then no problem. If not a valid timestamp then exception is thrown.
	 * [Optional]AdvID, if blank then default value is automatically assigned otherwise the value is checked first (if its a timestamp) and then assigned. This will automatically try to first close the  advance and  then proceed. If advance already closed then no problem. If not a valid timestamp then exception is thrown.
	 * [Optional]GuestName,GuestContact,GuestEmail,GuestComment
	 * TODO:: Guest num can be blank, if somebody dopesnt want to fill thne okay.Currentlyt only not null
	 * @return an associative array.
	 * json_out::[{"UserID":"SK","CBID":"1376002737","BookingStatus":"Closed","BookingStart":"2013-08-09 04:28:57","BookingEnd":"2013-08-09 04:46:41","GuestNum":"5","GuestNames":null,"GuestContactNos":null,"GuestEmail":null,"Comment":null,"TBID":"TB1,TB3","TableEndTime":"2013-08-09 04:46:41,2013-08-09 04:29:15","WaitStart":null,"WaitEnd":null,"AdvanceStatus":"","AdvanceBookingMethod":null,"AdvanceBookedOn":null,"AdvanceBookedFor":null}]
	 * TBID in json out is a csv string of various TBIDs under this booking  
	 */
	public function NewCurrentBookingAction(){
		$KeyArr=array("HRID","UserID","RoomName","TBIDs","ExpD","WaitID","AdvID","GuestNum","GID","GuestName","GuestContact","GuestEmail","GuestComment","Notes");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		//TODO:check if size of params is correct as per this method
		//$cbentry->HRID=$this->mparams['HRID'];
		 
		//TODO check if this USER is allowed to book
		
		$mRoomName=$this->mparams['RoomName'];
		$mTableNamesToBook=$this->mparams['TBIDs'];
		$mExpDuration =$this->mparams['ExpD'];
		$mWaitID=$this->mparams['WaitID'];
		$AmdvanceID=$this->mparams['AdvID'];

		$mGuestNum=$this->mparams['GuestNum'];
		$mgid = $this->mparams['GID'];
		$mNote = $this->mparams['Notes'];
		$mGuestName=$this->mparams['GuestName'];
		$mGuestContact=$this->mparams['GuestContact'];
		$mGuestEmail=$this->mparams['GuestEmail'];
		//$cbentry->GuestComment=$this->mparams['GuestComment'];

		return $cbentry->InsertCurrentBooking($mRoomName,$mTableNamesToBook, $mExpDuration, $mgid, $mGuestNum, $mGuestName, $mGuestContact, $mGuestEmail, $mNote, $mWaitID, $AmdvanceID);
		//TODO: handle successful entry or no entry		
	}
	
	public function UpdateGuestInfo_CBAction(){
		$KeyArr=array("HRID","UserID","CBID","GuestNum","GuestName","GuestContact","GuestEmail","GuestComment");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		 $cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		 
		$mcbid=$this->mparams['CBID'];
		$mguestnum=$this->mparams['GuestNum'];
		$mguestnames=$this->mparams['GuestName'];
		$mguestcontactnos=$this->mparams['GuestContact'];
		$mguestemail=$this->mparams['GuestEmail'];
		$mguestcomment=$this->mparams['GuestComment'];
		return $cbentry->UpdateGuestInfo_CB($mcbid,$mguestnum, $mguestnames, $mguestcontactnos, $mguestemail, $mguestcomment);
	}
	
	/**
	 * Reads the value from mparams and closes the current booking and all TBIDS associated with it
	 * [Required]HRID
	 * [Required]Userid, currentlly optional--TODO::make UserID mandatory
	 * [Required]CBID, the id of the booking to close. It is checked tht it is a valid timestamp and if so then assigns it. NOTE: The function that assigns CBID doesnt access database, only checks if it is a valid timestamp.
	 * Exception is thrown if CBID to close is already closed or doesnt exist, If the TBIDs are already closed then no problem. 
	 * @return associative array
	 * jsonout same as NewCurrentBookingAction 
	 * json_out:: [{"UserID":"SK","CBID":"1375899490","BookingStatus":"Closed","BookingStart":"2013-08-07 23:48:10","BookingEnd":"2013-08-07 23:52:50","GuestNum":"3","GuestNames":"Sharad Kohli","GuestContactNos":"987654323","GuestEmail":"ksharad@iitk.ac.in","Comment":"Looks Good","TBID":"TB2,TB3","WaitStart":null,"WaitEnd":null,"AdvanceStatus":"","AdvanceBookingMethod":null,"AdvanceBookedOn":null,"AdvanceBookedFor":null}] 
	 */
	public function CloseCurrentBookingAction(){
		$KeyArr=array("HRID","UserID","CBID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		//TODO::this will be a method which accepts post rquests only!!
		 $cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		 
		$mCBID=$this->mparams['CBID'];
		return $cbentry->CloseCurrentBooking($mCBID);
	}
	
	/**
	 * For a given booking closes and frees the tables supplied in the comma separated list supplied in mparams['TBIDs'].
	 * [Required] HRID
	 * [Required] CBID
	 * [Required] TBIDs
	 * @return same as NewCurrentBookingAction 
	 * TODO: IF ALL TABLES IN A BOOKING ARE CLOSED, Doesnt close the booking
	 */
	public function CloseCBTableAction(){
		$KeyArr=array("HRID","UserID","CBID","RoomName","TBIDs");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		//TODO::this will be a method which accepts post rquests only!!
		 $cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		//$cbentry->HRID=$this->mparams['HRID'];
		 
		$mCBID=$this->mparams['CBID'];
		$mTBID=$this->mparams['TBIDs'];
		$mRoomName=$this->mparams['RoomName'];
		return $cbentry->CloseCurrentBookingTable($mCBID,$mRoomName,$mTBID);
	}
	
	
	/**
	 * Closes all the current bookings
	 * [Required]HRID
	 * @throws Exception
	 * @return array carrying fields 'BookingsClosed' and 'TablesClosed' telling number of enteries closed
	 * json out:: {"BookingsClosed":1,"TablesClosed":2} 
	 */
	public function CloseAllCurrentAction(){
		$KeyArr=array("HRID","UserID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		//TODO::this will be a method which accepts post rquests only!!
		 $cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		 
		return $cbentry->CloseAllCurrent();
	}
	
	
	/**
	 * Returns the array of current live bookings.
	 * [Required]HRID
	 * [Required]UserID	
	 * @return an associative array,empty array if no bookings, where each row is a booking, the array index are:
	 * UserID,CBID,BookingStatus,BookingStart,BookingEnd,GuestNum,GuestNames,GuestContactNos,GuestEmail,Comment,WaitStart,WaitEnd,TBID,AdvanceStatus,AdvanceBookingMethod,AdvanceBookedOn,AdvanceBookedFor
	 * BookingStatus is Live or Closed
	 * If booking status is live then BookingEnd would be some default value otherwise would represent the time on which booking was closed
	 * If the current booking was a waiting then waitstart and waitend would be valid time otherwise empty
	 * if current was advance booking then booking fields will valid values otherwise empty
	 * TBID is a Comma separated string of the TBIDs under this booking
	 * 
	 * json out same as NewCurrentBookingAction for each booking
	 */
	public function GetCurrent_LiveBookingsAction(){
		$KeyArr=array("HRID","UserID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		//TODO:should we put a limit to number of enteries to return, just to make it fast...
		 $cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		 
		return $cbentry->GetLiveBookings();
	}
	
	/**
	 * Returns the array of current closed bookings.
	 * [Required]HRID
	 * [Required]UserID	
	 * @return same as GetCurrentLiveBookingsAction
	 */
	public function GetCurrent_ClosedBookingsAction(){
		$KeyArr=array("HRID","UserID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		//TODO:should we put a limit to number of enteries to return, just to make it fast...
		 $cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		 
		return $cbentry->GetCurrentClosedBookings();
	}
	
	public function GetCurrent_LiveClosedBookingsAction(){
		$KeyArr=array("HRID","UserID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		//returns the bookings that were closed by user Today
		//TODO:should we put a limit to number of enteries to return, just to make it fast...
		 $cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		 
		return $cbentry->GetCurrentLiveClosedBookings();
	}
	
	public function GetCurrent_AutoClosedBookingsAction(){
		$KeyArr=array("HRID","UserID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		//returns the bookings that were closed auto (i.e all bookings closed at once)
		//TODO:should we put a limit to number of enteries to return, just to make it fast...
		 $cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		 
		return $cbentry->GetCurrentAutoClosedBookings();
	}	
	
	/**
	 * Returns the array of all bookings between two dates (both inclusive).
	 * [Required]HRID
	 * [Required]UserID	
	 * [Required]startdate(possibly as DD-MMM-YYYY)
	 * [Required]enddate
	 * @return an associative array, same as GetCurrentLiveBookingsAction
	 */
	public function GetCurrent_AllBookingsAction(){
		$KeyArr=array("HRID","UserID","startdate","enddate");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		//TODO:should we put a limit to number of enteries to return, just to make it fast...
		 $cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		 
		$startdate=$this->mparams['startdate'];
		$enddate=$this->mparams['enddate'];
		return $cbentry->GetAllBookings($startdate,$enddate);
	}
	
	/**
	 * Returns the array of currently available tables.
	 * Currently available tables are those whioch are currently unoccupied and have their RT_Status as available
	 * [Required]HRID
	 * [Required]UserID	
	 * @return an associative array, with indices TBID,Capacity
	 * json out:: [{"TBID":"TB1","TableName":"Table-1","MinCapacity":"2","MaxCapacity":"3"},{"TBID":"TB5","TableName":"Table-5","MinCapacity":"3","MaxCapacity":"5"}] 
	 */
	public function GetCurrentAvailableTablesAction(){
		$KeyArr=array("HRID","UserID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		 $cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		 
		return $cbentry->GetCurrentAvailableTables();
	}
	
	public function GetDetails_ForCBIDAction(){
		$KeyArr=array("HRID","UserID","CBID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		 $cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		 
		return $cbentry->GetAllDetails_ForCBID($this->mparams['CBID']);
	}
	
	public function AddCBTableAction(){
		$KeyArr=array("HRID","UserID","CBID","RoomName","TBIDs");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		 $cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		 
		$mCBID=$this->mparams['CBID'];
		$mTBID=$this->mparams['TBIDs'];
		$mRoomName=$this->mparams['RoomName'];
		return $cbentry->AddCBTable($mCBID,$mRoomName,$mTBID);
	}
	
	public function UpdateExpectedDurationAction(){
		$KeyArr=array("HRID","UserID","CBID","ExpD");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		 $cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		 
		$mCBID=$this->mparams['CBID'];
		$mExpectedDuration=$this->mparams['ExpD'];
		return $cbentry->UpdateExpectedDuration_ForCBID($mCBID, $mExpectedDuration);
	}

	public function UpdateCurrentReservationAction(){
		$KeyArr=array("HRID","UserID","CBID","ExpD","GuestNum","RemoveTBID_RoomName","RemoveTBID","AddTBID_RoomName","AddTBID","Notes");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		 $cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		 
		$mCBID=$this->mparams['CBID'];
		$mExpectedDuration=$this->mparams['ExpD'];
		$mRemoveFrom_RoomName=$this->mparams['RemoveTBID_RoomName'];
		$mOccupiedTableNamesToRemove = $this->mparams['RemoveTBID'];
		$mAddFrom_RoomName = $this->mparams['AddTBID_RoomName'];
		$mFreeTableNamesToAdd = $this->mparams['AddTBID'];
		$mGuestNum = $this->mparams['GuestNum'];
		$mNotes=$this->mparams['Notes'];
		
		
		return $cbentry->UpdateBookingDetails_ForCBID($mCBID, $mRemoveFrom_RoomName,$mOccupiedTableNamesToRemove, $mAddFrom_RoomName,$mFreeTableNamesToAdd, $mExpectedDuration, $mGuestNum, $mNotes);		
	}
	
	public function GetBookingsHistory_ForGUIDAction(){
		$KeyArr=array("HRID","UserID","GID","Start","End","Type");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$cbentry = new CurrentBookingEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$mguid=$this->mparams['GID'];
		$mstart = $this->mparams['Start'];
		$mend = $this->mparams['End'];
		$mType = $this->mparams['Type'];
		return $cbentry->GetBookings_ForGUID($mguid, TRUE, $mstart, $mend,$mType);
	}
	
}
?> 