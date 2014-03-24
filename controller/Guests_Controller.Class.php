<?php

class Guests{
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

	public function NewGuestAction(){
		$KeyArr=array("HRID","UserID","GID","GuestName","GuestContact","AlternateContact","GuestEmail","GuestComment");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$mguid = $this->mparams['GID'];
		$mname=$this->mparams['GuestName'];
		$mcontactnumber=$this->mparams['GuestContact'];
		$malternatecontact=$this->mparams['AlternateContact'];
		$memail=$this->mparams['GuestEmail'];
		$mcomment=$this->mparams['GuestComment'];
		
		$guestobj = new GuestsEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		return $guestobj->NewGuest($mguid, $mcontactnumber, $mname, $memail, $malternatecontact, $mcomment);
	}
	
	public function GetGuestDetails_ForGUIDAction(){
		$KeyArr=array("HRID","UserID","GID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$guestobj = new GuestsEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$mguid = $this->mparams['GID'];
		return $guestobj->GetAllDetails_ForGUID($mguid, TRUE);
	}
	
	public function GetGuestDetails_ForContactAction(){
		$KeyArr=array("HRID","UserID","GuestContact");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$guestobj = new GuestsEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$mcontactnumber=$this->mparams['GuestContact'];
		return $guestobj->GetAllDetails_ForContactNumber($mcontactnumber, TRUE);
	}

	public function GetGuestDetails_ForNameAction(){
		$KeyArr=array("HRID","UserID","GuestName");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$guestobj = new GuestsEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$mname=$this->mparams['GuestName'];
		return $guestobj->GetAllDetails_ForName($mname, TRUE);
	}
	
	public function GetGuestStats_ForGUIDAction(){
		$KeyArr=array("HRID","UserID","GID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$guestobj = new GuestsEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$mguid = $this->mparams['GID'];
		return $guestobj->GetAllStats_ForGUID($mguid, TRUE);
	}
}
?>
