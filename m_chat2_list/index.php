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

	$filename="../common/template/chat2_list_part.html";
	$fp=$DOCUMENT_ROOT.$filename;
	$strM=@file_get_contents($fp);

	$hid="";
	// SQLインジェクション対策
	// 2020.12.23 yamamoto ETC02を案件IDとし、案件ごとにグループ化
	// $StrSQL="SELECT AID,ETC02, max(NEWDATE) as ldate from DAT_MESSAGE where AID = '".$_GET['word']."' and ETC02 is not null and ETC02 != '' group by AID,ETC02 order by ldate desc;";
	$StrSQL="SELECT AID,ETC02,ETC03, max(NEWDATE) as ldate from DAT_MESSAGE where AID = '".$_GET['word']."' group by AID,ETC02,ETC03 order by ldate desc;";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item=mysqli_num_rows($rs);

	// 2020.12.23 yamamoto 新規案件用IDの発番
	$StrSQL_max="SELECT ifnull(max(ETC02), 0) as max_id FROM DAT_MESSAGE where AID = '".$_GET['word']."'";
	$rs_max=mysqli_query(ConnDB(),$StrSQL_max);
	$item_max = mysqli_fetch_assoc($rs_max);
	$next_id = intval($item_max['max_id']) + 1;

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

			// 2020.12.22 yamamoto タイトルをメッセージから取得
			// デフォルトはO1,O2のタイトル
			//$TITLE = '(タイトル未設定)';
			$TITLE = '';
			$StrSQL_TITLE="SELECT COMMENT FROM DAT_MESSAGE where AID='".$_GET['word']."' and ETC02='".$item['ETC02']."' and ETC03='".$item['ETC03']."' and COMMENT like '[タイトル変更]%' order by ID desc;";
			$rs_TITLE=mysqli_query(ConnDB(),$StrSQL_TITLE);
			if ($rs==true) {
				$item_TITLE = mysqli_fetch_assoc($rs_TITLE);
				$preg = preg_match_all('/タイトル「.+?」/i', $item_TITLE['COMMENT'], $match);
				for($i = 0; $i < count($match[0]); $i++) {
					$tmp = str_replace('タイトル「', '', $match[0][$i]);
					$TITLE = str_replace('」', '', $tmp);
				}
			}
			if($TITLE == '') {
				$StrSQL="SELECT DAT_O1.O1_DVAL01 FROM DAT_O1 join DAT_IINE on DAT_O1.OID = DAT_IINE.OIDT where DAT_IINE.MID='".$_SESSION['MID']."' and DAT_IINE.MIDT='".$_GET['mid1']."';";
				$rs2=mysqli_query(ConnDB(),$StrSQL);
				$item2 = mysqli_fetch_assoc($rs2);
				$TITLE = $item2['O1_DVAL01'];
			}
			$str=str_replace("[TITLE]",$TITLE,$str);
			$str=str_replace("[ETC02]",$item['ETC02'],$str);
			$str=str_replace("[ETC03]",$item['ETC03'],$str);

			/*
			$tid=str_replace("-", "", str_replace($_SESSION['MID'], "", $item['AID']));
			$StrSQL="SELECT * FROM DAT_M1 where MID='".$tid."'";
			$rs2=mysqli_query(ConnDB(),$StrSQL);
			$item2=mysqli_fetch_assoc($rs2);
			$str=DispM1($item2, $str);
			 */

			$StrSQL="SELECT ID FROM DAT_MESSAGE where AID='".$item['AID']."' and RID<>'".$_SESSION['MID']."' and (NOREAD is null or NOREAD='') and ETC02='".$item['ETC02']."' and ETC03='".$item['ETC03']."';";
			$rs2=mysqli_query(ConnDB(),$StrSQL);
			$item2=mysqli_num_rows($rs2);
			if ($item2 !== null && $item2 !== false){
				$str=str_replace("[CHATLIST_MIDOKU]",$item2,$str);
				$str=DispParam($str, "CHATLIST_MIDOKU");
			} else {
				$str=DispParamNone($str, "CHATLIST_MIDOKU");
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

	$StrSQL="SELECT * FROM DAT_M1 where MID='".$_GET['mid1']."';";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item = mysqli_fetch_assoc($rs);
	$str=DispM1($item, $str);

	$str=str_replace("[PAGING]",$pagestr,$str);
	$str=str_replace("[SORT]",$sort,$str);
	$str=str_replace("[WORD]",$word,$str);
	$str=str_replace("[PAGE]",$page,$str);
	$str=str_replace("[KEY]",$key,$str);
	$str=str_replace("[LID]",$lid,$str);
	$str=str_replace("[RECCOUNT]",$reccount,$str);

		// 2020.12.22 yamamoto スレッド一覧用パラメータ
	$str=str_replace("[THREAD_WORD]",$_GET['word'],$str);
	$str=str_replace("[THREAD_MID1]",$_GET['mid1'],$str);
	$str=str_replace("[THREAD_MID2]",$_GET['mid2'],$str);
	$str=str_replace("[NEXT_ID]",$next_id,$str);

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
