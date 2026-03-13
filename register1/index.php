<?php

require "../config.php";
require "../base.php";
require '../a_m1/config.php';

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

	if(!isset($_POST['mode']) || $_POST['mode']===""){
		$mode=mysqli_real_escape_string(ConnDB(),$_GET['mode'] ?? '');
		$sort=mysqli_real_escape_string(ConnDB(),$_GET['sort'] ?? '');
		$word=mysqli_real_escape_string(ConnDB(),$_GET['word'] ?? '');
		$key=mysqli_real_escape_string(ConnDB(),$_GET['key'] ?? '');
		$page=mysqli_real_escape_string(ConnDB(),$_GET['page'] ?? '');
		$lid=mysqli_real_escape_string(ConnDB(),$_GET['lid'] ?? '');
		$token=mysqli_real_escape_string(ConnDB(),$_GET['token'] ?? '');
		$mid=$_GET['mid'] ?? '';
		$email=$_GET['email'] ?? '';
	} else {
		$mode=mysqli_real_escape_string(ConnDB(),$_POST['mode'] ?? '');
		$sort=mysqli_real_escape_string(ConnDB(),$_POST['sort'] ?? '');
		$word=mysqli_real_escape_string(ConnDB(),$_POST['word'] ?? '');
		$key=mysqli_real_escape_string(ConnDB(),$_POST['key'] ?? '');
		$page=mysqli_real_escape_string(ConnDB(),$_POST['page'] ?? '');
		$lid=mysqli_real_escape_string(ConnDB(),$_POST['lid'] ?? '');
		$token=mysqli_real_escape_string(ConnDB(),$_POST['token'] ?? '');
		$mid=$_POST['mid'] ?? '';
		$email=$_POST['email'] ?? '';
	}

	if ($mode==""){
		$mode="new";
	}
	if ($mode=="email"){
		$mode="end";
	}
// ソーシャル会員登録用
	if(($_GET['status'] ?? '')=="authorized"){
		$apikey="SP-APIKEY";
		$token=$_GET['token'] ?? '';
		$response=file_get_contents("https://api.socialplus.jp/api/authenticated_user?key=".$apikey."&token=".$token."&add_profile=true&delete_profile=true");
		$val=json_decode($response, true);
		$lineid=$val['user']['identifier'];
		$StrSQL="SELECT ID from DAT_M1 where SOCIALID='".$lineid."';";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item=mysqli_num_rows($rs);
		if($item>0){
			$filename = "err2.html";
			$fp=$DOCUMENT_ROOT.$filename;
			$str=@file_get_contents($fp);
			$str = MakeHTML($str,0,$lid);
			$str=str_replace("[BASE_URL]",BASE_URL,$str);
	print $str;
			exit;
		}

	}

	switch ($mode){
		case "new":
			InitData();
			if(($_GET['status'] ?? '')=="authorized"){
				$FieldValue[4]=$lineid;
			}
			break;
		case "edit":
			LoadData($key);
			RequestData($obj,$a,$b,$key,$mode);
			break;
		case "saveconf":
			LoadData($key);
			RequestData($obj,$a,$b,$key,$mode);

			$StrSQL="SELECT ID from DAT_M1 where EMAIL='".$FieldValue[2]."';";
			$rs=mysqli_query(ConnDB(),$StrSQL);
			$item=mysqli_num_rows($rs);
			if($item>0){
				$filename = "err1.html";
				$fp=$DOCUMENT_ROOT.$filename;
				$str=@file_get_contents($fp);
				$str = MakeHTML($str,0,$lid);
				$str=str_replace("[BASE_URL]",BASE_URL,$str);
	print $str;
				exit;
			}

			$StrSQL="SELECT ID from DAT_M1 where EMAIL='".$FieldValue[2]."';";
			$rs=mysqli_query(ConnDB(),$StrSQL);
			$item=mysqli_num_rows($rs);
			if($item>0){
				$filename = "err1.html";
				$fp=$DOCUMENT_ROOT.$filename;
				$str=@file_get_contents($fp);
				$str = MakeHTML($str,0,$lid);
				$str=str_replace("[BASE_URL]",BASE_URL,$str);
	print $str;
				exit;
			}

			break;
		case "deleteconf":
			LoadData($key);
			break;
		case "save":
			// CSRFチェック OKならDB書き込み
//			if ($_SESSION['token']==$token) {

				RequestData($obj,$a,$b,$key,$mode);

				// 2021.08.17 yamamoto エラーチェック復活1
				// $error_msg = ErrorCheck();
				// if($error_msg != '') {
				// 	break;
				// }

				// $StrSQL="SELECT MID from DAT_M1 order by MID desc limit 0,1;";
				// $rs=mysqli_query(ConnDB(),$StrSQL);
				// $item = mysqli_fetch_assoc($rs);
				// $FieldValue[1]="M1".sprintf("%05d", str_replace("M1", "", $item['MID'])+1);
				// $FieldValue[95]="ENABLE:非公開";
				// $FieldValue[96]=date("Y/m/d H:i:s");
				// $FieldValue[97]=date("Y/m/d H:i:s");
				// // $_SESSION['MATT'] = "1";
				// // $_SESSION['MID'] = $FieldValue[1];
				// // $_SESSION['MNAME'] = $FieldValue[5];
				// SaveData($key);
				SendMail();
//			}
			break;
		case "end":
			RequestData($obj,$a,$b,$key,$mode);

			$StrSQL="UPDATE DAT_M1 SET ";
			$StrSQL.=" ENABLE='ENABLE:公開中' ";
			$StrSQL.=" ,EDITDATE='".date("Y/m/d H:i:s")."' ";
			$StrSQL.=" WHERE MID = '".$mid."'";
			$StrSQL.=" AND EMAIL = '".$email."'";
			if (!(mysqli_query(ConnDB(),$StrSQL))) {
				die;
			}

			//セッション設定
			$StrSQL="SELECT * from DAT_M1 WHERE MID = '".$mid."'";
			$rs=mysqli_query(ConnDB(),$StrSQL);
			$item = mysqli_fetch_assoc($rs);
			$_SESSION['MATT'] = "1";
			$_SESSION['MID'] = $mid;
			$_SESSION['MNAME'] = $item["M1_DVAL01"];

			break;
		case "delete":
			// CSRFチェック OKならDB削除
			if ($_SESSION['token']==$token) {
				RequestData($obj,$a,$b,$key,$mode);
				DeleteData($key);
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
		case "export":
			ExportData();
			exit;
		case "import":
			ImportData($obj,$a,$b,$key,$mode);
			$mode="list";
			break;
	} 

	DispData($mode,$sort,$word,$key,$page,$lid,$token);

	return $function_ret;
} 

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function SendMail()
{

	eval(globals());

	//$fp="mail.txt";
	//$MailBody=@file_get_contents($fp);
	$maildata = GetMailTemplate('会員仮登録(M1)');
	$MailBody = $maildata['BODY'];
	$subject = $maildata['TITLE'];

	$MailBody=str_replace("[EMAIL]",strEncrypt($FieldValue[2]),$MailBody);
	$MailBody=str_replace("[EMAIL2]",$FieldValue[2],$MailBody);
  	$MailBody=str_replace("[NEWDATE]",strEncrypt(date("Y/m/d H:i:s")),$MailBody);
	// $MailBody=str_replace("[PASSWORD]",$FieldValue[3],$MailBody);
	// $MailBody=str_replace("[MID]",$FieldValue[1],$MailBody);
	$MailBody=str_replace("[M1_DVAL01]","ご登録者",$MailBody);

	mb_language("Japanese");
	mb_internal_encoding("UTF-8");

	mb_send_mail($FieldValue[2], $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 
	mb_send_mail(SENDER_EMAIL, $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 
}

//=========================================================================================================
//名前 画面表示処理
//機能 Modeによって画面表示
//引数 $mode,$sort,$word,$key,$page,$lid
//戻値 なし
//=========================================================================================================
function DispData($mode,$sort,$word,$key,$page,$lid,$token)
{

	eval(globals());

	//各テンプレートファイル名
	if(($_GET['status'] ?? '')!="authorized"){
		$htmlnew = "edit.html";
		$htmlerr = "edit.html";
		$htmledit = "edit.html";
		$htmlconf = "conf.html";
		$htmlend = "end.html";
		$htmlend2 = "end2.html";
		$htmldisp = "disp.html";
		$htmllist = "list.html";
	} else {
		$htmlnew = "editl.html";
		$htmlerr = "editl.html";
		$htmledit = "editl.html";
		$htmlconf = "conf.html";
		$htmlend = "end.html";
		$htmlend2 = "end2.html";
		$htmldisp = "disp.html";
		$htmllist = "list.html";
	}

	switch ($mode){
		case "new":
			$filename=$htmlnew;
			$msg01="";
			$msg02="";
			$errmsg="";
			break;
		case "edit":
			$filename=$htmledit;
			$msg01="";
			$msg02="";
			$errmsg="";
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
		case "deleteconf":
			$filename=$htmlconf;
			$msg01="削除";
			$msg02="delete";
			$errmsg="";
			break;
		case "save":

			// 2021.08.17 yamamoto エラーチェック復活2
			$error_msg = ErrorCheck();
			if($error_msg != '') {
				$filename=$htmlerr;
				$msg01=$error_msg;
				$msg02="";
				$errmsg=$error_msg;
				break;
			}

			$filename=$htmlend;

			$msg01="保存";
			$msg02="";
			$errmsg="";
			break;
		case "end":
			$filename=$htmlend2;
			$msg01="保存";
			$msg02="";
			$errmsg="";
			break;
		case "delete":
			$filename=$htmlend;
			$msg01="削除";
			$msg02="";
			$errmsg="";
			break;
		case "disp":
			$filename=$htmldisp;
			$msg01="";
			$msg02="";
			$errmsg="";
			break;
	} 

	$fp=$DOCUMENT_ROOT.$filename;
	$str=@file_get_contents($fp);

	$str = MakeHTML($str,0,$lid);

	if ($mode=="new"){
		$str=DispParam($str, "NEWDATA");
		$str=DispParamNone($str, "EDITDATA");
	} else {
		$str=DispParamNone($str, "NEWDATA");
		$str=DispParam($str, "EDITDATA");
	} 

	for ($i=0; $i<=$FieldMax; $i=$i+1){
		if ($FieldAtt[$i]==4){
			if ($FieldValue[$i]==""){
				$str=str_replace("[".$FieldName[$i]."]",$filepath1."s.gif",$str);
				$str=str_replace("[D-".$FieldName[$i]."]",$filepath1."s.gif",$str);
			} 

			if(strstr($FieldValue[$i],"s.gif") !== false){
				$str=DispParamNone($str, $FieldName[$i]);
			} else {
				$str=DispParam($str, $FieldName[$i]);
			} 
		} else {
			if ($FieldValue[$i]==""){
				$str=DispParamNone($str, $FieldName[$i]);
			} else {
				$str=DispParam($str, $FieldName[$i]);
			} 
		} 
		// HTMLエスケープ処理（詳細表示系、HIDDEN値）
		$str=str_replace("[".$FieldName[$i]."]",htmlspecialchars($FieldValue[$i]),$str);
		$str=str_replace("[D-".$FieldName[$i]."]",str_replace("\r\n","<br>",str_replace($FieldName[$i].":","",htmlspecialchars($FieldValue[$i]))),$str);
		if ($FieldAtt[$i]=="1"){
			$strtmp="";
			$strtmp=$strtmp."<option value=''>▼選択して下さい</option>";
			$tmp=explode("::",$FieldParam[$i]);
			for ($j=0; $j<count($tmp); $j=$j+1) {
				$strtmp=$strtmp."<option value='".$FieldName[$i].":".$tmp[$j]."'>".$tmp[$j]."</option>";

			}

			$str=str_replace("[OPT-".$FieldName[$i]."]",$strtmp,$str);
			if (($filename==$htmlerr || $mode=="new" || $mode=="edit") && $FieldValue[$i]!="") {

				$str=str_replace("'".$FieldValue[$i]."'","'".$FieldValue[$i]."' selected",$str);
			} 
		} 

		if ($FieldAtt[$i]=="2"){
			$strtmp="";
			$tmp=explode("::",$FieldParam[$i]);
			$strtmp=$strtmp."<ul>";
			for ($j=0; $j<count($tmp); $j=$j+1) {
				$strtmp=$strtmp."<li><input id=\"".$FieldName[$i].$j."\" type=\"radio\" name=\"".$FieldName[$i]."\" value=\"".$FieldName[$i].":".$tmp[$j]."\"><label for=\"".$FieldName[$i].$j."\">".$tmp[$j]."</label></li>";
			}
			$strtmp=$strtmp."</ul>";
			$str=str_replace("[OPT-".$FieldName[$i]."]",$strtmp,$str);
			if (($filename==$htmlerr || $mode=="new" || $mode=="edit") && $FieldValue[$i]!="") {
				$str=str_replace("\"".$FieldValue[$i]."\"","\"".$FieldValue[$i]."\" checked",$str);
			} 
		} 

		if ($FieldAtt[$i]=="3"){
			$strtmp="";
			$tmp=explode("::",$FieldParam[$i]);
			$strtmp=$strtmp."<ul class='mlist25p'>";
			for ($j=0; $j<count($tmp); $j=$j+1) {
				$strtmp=$strtmp."<li><input id=\"".$FieldName[$i].$j."\" type=\"checkbox\" name=\"".$FieldName[$i]."[]\" value=\"".$FieldName[$i].":".$tmp[$j]."\" required><label for=\"".$FieldName[$i].$j."\">".$tmp[$j]."</label></li>";
			}
			$strtmp=$strtmp."</ul>";
			$str=str_replace("[OPT-".$FieldName[$i]."]",$strtmp,$str);
			if (($filename==$htmlerr || $mode=="new" || $mode=="edit") && $FieldValue[$i]!="") {
				$tmp=explode("\t",$FieldValue[$i]);
				for ($j=0; $j<count($tmp); $j=$j+1) {
					$str=str_replace("\"".$tmp[$j]."\"","\"".$tmp[$j]."\" checked",$str);
				}
			} 
		} 

		if (is_numeric($FieldValue[$i])) {
			$str=str_replace("[N-".$FieldName[$i]."]",number_format($FieldValue[$i],0),$str);
		} else {
			$str=str_replace("[N-".$FieldName[$i]."]","",$str);
		} 
	}

	$str=str_replace("[MSG]",$msg01,$str);
	$str=str_replace("[NEXTMODE]",$msg02,$str);
	if($errmsg<>""){
		$str=str_replace("[ERRMSG]",$errmsg,$str);
		$str=DispParam($str, "ERR");
	} else {
		$str=DispParamNone($str, "ERR");
	}
	$str=str_replace("[SORT]",$sort,$str);
	$str=str_replace("[WORD]",$word,$str);
	$str=str_replace("[PAGE]",$page,$str);
	$str=str_replace("[KEY]",$key,$str);
	$str=str_replace("[LID]",$lid,$str);

	// CSRFトークン生成
	if($token==""){
		$token=htmlspecialchars(session_id());
		$_SESSION['token'] = $token;
	}
	$str=str_replace("[TOKEN]",$token,$str);

	$str=str_replace("[BASE_URL]",BASE_URL,$str);

	$str=str_replace("[PASS_CHECK]",'',$str);

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
		if ($FieldAtt[$i]==4 && ($mode=="saveconf")) {
			$filename = $_FILES["EP_".$FieldName[$i]]['name'];
			$extention = pathinfo($filename, PATHINFO_EXTENSION);


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

				move_uploaded_file($_FILES["EP_".$FieldName[$i]]["tmp_name"], $filedir1 . $filename);

				pic_resize($FieldValue[$i], 800,800);
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
			if($FieldName[$i]=="PASS"){
				$StrSQL.="'".str_replace("'","''",pwd_hash($FieldValue[$i]))."'";
			} else {
				$StrSQL.="'".str_replace("'","''",htmlspecialchars($FieldValue[$i]))."'";
			}
		}
		$StrSQL=$StrSQL.")";
	} else {
		$StrSQL="UPDATE ".$TableName." SET ";
		for ($i=1; $i<=$FieldMax; $i++) {
			if($i>1){
				$StrSQL.=",";
			}
			if($FieldName[$i]=="PASS"){
				$StrSQL.="`".$FieldName[$i]."`='".str_replace("'","''",pwd_hash($FieldValue[$i]))."'";
			} else {
				$StrSQL.="`".$FieldName[$i]."`='".str_replace("'","''",htmlspecialchars($FieldValue[$i]))."'";
			}
		}
		$StrSQL=$StrSQL." WHERE ".$FieldName[$FieldKey]."='".$key."'";
	} 
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
