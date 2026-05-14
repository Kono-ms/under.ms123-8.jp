<?php

require "../config.php";
require "../base.php";
require "../common.php";
require '../a_message/config.php';

set_time_limit(7200);

//データベース接続
ConnDB();
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

	global $contact_id;
	global $contact_status;
	global $TITLE;

	eval(globals());

	// 最初にセッションチェック
	if(!CheckSession(2)) {
		$url=BASE_URL . "/login2/";
		header("Location: {$url}");
		exit;
	}

	if($_POST['mode']!=""){
		$mode=mysqli_real_escape_string(ConnDB(),$_POST['mode'] ?? '');
		$word=mysqli_real_escape_string(ConnDB(),$_POST['word'] ?? '');
		$mid1=mysqli_real_escape_string(ConnDB(),$_POST['mid1']);
		$mid2=mysqli_real_escape_string(ConnDB(),$_POST['mid2']);
	} else {
		$mode=mysqli_real_escape_string(ConnDB(),$_GET['mode'] ?? '');
		$word=mysqli_real_escape_string(ConnDB(),$_GET['word'] ?? '');
		$mid1=mysqli_real_escape_string(ConnDB(),$_GET['mid1']);
		$mid2=mysqli_real_escape_string(ConnDB(),$_GET['mid2']);
	}

	$rid=$_SESSION['MID'];

	if($mode==""){
		$mode="list";
	}

	// 2020.12.17 yamamoto タイトルをメッセージから取得
	// デフォルトはO1,O2のタイトル
	//$TITLE = '(タイトルが入ります)';
	$TITLE = '';
	$StrSQL="SELECT COMMENT FROM DAT_MESSAGE where AID='".$word."' and ETC02='".$_GET['etc02']."' and ifnull(ETC03,'')='".$_GET['etc03']."' and COMMENT like '[タイトル変更]%' order by ID desc;";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	if ($rs==true) {
		$item = mysqli_fetch_assoc($rs);
		$preg = preg_match_all('/タイトル「.+?」/i', $item['COMMENT'], $match);
		for($i = 0; $i < count($match[0]); $i++) {
			$tmp = str_replace('タイトル「', '', $match[0][$i]);
			$TITLE = str_replace('」', '', $tmp);
		}
	}
	if($TITLE == '') {
		$StrSQL="SELECT DAT_O1.O1_DVAL01 FROM DAT_O1 join DAT_IINE on DAT_O1.OID = DAT_IINE.OIDT where DAT_IINE.MID='".$mid2."' and DAT_IINE.MIDT='".$mid1."';";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);
		$TITLE = $item['O1_DVAL01'];
	}

	

	// 2020.11.06 yamamoto 承認
	if ($mode=="presentation_ok"){
		// 2020.11.16 yamamoto
		// STATUSが「STATUS:承認」のときはなにもしない
		// 2020.12.23 yamamoto 案件ID(ETC02)追加
		$StrSQL_mcontact="SELECT * FROM DAT_MCONTACT where ID='".$_GET['contact_id']."';";
		$rs_mcontact=mysqli_query(ConnDB(),$StrSQL_mcontact);
		$item_mcontact = mysqli_fetch_assoc($rs_mcontact);
		if($item_mcontact['STATUS'] != 'STATUS:承認') {
			$StrSQL="INSERT INTO DAT_MESSAGE (AID, RID, ENABLE, NEWDATE, COMMENT, ETC02, ETC03, ETC10) values (";
			$StrSQL.="'".$word."',";
			$StrSQL.="'".$rid."',";
			$StrSQL.="'ENABLE:公開中',";
			$StrSQL.="'".date("Y/m/d H:i:s")."',";
			$StrSQL.="'[" . str_replace('STATUS:', '', $item_mcontact['STATUS']) . "]を承諾しました。',";
			$StrSQL.="'".$_GET['etc02']."',";
			$StrSQL.="'".$_GET['etc03']."',";
			$StrSQL.="'".$_GET['etc10']."'";
			$StrSQL.=")";
			if (!(mysqli_query(ConnDB(),$StrSQL))) {
				die;
			}

			$StrSQL="UPDATE DAT_MCONTACT SET ";
			$StrSQL.="STATUS = 'STATUS:承認',";
			$StrSQL.="EDITDATE = '".date("Y/m/d H:i:s")."' ";
			$StrSQL.="WHERE ID= ".$_GET['contact_id'];
			if (!(mysqli_query(ConnDB(),$StrSQL))) {
				die;
			}

			// メール送信
			$mids = explode(',', $word);
			SendMail($mids[0], $item_mcontact['STATUS'], '承諾');
			SendMail($mids[1], $item_mcontact['STATUS'], '承諾');

			$mode="list";
		} // すでに回答済みでないかどうか
	}

	// 2020.11.16 yamamoto 否認
	if ($mode=="presentation_cancel"){
		// 2020.11.16 yamamoto
		// STATUSが「STATUS:否認」のときはなにもしない
		// 2020.12.23 yamamoto 案件ID(ETC02)追加
		$StrSQL_mcontact="SELECT * FROM DAT_MCONTACT where ID='".$_GET['contact_id']."';";
		$rs_mcontact=mysqli_query(ConnDB(),$StrSQL_mcontact);
		$item_mcontact = mysqli_fetch_assoc($rs_mcontact);
		if($item_mcontact['STATUS'] != 'STATUS:否認') {
			$StrSQL="INSERT INTO DAT_MESSAGE (AID, RID, ENABLE, NEWDATE, COMMENT, ETC02, ETC03, ETC10) values (";
			$StrSQL.="'".$word."',";
			$StrSQL.="'".$rid."',";
			$StrSQL.="'ENABLE:公開中',";
			$StrSQL.="'".date("Y/m/d H:i:s")."',";
			$StrSQL.="'[" . str_replace('STATUS:', '', $item_mcontact['STATUS']) . "]を否認しました。',";
			$StrSQL.="'".$_GET['etc02']."',";
			$StrSQL.="'".$_GET['etc03']."',";
			$StrSQL.="'".$_GET['etc10']."'";
			$StrSQL.=")";
			if (!(mysqli_query(ConnDB(),$StrSQL))) {
				die;
			}

			$StrSQL="UPDATE DAT_MCONTACT SET ";
			$StrSQL.="STATUS = 'STATUS:否認',";
			$StrSQL.="EDITDATE = '".date("Y/m/d H:i:s")."' ";
			$StrSQL.="WHERE ID= ".$_GET['contact_id'];
			if (!(mysqli_query(ConnDB(),$StrSQL))) {
				die;
			}

			// メール送信
			$mids = explode(',', $word);
			SendMail($mids[0], $item_mcontact['STATUS'], '否認');
			SendMail($mids[1], $item_mcontact['STATUS'], '否認');

			$mode="list";
		} // すでに回答済みでないかどうか
	}

	//お問い合わせ・資料請求
	if($_GET["param"]=="contact"){
		$StrSQL="SELECT * FROM DAT_MESSAGE where AID='".$word."' and RID ='".$_SESSION['MID']."' and ETC02='".$_GET['etc02']."' and ifnull(ETC03,'')='".$_GET['etc03']."' order by ID desc;";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);
		if($item["ID"]==""){
			//メッセージ未送信の場合
			$mode="send";
			$_POST['COMMENT']="[メッセージが解放されました。]";
		}

	}



	if ($mode=="send"){

		// 2021.08.17 yamamoto エラーチェック復活1
		$error_msg = ErrorCheck();
		if($error_msg != '') {
			exit($error_msg);
		}

		//直近のメッセージ取得
		$StrSQL="SELECT * FROM DAT_MESSAGE where AID='".$word."' and RID ='".$_SESSION['MID']."' and ETC02='".$_GET['etc02']."' and ifnull(ETC03,'')='".$_GET['etc03']."' order by ID desc;";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);
		if($item["COMMENT"]!=$_POST['COMMENT']){

			// ファイル添付
			$file_msg = '';
			if(is_uploaded_file($_FILES['file1']['tmp_name'])){
				if(move_uploaded_file($_FILES['file1']['tmp_name'], "../files/".$_FILES['file1']['name'])) {
					if(trim(str_replace("'","''",htmlspecialchars($_POST['COMMENT'])))!=""){
						$file_msg = '<br><br>';
					}
					$file_msg .= '<!-- UPLOADED-FILE: --><a href="../files/'.$_FILES['file1']['name'].'" target="_blank">'.$_FILES['file1']['name'].'</a>';
				}
			}

			// 2020.12.23 yamamoto 案件ID(ETC02)追加
			if(trim(str_replace("'","''",htmlspecialchars($_POST['COMMENT'])))!="" || $file_msg != ''){

				$contact_newid = "";
				// ステータス取得
				$StrSQL="SELECT ID,STATUS FROM DAT_MCONTACT where MID='".$mid1."' and MIDT='".$mid2."'  and ETC02='".$_GET['etc02']."' and ifnull(ETC03,'')='".$_GET['etc03']."' order by ID desc;";
				$rs=mysqli_query(ConnDB(),$StrSQL);
				$item = mysqli_fetch_assoc($rs);
				if($item["ID"]==""){
					//DAT_MCONTACT　ステータス無しで登録　
					$StrSQL="INSERT INTO DAT_MCONTACT (MID, MIDT, STATUS, NEWDATE, ETC02, ETC03) values (";
					$StrSQL.="'".$mid1."',";
					$StrSQL.="'".$mid2."',";
					$StrSQL.="'STATUS:".FIRST_STATUS."',";
					$StrSQL.="'".date("Y/m/d H:i:s")."',";
					$StrSQL.="'".$_GET['etc02']."',";
					$StrSQL.="'".$_GET['etc03']."'";
					$StrSQL.=")";
					if (!(mysqli_query(ConnDB(),$StrSQL))) {
						die;
					}
					$StrSQL="SELECT ID,STATUS FROM DAT_MCONTACT where MID='".$mid1."' and MIDT='".$mid2."'  order by ID desc;";
					$rs=mysqli_query(ConnDB(),$StrSQL);
					$item = mysqli_fetch_assoc($rs);
					$contact_newid = $item['ID'];
				}




				$StrSQL="INSERT INTO DAT_MESSAGE (AID, RID, ENABLE, NEWDATE, COMMENT, ETC01, ETC02, ETC03, ETC10) values (";
				$StrSQL.="'".$word."',";
				$StrSQL.="'".$rid."',";
				$StrSQL.="'ENABLE:公開中',";
				$StrSQL.="'".date("Y/m/d H:i:s")."',";
				$StrSQL.="'".str_replace("'","''",htmlspecialchars($_POST['COMMENT'])).$file_msg."',";
				$StrSQL.="'".$contact_newid."',";
				$StrSQL.="'".$_GET['etc02']."',";
				$StrSQL.="'".$_GET['etc03']."',";
				$StrSQL.="'".$_GET['etc10']."'";
				$StrSQL.=")";
				if (!(mysqli_query(ConnDB(),$StrSQL))) {
					die;
				}

				//メール送信
				SendMailMsg($mid1,$mid2);
			}
		}
	}
// 2020.12.02 yamamoto 金額提示状況の取得
	$contact_status = FIRST_STATUS;
	$StrSQL="SELECT ID,STATUS FROM DAT_MCONTACT where MID='".$mid1."' and MIDT='".$mid2."' and ETC02='".$_GET['etc02']."' and ifnull(ETC03,'')='".$_GET['etc03']."' order by ID desc;";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$contact_id = '';
	if ($rs==true) {
		$item = mysqli_fetch_assoc($rs);
		if($item['STATUS'] == 'STATUS:申込' || $item['STATUS'] == 'STATUS:金額変更') {
			$contact_id = $item['ID'];
			$contact_status = '金額提示中';
		}
		else if($item['STATUS'] == 'STATUS:承認') {
			$contact_status = '金額承認済';
		}
		else if($item['STATUS'] == 'STATUS:否認') {
			$contact_status = '金額否認';
		}
		else if($item['STATUS'] != '') {
			$contact_status = str_replace('STATUS:', '', $item['STATUS']);
		}
	}

	switch ($mode){
		case "new":
			InitData();
			break;
		case "edit":
			LoadData($key);
			break;
		case "saveconf":
			$msg=ErrorCheck();
			if ($msg==""){
				$filename=$htmlconf;
				$msg01="保存";
				$msg02="save";
				$errmsg="";
			} else {
				$filename=$htmlerr;
				$msg01=$msg;
				$msg02="";
				$errmsg=$msg;
			}
			break;
			LoadData($key);
			RequestData($obj,$a,$b,$key,$mode);
			break;
		case "deleteconf":
			LoadData($key);
			break;
		case "save":
			// CSRFチェック OKならDB書き込み
			if ($_SESSION['token']==$token) {
				LoadData($key);
				RequestData($obj,$a,$b,$key,$mode);
				SaveData($key);
			}
			$mode="list";
			if ($page==""){
				$page=1;
			} 
			break;
		case "delete":
			// CSRFチェック OKならDB削除
			if ($_SESSION['token']==$token) {
				RequestData($obj,$a,$b,$key,$mode);
				DeleteData($key);
			}
			$mode="list";
			if ($page==""){
				$page=1;
			} 
			break;
		case "back":
			RequestData($obj,$a,$b,$key,$mode);
			$mode="edit";
			break;
		case "disp":
			LoadData($key);
			break;
		case "list":
			if ($page==""){
				$page=1;
			} 
			break;
	} 

	DispData($mode,$sort,$word,$key,$page,$lid,$token,$mid1,$mid2);

	return $function_ret;
} 
//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function SendMailMsg($mid1,$mid2)
{

	eval(globals());

	$StrSQL="SELECT * FROM DAT_M1 where MID='".$mid1."'";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$itemM1 = mysqli_fetch_assoc($rs);

	$StrSQL="SELECT * FROM DAT_M2 where MID='".$mid2."'";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$itemM2 = mysqli_fetch_assoc($rs);

	$maildata = GetMailTemplate('メッセージ通知（M1）');
	$MailBody = $maildata['BODY'];
	$subject = $maildata['TITLE'];

	$subject=str_replace("[M2_DVAL01]",$itemM2["M2_DVAL01"],$subject);
	$MailBody=str_replace("[M1_DVAL01]",$itemM1["M1_DVAL01"],$MailBody);

	mb_language("Japanese");
	mb_internal_encoding("UTF-8");

	

	$mailtoM1=$itemM1['EMAIL'];

	
	mb_send_mail($mailtoM1, $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 
	
}
//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function SendMail($mid, $status, $mode)
{

	eval(globals());

	$status =  str_replace('STATUS:', '', $status);

	// 相手のメールアドレスを取得
	$tbl = 'DAT_M' . ($_SESSION['MATT'] == '1' ? '2' : '1');

	$StrSQL="SELECT EMAIL FROM $tbl where MID='".$mid."'";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item = mysqli_fetch_assoc($rs);

	//$fp="mail.txt";
	//$MailBody=@file_get_contents($fp) . $item['EMAIL'];
	$maildata = GetMailTemplate('提示の承認/拒否');
	$MailBody = $maildata['BODY'];

	$MailBody=str_replace("[STATUS]",$status,$MailBody);
	$MailBody=str_replace("[MODE]",$mode,$MailBody);
	$MailBody=str_replace("[DATE]",date('Y-m-d H:i:s'),$MailBody);

	$subject = $maildata['TITLE'];
	$subject=str_replace("[STATUS]",$status,$subject);
	$subject=str_replace("[MODE]",$mode,$subject);

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
function DispData($mode,$sort,$word,$key,$page,$lid,$token,$mid1,$mid2)
{

	eval(globals());

	//各テンプレートファイル名
	$htmllist = "list.html";

	$fp=$DOCUMENT_ROOT.$htmllist;
	$str=@file_get_contents($fp);

	$StrSQL="UPDATE DAT_MESSAGE SET NOREAD='".$_SESSION['MID']."' WHERE AID='".$word."' and RID<>'".$_SESSION['MID']."' and ETC02='".$_GET['etc02']."' and ifnull(ETC03,'')='".$_GET['etc03']."'";
	if (!(mysqli_query(ConnDB(),$StrSQL))) {
		die;
	}

	$str = MakeHTML($str,1,$lid);

	$StrSQL="SELECT * FROM DAT_M1 where MID='".$mid1."';";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item = mysqli_fetch_assoc($rs);
	$str=DispM1($item, $str);

	$str=str_replace("[MID1]",$mid1,$str);
	$str=str_replace("[MID2]",$mid2,$str);
	$str=str_replace("[AID]",$word,$str);

	$StrSQL="SELECT CASE WHEN ifnull(EDITDATE,'')='' THEN NEWDATE ELSE EDITDATE END as NEWDATE  FROM DAT_MCONTACT where MID='".$mid1."' and MIDT='".$_SESSION['MID']."' and ETC02='".$_GET['etc02']."' and ifnull(ETC03,'')='".$_GET['etc03']."' order by CASE WHEN ifnull(EDITDATE,'')='' THEN NEWDATE ELSE EDITDATE END desc;";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item = mysqli_fetch_assoc($rs);
	$str=str_replace("[NEWDATE]",$item["NEWDATE"],$str);

	//最新ステータス取得
	$StrSQL="SELECT * FROM DAT_MCONTACT WHERE MID = '".$mid1."' AND MIDT = '".$_SESSION['MID']."' AND ETC02 = '".$_GET['etc02']."' and ifnull(ETC03,'')='".$_GET['etc03']."' order by id desc";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item = mysqli_fetch_assoc($rs); 
	$latest_status = str_replace("STATUS:","",$item["STATUS"]);
	$str=str_replace("[LATEST_STATUS]",$contact_status,$str);

	// 2020.12.02 yamamoto 金額提示状況
	global $contact_id;
	global $contact_status;
	global $TITLE;
	$str=str_replace("[CONTACT_ID]",$contact_id,$str);
	$str=str_replace("[CONTACT_STATUS]",$contact_status,$str);
	$str=str_replace("[TITLE]",$TITLE,$str);
	$str=str_replace("[ETC02]",$_GET['etc02'],$str);
	$str=str_replace("[ETC03]",$_GET['etc03'],$str);
	$str=str_replace("[ETC10]",$_GET['etc10'],$str);
	$str=str_replace("[BASE_URL]",BASE_URL,$str);
	print $str;

	return $function_ret;
} 

//=========================================================================================================
//名前 データリクエストパラメータ処理
//機能 データリクエストパラメータの処理と画像の保存
//引数 $obj,$a,$b,$key,$mode
//戻値 $function_ret;
//=========================================================================================================
function RequestData($obj,$a,$b,$key,$mode)
{
	eval(globals());

	// HTMLエスケープ処理（リクエストパラメータ）
	// クロスサイトスクリプティング対策
	for ($i=0; $i<=$FieldMax; $i=$i+1) {
		if ($FieldAtt[$i]==3) {
			if (isset($_POST[$FieldName[$i]])) {
				$postVal = $_POST[$FieldName[$i]];
				if(is_string($postVal) && strstr($postVal,"\t") !== false) {
					$FieldValue[$i]=htmlspecialchars($postVal);
				} else {
					$FieldValue[$i]="";
					if (is_array($postVal)) {
						for ($j=0; $j<count($postVal); $j=$j+1) {
						if ($j!=0) {
							$FieldValue[$i]=$FieldValue[$i]."\t";
						}
						$FieldValue[$i]=$FieldValue[$i].$postVal[$j];
						}
					}
				}
			}
		} else {
			$FieldValue[$i]=htmlspecialchars(str_replace("\\","",($_POST[$FieldName[$i]] ?? '')));
		}
		if ($FieldAtt[$i]==4 && $mode=="save") {
			$exts = split("[/\\.]", $_FILES["EP_".$FieldName[$i]]['name']);
			$n = count($exts) - 1;
			$extention = $exts[$n];
			if ($extention=="jpeg") {
				$extention="jpg";
			} 

			if ($extention!="" && !!isset($extention)) {
				$filename=$FieldName[$i]."-".date("YmdHis").".".$extention;
				$FieldValue[$i]=$filepath1.$filename;
			} else {
				if ($FieldValue[$i]=="" || !isset($FieldValue[$i])) {
					$filename="s.gif";
					$FieldValue[$i]=$filepath1.$filename;
				} 
			} 
			if (($_POST["DEL_IMAGE_".$FieldName[$i]] ?? '')=="on") {
				$filename="s.gif";
				$FieldValue[$i]=$filepath1.$filename;
			}
			if ($filename!="s.gif" && ($extention!="" && !!isset($extention))) {
				move_uploaded_file($_FILES["EP_".$FieldName[$i]]["tmp_name"], "data/".$filename);
			} 
		} 
	}

	return $function_ret;
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
//名前 DB書き込み
//機能 DBにレコードを保存
//引数 $key
//戻値 $function_ret
//=========================================================================================================
function SaveData($key)
{
	eval(globals());

	// SQLインジェクション対策
	// HTMLエスケープ処理（SQL書き込み）
	$StrSQL="SELECT * FROM ".$TableName." WHERE `".$FieldName[$FieldKey]."`='".mysqli_real_escape_string(ConnDB(),$key)."';";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item=mysqli_num_rows($rs);
	if($item==0) {
		$StrSQL="INSERT INTO ".$TableName." (";
		for ($i=1; $i<=$FieldMax; $i++) {
			if($i>1){
				$StrSQL.=",";
			}
			$StrSQL.="`".$FieldName[$i]."`";
		}
		$StrSQL=$StrSQL.") VALUES (";
		for ($i=1; $i<=$FieldMax; $i++) {
			if($i>1){
				$StrSQL.=",";
			}
			$StrSQL.="'".str_replace("'","''",htmlspecialchars($FieldValue[$i]))."'";
		}
		$StrSQL=$StrSQL.")";
	} else {
		$StrSQL="UPDATE ".$TableName." SET ";
		for ($i=1; $i<=$FieldMax; $i++) {
			if($i>1){
				$StrSQL.=",";
			}
			$StrSQL.="`".$FieldName[$i]."`='".str_replace("'","''",htmlspecialchars($FieldValue[$i]))."'";
		}
		$StrSQL=$StrSQL." WHERE ".$FieldName[$FieldKey]."='".$key."'";
	} 
	if (!(mysqli_query(ConnDB(),$StrSQL))) {
		die;
	}

	return $function_ret;
} 

//=========================================================================================================
//名前 DB削除
//機能 DBからレコードを削除
//引数 $key
//戻値 $function_ret
//=========================================================================================================
function DeleteData($key)
{
	eval(globals());

	// SQLインジェクション対策
	$StrSQL="DELETE FROM ".$TableName." WHERE ".$FieldName[$FieldKey]."='".mysqli_real_escape_string(ConnDB(),$key)."';";
	if (!(mysqli_query(ConnDB(),$StrSQL))) {
		die;
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
