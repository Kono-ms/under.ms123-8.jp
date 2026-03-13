<?php
require "../config.php";
require "../base.php";
require '../a_m1/config.php';

ini_set( 'display_errors', 0 );
set_time_limit(7200);

//InitSub();//データベースデータの読み込み
//ConnDB();//データベース接続
Main();//メイン処理

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function Main()
{

	eval(globals());

	if($_POST['id']==""){
		$id=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['id'] ?? ''));
		$pass=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['pass']));
		$token=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['token'] ?? ''));
	} else {
		$id=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['id'] ?? ''));
		$pass=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['pass']));
		$token=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['token'] ?? ''));
	}

	$mode="new";

	DispData($id,$pass,$token);

	return $function_ret;
} 

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function DispData($id,$pass,$token)
{

	eval(globals());

	//各テンプレートファイル名
	$htmlnew = "edit.html";

	if(($_GET['status'] ?? '')=="authorized"){
		$apikey="SP-APIKEY";
		$token=$_GET['token'] ?? '';
		$response=file_get_contents("https://api.socialplus.jp/api/authenticated_user?key=".$apikey."&token=".$token."&add_profile=true&delete_profile=true");
		$val=json_decode($response, true);
		$email=$val['email'][0]['email'];
		$lineid=$val['user']['identifier'];
		$StrSQL="SELECT ID, MID, M1_DVAL01 from DAT_M1 where SOCIALID='".trim($lineid)."' and ENABLE='ENABLE:公開中';";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item=mysqli_num_rows($rs);
		if($item>0){
			$item = mysqli_fetch_assoc($rs);
			session_regenerate_id(); // セッションID再発行
			$_SESSION['MATT'] = "1";
			$_SESSION['M-ID'] = $item['ID'];
			$_SESSION['MID'] = $item['MID'];
			$_SESSION['MNAME'] = $item['M1_DVAL01'];
//			$url = $_SESSION['REFERER'];
			$url=BASE_URL . "/m_top1/";
			header("Location: {$url}");
			exit;
		}
	}

	$filename=$htmlnew;
	$errmsg="";
	if($id!=""){
		// CSRFチェック
		// if ($_SESSION['token'] != $token) {
		// 	exit('トークンが一致しません');
		// }


		$StrSQL="SELECT * from DAT_M1 where EMAIL='".trim($id)."' and ENABLE='ENABLE:公開中';";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);
		if(password_verify($pass,$item["PASS"])){
			session_regenerate_id(); // セッションID再発行
			$_SESSION['MATT'] = "1";
			$_SESSION['M-ID'] = $item['ID'];
			$_SESSION['MID'] = $item['MID'];
			$_SESSION['MNAME'] = $item['M1_DVAL01'];
			$url=BASE_URL . "/m_top1/";
			header("Location: {$url}");
			exit;
		} else {
			$errmsg="<font style='color:#ff0000;'>メールアドレスまたはパスワードが正しくありません。</font>";
		}
	} else {
		$_SESSION['REFERER'] = $_SERVER['HTTP_REFERER'];
	}

	$fp=$DOCUMENT_ROOT.$filename;
	$str=@file_get_contents($fp);

	$str = MakeHTML($str,0,$lid);

	$str=str_replace("[ERRMSG]",$errmsg,$str);
	if($errmsg<>""){
		$str=DispParam($str, "ERR");
	} else {
		$str=DispParamNone($str, "ERR");
	}

	$str=str_replace("[BASE_URL]",BASE_URL,$str);

	// CSRFトークン生成
	if($token==""){
		$token=htmlspecialchars(session_id());
		$_SESSION['token'] = $token;
	}
	$str=str_replace("[TOKEN]",$token,$str);

	print $str;

	return $function_ret;
} 

//=========================================================================================================
//【関数名】	:ConnDB()
//【機能\】	:データベースへの接続
//【引数】	:なし
//【戻り値】	:なし
//【備考】	:DB接続
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
