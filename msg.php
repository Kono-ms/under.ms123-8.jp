<?php

// session_start();

define('DB_HOST', 'mysql203.xbiz.ne.jp');
define('DB_USERNAME', 'ms123_mbase');
define('DB_PASSWD', 'x7WYr3a9');
define('DB_DBNAME', 'ms123_mbase');


set_time_limit(7200);


//メイン処理
Main();

//=========================================================================================================
//名前 Main関数
//機能 プログラムのメイン関数
//引数 なし
//戻値 なし
//=========================================================================================================
function Main()
{

	if(!isset($_POST['mid1']) || $_POST['mid1']==""){
		$mid1=mysqli_real_escape_string(ConnDB(),$_GET['mid1'] ?? '');
		$mid2=mysqli_real_escape_string(ConnDB(),$_GET['mid2'] ?? '');
		$etc02=mysqli_real_escape_string(ConnDB(),$_GET['etc02'] ?? '');
		$etc03=mysqli_real_escape_string(ConnDB(),$_GET['etc03'] ?? '');
		$newdate=mysqli_real_escape_string(ConnDB(),$_GET['newdate'] ?? '');
	} else {
		$mid1=mysqli_real_escape_string(ConnDB(),$_POST['mid1'] ?? '');
		$mid2=mysqli_real_escape_string(ConnDB(),$_POST['mid2'] ?? '');
		$etc02=mysqli_real_escape_string(ConnDB(),$_POST['etc02'] ?? '');
		$etc03=mysqli_real_escape_string(ConnDB(),$_POST['etc03'] ?? '');
		$newdate=mysqli_real_escape_string(ConnDB(),$_POST['newdate'] ?? '');
	}


	
	
	$StrSQL="SELECT ID FROM DAT_MCONTACT where MID='".$mid1."' and MIDT='".$mid2."' and ETC02='".$etc02."' and ifnull(ETC03,'')='".$etc03."' ";
	$StrSQL.=" AND STR_TO_DATE(CASE WHEN ifnull(EDITDATE,'')='' THEN NEWDATE ELSE EDITDATE END,'%Y/%m/%d %H:%i:%s') > STR_TO_DATE('$newdate','%Y/%m/%d %H:%i:%s') order by ID desc;";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$cnt=mysqli_num_rows($rs);
	
	if($cnt==""){
		$cnt="0";
	}


	print $cnt;

	return $function_ret;
} 

//=========================================================================================================
//名前 DB初期化
//機能 DBとの接続を確立する
//引数 なし
//戻値 $function_ret
//=========================================================================================================
function ConnDB()
{
	static $conn = null;
	if ($conn === null) {
		$conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWD, DB_DBNAME);
	}
	return $conn;
} 

?>
