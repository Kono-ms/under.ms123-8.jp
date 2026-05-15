<?php

require "../config.php";
require "../base.php";
require "../common.php";
require '../a_o1/config.php';

ini_set( 'display_errors', 0 );

set_time_limit(7200);

//データベース接続
//ConnDB();
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

	// 最初にセッションチェック
	if(!CheckSession(1)&&!CheckSession(2)) {
		$url=BASE_URL . "/login2/";
		header("Location: {$url}");
		exit;
	}

	if(!isset($_POST['mode']) || $_POST['mode']===""){
		$mode=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['mode'] ?? ''));
		$sort=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['sort'] ?? ''));
		if(isset($_GET['word']) && is_array($_GET['word'])) {
			$word = array();
			for($i = 0; $i < count($_GET['word']); $i++) {
				$word[]=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['word'][$i]));
			}
		}
		else {
			$word=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['word'] ?? ''));
		}
		$key=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['key'] ?? ''));
		$page=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['page'] ?? ''));
		$lid=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['lid'] ?? ''));
		$token=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['token'] ?? ''));
		$sel1=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['sel1']));
		$sel2=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['sel2']));
		$status=mysqli_real_escape_string(ConnDB(),$_GET['status'] ?? '');
		$mid1=mysqli_real_escape_string(ConnDB(),$_GET['mid1']);
		$mid2=mysqli_real_escape_string(ConnDB(),$_GET['mid2']);
	} else {
		$mode=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['mode'] ?? ''));
		$sort=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['sort'] ?? ''));
		if(isset($_POST['word']) && is_array($_POST['word'])) {
			$word = array();
			for($i = 0; $i < count($_POST['word']); $i++) {
				$word[]=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['word'][$i]));
			}
		}
		else {
			$word=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['word'] ?? ''));
		}
		$key=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['key'] ?? ''));
		$page=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['page'] ?? ''));
		$lid=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['lid'] ?? ''));
		$token=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['token'] ?? ''));
		$sel1=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['sel1']));
		$sel2=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['sel2']));
		$status=mysqli_real_escape_string(ConnDB(),$_POST['status'] ?? '');
		$mid1=mysqli_real_escape_string(ConnDB(),$_POST['mid1']);
		$mid2=mysqli_real_escape_string(ConnDB(),$_POST['mid2']);
	}

	if(!empty($key) && !is_numeric($key)) {
		exit('情報が見つかりませんでした');
	}

	if(is_array($word)==true){
		$tmp="";
		for($i=0; $i<count($word); $i++){
			if($tmp!=""){
				$tmp.="\t";
			}
			$tmp.=$word[$i];
		}
		$word=$tmp;
		$word=str_replace("\t\t", "\t", $word);
		$word=str_replace("\t\t", "\t", $word);
		$word=str_replace("\t\t", "\t", $word);
		$word=str_replace("\t\t", "\t", $word);
		$word=str_replace("\t\t", "\t", $word);
		$word=str_replace("\t\t", "\t", $word);
		$word=str_replace("\t\t", "\t", $word);
		$word=str_replace("\t\t", "\t", $word);
		$word=str_replace("\t\t", "\t", $word);
		$word=str_replace("\t\t", "\t", $word);
	}

	if ($mode==""){
		$mode="list";
	}

	if($mode=="status1"){
		if ($_SESSION['token']==$token) {
			$aid=$mid1."-".$mid2;

			$ETC04=$_GET['date1'];
			$ETC05="";
			if($_GET['COMMENT1']!=""){
				$ETC05= $_GET['COMMENT1'];
			}
			if($_GET['COMMENT2']!=""){
				$ETC05= $_GET['COMMENT2'];
			}

			$price=$_GET['price1'];
			$price=$_GET['price1'];
			$StrSQL="INSERT INTO DAT_MCONTACT (MID, MIDT, STATUS,PRICE, NEWDATE, ETC02, ETC03, ETC04, ETC05) values (";
			$StrSQL.="'".$mid1."',";
			$StrSQL.="'".$mid2."',";
			$StrSQL.="'STATUS:".$status."',";
			$StrSQL.="'".$price."',";
			$StrSQL.="'".date("Y/m/d H:i:s")."',";
			$StrSQL.="'".$_GET['etc02']."',";
			$StrSQL.="'".$_GET['etc03']."',";
			$StrSQL.="'".$ETC04."',";
			$StrSQL.="'".$ETC05."'";
			$StrSQL.=")";
			if (!(mysqli_query(ConnDB(),$StrSQL))) {
				die;
			}

			$StrSQL="SELECT ID,STATUS FROM DAT_MCONTACT where MID='".$mid1."' and MIDT='".$mid2."'  order by ID desc;";
			$rs=mysqli_query(ConnDB(),$StrSQL);
			$item = mysqli_fetch_assoc($rs);
			$contact_newid = $item['ID'];

			$comment = '[' . str_replace('STATUS:', '', $status) . ']が送信されました。' . "\n";
			if($_GET['price1']!=""){
				$comment.= '提示金額（円）：' .$_GET['price1']. "円\n";
			}
			if($_GET['COMMENT1']!=""){
				$comment.= 'ユーザーへの連絡事項：' .$_GET['COMMENT1']. "\n";
			}
			if($_GET['date1']!=""){
				$comment.= '希望日：' .$_GET['date1']. "\n";
			}
			if($_GET['COMMENT2']!=""){
				$comment.= 'ユーザーへの連絡' .$_GET['COMMENT2']. "\n";
			}
			$StrSQL="INSERT INTO DAT_MESSAGE (AID, RID, ENABLE, NEWDATE, COMMENT, ETC01, ETC02, ETC03) values (";
			$StrSQL.="'".$aid."',";
			$StrSQL.="'".$mid1."',";
			$StrSQL.="'ENABLE:公開中',";
			$StrSQL.="'".date("Y/m/d H:i:s")."',";
			$StrSQL.="'".str_replace("'","''",htmlspecialchars($comment))."',";
			$StrSQL.="'".$contact_newid."',";
			$StrSQL.="'".$_GET['etc02']."',";
			$StrSQL.="'".$_GET['etc03']."'";
			$StrSQL.=")";
			if (!(mysqli_query(ConnDB(),$StrSQL))) {
				die;
			}

			SendMailStatus($mid1, $mid2);
			if($status == LAST_STATUS) {
				SendMailStatus($mid1, $mid2, $contact_newid, 'mail_eval.txt');
			}
		}

		$mode="disp";
	}


	if($mode=="status2"){

		if ($_SESSION['token']==$token) {
			$aid=$mid1."-".$mid2;

			$ETC04=$_GET['date1'];
			$ETC05="";
			if($_GET['COMMENT1']!=""){
				$ETC05= $_GET['COMMENT1'];
			}
			if($_GET['COMMENT2']!=""){
				$ETC05= $_GET['COMMENT2'];
			}
			$price=$_GET['price1'];
			$StrSQL="INSERT INTO DAT_MCONTACT (MID, MIDT, STATUS, PRICE,NEWDATE, ETC02, ETC03, ETC04, ETC05) values (";
			$StrSQL.="'".$mid1."',";
			$StrSQL.="'".$mid2."',";
			$StrSQL.="'STATUS:".$status."',";
			$StrSQL.="'".$price."',";
			$StrSQL.="'".date("Y/m/d H:i:s")."',";
			$StrSQL.="'".$_GET['etc02']."',";
			$StrSQL.="'".$_GET['etc03']."',";
			$StrSQL.="'".$ETC04."',";
			$StrSQL.="'".$ETC05."'";
			$StrSQL.=")";
			if (!(mysqli_query(ConnDB(),$StrSQL))) {
				die;
			}

			$StrSQL="SELECT ID,STATUS FROM DAT_MCONTACT where MID='".$mid1."' and MIDT='".$mid2."'  order by ID desc;";
			$rs=mysqli_query(ConnDB(),$StrSQL);
			$item = mysqli_fetch_assoc($rs);
			$contact_newid = $item['ID'];

			$comment = '[' . str_replace('STATUS:', '', $status) . ']が送信されました。' . "\n";
			if($_GET['date1']!=""){
				$comment.= '希望日：' .$_GET['date1']. "\n";
			}
			if($_GET['price1']!=""){
				$comment.= '提示金額（円）：' .$_GET['price1']. "円\n";
			}
			if($_GET['COMMENT1']!=""){
				$comment.= 'サポーターへの連絡事項：' .$_GET['COMMENT1']. "\n";
			}
			
			$StrSQL="INSERT INTO DAT_MESSAGE (AID, RID, ENABLE, NEWDATE, COMMENT, ETC01, ETC02, ETC03) values (";
			$StrSQL.="'".$aid."',";
			$StrSQL.="'".$mid2."',";
			$StrSQL.="'ENABLE:公開中',";
			$StrSQL.="'".date("Y/m/d H:i:s")."',";
			$StrSQL.="'".str_replace("'","''",htmlspecialchars($comment))."',";
			$StrSQL.="'".$contact_newid."',";
			$StrSQL.="'".$_GET['etc02']."',";
			$StrSQL.="'".$_GET['etc03']."'";
			$StrSQL.=")";
			if (!(mysqli_query(ConnDB(),$StrSQL))) {
				die;
			}

			SendMailStatus($mid1, $mid2);
			if($status == LAST_STATUS) {
				SendMailStatus($mid1, $mid2, $contact_newid, 'mail_eval.txt');
			}
		}

		$mode="disp";
	}

	if($mode=="disp"){
		if($key==""){
			$StrSQL="SELECT ID FROM DAT_O1 where MID='".$_SESSION['MID']."'";
			$rs=mysqli_query(ConnDB(),$StrSQL);
			$item=mysqli_fetch_assoc($rs);
			$key=$item['ID'];
			if($key==""){
				print "自分のプロフィールを見るには、不動産情報を登録してください。";
				exit;
			}
		}
		$StrSQL="SELECT * FROM DAT_O1 where (ENABLE='ENABLE:公開中' and ID=".$key.") or (MID='".$_SESSION['MID']."' and ID=".$key.")";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item=mysqli_num_rows($rs);
		if($_SESSION['MID']=="" || $item<=0){
			$url=BASE_URL . "/login1/";
			header("Location: {$url}");
			exit;
		}
	}

	switch ($mode){
	case "like":
		if($_SESSION['MID']!=""){
			$StrSQL="SELECT * FROM DAT_O1 where ID=".$key.";";
			$rs=mysqli_query(ConnDB(),$StrSQL);
			$item = mysqli_fetch_assoc($rs);
			$midt=$item['MID'];
			$oidt=$item['OID'];
			$mid=$_SESSION['MID'];
			if(strstr($midt,"M1") !== false){
				// リロード対策
				$StrSQL="SELECT ID FROM DAT_IINE where MID='".$mid."' and MIDT='".$midt."' and OIDT='".$oidt."';";
				$rs_exists=mysqli_query(ConnDB(),$StrSQL);
				$item_exists=mysqli_num_rows($rs_exists);
				if($item_exists>0) {
					$mode="disp";
					break;
				}

				$StrSQL="DELETE FROM DAT_IINE where MID='".$mid."' and MIDT='".$midt."' and OIDT='".$oidt."';";
				if (!(mysqli_query(ConnDB(),$StrSQL))) {
					die;
				}
				$StrSQL="INSERT INTO DAT_IINE (MID, MIDT, OIDT, NEWDATE) values ('".$mid."', '".$midt."', '".$oidt."', '".date('Y/m/d H:i:s')."')";
				if (!(mysqli_query(ConnDB(),$StrSQL))) {
					die;
				}
				SendMail($midt,$mid);
			}
		}
		$mode="disp";
		break;
	case "notlike":
		if($_SESSION['MID']!=""){
			$StrSQL="SELECT * FROM DAT_O1 where ID=".$key.";";
			$rs=mysqli_query(ConnDB(),$StrSQL);
			$item = mysqli_fetch_assoc($rs);
			$midt=$item['MID'];
			$oidt=$item['OID'];
			$mid=$_SESSION['MID'];
			if(strstr($midt,"M1") !== false){
				$StrSQL="DELETE FROM DAT_IINE where MID='".$mid."' and MIDT='".$midt."' and OIDT='".$oidt."';";
				if (!(mysqli_query(ConnDB(),$StrSQL))) {
					die;
				}

			}
		}
		$mode="disp";
		break;
	case "disp":
		LoadData($key);
		break;
	case "list":
		if ($page==""){
			$page=1;
		} 
		if ($sort==""){
			$sort=1;
		}
		break;
	} 

	DispData($mode,$sort,$word,$key,$page,$lid,$token,$sel1,$sel2);

	return $function_ret;
} 
//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function SendMailStatus($mid, $mid2 = null, $contact_id = null, $template = "mail.txt")
{

	eval(globals());

	if($template == 'mail.txt') {
		// $maildata = GetMailTemplate('金額提示');


		$status =  str_replace('STATUS:', '', $FieldValue[5]);


		$StrSQL="SELECT * FROM DAT_M1 where MID='".$mid."'";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$itemM1 = mysqli_fetch_assoc($rs);

		$StrSQL="SELECT * FROM DAT_M2 where MID='".$mid2."'";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$itemM2 = mysqli_fetch_assoc($rs);

		if($_SESSION['MATT'] == '2' ){
			$maildata = GetMailTemplate('ステータス通知（M1）');
			$MailBody = $maildata['BODY'];
			$subject = $maildata['TITLE'];
			$subject=str_replace("[M2_DVAL01]",$itemM2["M2_DVAL01"],$subject);
			$subject=str_replace("[STATUS]",$status,$subject);
			$MailBody=str_replace("[STATUS]",$status,$MailBody);

			$MailBody=str_replace("[M1_DVAL01]",$itemM1["M1_DVAL01"],$MailBody);

			$mailtoM1=$itemM1['EMAIL'];
			mb_language("Japanese");
			mb_internal_encoding("UTF-8");



			mb_send_mail($mailtoM1, $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 
		
		} else if($_SESSION['MATT'] == '1' ){
		
			$maildata = GetMailTemplateStatus('ステータス通知（M2）',$status);
			$MailBody = $maildata['BODY'];
			$subject = $maildata['TITLE'];
			$subject=str_replace("[M1_DVAL01]",$itemM1["M1_DVAL01"],$subject);
			$subject=str_replace("[STATUS]",$status,$subject);
			$MailBody=str_replace("[STATUS]",$status,$MailBody);

			$MailBody=str_replace("[M2_DVAL01]",$itemM2["M2_DVAL01"],$MailBody);

			$mailtoM2=$itemM2['EMAIL'];
			mb_language("Japanese");
			mb_internal_encoding("UTF-8");



			mb_send_mail($mailtoM2, $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 
		}

	
	
	
		return;


	}
	else {
		$maildata = GetMailTemplate('評価依頼');
	}

$status =  str_replace('STATUS:', '', $FieldValue[5]);
	



	$StrSQL="SELECT * FROM DAT_M1 where MID='".$mid."'";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$itemM1 = mysqli_fetch_assoc($rs);

	$StrSQL="SELECT * FROM DAT_M2 where MID='".$mid2."'";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$itemM2 = mysqli_fetch_assoc($rs);

	$MailBody = $maildata['BODY'];

	$MailBody=str_replace("[STATUS]",$status,$MailBody);
	$MailBody=str_replace("[MSG]",$_POST['MSG'],$MailBody);
	$MailBody=str_replace("[PRICE]",number_format($_POST['PRICE']),$MailBody);
	$MailBody=str_replace("[DATE]",$FieldValue[7],$MailBody);

	$subject = $maildata['TITLE'];
	$subject=str_replace("[STATUS]",$status,$subject);

	//$subject = "【[WEBSITE_NAME】[" . $status . "]が" . $mode . "されました";
	if($template == 'mail_eval.txt') {
		//$subject = "【Gライフパートナー】評価のお願い";
		$MailBody1=$MailBody;
		$MailBody1=str_replace("[MID1]",$mid,$MailBody1);
		$MailBody1=str_replace("[MID2]",$mid2,$MailBody1);
		$MailBody1=str_replace("[CONTACT_ID]",$contact_id,$MailBody1);

		$MailBody2=$MailBody;
		$MailBody2=str_replace("[MID2]",$mid,$MailBody2);
		$MailBody2=str_replace("[MID1]",$mid2,$MailBody2);
		$MailBody2=str_replace("[CONTACT_ID]",$contact_id,$MailBody2);
	}

	mb_language("Japanese");
	mb_internal_encoding("UTF-8");

	$mailto1=$itemM1['EMAIL'];
	$mailto2=$itemM1['EMAIL'];
	// $mailto1="toretoresansan00@gmail.com";
	// $mailto2="toretoresansan11@gmail.com";
	mb_send_mail($mailto1, $subject.$itemM1['EMAIL'], $MailBody1, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 
	mb_send_mail($mailto2, $subject.$itemM2['EMAIL'], $MailBody2, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 
	// mb_send_mail("info@msc-dev.com", $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 
}
//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function SendMail($mid1,$mid2)
{

	eval(globals());

	$StrSQL="SELECT * FROM DAT_M2 where MID='".$mid2."'";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$itemM2 = mysqli_fetch_assoc($rs);

	$StrSQL="SELECT * FROM DAT_M1 where MID='".$mid1."'";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item = mysqli_fetch_assoc($rs);


	$maildata = GetMailTemplate('いいね(M2)');
	$MailBody = $maildata['BODY'];
	$subject = $maildata['TITLE'];

	
	$MailBody=str_replace("[M1_DVAL01]",$item["M1_DVAL01"],$MailBody);

	mb_language("Japanese");
	mb_internal_encoding("UTF-8");

	mb_send_mail($item['EMAIL'], $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 
	mb_send_mail(SENDER_EMAIL, $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 
}

//=========================================================================================================
//名前 画面表示処理
//機能 Modeによって画面表示
//引数 $mode,$sort,$word,$key,$page,$lid
//戻値 なし
//=========================================================================================================
function DispData($mode,$sort,$word,$key,$page,$lid,$token,$sel1,$sel2)
{

	eval(globals());

	//各テンプレートファイル名
	$htmlnew = "edit.html";
	$htmledit = "edit.html";
	$htmlconf = "conf.html";
	$htmlend = "end.html";

	$htmldisp = "disp.html";
	$htmllist = "list.html";

	if ($mode!="list"){

		$filename=$htmldisp;
		$msg01="";
		$msg02="";
		$errmsg="";

		$fp=$DOCUMENT_ROOT.$filename;
		$str=@file_get_contents($fp);

		$StrSQL="SELECT * FROM DAT_O1 where ID='".$key."';";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);
		$mid1=$item["MID"];

		if($_SESSION['MATT']=="1"){
			$StrSQL2="SELECT * FROM DAT_MCONTACT WHERE MID = '".$mid1."' AND ETC02 = '".$_GET['etc02']."' AND ETC03 = '".$_GET['etc03']."' order by id desc";
			$rsMCONTACT=mysqli_query(ConnDB(),$StrSQL2);
			$itemMCONTACT = mysqli_fetch_assoc($rsMCONTACT); 

			$mid2=$itemMCONTACT['MIDT'];
		} else {
			$mid2=$_SESSION['MID'];
		}
		$oid1=$item["OID"];

		echo "<!--mid1:".$mid1."-->";
		echo "<!--mid2:".$mid2."-->";
		echo "<!--oid1:".$oid1."-->";

		if(!$item) {
			exit('情報が見つかりませんでした');
		}

		// ----------------------------------------------------------------------------------------
		// マーク制御1
		// ----------------------------------------------------------------------------------------
		// O系のデータにだけマークを付ける
		// M系やその他の情報にはマークは付けない
		// タグエスケープ回避のためここではタグにせず[mark1][mark2]で囲む
		$StrSQL="SELECT * FROM DAT_MATCH where MID2='".$_SESSION['MID']."' and OID1='".$item["OID"]."';";
		$rs2=mysqli_query(ConnDB(),$StrSQL);
		$item_mark = mysqli_fetch_assoc($rs2);
		$tmp=$item_mark['VAL_O1'];
		$t=explode("::", $tmp."::");
		foreach($item as $keys => $val) {
			if(strpos($item[$keys], '.jpeg') !== false || strpos($item[$keys], '.jpg') !== false || strpos($item[$keys], '.jpe') !== false || strpos($item[$keys], '.gif') !== false || strpos($item[$keys], '.png') !== false || strpos($item[$keys], '.bmp') !== false) {
				continue;
			}

			// 2021.04.13 yamamoto 複数リストに限り完全一致のみマークをつける
			if(strpos($item[$keys], ":") === false) {
				// 2021.05.10 yamamoto リスト以外にマークはつけない
			}
			else if(strpos($item[$keys], "\t") === false) {
				// 除外ワード
				$ex_word = array('あり'=>1,'なし'=>1,'可'=>1,'不可'=>1,'可能'=>1,'不可能'=>1,'応相談'=>1);
				for($i=0; $i<count($t); $i++){
					if(isset($ex_word[$t[$i]])) {
						continue;
					}
					if($t[$i]!=""){
						$item[$keys]=str_replace($t[$i],"[mark1]".$t[$i]."[mark2]",$item[$keys]);
					}
				}
			}
			else {
				$extab = explode("\t", $item[$keys]);
				$ret_item = '';
				for($j = 0; $j < count($extab); $j++) {
					$extab2 = explode(':', $extab[$j]);
					if(count($extab2) == 1) {
						break;
					}
					$ret_flg = false;
					for($i=0; $i<count($t); $i++){
						if($extab2[1] == $t[$i]) {
							$ret_flg = true;
							break;
						}
					}
					if($ret_flg) {
						$ret_item .= "[mark1]".$extab[$j]."[mark2]" . "\t";
					}
					else {
						$ret_item .= $extab[$j] . "\t";
					}
				}
				$item[$keys] = $ret_item;
			}

		}
		// ----------------------------------------------------------------------------------------


		$str=DispO1($item, $str);
		$str=DispPoint1($item['OID'], $str);
		$StrSQL="SELECT * FROM DAT_M1 where MID='".$item['MID']."'";
		$rs2=mysqli_query(ConnDB(),$StrSQL);
		$item2=mysqli_fetch_assoc($rs2);

		// M系のマーク用だが不要だったためコメントアウト
		/*
		foreach($item2 as $keys => $val) {
			if(strpos($item2[$keys], '.jpeg') !== false || strpos($item2[$keys], '.jpg') !== false || strpos($item2[$keys], '.jpe') !== false || strpos($item2[$keys], '.gif') !== false || strpos($item2[$keys], '.png') !== false || strpos($item2[$keys], '.bmp') !== false) {
				continue;
			}
			for($i=0; $i<count($t); $i++){
				if($t[$i]!=""){
					$item2[$keys]=str_replace($t[$i],"[mark1]".$t[$i]."[mark2]",$item2[$keys]);
				}
			}
		}
		*/

		$str=DispM1($item2, $str);

		// ----------------------------------------------------------------------------------------
		// マーク制御2
		// ----------------------------------------------------------------------------------------
		// データにだけマークを付けるのでここはコメントアウト
		/*
		$StrSQL="SELECT * FROM DAT_O1 where ID='".$key."';";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);
		$StrSQL="SELECT * FROM DAT_MATCH where MID2='".$_SESSION['MID']."' and OID1='".$item["OID"]."';";
		$rs2=mysqli_query(ConnDB(),$StrSQL);
		$item2 = mysqli_fetch_assoc($rs2);
		$tmp=$item2['VAL_O1'];
		$t=explode("::", $tmp."::");
		for($i=0; $i<count($t); $i++){
			if($t[$i]!=""){
				$str=str_replace($t[$i],"<mark>".$t[$i]."</mark>",$str);
			}
		}
		*/

		// ---------------------------------------------------------------------------
		// 2021.02.16 yamamoto 両方からいいねしていないとメッセージが送れない処理
		// ---------------------------------------------------------------------------
		$StrSQL="SELECT MID FROM DAT_O1 where ID='".$key."';";
	   	$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);
		$mid=$item['MID'];

		$StrSQL="SELECT ID FROM DAT_IINE where MID='".$_SESSION['MID']."' and MIDT='".$mid."';";
		$rs1=mysqli_query(ConnDB(),$StrSQL);
		$item1=mysqli_num_rows($rs1);

		$StrSQL="SELECT ID FROM DAT_IINE where MIDT='".$_SESSION['MID']."' and MID='".$mid."';";
		$rs2=mysqli_query(ConnDB(),$StrSQL);
		$item2=mysqli_num_rows($rs2);

		if($item1>0 && $item2>0){
			// 両方からいいねしている場合
			$str=DispParam($str, "MSG-BTN-ON");
			$str=DispParamNone($str, "MSG-BTN-OFF");
		} else {
			// 両方からいいねしている場合以外
			$str=DispParamNone($str, "MSG-BTN-ON");
			$str=DispParam($str, "MSG-BTN-OFF");
		}
		// ---------------------------------------------------------------------------

		// 2021.03.15 yamamoto 同属性のいいねとブラックリストの禁止
		if(mb_substr($_SESSION['MID'],0,2,"UTF-8")=="M1"){
			$str=str_replace("[ZOKUSEI]",'同属性',$str);
		}
		else {
			$str=str_replace("[ZOKUSEI]",'他属性',$str);
		}

		$str = MakeHTML($str,0,$lid);

		$str=str_replace("[KEY]",$key,$str);

		// 2021.01.18 yamamoto 評価一覧
		$eval_list = GetEvalList($item['MID']);
		$str=str_replace("[D-O1_EVAL_LIST]",$eval_list,$str);

		$str=str_replace("[BASE_URL]",BASE_URL,$str);

		// ----------------------------------------------------------------------------------------
		// マーク制御3
		// ----------------------------------------------------------------------------------------
		// ここでタグにする
		$str=str_replace("[mark1]","<mark>",$str);
		$str=str_replace("[mark2]","</mark>",$str);
		// titleがマークされるのを防止
		preg_match('/<title>(.*?)<\/title>/', $str, $match);
		$val = str_replace('<mark>', '', $match[0]);
		$val = str_replace('</mark>', '', $val);
		$str = preg_replace('/<title>(.*?)<\/title>/', $val, $str);
		// h1がマークされるのを防止
		preg_match('/<h1(.*?)<\/h1>/', $str, $match);
		$val = str_replace('<mark>', '', $match[0]);
		$val = str_replace('</mark>', '', $val);
		$str = preg_replace('/<h1(.*?)<\/h1>/', $val, $str);
		// パンくずがマークされるのを防止
		preg_match('/<section class="breadcrumbs">(.*?)<\/section>/s', $str, $match);
		$val = str_replace('<mark>', '', $match[0]);
		$val = str_replace('</mark>', '', $val);
		$str = preg_replace('/<section class="breadcrumbs">(.*?)<\/section>/s', $val, $str);
		// ----------------------------------------------------------------------------------------

		//最新ステータス取得
		$StrSQL="SELECT * FROM DAT_MCONTACT WHERE MID = '".$mid1."' AND MIDT = '".$mid2."' AND ETC02 = '".$_GET['etc02']."' AND ETC03 = '".$oid1."' order by id desc";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs); 
		$current_status = str_replace("STATUS:","",$item["STATUS"]);
		$etc03 = $oid1;
		$etc02 = $item["ETC02"];
		$contact_id = $item["ID"];
// echo "<!--最新ステータス取得:".$StrSQL."-->";
echo "<!--最新ステータス取得:".$current_status."-->";

		$m_mode="";
		$evalend="";
		if($_SESSION['MATT']=="1"){
			$m_mode="M1";

			//評価が終わっているか確認
			$StrSQL3="SELECT ID,NEWDATE FROM DAT_EVAL where MID='".$_SESSION['MID']."' and MIDT='".$item['MIDT']."' order by id desc";
			$rs3=mysqli_query(ConnDB(),$StrSQL3);
			$cnt3=mysqli_num_rows($rs3);
			$item3=mysqli_fetch_assoc($rs3);
// echo "<!--評価SQL:".$StrSQL3."-->";
// echo "<!--評価cnt:".$cnt3."-->";
// echo "<!--NEWDATE:".$item["NEWDATE"]."-->";
// echo "<!--NEWDATE3:".$item3["NEWDATE"]."-->";
			if(($cnt3==0 || $cnt3=="") || $item["NEWDATE"] > $item3["NEWDATE"]){
			} else{
				//終わっている
				$evalend="1";
			}
		} else {
			$m_mode="M2";

			//評価が終わっているか確認
			$StrSQL3="SELECT ID,NEWDATE FROM DAT_EVAL where MID='".$_SESSION['MID']."' and MIDT='".$item['MID']."' order by id desc";
			$rs3=mysqli_query(ConnDB(),$StrSQL3);
			$cnt3=mysqli_num_rows($rs3);
			$item3=mysqli_fetch_assoc($rs3);
// echo "<!--評価SQL:".$StrSQL3."-->";
// echo "<!--評価cnt:".$cnt3."-->";
			if(($cnt3==0 || $cnt3=="") || $item["NEWDATE"] > $item3["NEWDATE"]){
			} else{
				//終わっている
				$evalend="1";
			}

		}
echo "<!--evalend:".$evalend."-->";
		if($evalend=="1"){
			$current_status="";//評価済みなら、ステータスを初期にする
		}

		$statuslist = STATUS_ARRAY;
		$statusitem1="";
		$statusitem2="";
		for($i=0; $i<count($statuslist); $i++){
			$tmp=$statuslist[$i];
			$tmp=str_replace("なし","",$tmp);
			$tmps=explode("::",$tmp);

			if($current_status==$tmps[1] && $m_mode==$tmps[0]){
				if($statusitem1==""){
					$statusitem1=$tmps;
					echo "<!--statusitem1:".$tmp."-->";
				} else if($statusitem2==""){
					$statusitem2=$tmps;
					echo "<!--statusitem2:".$tmp."-->";
					break;
				}
			}
		}


		if($statusitem1!=""){
			$str=DispParam($str, "STATUS1");

			if(LAST_STATUS==$current_status){
				$str=DispParam($str, "EVAL1");
				$str=DispParamNone($str, "STATUSBTN1");
			} else {
				$str=DispParamNone($str, "EVAL1");
			}

			if($statusitem1[2]==""){
				$str=DispParamNone($str, "STATUSBTN1");
			} else {
				$str=DispParam($str, "STATUSBTN1"); 
				$str=str_replace("[MCONTACT-COMMENT]","",$str);
			} 
			$str=str_replace("[STATUS_BTN1]",$statusitem1[2],$str);
			$str=str_replace("[STATUS_TEXT1]",$statusitem1[3],$str);
			$str=str_replace("[STATUS_VAL1]",$statusitem1[4],$str);
		} 
		if($statusitem2!=""){
			$str=DispParam($str, "STATUS2");


			if(LAST_STATUS==$current_status){
				$str=DispParam($str, "EVAL2");
				$str=DispParamNone($str, "STATUSBTN2");
			} else {
				$str=DispParamNone($str, "EVAL2");
			}
   
		 	if($statusitem2[2]==""){
				$str=DispParamNone($str, "STATUSBTN2");
			} else {
				$str=DispParam($str, "STATUSBTN2");
				$str=str_replace("[MCONTACT-COMMENT]","",$str);
			} 
			$str=str_replace("[STATUS_BTN2]",$statusitem2[2],$str);
			$str=str_replace("[STATUS_TEXT2]",$statusitem2[3],$str);
			$str=str_replace("[STATUS_VAL2]",$statusitem2[4],$str);
		} 
		$str=DispParamNone($str, "STATUS1");
		$str=DispParamNone($str, "STATUS2");

		if (is_numeric($item["PRICE"])) {
			$str=str_replace("[MCONTACT-PRICE]",number_format($item["PRICE"],0),$str);
		} else {
			$str=str_replace("[MCONTACT-PRICE]",$item["PRICE"],$str);
		} 

		$aid=$mid1."-".$mid2;
		$StrSQL_max="SELECT ifnull(max(ETC02), 0) as max_id FROM DAT_MESSAGE where AID = '".$aid."'";
		$rs_max=mysqli_query(ConnDB(),$StrSQL_max);
		$item_max = mysqli_fetch_assoc($rs_max);
		$next_id = intval($item_max['max_id']) + 1;
		

		$str=str_replace("[MCONTACT-DATE]",$item["ETC04"],$str);
		$str=str_replace("[MCONTACT-COMMENT]",str_replace("\n","<br>",$item["ETC05"]),$str);
		if($current_status==""){
			$str=str_replace("[MCONTACT-ETC02]",$next_id,$str);
		} else {
			$str=str_replace("[MCONTACT-ETC02]",$etc02,$str);
		}
		
		$str=str_replace("[MCONTACT-CONTACT_ID]",$contact_id,$str);
		$str=str_replace("[MID1]",$mid1,$str);
		$str=str_replace("[MID2]",$mid2,$str);
		$str=str_replace("[OID]",$etc03,$str);

		

		if($_SESSION['MATT']=="1"){
			$str=str_replace("[STATUS_MODE]","status1",$str);
			$str=str_replace("[EVAL_MID1]",$mid1,$str);
			$str=str_replace("[EVAL_MID2]",$mid2,$str);
		} else {
			$str=str_replace("[STATUS_MODE]","status2",$str);
			$str=str_replace("[EVAL_MID1]",$mid2,$str);
			$str=str_replace("[EVAL_MID2]",$mid1,$str);


		}



		// CSRFトークン生成
		// if($token==""){
			$token=htmlspecialchars(session_id().date("YmdHis") . substr(explode(".", microtime(true))[1], 0, 3));
			$_SESSION['token'] = $token;
		// }
		$str=str_replace("[TOKEN]",$token,$str);



	print $str;

	} else {

		$filename=$htmllist;

		$fp=$filename;
		$tso=@fopen($fp,"r");

		while( $line = fgets($tso,1024) ){
			if(strstr($line,"LIST-START") !== false){
				break;
			}
			$strU=$strU.$line.chr(13);
		}
		while( $line = fgets($tso,1024) ){
			if(strstr($line,"LIST-END") !== false){
				break;
			}
			$strM=$strM.$line.chr(13);
		}
		while( $line = fgets($tso,1024) ){
			$strD=$strD.$line.chr(13);
		}
		fclose($tso);

		$filename="../common/template/listo1.html";
		$fp=$DOCUMENT_ROOT.$filename;
		$strM=@file_get_contents($fp);

		// SQLインジェクション対策
		$StrSQL="SELECT DAT_O1.* FROM DAT_O1 inner join DAT_M1 on DAT_M1.MID=DAT_O1.MID inner join DAT_MATCH on DAT_MATCH.OID1=DAT_O1.OID and DAT_MATCH.MID2='".$_SESSION['MID']."' ";
		// 2021.03.15 yamamoto ENABLE:公開中のみ表示
		$StrSQL.=" and DAT_M1.ENABLE = 'ENABLE:公開中' and DAT_O1.ENABLE = 'ENABLE:公開中'";
		//2020/12/28 gaosan ADD START
		$StrSQL .= " and NOT EXISTS (SELECT * FROM DAT_BL WHERE DAT_BL.MID1 = '" . $_SESSION['MID'] . "' and DAT_BL.MID2 = DAT_O1.MID) ";
		//2020/12/28 gaosan ADD END
		$StrSQL .= " and ".ListSQLSearch($sort,$word,$sel1,$sel2);

		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item=mysqli_num_rows($rs);
		if($item==0) {
			$reccount=0;
			$pagestr="";
			$strMain="<div class='result__item'><p class='result__ttl'>検索結果が見つかりませんでした。<p></div><div class='result__item'><p class='result__txt'>ユーザー希望条件の登録はお済みでしょうか？検索結果の表示にはユーザー希望条件の登録が必要になります。まだ未登録の場合は先にこちらから登録をお願いします。</p><div class='btn result__btn'><a href='/m_o2/?mode=new&sort=&word=[L-MID]&page=1'>ユーザー希望条件を作成する</a></div></div>";
		} else {
			//================================================================================================
			//ページング処理
			//================================================================================================
			$reccount=mysqli_num_rows($rs);
			$pagecount=intval(($reccount-1)/$PageSize+1);
			mysqli_data_seek($rs, $PageSize*($page-1));

			$str="";
			$s=$page-5;
			if ($s<1) {
				$s=1;
			} 
			$e=$s+9;
			if ($e>$pagecount) {
				$e=$pagecount;
			} 
			for ($i=$s; $i<=$e; $i=$i+1) {
				if ($i==intval($page)) {
					$str=$str."<span class=\"current\">".$i."</span>";
				} else {
					$str=$str." <a href=\"".MakeUrl($sort,$word,$sel1,$sel2,$i)."\" class=\"inactive\">".$i."</a>";
				} 
			}
			$pagestr=$str;

			$CurrentRecord=1;
			$strMain="";
			while ($item = mysqli_fetch_assoc($rs)) {

				$str=$strM;

				$str=DispO1($item, $str);
				$str=DispPoint1($item['OID'], $str);

				$StrSQL="SELECT * FROM DAT_M1 where MID='".$item['MID']."'";
				$rs2=mysqli_query(ConnDB(),$StrSQL);
				$item2=mysqli_fetch_assoc($rs2);
				$str=DispM1($item2, $str);

				$strMain=$strMain.$str.chr(13);

				$CurrentRecord=$CurrentRecord+1; //CurrentRecordの更新

				if ($CurrentRecord>$PageSize){
					break;
				}
			} 
		} 

		$str=$strU.$strMain.$strD;

		$str = MakeHTML($str,0,$lid);

		$str=str_replace("[PAGING]",$pagestr,$str);
		$str=str_replace("[SORT]",$sort,$str);
		$str=str_replace("[WORD]",$word,$str);
		$str=str_replace("[PAGE]",$page,$str);
		$str=str_replace("[SEL1]",$sel1,$str);
		$str=str_replace("[SEL2]",$sel2,$str);
		$str=str_replace("[KEY]",$key,$str);
		$str=str_replace("[LID]",$lid,$str);
		$str=str_replace("[RECCOUNT]",$reccount,$str);

		// CSRFトークン生成
		// if($token==""){
			$token=htmlspecialchars(session_id().date("YmdHis") . substr(explode(".", microtime(true))[1], 0, 3));
			$_SESSION['token'] = $token;
		// }
		$str=str_replace("[TOKEN]",$token,$str);

		$h1="";
		if($sort==1){
			$h1.="<li class=\"search__item\"><a href=\"".MakeUrl(1, $word, $sel1, $sel2, 1)."\">マッチング順</a></li>";
		} else {
			$h1.="<li class=\"search__item\"><a href=\"".MakeUrl(1, $word, $sel1, $sel2, 1)."\">マッチング順</a></li>";
		}
		if($sort==2){
			$h1.="<li class=\"search__item\"><a href=\"".MakeUrl(2, $word, $sel1, $sel2, 1)."\">新着順</a></li>";
		} else {
			$h1.="<li class=\"search__item\"><a href=\"".MakeUrl(2, $word, $sel1, $sel2, 1)."\">新着順</a></li>";
		}
		$str=str_replace("[SEL_SORT]",$h1,$str);

		$tmp="";
		$sel=explode("::", $FieldParam[63]."::");
		for($i=0; $i<count($sel); $i++){
			if($sel[$i]!=""){
				if(strstr($sel1, $sel[$i]) !== false){
					$tmp.="<option value=\"".$FieldName[63].":".$sel[$i]."\" selected>".$sel[$i]."</option>";
				} else {
					$tmp.="<option value=\"".$FieldName[63].":".$sel[$i]."\">".$sel[$i]."</option>";
				}
			}
		}
		$str=str_replace("[SEL_S1]",$tmp,$str);

		$tmp="";
		$sel=explode("::", $FieldParam[64]."::");
		for($i=0; $i<count($sel); $i++){
			if($sel[$i]!=""){
				if(strstr($sel2, $sel[$i]) !== false){
					$tmp.="<option value=\"".$FieldName[64].":".$sel[$i]."\" selected>".$sel[$i]."</option>";
				} else {
					$tmp.="<option value=\"".$FieldName[64].":".$sel[$i]."\">".$sel[$i]."</option>";
				}
			}
		}
		$str=str_replace("[SEL_S2]",$tmp,$str);

		for ($i=0; $i<=$FieldMax; $i=$i+1){
			$strtmp="";
			$tmp=explode("::",$FieldParam[$i]);
			for ($j=0; $j<count($tmp); $j++) {
				if(strstr($word, $FieldName[$i].":".$tmp[$j]) !== false){
					$strtmp=$strtmp."<li><input id=\"".$FieldName[$i].$j."\" type=\"checkbox\" name=\"word[]\" value=\"".$FieldName[$i].":".$tmp[$j]."\" checked><label for=\"".$FieldName[$i].$j."\">".$tmp[$j]."</label></li>";
				} else {
					$strtmp=$strtmp."<li><input id=\"".$FieldName[$i].$j."\" type=\"checkbox\" name=\"word[]\" value=\"".$FieldName[$i].":".$tmp[$j]."\"><label for=\"".$FieldName[$i].$j."\">".$tmp[$j]."</label></li>";
				}
			}
			$str=str_replace("[OPT-".$FieldName[$i]."]",$strtmp,$str);
		}

		$tmp="";
		if($sel1!=""){
			if($tmp!=""){
				$tmp.="、";
			}
			$tmp.=str_replace($FieldName[63].":", "", $sel1);
		}
		if($sel2!=""){
			if($tmp!=""){
				$tmp.="、";
			}
			$tmp.=str_replace($FieldName[64].":", "", $sel2);
		}
		if($word!=""){
			if($tmp!=""){
				$tmp.="、";
			}
			$val=$word;
			for($i=3; $i<=92; $i++){
				$val=str_replace($FieldName[$i].":", "", $val);
			}
			$tmp.=str_replace("\t", "、", $val);
		}
		if($tmp!=""){
			$str=str_replace("[SEL_WORD]",$tmp,$str);
		} else {
			$str=str_replace("[SEL_WORD]","指定なし",$str);
		}

		// 2021.01.18 yamamoto 評価一覧
		$eval_list = GetEvalList($item['MID']);
		$str=str_replace("[D-O1_EVAL_LIST]",$eval_list,$str);

		$str=str_replace("[BASE_URL]",BASE_URL,$str);
	print $str;

	} 


	return $function_ret;
} 

//=========================================================================================================
//名前 データリクエストパラメータ処理
//機能 データリクエストパラメータの処理と画像の保存
//引数 $obj,$a,$b,$key,$mode
//戻値 $function_ret;
//=========================================================================================================
function MakeUrl($sort,$word, $sel1, $sel2,$page)
{

	return "/m_teian2/?mode=list&sort=".urlencode($sort)."&word=".urlencode($word)."&page=".urlencode($page)."&sel1=".urlencode($sel1)."&sel2=".urlencode($sel2);

}

//=========================================================================================================
//名前 DB読み込み
//機能 DBからレコードを取得
//引数 $key
//戻値 $function_ret
//=========================================================================================================
function LoadData($key)
{
	eval(globals());

	// SQLインジェクション対策
	// HTMLエスケープ処理（SQL読み込み）
	$StrSQL="SELECT * FROM ".$TableName." WHERE ".$FieldName[$FieldKey]."='".mysqli_real_escape_string(ConnDB(),$key)."';";
	$rs=mysqli_query(ConnDB(),$StrSQL);

	if ($rs==true) {
		$item = mysqli_fetch_assoc($rs);
		for ($i=0; $i<=$FieldMax; $i=$i+1) {
			$FieldValue[$i]=htmlspecialchars($item[$FieldName[$i]]);
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
