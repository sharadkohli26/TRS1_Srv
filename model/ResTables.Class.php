<?php

class ResTables{
	
	private $dbcon=NULL;
	private $DBOO;
	public $HRID;
	
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
	
	public function GetAllAvaialableTables(){
		
		//get list of all tables in the restaurant which are currently in restaurant list
		$selectarr=array(RT_TBID=>"TBID",RT_DISPLAYNAME=>"TableName",RT_MINCAPACITY=>"MinCapacity",RT_MAXCAPACITY=>"MaxCapacity");
		
		$selectstr = $this->DBOO->GetSelectColumn($selectarr);
		$mHRID=$this->DBOO->RealEscapeString($this->HRID);
		
		$sqlquery_str=$selectstr
		." FROM ".DBT_RESTABLES
		." WHERE ".RT_HRID."=".$mHRID
		." AND ".RT_STATUS."=".RT_ACTIVESTATUS;
		
		$SelectReturnArr=$this->DBOO->Select($sqlquery_str);
		$out = $SelectReturnArr;
		return $out;
	} 
	
	public function GetTableNames_FromTBID($mTBID){
		$where_in=$this->DBOO->SelectWhere_In_QString(RT_TBID, $mTBID);
		$mHRID=$this->DBOO->RealEscapeString($this->HRID);
		
		//get list of all tables in the restaurant which are currently in restaurant list
		$selectarr=array(RT_DISPLAYNAME=>"TableName");
		$selectstr =$this->DBOO->GetSelectColumn($selectarr);
		$sqlquery_str=$selectstr
		." FROM ".DBT_RESTABLES
		." WHERE ".RT_HRID."=".$mHRID
		." AND ".RT_STATUS."=".RT_ACTIVESTATUS
		." AND ".$where_in;
		//return $sqlquery_str;
		$SelectReturnArr=$this->DBOO->Select($sqlquery_str);
		$out=array();
		foreach ($SelectReturnArr as $row) {
			$out[]=$row['TableName'];
		}
		return $out;
	}
	
	public function GetTBID_FromTableNames($mTableNames){
		$where_in=$this->DBOO->SelectWhere_In_QString(RT_DISPLAYNAME, $mTableNames);
		$mHRID=$this->DBOO->RealEscapeString($this->HRID);
		
		$selectarr=array(RT_TBID=>"TBID");
		$selectstr =$this->DBOO->GetSelectColumn($selectarr);
		$sqlquery_str=$selectstr
		." FROM ".DBT_RESTABLES
		." WHERE ".RT_HRID."=".$mHRID
		." AND ".RT_STATUS."=".RT_ACTIVESTATUS
		." AND ".$where_in;
		$SelectReturnArr=$this->DBOO->Select($sqlquery_str);
		$out=array();
		foreach ($SelectReturnArr as $row) {
			$out[]=$row['TBID'];
		}
		return $out;
	}
	

}
?>
