<?php

class WaitingList{
	private $mparams;

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
	 * Adds a new waiting list entry.
	 * [Required]HRID
	 * [Required]UserID
	 * [Required]ExpD
	 * [Optional]AdvID
	 * [Optional]GuestNum
	 * [Optional]GuestContact
	 * [Optional]GuestEmail
	 * [Optional]GuestComment
	 * 
	 * 	Returns the entry added. An associative array, with indices as
	 * Status,WaitID,WaitStart,ExpectedDuration,GuestNum,GuestName,GuestContact,GuestEmail,GuestComment
	 * Status if successful entry then Live\
	 * WaitID unique identifier
	 * TODO: currently upon failue it throws exception, should that be handeled
	 * TODO: this data we are returniong, is already with the user. IS THERE  a  need to return it?
	 * 
	 */
	
	public function AddTOWaitingListAction(){
		$KeyArr=array("HRID","UserID","ExpD","AdvID","GID","GuestNum","GuestName","GuestContact","GuestEmail","GuestComment","Notes");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);	
		$wlentry = new WaitingListEntry($this->mparams['HRID'],$this->mparams[DBV_DBOO]);
		//TODO:check if size of params is correct as per this method
		$wlentry->UserID=$this->mparams['UserID'];
		//TODO check if this USER is allowed to book
		$mExpD =$this->mparams['ExpD'];
		$mAdvID=$this->mparams['AdvID'];
		$mGuestNum=$this->mparams['GuestNum'];
		$mGuestName=$this->mparams['GuestName'];
		$mGuestContact=$this->mparams['GuestContact'];
		$mGuestEmail=$this->mparams['GuestEmail'];
		$wlentry->GuestComment=$this->mparams['GuestComment'];
		$mgid = $this->mparams['GID'];
		$mNote = $this->mparams['Notes'];
		return $wlentry->AddToWaitingList($mExpD, $mGuestNum, $mAdvID, $mGuestName, $mGuestContact, $mGuestEmail, $mgid, $mNote);
		//return $wlentry->AddToWaitingList($mgid,$mNote);
	}	
	
	
	/**
	 * To close a waiting list entry that did not convert to successful booking
	 * [Required]HRID
	 * [Required]UserID
	 * [Required]WaitID
	 * @return true if successfully closed, false otherwise
	 */
	public function CloseWaitingListAction(){
		$KeyArr=array("HRID","UserID","WaitID");
		$temp = array("Method"=>__METHOD__,"HRID"=>$this->mparams['HRID'],"WaitID"=>$this->mparams['WaitID']);
		//MyErrorHandeler::SimpleLogger($temp);
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$wlentry = new WaitingListEntry($this->mparams['HRID'],$this->mparams[DBV_DBOO]);
		$wlentry->UserID=$this->mparams['UserID'];
		//$wlentry->WaitID=$wlentry->SetWaitID($this->mparams['WaitID']);
		//$wlentry->status=$wlentry->SetStatus($this->mparams['Status']);
		return $wlentry->CloseWL_NotConvCurrent($this->mparams['WaitID']);
	}
	
	/**
	 * To close all the waiting list enteries
	 * [Required]HRID
	 * [Required]UserID
	 * @return true if closed, else false if already closed or not able to closed
	 * TODO: correct return type
	 */
	public function CloseAllWaitingListAction(){
		$KeyArr=array("HRID","UserID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$wlentry = new WaitingListEntry($this->mparams['HRID'],$this->mparams[DBV_DBOO]);
		$wlentry->UserID=$this->mparams['UserID'];
		//$wlentry->WaitID=$wlentry->SetWaitID($this->mparams['WaitID']);
		//$wlentry->status=$wlentry->SetStatus($this->mparams['Status']);
		return $wlentry->CloseWL_AutoClosed();
	}
	
	/**
	 * Returns the list of current live waiting list
	 * [Required]HRID
	 * [Required]UserID
	 * @return an associative array of current waiting list, index as
	 * Status,WaitStart,WaitEnd,ExpectedWaiting,GuestNum,GuestNames,GuestContactNos,GuestEmail,Comment
	 * Staus is Waiting,ClosedToCurrent or Closed(i.e not converted to current)
	 * WaitEnd is the end time of a waiting, if the waiting is still live then empty
	 * ExpectedWaiting is the waiting time inserted initially
	 */
	public function GetWL_LiveAction(){
		$KeyArr=array("HRID","UserID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$wlentry = new WaitingListEntry($this->mparams['HRID'],$this->mparams[DBV_DBOO]);
		$wlentry->UserID=$this->mparams['UserID'];
		return $wlentry->GetCurrentLiveWaitingList();
	}
	
	public function GetWL_ClosedAction(){
		$KeyArr=array("HRID","UserID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$wlentry = new WaitingListEntry($this->mparams['HRID'],$this->mparams[DBV_DBOO]);
		$wlentry->UserID=$this->mparams['UserID'];
		return $wlentry->GetCurrentClosedWaitingList();
	}
	
	/**
	 * Returns the waiting list enteries between two dates(both inclusive)
	 * [Required]HRID
	 * [Required]UserID
	 * [Required]startdate (if possible, then in the format DD-MMM-YYYY)
	 * [Required]enddate
	 * @return an associative array of all the waiting list enteries between two dates. Format same as above
	 */
	public function GetWL_AllAction(){
		$KeyArr=array("HRID","UserID","startdate","enddate");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$wlentry = new WaitingListEntry($this->mparams['HRID'],$this->mparams[DBV_DBOO]);
		$wlentry->UserID=$this->mparams['UserID'];
		return $wlentry->GetAllWaitingList($this->mparams['startdate'], $this->mparams['enddate']);
	}
	
	public function GetDetails_ForWaitIDAction(){
		$KeyArr=array("HRID","UserID","ExpD","WaitID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$wlentry = new WaitingListEntry($this->mparams['HRID'],$this->mparams[DBV_DBOO]);
		$wlentry->UserID=$this->mparams['UserID'];
		return $wlentry->GetAllDetails_ForWaitID($this->mparams['WaitID']);
	}
	
	public function UpdateWaitingListEntryAction(){
		$KeyArr=array("HRID","UserID","WaitID","ExpD","GuestNum","Notes");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$wlentry = new WaitingListEntry($this->mparams['HRID'],$this->mparams[DBV_DBOO]);
		$wlentry->UserID=$this->mparams['UserID'];
		$mWaitID=$this->mparams['WaitID'];
		$mExpectedWaitingTime=$this->mparams['ExpD'];
		$mGuestNum = $this->mparams['GuestNum'];
		$mNotes=$this->mparams['Notes'];
		return $wlentry->UpdateBookingDetails_ForWaitID($mWaitID, $mGuestNum, $mExpectedWaitingTime, $mNotes);		
	}
	
	
}

?>
