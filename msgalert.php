<?php

ini_set( 'display_errors', 0 );

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

	date_default_timezone_set('Asia/Tokyo');

	$stid="";
	$StrSQL="SELECT AID, RID, NEWDATE FROM DAT_MESSAGE where (NOREAD is null or NOREAD='') group by AID, RID order by AID, RID, ID desc;";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	while ($item = mysqli_fetch_assoc($rs)) {
		$tid=str_replace("-", "", str_replace($item['RID'], "", $item['AID']));
		if(strstr($stid, "!".$tid."!")==false){
			SendMail($tid);
			$stid.="!".$tid."!";
		}
	}
	//cron停止
	exit();
} 

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function SendMail($tid)
{

	eval(globals());

	if(strstr($tid, "M1") !== false){

		$StrSQL="SELECT EMAIL FROM DAT_M1 where MID='".$tid."'";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);

$MailBody = <<<EOT
ご登録者様

WEBSITE-NAMEをご利用いただき誠にありがとうございます。

投資ユーザー様から新しいメッセージが送られました。
マイアカウントページのメッセージ画面から内容を確認することが出来ます。

━━━━━━━━━━━━━━━━━━━━━━━━━━
　メッセージ内容を確認する
━━━━━━━━━━━━━━━━━━━━━━━━━━
https://mbase.msc-dev.com/

ご登録いただいた際のメールアドレス（本メール受信アドレス）とパスワードでログインできます。

━━━━━━━━━━━━━━━━━━━━━━━━━━

ログインができない場合
-----------------------------------------------------
ログインができない場合は、以下の理由が考えられます。
・メールアドレス、パスワードを間違えて入力している。
・大文字小文字に誤りがある。
・パスワードを忘れた。

解決できない場合は、お手数ですが一度「info@msc-dev.com」までお問合せください。

パスワードをお忘れの場合
-----------------------------------------------------
ログインページの「パスワードを忘れた方」からパスワードの再送信をお願いします。

━━━━━━━━━━━━━━━━━━━━━━━━━━


本自動配信メールは、ご登録いただいたメールアドレス宛てに、
WEBSITE-NAME事務局から送られたものです。

お心当たりのない方は、お問合せ窓口（info@msc-dev.com）まで
お手数ではございますが、ご連絡くださいませ。

````````````````````````````````````````````

WEBSITE-NAME事務局

お問合せ窓口：info@msc-dev.com
WEBSITE-NAME URL：https://mbase.msc-dev.com/

````````````````````````````````````````````
COMPANY-NAME
EOT;


		mb_language("Japanese");
		mb_internal_encoding("UTF-8");
		mb_send_mail($item['EMAIL'], "【WEBSITE-NAME】投資ユーザー様からメッセージが届きました！", $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding("WEBSITE-NAME事務局","ISO-2022-JP","AUTO"))."<info@msc-dev.com>"); 
	} else {

		$StrSQL="SELECT EMAIL FROM DAT_M2 where MID='".$tid."'";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);

$MailBody = <<<EOT
ご登録者様

WEBSITE-NAMEをご利用いただき誠にありがとうございます。

不動産会社様から新しいメッセージが送られました。
マイアカウントページのメッセージ画面から内容を確認することが出来ます。

━━━━━━━━━━━━━━━━━━━━━━━━━━
　メッセージ内容を確認する
━━━━━━━━━━━━━━━━━━━━━━━━━━
https://mbase.msc-dev.com/

ご登録いただいた際のメールアドレス（本メール受信アドレス）とパスワードでログインできます。

━━━━━━━━━━━━━━━━━━━━━━━━━━

ログインができない場合
-----------------------------------------------------
ログインができない場合は、以下の理由が考えられます。
・メールアドレス、パスワードを間違えて入力している。
・大文字小文字に誤りがある。
・パスワードを忘れた。

解決できない場合は、お手数ですが一度「info@msc-dev.com」までお問合せください。

パスワードをお忘れの場合
-----------------------------------------------------
ログインページの「パスワードを忘れた方」からパスワードの再送信をお願いします。

━━━━━━━━━━━━━━━━━━━━━━━━━━


本自動配信メールは、ご登録いただいたメールアドレス宛てに、
WEBSITE-NAME事務局から送られたものです。

お心当たりのない方は、お問合せ窓口（info@msc-dev.com）まで
お手数ではございますが、ご連絡くださいませ。

````````````````````````````````````````````

WEBSITE-NAME事務局

お問合せ窓口：info@msc-dev.com
WEBSITE-NAME URL：https://mbase.msc-dev.com/

````````````````````````````````````````````
COMPANY-NAME
EOT;


		mb_language("Japanese");
		mb_internal_encoding("UTF-8");
		mb_send_mail($item['EMAIL'], "【WEBSITE-NAME】不動産会社様からメッセージが届きました！", $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding("WEBSITE-NAME事務局","ISO-2022-JP","AUTO"))."<info@msc-dev.com>"); 
	}

}

//=========================================================================================================
//名前
//機能
//引数
//戻値
//=========================================================================================================
function globals(){

	$vars = array();
	foreach($GLOBALS as $k => $v){
		$vars[] = "$".$k;
	}
	return "global ".join(",", $vars).";";
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
