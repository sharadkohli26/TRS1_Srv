<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once './Inc/CommonFunctionsDateTime.Class.php';

echo "Php version".phpversion();
echo "<br>".phpinfo();
die();
$str = "Hello,world,It's a ,beautiful day.";
print_r (explode("-",$str));

$arr1 = array(1,2,3,4,5);
$arr1_2 = array();
$arr1_2[] = 1;
$arr1_2[]=3;
var_dump($arr1);
echo "<br>";
var_dump($arr1_2);
echo "<br><br>";


$arr1=array(1,2,3,4,5);
foreach ($arr1 as $key=>$val){
	$arr2[$key]=time();
	echo $key;
}
print_r($arr2);
$mcurrdate1=date('Y-m-d');
$mcurrdate2=Date('Y-m-d', strtotime("+1 days"));
//$mcurrdate2=date('Y-m-d');date_add($mcurrdate2,date_interval_create_from_date_string("1 day"));
echo $mcurrdate1."--".$mcurrdate2."<br>";

$mcurrdateobj1=new DateTime($mcurrdate1);
$mcurrdateobj2=new DateTime($mcurrdate2);
var_dump($mcurrdateobj1<$mcurrdateobj2);

require_once './Inc/CommonFunctions.Class.php';
echo CommonFunctions::GetDateTime_YMDHIS("1 Aug 2013");


$arr1 = array("sharad","dfdsfsd","2323");
$arr2 = array(3,4,5,6);
print_r(array_merge($arr1,$arr2));
echo "<br>";

$arr3=array("Booking for"=>2324,"*"=>5432432);
$arr3[]=array("Booking for"=>98789);
print_r($arr3);

$f=TRUE;
echo "asdas".($f and $ddd)."adas<br>";
$out123=array();
$out123['var1']='shahahahh';
$out123['arr3']=$arr1;
print_r($out123);
echo "<br><br>";
$out=array();
foreach ($arr1 as $key=>$val){
	$out[]=&$arr1[$key];
}
//$out22=&$arr1;
var_dump($out);
echo "<br><br>";
var_dump($arr1);

echo "***********<br><br>";
$arr1 = array("SHARAD1","SHARAD2","SHARAD3");
$arr2 =array("rocks1","rocks2","rocks3");
$arr3=array($arr1,$arr2);
foreach ($arr3 as $inarr) {
	$out=array();
	foreach ($inarr as $key => $value) {
		$out[]=&$inarr[$key];
	}
	echo "<br>out array<br>";
	var_dump($out);
	echo "<br>inarr array<br>";
	var_dump($inarr);
}
echo "***********<br><br>************<br>";
$arr1 = array("LEFT OUTER JOIN"=>"AB.TAB","ON"=>array("CBID"=>"shha","HRID"=>"NOT NULL"));
$arr2=array($arr1);
var_dump($arr2);

dummyf("jsdlkfjl");
dummyf(NULL);
$test1="";
echo "<br>dsd  ".empty($test1)." fdsfd<br>";

$test2=NULL;
echo "isset ".isset($test2)." fsdjf<br>";
dummyf();

function dummyf ($marg){
	echo "<br>*****".$marg."***<br>";
}

echo "*****************************************************<br>";
$arr1 = array();
$arr1 = array_fill(0, 5, "fkjsdhjfkjsdhfkjdshfkj");
var_dump($arr1);

$mdate = '2013-08-18 23:00:00';
$newdate=date('Y-m-d H:i:s',strtotime($mdate)+60*30);
echo "<br>CurrDtae ".$mdate."<br>NewDate ".$newdate."<br>";

$seconds = time();
$rounded_seconds = round($seconds / (15 * 60)) * (15 * 60);

echo "Original: " . date('Y-m-d H:i', $seconds) . "\n";
echo "Rounded: " . date('Y-m-d H:i', $rounded_seconds) . "\n";

echo "<br>".CommonFunctionsDateTime::GetDT_YMD_DayStart(NULL);

?>