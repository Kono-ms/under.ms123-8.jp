<?php

require "../config.php";
require "../base.php";
require "../common.php";
require '../a_o1/config.php';

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

	if ($page==""){
		$page=1;
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

	$htmllist = "list.html";

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
	// $StrSQL="SELECT DAT_O1.* FROM DAT_O1 inner join DAT_M1 on DAT_M1.MID=DAT_O1.MID inner join DAT_MATCH on DAT_MATCH.OID1=DAT_O1.OID and DAT_MATCH.MID2='".$_SESSION['MID']."' inner join DAT_IINE on DAT_IINE.MIDT=DAT_O1.MID and DAT_IINE.MID='".$_SESSION['MID']."' ";
	$StrSQL="SELECT DAT_O1.* FROM DAT_O1 inner join DAT_M1 on DAT_M1.MID=DAT_O1.MID inner join DAT_IINE on DAT_IINE.MIDT=DAT_O1.MID and DAT_IINE.OIDT=DAT_O1.OID and DAT_IINE.MID='".$_SESSION['MID']."' ";
	$StrSQL.=" and DAT_M1.ENABLE = 'ENABLE:公開中' and DAT_O1.ENABLE = 'ENABLE:公開中'";
	$StrSQL .= " and NOT EXISTS (SELECT * FROM DAT_BL WHERE DAT_BL.MID1 = '" . $_SESSION['MID'] . "' and DAT_BL.MID2 = DAT_O1.MID) ";
	
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item=mysqli_num_rows($rs);
	if($item==0) {
		$reccount=0;
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
				$str=$str." <a href=\"".$aspname."?mode=list&lid=".$lid."&sort=".$sort."&word=".$word."&page=".$i."\" class=\"inactive\">".$i."</a>";
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
	$str=str_replace("[KEY]",$key,$str);
	$str=str_replace("[LID]",$lid,$str);
	$str=str_replace("[RECCOUNT]",$reccount,$str);

	$str=str_replace("[BASE_URL]",BASE_URL,$str);
	print $str;

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
