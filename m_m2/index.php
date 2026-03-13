<?php

require "../config.php";
require "../base.php";
require "../common.php";
require '../a_m2/config.php';

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
	if(!CheckSession(2)) {
		$url=BASE_URL . "/login2/";
		header("Location: {$url}");
		exit;
	}

	if(!isset($_POST['mode']) || $_POST['mode']===""){
		$mode=mysqli_real_escape_string(ConnDB(),$_GET['mode'] ?? '');
		$sort=mysqli_real_escape_string(ConnDB(),$_GET['sort'] ?? '');
		$word=mysqli_real_escape_string(ConnDB(),$_GET['word'] ?? '');
		$key=mysqli_real_escape_string(ConnDB(),$_GET['key'] ?? '');
		$page=mysqli_real_escape_string(ConnDB(),$_GET['page'] ?? '');
		$lid=mysqli_real_escape_string(ConnDB(),$_GET['lid'] ?? '');
		$token=mysqli_real_escape_string(ConnDB(),$_GET['token'] ?? '');
	} else {
		$mode=mysqli_real_escape_string(ConnDB(),$_POST['mode'] ?? '');
		$sort=mysqli_real_escape_string(ConnDB(),$_POST['sort'] ?? '');
		$word=mysqli_real_escape_string(ConnDB(),$_POST['word'] ?? '');
		$key=mysqli_real_escape_string(ConnDB(),$_POST['key'] ?? '');
		$page=mysqli_real_escape_string(ConnDB(),$_POST['page'] ?? '');
		$lid=mysqli_real_escape_string(ConnDB(),$_POST['lid'] ?? '');
		$token=mysqli_real_escape_string(ConnDB(),$_POST['token'] ?? '');
	}

	if ($mode==""){
		$mode="edit";
	}

	if ($key==""){
		$StrSQL="SELECT ID from DAT_M2 where MID='".$_SESSION['MID']."' and ENABLE='ENABLE:公開中';";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item=mysqli_num_rows($rs);
		if($item>0){
			$item = mysqli_fetch_assoc($rs);
			$key = $item['ID'];
		} else {
			$url=BASE_URL . "/login1/";
			header("Location: {$url}");
			exit;
		}
	}

	switch ($mode){
		case "new":
			InitData();
			break;
		case "edit":
			LoadData($key);
//			RequestData($obj,$a,$b,$key,$mode);
			break;
		case "save":
			LoadData($key);
			RequestData($obj,$a,$b,$key,$mode);
			$msg = ErrorCheckM2();

			// CSRFチェック OKならDB書き込み
			if ($_SESSION['token']==$token&&$msg=="") {


				// 2021.08.17 yamamoto エラーチェック復活1
				$error_msg = ErrorCheck();
				if($error_msg != '') {
					break;
				}
	
				SaveData($key);

				$_SESSION['MNAME'] = $FieldValue[5];

// 要調整
/*
				$StrSQL="DELETE FROM DAT_MATCH where MID='".$FieldValue[1]."';";
				if (!(mysqli_query(ConnDB(),$StrSQL))) {
					die;
				}
				$tmp="";
				$StrSQL="SELECT DAT_JOB.HID, DAT_JOB.JID FROM DAT_JOB inner join DAT_HOSPITAL on DAT_HOSPITAL.HID=DAT_JOB.HID order by DAT_JOB.JID;";
				$rs=mysqli_query(ConnDB(),$StrSQL);
				while ($item = mysqli_fetch_assoc($rs)) {
					if($tmp!=""){
						$tmp.="::";
					}
					$tmp.=$item['JID'];
				}
				$jids=explode("::", $tmp);

				for($j=0; $j<count($jids); $j++){
					CulcMatching($FieldValue[1], $jids[$j], 0);
				}
*/
// 要調整

			}
			break;
		case "back":
			RequestData($obj,$a,$b,$key,$mode);
			$mode="edit1";
			break;
	} 

	DispData($mode,$sort,$word,$key,$page,$lid,$token);

	return $function_ret;
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
	$htmlnew = "edit.html";
	$htmledit = "edit.html";
	$htmlconf = "conf.html";
	$htmlend = "end.html";
	$htmldisp = "disp.html";
	$htmlerr = "edit.html";
	$htmllist = "list.html";

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
			$msg = ErrorCheckM2();
			if($msg != "") {
				$filename=$htmlerr;
				$msg01=$msg;
				$msg02="";
				$errmsg=$msg;
				break;
			}


			$filename=$htmlend;
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

	$str = MakeHTML($str,1,$lid);

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
			if (($filename==$htmlerr || $mode=="new" || $mode=="edit1" || $mode=="edit2" || $mode=="edit3" || $mode=="edit4" || $mode=="edit5" || $mode=="edit6" || $mode=="edit7" || $mode=="edit8") && $FieldValue[$i]!="") {

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
			if (($filename==$htmlerr || $mode=="new" || $mode=="edit1" || $mode=="edit2" || $mode=="edit3" || $mode=="edit4" || $mode=="edit5" || $mode=="edit6" || $mode=="edit7" || $mode=="edit8") && $FieldValue[$i]!="") {
				$str=str_replace("\"".$FieldValue[$i]."\"","\"".$FieldValue[$i]."\" checked",$str);
			} 
		} 

		if ($FieldAtt[$i]=="3"){
			$strtmp="";
			$tmp=explode("::",$FieldParam[$i]);
			$strtmp=$strtmp."<ul class='mlist25p'>";
			for ($j=0; $j<count($tmp); $j=$j+1) {
				$strtmp=$strtmp."<li><input id=\"".$FieldName[$i].$j."\" type=\"checkbox\" name=\"".$FieldName[$i]."[]\" value=\"".$FieldName[$i].":".$tmp[$j]."\"><label for=\"".$FieldName[$i].$j."\">".$tmp[$j]."</label></li>";
			}
			$strtmp=$strtmp."</ul>";
			$str=str_replace("[OPT-".$FieldName[$i]."]",$strtmp,$str);
			if (($filename==$htmlerr || $mode=="new" || $mode=="edit1" || $mode=="edit2" || $mode=="edit3" || $mode=="edit4" || $mode=="edit5" || $mode=="edit6" || $mode=="edit7" || $mode=="edit8") && $FieldValue[$i]!="") {
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
	print $str;

	return $function_ret;
} 
//=========================================================================================================
//名前 入力後のエラーチェック（エラーがない場合は空を指定）
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function ErrorCheckM2()
{
	//extract($GLOBALS);
	eval(globals());

	$function_ret="";

	//登録前情報の読み込み
	$StrSQL="SELECT * FROM ".$TableName." WHERE ID = '".$FieldValue[0]."';";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item = mysqli_fetch_assoc($rs);

	//メールアドレスが変更されている場合
	if($item["EMAIL"]!=$FieldValue[2]){
		// メールアドレス重複チェック
		$StrSQL2="SELECT * FROM ".$TableName." WHERE EMAIL = '".$FieldValue[2]."' and ID != '".$FieldValue[0]."'";
		$rs2=mysqli_query(ConnDB(),$StrSQL2);
		$item2 = mysqli_fetch_assoc($rs2);
		if($item2["ID"]!=""){
			$function_ret.="<span >メールアドレスが重複しています。</span><br>";
		}
	}


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
			if (isset($_POST[$FieldName[$i]])) {
				$FieldValue[$i]=htmlspecialchars(str_replace("\\","",($_POST[$FieldName[$i]] ?? '')));
			}

		}
		if ($FieldAtt[$i]==4 && $mode=="save") {
			$file = pathinfo($_FILES["EP_".$FieldName[$i]]['name'] ?? '');
			$extention=$file['extension'] ?? '';

			if ($extention!="" && !!isset($extention)) {
				//特殊文字削除
				$filename=$FieldName[$i]."-".date("YmdHis");
				$filename = preg_replace("/[^ぁ-んァ-ンーa-zA-Z0-9一-龠０-９\-\r]+/u",'A' ,$filename).".".$extention;
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
				// 2021.08.18 yamamoto エラーなのにアップロードされる問題の対応
				if(ErrorCheck() == '') {
					move_uploaded_file($_FILES["EP_".$FieldName[$i]]["tmp_name"], "../a_m2/data/".$filename);
					pic_resize("data/".$filename, 800,800);
				}
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

	if($FieldValue[1] != $_SESSION['MID']) {
		exit('SESSION ERROR');
	}


	// SQLインジェクション対策
	// HTMLエスケープ処理（SQL書き込み）
	$StrSQL="SELECT * FROM ".$TableName." WHERE `".$FieldName[$FieldKey]."`='".mysqli_real_escape_string(ConnDB(),$key)."';";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item=mysqli_num_rows($rs);
	if($item==0) {
		$FieldValue[96]=date("Y/m/d H:i:s");
		$FieldValue[97]=date("Y/m/d H:i:s");

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
		$FieldValue[97]=date("Y/m/d H:i:s");

		$StrSQL="UPDATE ".$TableName." SET ";
		for ($i=1; $i<=$FieldMax; $i++) {

			if($FieldName[$i]=="PASS"){
				// $StrSQL.="`".$FieldName[$i]."`='".str_replace("'","''",pwd_hash($FieldValue[$i]))."'";
			} else {
				if($i>1){
					$StrSQL.=",";
				}
				$StrSQL.="`".$FieldName[$i]."`='".str_replace("'","''",htmlspecialchars($FieldValue[$i]))."'";
			}
		}
		$StrSQL=$StrSQL." WHERE ".$FieldName[$FieldKey]."='".$key."'";
	} 
	// echo "<!--".$StrSQL."-->";
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
