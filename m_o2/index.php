<?php

require "../config.php";
require "../base.php";
require "../common.php";
require '../a_o2/config.php';

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
		$mode="list";
	}

	$word=$_SESSION['MID'];

	switch ($mode){
		case "new":
			InitData();
//			RequestData($obj,$a,$b,$key,$mode);
			break;
		case "edit":
			LoadData($key);
			RequestData($obj,$a,$b,$key,$mode);
			break;
		case "saveconf":
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

				// 2021.08.17 yamamoto エラーチェック復活1
				$error_msg = ErrorCheck();
				if($error_msg != '') {
					break;
				}

				SaveData($key);

				$StrSQL="DELETE FROM DAT_MATCH where MID2='".$_SESSION['MID']."';";
				if (!(mysqli_query(ConnDB(),$StrSQL))) {
					die;
				}

				$tmp="";
				$StrSQL="SELECT OID FROM DAT_O1 order by OID;";
				$rs=mysqli_query(ConnDB(),$StrSQL);
				while ($item = mysqli_fetch_assoc($rs)) {
					if($tmp!=""){
						$tmp.="::";
					}
					$tmp.=$item['OID'];
				}
				$cid1s=explode("::", $tmp);

				$tmp="";
				$StrSQL="SELECT OID FROM DAT_O2 where MID='".$_SESSION['MID']."' order by OID;";
				$rs=mysqli_query(ConnDB(),$StrSQL);
				while ($item = mysqli_fetch_assoc($rs)) {
					if($tmp!=""){
						$tmp.="::";
					}
					$tmp.=$item['OID'];
				}
				$cid2s=explode("::", $tmp);

				for($i=0; $i<count($cid1s); $i++){
					for($j=0; $j<count($cid2s); $j++){
						CulcMatching($cid1s[$i], $cid2s[$j], 0);
					}
				}

				$mode="list";
				if ($page==""){
					$page=1;
				}

				$_SESSION['token']="";
			}
			break;
		case "disable":
			LoadData($key);
			$FieldValue[93]="ENABLE:非公開";
			SaveData($key);
			$mode="list";
			if ($page==""){
				$page=1;
			}
			break;
		case "enable":
			LoadData($key);
			$FieldValue[93]="ENABLE:公開中";
			SaveData($key);
			$mode="list";
			if ($page==""){
				$page=1;
			}
			break;
		case "delete":
			RequestData($obj,$a,$b,$key,$mode);
			DeleteData($key);

// 要調整
/*
			$StrSQL="DELETE FROM DAT_MATCH where JID='".$FieldValue[1]."';";
			if (!(mysqli_query(ConnDB(),$StrSQL))) {
				die;
			}
*/
// 要調整

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

	if ($mode!="list"){
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

		$StrSQL="SELECT * FROM DAT_M2 where MID='".$_SESSION['MID']."'";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$itemM2 = mysqli_fetch_assoc($rs);
		

		$pref=str_replace("M2_MSEL01:","",$itemM2["M2_MSEL01"]);
		$StrSQL="SELECT CD1 FROM DAT_ADDRESS WHERE N1='".str_replace("M2_MSEL01:","",$itemM2["M2_MSEL01"])."'";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);
		$cd1=$item["CD1"];

		//沿線
		$FieldParam[75]="";
		$tmp="";
		$StrSQL="SELECT CD2, N2 FROM DAT_ROSEN WHERE PREFCD = '".$cd1."' group by CD2, N2 order by CD2";
		$rs=mysqli_query(ConnDB(), $StrSQL);
		while ($item = mysqli_fetch_assoc($rs)) {
			if($tmp!=""){
				$tmp.="::";
			}
			$val=$item['N2'];
			$tmp.=$val;
		}
		$FieldParam[75]=$tmp;


		//最寄り駅
		$FieldParam[76]="";
		$tmp="";
		//$StrSQL="SELECT CD4, N3 FROM DAT_ROSEN WHERE PREFCD = '".$cd1."' AND N2 = '".str_replace("O1_MRDO04:","",$FieldValue[76])."' group by CD4, N3 order by CD4";
		$StrSQL="SELECT CD4, N3 FROM DAT_ROSEN WHERE PREFCD = '".$cd1."' group by CD4, N3 order by CD4";
		$rs=mysqli_query(ConnDB(), $StrSQL);
		while ($item = mysqli_fetch_assoc($rs)) {
			if($tmp!=""){
				$tmp.="::";
			}
			$val=$item['N3']."駅";
			$tmp.=$val;
		}
		$FieldParam[76]=$tmp;

		
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
				if (($filename==$htmlerr || $mode=="new" || $mode=="edit") && $FieldValue[$i]!="") {

					$str=str_replace("'".$FieldValue[$i]."'","'".$FieldValue[$i]."' selected",$str);
				}
			}

			if ($FieldAtt[$i]=="2"){
				$strtmp="";
				$tmp=explode("::",$FieldParam[$i]);
				$strtmp=$strtmp."<ul>";
				for ($j=0; $j<count($tmp); $j=$j+1) {
					$strtmp=$strtmp."<li><input id=\"".$FieldName[$i].$j."\" type=\"radio\" name=\"".$FieldName[$i]."\" class=\"".$FieldName[$i]."\" value=\"".$FieldName[$i].":".$tmp[$j]."\"><label for=\"".$FieldName[$i].$j."\">".$tmp[$j]."</label></li>";
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
					if($FieldValue[$i]==""){
						$strtmp=$strtmp."<li><input id=\"".$FieldName[$i].$j."\" type=\"checkbox\" name=\"".$FieldName[$i]."[]\" class=\"".$FieldName[$i]."\" value=\"".$FieldName[$i].":".$tmp[$j]."\"><label for=\"".$FieldName[$i].$j."\">".$tmp[$j]."</label></li>";
					} else {
						$strtmp=$strtmp."<li><input id=\"".$FieldName[$i].$j."\" type=\"checkbox\" name=\"".$FieldName[$i]."[]\" class=\"".$FieldName[$i]."\" value=\"".$FieldName[$i].":".$tmp[$j]."\"><label for=\"".$FieldName[$i].$j."\">".$tmp[$j]."</label></li>";
					}
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
		$str=str_replace("[M2_PREF]",$pref,$str);

		// CSRFトークン生成
		if($token==""){
			$token=htmlspecialchars(session_id());
			$_SESSION['token'] = $token;
		}
		$str=str_replace("[TOKEN]",$token,$str);

		$str=str_replace("[BASE_URL]",BASE_URL,$str);
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

		// SQLインジェクション対策
        	$reccount=0;

		$StrSQL="SELECT * FROM ".$TableName." ".ListSql(mysqli_real_escape_string(ConnDB(),$sort),mysqli_real_escape_string(ConnDB(),$word)).";";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item=mysqli_num_rows($rs);
		if($item==0) {
			$pagestr="";
			$strMain="<tr class=tableset__list><td align=center colspan=7>対象データがありません。</td></tr>";
		} else {
			//================================================================================================
			//ページング処理
			//================================================================================================
			$reccount=mysqli_num_rows($rs);
			$pagecount=intval(($reccount-1)/$PageSize+1);
			mysqli_data_seek($rs, $PageSize*($page-1));

			$str="";
			if (intval($page)==1) {
				$str=$str."対象件数(".$reccount."件)　　&lt;前の".$PageSize."件&gt;";
			} else {
				$str=$str."対象件数(".$reccount."件)　　&lt;<a href=\"".$aspname."?mode=list&lid=".$lid."&sort=".$sort."&word=".$word."&page=".($page-1)."\">前の".$PageSize."件</a>&gt;";
			}

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
					$str=$str." <b>".$i."</b>";
				} else {
					$str=$str." <a href=\"".$aspname."?mode=list&lid=".$lid."&sort=".$sort."&word=".$word."&page=".$i."\">".$i."</a>";
				}
			}
			if (intval($page)<$pagecount) {
				$str=$str." &lt;<a href=\"".$aspname."?mode=list&lid=".$lid."&sort=".$sort."&word=".$word."&page=".($page+1)."\">次の".$PageSize."件</a>&gt;";
			} else {
				$str=$str." &lt;次の".$PageSize."件&gt;";
			}

			$pagestr=$str;
			$CurrentRecord=1;
			$strMain="";
			while ($item = mysqli_fetch_assoc($rs)) {

				$str=$strM;

				$str=DispO2($item, $str);

				if($item['ENABLE']=="ENABLE:公開中"){
					$str=DispParam($str, "BDISABLE");
					$str=DispParamNone($str, "BENABLE");
				} else {
					$str=DispParamNone($str, "BDISABLE");
					$str=DispParam($str, "BENABLE");
				}

				$strMain=$strMain.$str.chr(13);

				$CurrentRecord=$CurrentRecord+1; //CurrentRecordの更新

				if ($CurrentRecord>$PageSize){
					break;
				}
			}
		}


		$str=$strU.$strMain.$strD;

		$str = MakeHTML($str,1,$lid);

		$str=str_replace("[PAGING]",$pagestr,$str);
		$str=str_replace("[RECCOUNT]",$reccount,$str);
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
				if (is_string($postVal) && strstr($postVal, "\t") !== false) {
					$FieldValue[$i] = htmlspecialchars($postVal);
				} else {
					$FieldValue[$i] = "";
					if (is_array($postVal)) {
						for ($j = 0; $j < count($postVal); $j = $j + 1) {
							if ($j != 0) {
								$FieldValue[$i] = $FieldValue[$i] . "\t";
							}
							$FieldValue[$i] = $FieldValue[$i] . $postVal[$j];
						}
					}
				}
			}
		} else {
//			$FieldValue[$i]=htmlspecialchars(str_replace("\\","",($_POST[$FieldName[$i]] ?? '')));
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
					move_uploaded_file($_FILES["EP_".$FieldName[$i]]["tmp_name"], "../a_o2/data/".$filename);
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

//	if($FieldValue[2] != $_SESSION['MID']) {
//		exit('SESSION ERROR');
//	}


	// SQLインジェクション対策
	// HTMLエスケープ処理（SQL書き込み）
	$StrSQL="SELECT * FROM ".$TableName." WHERE `".$FieldName[$FieldKey]."`='".mysqli_real_escape_string(ConnDB(),$key)."';";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item=mysqli_num_rows($rs);
	if($item==0) {
		$StrSQL="SELECT OID from DAT_O2 where MID='".$_SESSION['MID']."' order by cast(replace(OID,concat('".$_SESSION['MID']."','-'),'') as signed) desc limit 0,1;";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);
		$lastNum = ($item && isset($item['OID'])) ? intval(str_replace($_SESSION['MID']."-", "", $item['OID'])) : 0;
		$FieldValue[1]=$_SESSION['MID']."-".sprintf("%02d", $lastNum+1);
		$FieldValue[2]=$_SESSION['MID'];
		$FieldValue[93]="ENABLE:公開中";
		$FieldValue[94]=date("Y/m/d H:i:s");
		$FieldValue[95]=date("Y/m/d H:i:s");

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
		$FieldValue[95]=date("Y/m/d H:i:s");

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
