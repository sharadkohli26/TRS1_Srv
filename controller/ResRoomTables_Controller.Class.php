<?php

class ResRoomTables{
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
	
	public function CreateNewRoomAction(){
		$KeyArr=array("HRID","UserID","RoomName");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$mRoomName = $this->mparams['RoomName'];
		$rrtobj = new ResRoomsTablesEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		return $rrtobj->CreateNewRoom($mRoomName);
	}
	
	public function ChangeRoomNameAction(){
		$KeyArr=array("HRID","UserID","OldRoomName","NewRoomName");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$mOldRoomName = $this->mparams['OldRoomName'];
		$mNewRoomName = $this->mparams['NewRoomName'];
		$rrtobj = new ResRoomsTablesEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		return $rrtobj->ChangeRoomName($mNewRoomName, $mOldRoomName);		
	}
	
	public function ActivateDeactivateRoomAction(){
		$KeyArr=array("HRID","UserID","RoomName","CurrentStatus");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$mRoomName = $this->mparams['RoomName'];
		$mCurrentStatus = $this->mparams['CurrentStatus'];
		$rrtobj = new ResRoomsTablesEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		return $rrtobj->ActivateDeactivateRoom_ForRoomName($mRoomName, $mCurrentStatus, TRUE);				
	}
	
	public function GetRoomDetails_ForStatusAction(){
		$KeyArr=array("HRID","UserID","CurrentStatus");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$mCurrentStatus = $this->mparams['CurrentStatus'];
		$rrtobj = new ResRoomsTablesEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		return $rrtobj->GetRoomDetails_ForGivenStatus($mCurrentStatus);	
	}
	
	public function GetRoomDetails_ForRoomNamesAction(){
		//RoomName is comma separated string of roomnames
		$KeyArr=array("HRID","UserID","RoomName");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$mRoomName = $this->mparams['RoomName'];
		$rrtobj = new ResRoomsTablesEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		return $rrtobj->GetRoomDetails_ForRoomNames($mRoomName, TRUE);
	}
	
	public function AddNewTableAction(){
		$KeyArr=array("HRID","UserID","RoomName","TableName","MinCapacity","MaxCapacity","OnlineAvailability");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$rrtobj = new ResRoomsTablesEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$mTableName = $this->mparams['TableName'];
		$mRoomName = $this->mparams['RoomName'];
		$mMinCapacity = $this->mparams['MinCapacity'];
		$mMaxCapacity = $this->mparams['MaxCapacity'];
		$mOnlineAvailability = $this->mparams['OnlineAvailability'];
		return $rrtobj->AddNewTable($mTableName, $mRoomName, $mMinCapacity, $mMaxCapacity, $mOnlineAvailability, TRUE);
	}
	
	public function ChangeTableNameAction(){
		$KeyArr=array("HRID","UserID","RoomName","OldTableName","NewTableName");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$rrtobj = new ResRoomsTablesEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$mRoomName = $this->mparams['RoomName'];
		$mOldTableName = $this->mparams['OldTableName'];
		$mNewTableName = $this->mparams['NewTableName'];
		return $rrtobj->ChangeTableName($mRoomName,$mOldTableName,$mNewTableName);
	}
	
	public function GetTableDetails_ForTableNamesAction(){
		//TableNamesStr is a csv string containing the tablenames
		$KeyArr=array("HRID","UserID","RoomName","TableName");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$rrtobj = new ResRoomsTablesEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$mRoomName = $this->mparams['RoomName'];
		$TableNameStr = $this->mparams['TableName'];
		return $rrtobj->GetTableDetails_ForTableNames($TableNameStr, TRUE, $mRoomName);
	}
	
	public function GetRoomTableDetails_ForRoomNamesAction(){
		//RoomTable functions return the room details and the details of all the tables in this room...
		$KeyArr=array("HRID","UserID","RoomName");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$rrtobj = new ResRoomsTablesEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		$mRoomNameStr = $this->mparams['RoomName'];
		return $rrtobj->GetRoomTableDetails_ForRoomNames($mRoomNameStr,TRUE);
	}
	
	public function GetRoomTableDetails_ForRoomStatusAction(){
		//will return room details and details of all the tables in the room with given room status
		$KeyArr=array("HRID","UserID","CurrentStatus");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$mCurrentStatus = $this->mparams['CurrentStatus'];
		$rrtobj = new ResRoomsTablesEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		return $rrtobj->GetRoomTableDetails_ForRoomStatus($mCurrentStatus, TRUE);
	}
	
	public function GetRoomTableDetails_ForActiveRoomsActiveTableAction(){
		//will return room details and details of all the tables in the room with given room status
		$KeyArr=array("HRID","UserID");
		$this->mparams=$this->ValidateInputs($this->mparams, $KeyArr);
		$rrtobj = new ResRoomsTablesEntry($this->mparams['HRID'],$this->mparams['UserID'],$this->mparams[DBV_DBOO]);
		return $rrtobj->GetRoomTableDetails_ForActiveRoomsActiveTable();
	}	
		
	
}

?>
