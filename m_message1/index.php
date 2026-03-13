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
	if(!CheckSession(1)) {
		$url=BASE_URL . "/login1/";
		header("Location: {$url}");
		exit;
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

	$filename="../common/template/listmm2.html";
	$fp=$DOCUMENT_ROOT.$filename;
	$strM=@file_get_contents($fp);

	$hid="";
	// SQLインジェクション対策
	// 2021.03.15 yamamoto ENABLE:公開中のみ表示
	$StrSQL="SELECT DAT_MESSAGE.AID, max(DAT_MESSAGE.NEWDATE) as ldate from DAT_MESSAGE ";
	$StrSQL.=" inner join DAT_M2 on DAT_M2.MID = SUBSTRING(DAT_MESSAGE.AID,9,7) and DAT_M2.ENABLE = 'ENABLE:公開中'";
	$StrSQL.=" where  DAT_MESSAGE.AID like '%".$_SESSION['MID']."%' ";
	//2020/12/28 gaosan ADD START
	$StrSQL .= " AND NOT EXISTS (SELECT * FROM DAT_BL WHERE DAT_BL.MID1 = '" . $_SESSION['MID'] . "' and DAT_BL.MID2 = SUBSTRING(DAT_MESSAGE.AID,1,7)) ";
	$StrSQL .= " AND NOT EXISTS (SELECT * FROM DAT_BL WHERE DAT_BL.MID1 = '" . $_SESSION['MID'] . "' and DAT_BL.MID2 = SUBSTRING(DAT_MESSAGE.AID,9,7)) ";
	//2020/12/28 gaosan ADD END
    $StrSQL .= " group by AID order by ldate desc;";

	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item=mysqli_num_rows($rs);
	if($item==0) {
		$reccount=0;
		$pagestr="";
		$strMain="まだメッセージのやり取りはありません。";
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

			$tid=str_replace("-", "", str_replace($_SESSION['MID'], "", $item['AID']));
			$StrSQL="SELECT * FROM DAT_M2 where MID='".$tid."'";
			$rs2=mysqli_query(ConnDB(),$StrSQL);
			$item2=mysqli_fetch_assoc($rs2);
			$str=DispM2($item2, $str);

			$StrSQL="SELECT ID FROM DAT_MESSAGE where AID='".$item['AID']."' and RID<>'".$_SESSION['MID']."' and (NOREAD is null or NOREAD='');";
			$rs2=mysqli_query(ConnDB(),$StrSQL);
			$item2=mysqli_num_rows($rs2);
			if ($item2 !== null && $item2 !== false){
				$str=DispParam($str, "MIDOKU");
			} else {
				$str=DispParamNone($str, "MIDOKU");
			}

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
