<?php


require "./config.php";
require "./base.php";


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

	eval(globals());

$pass="test";

	$StrSQL="SELECT * FROM DAT_M1 ";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	while ($item = mysqli_fetch_assoc($rs)) {
		$StrSQL2=" UPDATE DAT_M1 SET ";
		$StrSQL2.=" PASS = '".pwd_hash($pass)."'";
		$StrSQL2.=" WHERE EMAIL = '".$item["EMAIL"]."'";
		if (!(mysqli_query(ConnDB(),$StrSQL2))) {
			var_dump($StrSQL2);
			die;
		}

	}

	$StrSQL="SELECT * FROM DAT_M2 ";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	while ($item = mysqli_fetch_assoc($rs)) {
		$StrSQL2=" UPDATE DAT_M2 SET ";
		$StrSQL2.=" PASS = '".pwd_hash($pass)."'";
		$StrSQL2.=" WHERE EMAIL = '".$item["EMAIL"]."'";
		if (!(mysqli_query(ConnDB(),$StrSQL2))) {
			var_dump($StrSQL2);
			die;
		}

	}

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
