<?php
require "../config.php";
require "../base.php";
require '../a_m2/config.php';

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

	// 最初にセッションチェック
	// if(!CheckSession(2)) {
	// 	$url=BASE_URL . "/login2/";
	// 	header("Location: {$url}");
	// 	exit;
	// }

	if(!isset($_POST['mode']) || $_POST['mode']===""){
		$pass1=mysqli_real_escape_string(ConnDB(),$_GET['pass1']);
		$pass2=mysqli_real_escape_string(ConnDB(),$_GET['pass2']);
		$pass3=mysqli_real_escape_string(ConnDB(),$_GET['pass3']);
		$mode=mysqli_real_escape_string(ConnDB(),$_GET['mode'] ?? '');
		$email=mysqli_real_escape_string(ConnDB(),$_GET['email'] ?? '');
		$date=mysqli_real_escape_string(ConnDB(),$_GET['date']);
	} else {
		$pass1=mysqli_real_escape_string(ConnDB(),$_POST['pass1']);
		$pass2=mysqli_real_escape_string(ConnDB(),$_POST['pass2']);
		$pass3=mysqli_real_escape_string(ConnDB(),$_POST['pass3']);
		$mode=mysqli_real_escape_string(ConnDB(),$_POST['mode'] ?? '');
		$email=mysqli_real_escape_string(ConnDB(),$_POST['email'] ?? '');
		$date=mysqli_real_escape_string(ConnDB(),$_POST['date']);

	}

	$errmsg="";
	if($mode=="regist"){
		$email=strDecrypt($email);
		$date=strDecrypt($date);

		$StrSQL="SELECT * from DAT_M2 where EMAIL='".$email."'";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$cnt=mysqli_num_rows($rs);
		if($cnt>0){
			print "既に登録済みです。";
			exit;
		}

		$mode="new";
	}

	if($date!=""){
		$dt1=date("Y/m/d H:i:s",strtotime($date . "+1 hour"));
		$dt2=date("Y/m/d H:i:s");
		// echo "<!--１時間後:".$dt1."-->";
		// echo "<!--現在日時:".$dt2."-->";
		if($dt1<$dt2){
			print "期限切れです。";
			exit;
		}
	}

	if($email==""){
		
		$StrSQL="SELECT * from DAT_M2 WHERE MID = '".$_SESSION['MID']."'";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);
		$email = $item['EMAIL'];
	}

	if($mode=="save"){
		$newregist=0;
		//登録状態チェック
		$StrSQL="SELECT * from DAT_M2 where EMAIL='".$email."'";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$cnt=mysqli_num_rows($rs);
		if($cnt==0){
			$newregist=1;
		}

		//エラーチェック
		// $StrSQL="SELECT * from DAT_M2 where MID='".$_SESSION['MID']."' and PASS='".trim($pass1)."' and ENABLE='ENABLE:公開中';";
		// $rs=mysqli_query(ConnDB(),$StrSQL);
		// $cnt=mysqli_num_rows($rs);
		// if($cnt=="" || $cnt==0){
		// 	$errmsg.="<font style='color:#ff0000;'>旧パスワードが正しくありません。</font><br>";
		// }
		if($pass2==""){
			$errmsg.="<font style='color:#ff0000;'>パスワードが入力されていません。</font><br>";
		}
		if($pass2!=$pass3){
			$errmsg.="<font style='color:#ff0000;'>パスワードが確認用と一致しません。</font><br>";
		}
		//新規登録モードの場合
		if($newregist==1&&$errmsg==""){
			$mode="save2";
		}
	}

	DispData($mode,$pass1,$pass2,$pass3,$errmsg,$email,$date);

	return $function_ret;
} 

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function DispData($mode,$pass1,$pass2,$pass3,$errmsg,$email,$date)
{

	eval(globals());

	//各テンプレートファイル名
	$htmlnew = "edit.html";
	$htmlend = "end.html";
	$htmlend2 = "end2.html";

	$filename=$htmlnew;

	if($mode=="save" && $errmsg==""){
	
		$StrSQL ="UPDATE DAT_M2 SET ";
		$StrSQL.=" PASS = '".pwd_hash($pass2)."'"; 
		$StrSQL.=" where EMAIL='".$email."'"; 
		if (!(mysqli_query(ConnDB(),$StrSQL))) {
			var_dump("err1:".$StrSQL);
			die;
		}


		$filename=$htmlend;
		
	}
	if($mode=="save2"){


		$StrSQL="SELECT MID from DAT_M2 order by MID desc limit 0,1;";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);
		$mid2="M2".sprintf("%05d", str_replace("M2", "", $item['MID'])+1);


		// ・顧客情報保存時に顧客認証テーブルのメアドが存在していた場合、顧客認証名を保存する
		$StrSQL="SELECT AUTH_NAME,convert(AES_DECRYPT(UNHEX(EMAIL), '".DB_ENC_KEY."') USING utf8) as email_dec from DAT_MEMBER_AUTH  ";
		$StrSQL.=" WHERE convert(AES_DECRYPT(UNHEX(EMAIL), '".DB_ENC_KEY."') USING utf8) LIKE '%".$email."%' order by id desc";
		// echo "<!--DAT_MEMBER_AUTH:".$StrSQL."-->";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);
		if($item["AUTH_NAME"]!=""){
			$M2_ETC02=$item["AUTH_NAME"];
		}

		$StrSQL ="INSERT INTO DAT_M2 (MID,EMAIL,PASS,M2_ETC02,M2_DFIL01,M2_DFIL02,M2_DFIL03,M2_DFIL04,M2_DFIL05,M2_DFIL06,M2_DFIL07,M2_DFIL08,M2_DFIL09,M2_DFIL10,ENABLE,NEWDATE,EDITDATE) VALUES (";
		$StrSQL.=" '".$mid2."',"; 
		$StrSQL.=" '".$email."',"; 
		$StrSQL.=" '".pwd_hash($pass2)."',"; 
		$StrSQL.=" '".$M2_ETC02."',"; 
		$StrSQL.=" '/a_m2/data/s.gif',"; 
		$StrSQL.=" '/a_m2/data/s.gif',"; 
		$StrSQL.=" '/a_m2/data/s.gif',"; 
		$StrSQL.=" '/a_m2/data/s.gif',"; 
		$StrSQL.=" '/a_m2/data/s.gif',"; 
		$StrSQL.=" '/a_m2/data/s.gif',"; 
		$StrSQL.=" '/a_m2/data/s.gif',"; 
		$StrSQL.=" '/a_m2/data/s.gif',"; 
		$StrSQL.=" '/a_m2/data/s.gif',"; 
		$StrSQL.=" '/a_m2/data/s.gif',"; 
		$StrSQL.=" 'ENABLE:公開中',"; 
		$StrSQL.=" '".date("Y/m/d H:i:s")."',"; 
		$StrSQL.=" '".date("Y/m/d H:i:s")."' "; 
		$StrSQL.=" ) ";
		if (!(mysqli_query(ConnDB(),$StrSQL))) {
			var_dump("err2:".$StrSQL);
			die;
		}

		//セッション設定
		$StrSQL="SELECT * from DAT_M2 WHERE MID = '".$mid2."'";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);
		$_SESSION['MATT'] = "2";
		$_SESSION['MID'] = $mid2;
		$_SESSION['MNAME'] = $item["M2_DVAL01"];

		$filename=$htmlend2;
		SendMail($item);
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

	$str=str_replace("[email]",$email,$str);
	$str=str_replace("[date]",$date,$str);
	$str=str_replace("[BASE_URL]",BASE_URL,$str);
	print $str;

	return $function_ret;
} 
//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function SendMail($item)
{

	eval(globals());

	$maildata = GetMailTemplate('会員登録完了(M2)');
	$MailBody = $maildata['BODY'];
	$subject = $maildata['TITLE'];

	$MailBody=str_replace("[MID]",$item["MID"],$MailBody);
	$MailBody=str_replace("[EMAIL]",$item["EMAIL"],$MailBody);
	$MailBody=str_replace("[M2_DVAL01]","ご登録者",$MailBody);
	
	mb_language("Japanese");
	mb_internal_encoding("UTF-8");

	mb_send_mail($item["EMAIL"], $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 
	// mb_send_mail(SENDER_EMAIL, $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 
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
