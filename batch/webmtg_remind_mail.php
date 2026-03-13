<?php

// WEB会議リマインドメール送信バッチ

require "../config.php";
require "../base.php";
require "../common.php";
require '../a_mcontact/config.php';

set_time_limit(7200);

// 2020.12.01 yamamoto エラー捕捉
require_once('../handler.php');
try {

echo("batch start\n");

//データベース接続
ConnDB();
//メイン処理
Main();

echo("batch end\n");

}
catch(\Throwable $e) {
	$dir = '../logs/';
	if(!file_exists($dir)) {
		mkdir($dir, 0777, true);
	}
	$msg = str_replace("\n", " ", $e->getMessage());
	error_log(date('Y-m-d H:i:s') . ' ; ' . rtrim($msg) . "\n", 3, $dir . 'error_' . date('Ymd') . '.log');
}

//=========================================================================================================
//名前 Main関数
//機能 プログラムのメイン関数
//引数 なし
//戻値 なし
//=========================================================================================================
function Main()
{

	eval(globals());

	// 翌日がWEB会議のMCONTACTを取得
	$StrSQL = "
		SELECT
      *
		FROM
      DAT_MCONTACT
		WHERE
      STATUS = 'STATUS:WEB会議に招待'
      and DATE_FORMAT(date_add(now(), interval 1 day), '%Y%m%d') = concat(substr(ETC06, 7), substr(ETC07, 7), substr(ETC08, 7))
	";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$mail_data = array();
	while ($item = mysqli_fetch_assoc($rs)) {
    SendMail($item);
	}

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

	$maildata = GetMailTemplate('WEB会議リマインド');

  $status =  str_replace('STATUS:', '', $FieldValue[5]);

  $m1_mid = $item['MID'];
  $m2_mid = $item['MIDT'];

	$StrSQL="SELECT EMAIL,M1_DVAL01 as NAME FROM DAT_M1 where MID='".$m1_mid."'";
  $rs=mysqli_query(ConnDB(),$StrSQL);
  $item1 = mysqli_fetch_assoc($rs);

	$StrSQL="SELECT EMAIL,M2_DVAL01 as NAME FROM DAT_M2 where MID='".$m2_mid."'";
  $rs=mysqli_query(ConnDB(),$StrSQL);
  $item2 = mysqli_fetch_assoc($rs);

  $url = 'https://msc-meet.com/mbase-' . $m1_mid . '-' . $m2_mid;

  $dt = str_replace('ETC06:', '', $item['ETC06']) . '年' .
    intval(str_replace('ETC07:', '', $item['ETC07'])) . '月' .
    intval(str_replace('ETC08:', '', $item['ETC08'])) . '日 ' .
    intval(str_replace('ETC09:', '', $item['ETC09'])) . '時' .
    str_replace('ETC10:', '', $item['ETC10']) . '分';

 	$MailBody = $maildata['BODY'];

  $MailBody=str_replace("[MESSAGE]",$item['MSG'],$MailBody);
	$MailBody=str_replace("[DATETIME]",$dt,$MailBody);
  $MailBody=str_replace("[URL]",$url,$MailBody);

	$subject = $maildata['TITLE'];
	$subject=str_replace("[DATETIME]",$dt,$subject);

	mb_language("Japanese");
  mb_internal_encoding("UTF-8");

	mb_send_mail($item1['EMAIL'], $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 
	mb_send_mail($item2['EMAIL'], $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 

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
