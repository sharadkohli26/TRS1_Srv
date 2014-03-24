<?php

//error_reporting(E_ALL);
//error_reporting(0);
//ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);
ini_set('log_errors',TRUE);
ini_set('html_errors',FALSE);
//ini_set('error_log','/home/htdocs/webXX/html/error_log.txt');
ini_set('display_errors',FALSE);



//include the models
$userTimezone = 'Asia/Kolkata';//this is used everywhere
date_default_timezone_set($userTimezone);

//require_once("./Inc/dbfunc.inc.php");
require_once './Inc/CommonFunctions.Class.php';
require_once './Inc/CommonFunctionsDateTime.Class.php';
require_once './model/allconstants.php';
require_once './Inc/MyErrorHandeler.Class.php';
require_once './Inc/DBOperations.Class.php';

require_once "./model/CurrentBookingEntry.Class.php";
require_once "./model/WaitingListEntry.Class.php";
require_once "./model/AdvanceBookingEntry.Class.php";
require_once "./model/GuestsEntry.Class.php";
require_once "./model/ResTables.Class.php";
require_once "./model/ResRoomsTables.Class.php";

$old_error_handler = set_error_handler('MyErrorHandeler::userErrorHandler');



try {
	//get all of the parameters in REQUEST
	//$params=$_REQUEST;	
	
	//get the controller and format it correctly so the first 
   //get all the post variables...
   $params=array();
   foreach ($_POST as $key => $value) {
   		//echo "$key::$value<br>";
   		if(strcmp($key, "controller")==0){
   			$controller_str = $value;
			continue;
   		}
		if(strcmp($key, "action")==0){
			$action_str = $value."Action";
			continue;
		}
       $params[$key]=$value;
   }
   //var_dump($_POST);
   	if(!isset($controller_str) or !isset($action_str) ){
		MyErrorHandeler::UserError("Missing Controller or Action.",debug_backtrace(), $_POST);
		throw new Exception('Missing Controller or Action.');
	} 
   
   //check if the controller exists. if not, throw an exception
   if( file_exists("./controller/{$controller_str}_Controller.Class.php") ) {
      include_once "./controller/{$controller_str}_Controller.Class.php";
   } else {
   		MyErrorHandeler::UserError("Controller is invalid.",debug_backtrace(), $_POST);
		throw new Exception('Controller is invalid.');
   }
               
   //check if the action exists in the controller. if not, throw an exception.
   if( method_exists($controller_str, $action_str) === false ) {
		MyErrorHandeler::UserError("Action is invalid.",debug_backtrace(), $_POST);
      	throw new Exception('Action is invalid.');
   }     	
   //create a new instance of the controller, and pass
   //it the parameters from the request
   
   //launch the database connection and store it into params
	$params[DBV_DBOO] = NULL;
   	$controller = new $controller_str($params);
   	$result = $controller->$action_str();//if successfull entry then CBID for this booking 
	
} catch (Exception $e) {
	//echo($e->getMessage());
	$result = array();
   	$result[RESULT_STATUS] = FALSE;
   	$result[RESULT_PAYLOAD] = array(ERRPAYLOAD_MESSAGE=>$e->getMessage());
}

echo json_encode($result);

?> 
