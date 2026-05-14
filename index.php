<?php

require "./config.php";
require "./base.php";
require "./common.php";
require './a_m1/config.php';

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

	$filename="top.html";
	$fp=$DOCUMENT_ROOT.$filename;
	$str=@file_get_contents($fp);

	$filename="common/template/listo1.html";
	$fp=$DOCUMENT_ROOT.$filename;
	$strM=@file_get_contents($fp);
	$strMain="";
	$StrSQL="SELECT DAT_O1.* FROM DAT_O1 inner join DAT_M1 on DAT_M1.MID=DAT_O1.MID and DAT_M1.ENABLE='ENABLE:公開中' and DAT_O1.ENABLE='ENABLE:公開中' order by rand() limit 0,5";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	while ($item = mysqli_fetch_assoc($rs)) {
		$tmp=$strM;

		$tmp=DispO1($item, $tmp);
		$tmp=DispPoint1($item['OID'], $tmp);

		$StrSQL="SELECT * FROM DAT_M1 where MID='".$item['MID']."'";
		$rs2=mysqli_query(ConnDB(),$StrSQL);
		$item2=mysqli_fetch_assoc($rs2);
		$tmp=DispM1($item2, $tmp);

		// 未ログインの場合にm_searchをsearchに変換
		if(!isset($_SESSION['MID'])) {
			$tmp = str_replace('/m_search2/', '/search2/', $tmp);
		}

		$strMain=$strMain.$tmp.chr(13);
	}

	$str=str_replace("[LIST_1]", $strMain, $str);

	$filename="common/template/listo2.html";
	$fp=$DOCUMENT_ROOT.$filename;
	$strM=@file_get_contents($fp);
	$strMain="";
	$StrSQL="SELECT DAT_O2.* FROM DAT_O2 inner join DAT_M2 on DAT_M2.MID=DAT_O2.MID and DAT_M2.ENABLE='ENABLE:公開中' and DAT_O2.ENABLE='ENABLE:公開中' order by rand() limit 0,5";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	while ($item = mysqli_fetch_assoc($rs)) {
		$tmp=$strM;

		$tmp=DispO2($item, $tmp);
		$tmp=DispPoint2($item['OID'], $tmp);

		$StrSQL="SELECT * FROM DAT_M2 where MID='".$item['MID']."'";
		$rs2=mysqli_query(ConnDB(),$StrSQL);
		$item2=mysqli_fetch_assoc($rs2);
		$tmp=DispM2($item2, $tmp);

		// 未ログインの場合にm_searchをsearchに変換
		if(!isset($_SESSION['MID'])) {
			$tmp = str_replace('/m_search1/', '/search1/', $tmp);
		}

		$strMain=$strMain.$tmp.chr(13);
	}
	$str=str_replace("[LIST_2]", $strMain, $str);

	// カテゴリ別に記事一覧を生成（投資豆知識 / はじめての不動産投資）
	$pressCategories = array(
		"[PRESS_TIPS]"   => "CCATE:豆知識",
		"[PRESS_COLUMN]" => "CCATE:記事",
	);
	foreach ($pressCategories as $placeholder => $ccate) {
		$press = "";
		$StrSQL = "SELECT DAT_INFO.* FROM DAT_INFO join DAT_CCATE on DAT_INFO.CCATE = concat('CCATE:', DAT_CCATE.CNAME) where DAT_INFO.ETC12='ETC12:公開中' AND DAT_CCATE.ETC01 like '%ETC01:公開用%' AND DAT_INFO.CCATE='".$ccate."' order by DAT_INFO.DATE desc, DAT_INFO.ID desc limit 0,4";
		$rs = mysqli_query(ConnDB(), $StrSQL);
		while ($item = mysqli_fetch_assoc($rs)) {
			$press .= "<div class='knowledge__item'>
<a href='/info/".$item['URL']."/'>
<div class='knowledge__img'><img src='".$item['PIC']."' alt=''></div>
<div class='knowledge__desc'>
<p class='knowledge__date'>".str_replace("/", ".", $item['DATE'])."</p>
<h3 class='knowledge__ttl'>".$item['TITLE']."</h3>
<p class='knowledge__txt'>".mb_substr(strip_tags($item['COMMENT']),0,60,"UTF-8")."…</p>
</div>
</a>
</div>";
		}
		if ($press != "") {
			$str = str_replace($placeholder, $press, $str);
		} else {
			$str = str_replace($placeholder, "公開までしばらくおちください", $str);
		}
	}

	$news="";
	// 公開先：公開用
	$StrSQL="SELECT DAT_INFO.* FROM DAT_INFO join DAT_CCATE on DAT_INFO.CCATE = concat('CCATE:', DAT_CCATE.CNAME) where DAT_INFO.ETC12='ETC12:公開中' AND DAT_INFO.CCATE like '%お知らせ%' AND DAT_CCATE.ETC01 like '%ETC01:公開用%' order by DAT_INFO.ID desc limit 0,10";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	while ($item = mysqli_fetch_assoc($rs)) {
		// $news.="<div class='knowledge__item'> <a href='/info/".sprintf("%04d", $item['ID'])."/'><div class='knowledge__img'><img src='".$item['PIC']."' alt=''></div><div class='knowledge__desc'><h3 class='knowledge__ttl'>".$item['TITLE']."</h3><p class='knowledge__txt'>".mb_substr(strip_tags($item['COMMENT']),0,60,"UTF-8")."… <span class='knowledge__txt--more'>続きを読む</span></p></div></a> </div>";
		$news.="<div class='knowledge__item'> <a href='/info/".$item['URL']."/'><div class='knowledge__img'><img src='".$item['PIC']."' alt=''></div><div class='knowledge__desc'><h3 class='knowledge__ttl'>".$item['TITLE']."</h3><p class='knowledge__txt'>".mb_substr(strip_tags($item['COMMENT']),0,60,"UTF-8")."… <span class='knowledge__txt--more'>続きを読む</span></p></div></a> </div>";
		
	}
	if($news!=""){
		$str=str_replace("[NEWS]", $news, $str);
	} else {
		$str=str_replace("[NEWS]", "公開までしばらくおちください", $str);
	}

	$banner="";
	$StrSQL="SELECT * FROM DAT_BANNER order by ID desc";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	while ($item = mysqli_fetch_assoc($rs)) {
		$banner.="<div class='banner_item'><a href='".$item['LINK']."' target='_blank'><img src='".$item['PIC']."' alt='".$item['TITLE']."'></a></div>";
	}
	if($banner!=""){
		$str=str_replace("[BANNER]", $banner, $str);
	} else {
		$str=str_replace("[BANNER]", "公開までしばらくおちください", $str);
	}

	$str = MakeHTML($str,0,$lid);

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
