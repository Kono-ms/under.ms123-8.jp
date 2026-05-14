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
		if(isset($_GET['sel']) && is_array($_GET['sel'])) {
			$sel = array();
			for($i = 0; $i < count($_GET['sel']); $i++) {
				$sel[]=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['sel'][$i]));
			}
		}
		else {
			$sel=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['sel'] ?? ''));
		}
		$word=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['word'] ?? ''));
		$key=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['key'] ?? ''));
		$page=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['page'] ?? ''));
		$lid=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['lid'] ?? ''));
		$token=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['token'] ?? ''));
		$COMMENT=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['COMMENT'] ?? ''));


		$find_price1=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['find_price1'] ?? ''));
		$find_price2=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['find_price2'] ?? ''));
		$find_ritu1=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['find_ritu1'] ?? ''));
		$find_ritu2=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['find_ritu2'] ?? ''));
		$find_pref=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['find_pref'] ?? ''));
		$find_line=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['find_line'] ?? ''));
		$find_station=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_GET['find_station'] ?? ''));

	} else {
		$mode=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['mode'] ?? ''));
		$sort=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['sort'] ?? ''));
		if(isset($_POST['sel']) && is_array($_POST['sel'])) {
			$sel = array();
			for($i = 0; $i < count($_POST['sel']); $i++) {
				$sel[]=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['sel'][$i]));
			}
		}
		else {
			$sel=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['sel'] ?? ''));
		}
		$word=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['word'] ?? ''));
		$key=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['key'] ?? ''));
		$page=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['page'] ?? ''));
		$lid=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['lid'] ?? ''));
		$token=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['token'] ?? ''));
		$COMMENT=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['COMMENT'] ?? ''));

		

		$find_price1=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['find_price1'] ?? ''));
		$find_price2=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['find_price2'] ?? ''));
		$find_ritu1=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['find_ritu1'] ?? ''));
		$find_ritu2=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['find_ritu2'] ?? ''));
		$find_pref=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['find_pref'] ?? ''));
		$find_line=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['find_line'] ?? ''));
		$find_station=htmlspecialchars(mysqli_real_escape_string(ConnDB(),$_POST['find_station'] ?? ''));

	}



	if ($mode=="teian"){
		//提案する

		$mid1=$_SESSION['MID'];

		$StrSQL="SELECT ID,OID FROM DAT_O1 where MID='".$_SESSION['MID']."' and ID = '".$key."' order by ID;";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);
		$O1_id=$item["ID"];
		$oid=$item["OID"];


		for ($j=0; $j<count($sel); $j=$j+1) {
			$mid2=$sel[$j];

			$aid=$mid1."-".$mid2;
			$StrSQL_max="SELECT ifnull(max(ETC02), 0) as max_id FROM DAT_MESSAGE where AID = '".$aid."'";
			$rs_max=mysqli_query(ConnDB(),$StrSQL_max);
			$item_max = mysqli_fetch_assoc($rs_max);
			$next_id = intval($item_max['max_id']) + 1;

			$status="STATUS:申込";
			$StrSQL="INSERT INTO DAT_MCONTACT (MID, MIDT, STATUS,PRICE, NEWDATE, ETC02, ETC03, ETC05, ETC10) values (";
			$StrSQL.="'".$mid1."',";
			$StrSQL.="'".$mid2."',";
			$StrSQL.="'".$status."',";
			$StrSQL.="'',";
			$StrSQL.="'".date("Y/m/d H:i:s")."',";
			$StrSQL.="'".$next_id."',";
			$StrSQL.="'".$oid."',";
			$StrSQL.="'".$COMMENT."',";
			$StrSQL.="'ETC10:提案'";
			$StrSQL.=")";
			echo "<!--INSERT INTO DAT_MCONTACT:".$StrSQL."-->";
			if (!(mysqli_query(ConnDB(),$StrSQL))) {
				var_dump($StrSQL);
				die;
			}

			SendMailStatus($mid, $mid2,$status);

		}


	}
	$mode="disp";
	

	switch ($mode){
		
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


	DispData($mode,$sort,$word,$key,$page,$lid,$token,$sel,$find_price1,$find_price2,$find_ritu1,$find_ritu2,$find_pref,$find_line,$find_station);

	return $function_ret;
} 

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function SendMailStatus($mid, $mid2,$status)
{

	eval(globals());


		$status =  str_replace('STATUS:', '', $status);

		$StrSQL="SELECT * FROM DAT_M1 where MID='".$mid."'";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$itemM1 = mysqli_fetch_assoc($rs);

		$StrSQL="SELECT * FROM DAT_M2 where MID='".$mid2."'";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$itemM2 = mysqli_fetch_assoc($rs);
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



		echo "<!--mailtoM2:".$mailtoM2."-->";
		echo "<!--MailBody:".$MailBody."-->";
		mb_send_mail($mailtoM2, $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 
		

	
	
}
//=========================================================================================================
//名前 画面表示処理
//機能 Modeによって画面表示
//引数 $mode,$sort,$word,$key,$page,$lid
//戻値 なし
//=========================================================================================================
function DispData($mode,$sort,$word,$key,$page,$lid,$token,$sel,$find_price1,$find_price2,$find_ritu1,$find_ritu2,$find_pref,$find_line,$find_station)
{

	eval(globals());

	//各テンプレートファイル名
	$htmlnew = "edit.html";
	$htmledit = "edit.html";
	$htmlconf = "conf.html";
	$htmlend = "end.html";

	$htmldisp = "disp.html";
	$htmllist = "list.html";

	
	$filename=$htmldisp;
	$msg01="";
	$msg02="";
	$errmsg="";

	$fp=$DOCUMENT_ROOT.$filename;
	$str=@file_get_contents($fp);

	$StrSQL="SELECT * FROM DAT_O1 where ID='".$key."';";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$itemO1 = mysqli_fetch_assoc($rs);
	$mid1=$item["MID"];

	$str=DispO1($itemO1, $str);
	$str=DispPoint1($itemO1['OID'], $str);
	$StrSQL="SELECT * FROM DAT_M1 where MID='".$itemO1['MID']."'";
	$rs2=mysqli_query(ConnDB(),$StrSQL);
	$item2=mysqli_fetch_assoc($rs2);

	$str=DispM1($item2, $str);

	$str = MakeHTML($str,0,$lid);


	//ユーザーの選択
	// ①o2の物件価格の範囲にNo41で登録した物件の物件価格が収まっている O2_DVAL01~O2_DVAL02
	$price=str_replace(",","",str_replace("万円","0000",str_replace("億","",$itemO1["O1_DVAL01"])));

	// ②o2の利回りの範囲にNo41で登録した物件の利回りが収まっている  O2_DVAL29~O2_DVAL30
	$ritu=str_replace(",","",str_replace("%","",$itemO1["O1_DVAL29"]));

	// ③o2とNo41で登録した物件を比較して、エリア、路線、駅のいずれかで共通選択肢を持つ O2_MRDO03 O2_MRDO04
	$pref=str_replace("O1_MRDO01:","",$itemO1["O1_MRDO01"]);
	$city=str_replace("O1_MRDO02:","",$itemO1["O1_MRDO02"]);
	$address=$itemO1["O1_DVAL02"];

	$line1=str_replace("O1_MRDO04:","",$itemO1["O1_MRDO04"]);
	$line2=str_replace("O1_MRDO06:","",$itemO1["O1_MRDO06"]);
	$line3=str_replace("O1_MRDO08:","",$itemO1["O1_MRDO08"]);

	$station1=str_replace("O1_MRDO03:","",$itemO1["O1_MRDO03"]);
	$station2=str_replace("O1_MRDO07:","",$itemO1["O1_MRDO07"]);
	$station3=str_replace("O1_MRDO09:","",$itemO1["O1_MRDO09"]);

	$find_line=str_replace("find_line:","",$find_line);
	$find_station=str_replace("find_station:","",$find_station);

	// 上記を同時に満たす場合、そのo2を保有するm2ユーザーが表示されるようにお願いします
	$filename="item.html";
	$fp=$DOCUMENT_ROOT.$filename;
	$strM_item=@file_get_contents($fp);



	$filename="searh_str.html";
	$fp=$DOCUMENT_ROOT.$filename;
	$strM_searh_str=@file_get_contents($fp);

	$SEARCH_STR="";
	$ITEM_LIST="";
	$StrSQL="SELECT DAT_M2.MID FROM DAT_O2 INNER JOIN DAT_M2 ON DAT_O2.MID = DAT_M2.MID  where 1=1";
	if($find_price1=="" && $find_price2=="" && $find_ritu1=="" && $find_ritu2=="" && $find_pref=="" && $find_line=="" && $find_station==""){

		
		$StrSQL.=" AND  cast('".$price."' as signed) BETWEEN cast(replace(replace(replace(O2_DVAL01,'億',''),'万円','0000'),',','') as signed) ";
		$StrSQL.="                         AND cast(replace(replace(replace(O2_DVAL02,'億',''),'万円','0000'),',','') as signed) ";

		$SEARCH_STR.=$strM_searh_str;
		$SEARCH_STR=str_replace("[TEXT]","希望物件価格:".$itemO1["O1_DVAL01"],$SEARCH_STR);


		$StrSQL.=" AND   cast('".$ritu."' as signed) BETWEEN cast(replace(replace(O2_DVAL29,'%',''),',','') as float) ";
		$StrSQL.="                         AND cast(replace(replace(O2_DVAL30,'%',''),',','') as float) ";
		$SEARCH_STR.=$strM_searh_str;
		$SEARCH_STR=str_replace("[TEXT]","希望利回り:".$itemO1["O1_DVAL29"],$SEARCH_STR);

		$StrSQL.=" AND  ( ";
		$StrSQL.="           ( replace(M2_MSEL01,'M2_MSEL01:','') LIKE '%".$pref."%' OR M2_DVAL06 LIKE '%".$city."%' OR M2_DVAL07 LIKE '%".$address."%')";
		$StrSQL.="        OR ( replace(O2_MRDO03,'O2_MRDO03:','') LIKE '%".$line1."%' OR replace(O2_MRDO03,'O2_MRDO03:','') LIKE '%".$line2."%' OR replace(O2_MRDO03,'O2_MRDO03:','') LIKE '%".$line3."%')";
		$StrSQL.="        OR ( replace(O2_MRDO04,'O2_MRDO04:','') LIKE '%".$station1."%' OR replace(O2_MRDO04,'O2_MRDO04:','') LIKE '%".$station2."%' OR replace(O2_MRDO04,'O2_MRDO04:','') LIKE '%".$station3."%')";
		$StrSQL.="      ) ";

		$SEARCH_STR.=$strM_searh_str;
		$SEARCH_STR=str_replace("[TEXT]","希望エリア:".$pref.$city.$address,$SEARCH_STR);
		$SEARCH_STR.=$strM_searh_str;
		$SEARCH_STR=str_replace("[TEXT]","路線:".$line1." ".$line2." ".$line3,$SEARCH_STR);
		$SEARCH_STR.=$strM_searh_str;
		$SEARCH_STR=str_replace("[TEXT]","駅:".$station1." ".$station2." ".$station3,$SEARCH_STR);
	} else {
	
		if($find_price1!=""){
			$StrSQL.=" AND  cast('".$find_price1."' as signed) >= cast(replace(replace(replace(O2_DVAL01,'億',''),'万円','0000'),',','') as signed) ";

			$SEARCH_STR.=$strM_searh_str;
			$SEARCH_STR=str_replace("[TEXT]","希望物件価格(下限):".$find_price1,$SEARCH_STR);
		}
		if($find_price2!=""){
			$StrSQL.=" AND  cast('".$find_price2."' as signed) <= cast(replace(replace(replace(O2_DVAL02,'億',''),'万円','0000'),',','') as signed) ";
			$SEARCH_STR.=$strM_searh_str;
			$SEARCH_STR=str_replace("[TEXT]","希望物件価格(上限):".$find_price2,$SEARCH_STR);
		}

		if($find_ritu1!=""){
			$StrSQL.=" AND   cast('".$find_ritu1."' as signed) >= cast(replace(replace(O2_DVAL29,'%',''),',','') as float) ";

			$SEARCH_STR.=$strM_searh_str;
			$SEARCH_STR=str_replace("[TEXT]","希望利回り(下限):".$find_ritu1,$SEARCH_STR);
		}

		if($find_ritu2!=""){
			$StrSQL.=" AND   cast('".$find_ritu1."' as signed) <= cast(replace(replace(O2_DVAL30,'%',''),',','') as float) ";

			$SEARCH_STR.=$strM_searh_str;
			$SEARCH_STR=str_replace("[TEXT]","希望利回り(上限):".$find_ritu1,$SEARCH_STR);
		}


		if($find_pref!=""){
			$StrSQL.=" AND    ( replace(M2_MSEL01,'M2_MSEL01:','') LIKE '%".$find_pref."%')";
			$SEARCH_STR.=$strM_searh_str;
			$SEARCH_STR=str_replace("[TEXT]","希望エリア:".$find_pref,$SEARCH_STR);
		}

	
		if($find_line!=""){
			$StrSQL.=" AND    ( replace(O2_MRDO03,'O2_MRDO03:','') LIKE '%".$find_line."%')";
			$SEARCH_STR.=$strM_searh_str;
			$SEARCH_STR=str_replace("[TEXT]","路線:".$find_line,$SEARCH_STR);
		}

		if($find_station!=""){
			$StrSQL.=" AND    ( replace(O2_MRDO04,'O2_MRDO04:','') LIKE '%".$find_station."%')";
			$SEARCH_STR.=$strM_searh_str;
			$SEARCH_STR=str_replace("[TEXT]","駅:".$find_station,$SEARCH_STR);
		}


	}

	$StrSQL.=" GROUP BY DAT_M2.MID order by MID limit 0,10";
	echo "<!--ユーザーの選択:".$StrSQL."-->";
	$rs=mysqli_query(ConnDB(), $StrSQL);
	while ($item = mysqli_fetch_assoc($rs)) {


		$mid2=$item["MID"];

		$tmp=$strM_item;
		
		$StrSQL="SELECT * FROM DAT_M2 where MID='".$mid2."'";
		$rs2=mysqli_query(ConnDB(),$StrSQL);
		$itemM2 = mysqli_fetch_assoc($rs2);

		$StrSQL="SELECT * FROM DAT_O2 where MID='".$mid2."' order by id desc";
		$rs2=mysqli_query(ConnDB(),$StrSQL);
		$itemO2 = mysqli_fetch_assoc($rs2);

		$tmp=DispM2($itemM2, $tmp);
		$tmp=DispO2($itemO2, $tmp);

		$ITEM_LIST.=$tmp;

	}
	$str=str_replace("[ITEM_LIST]",$ITEM_LIST,$str);


	//都道府県
	$tmp="<option value=''>▼選択して下さい</option>";
	$StrSQL="SELECT CD1,N1 FROM DAT_ADDRESS group by CD1,N1 order by cast(CD1 as signed) asc";
	$rs=mysqli_query(ConnDB(), $StrSQL);
	while ($item = mysqli_fetch_assoc($rs)) {
		$val=$item["N1"];
		$selected="";
		if($find_pref==$val){
			$selected="selected";
		}
		$tmp.="<option ".$selected." value='".$val."'>".$val."</option>";
	}
	$str=str_replace("[OPT-PREF]",$tmp,$str);

	$StrSQL="SELECT CD1 FROM DAT_ADDRESS WHERE N1='".$find_pref."'";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item = mysqli_fetch_assoc($rs);
	$cd1=$item["CD1"];

	$j=0;
	$tmp="";
	$fieldname1="find_line";
	$StrSQL="SELECT CD2, N2 FROM DAT_ROSEN WHERE PREFCD = '".$cd1."' group by CD2, N2 order by CD2";
	$rs=mysqli_query(ConnDB(), $StrSQL);
	while ($item = mysqli_fetch_assoc($rs)) {
		$checked="";
		$val=str_replace("\r", "", str_replace("\n", "", $item['N2'].$item['N3']));
		if(strpos($find_line,$val)!==false){
			$checked="checked";
		}
		$tmp.="<li><input ".$checked." id=\"".$fieldname1.$j."\" type=\"radio\" class=\"".$fieldname1."\" name=\"".$fieldname1."\" value=\"".$val."\"><label for=\"".$fieldname1.$j."\">".$val."</label></li>";
		$j=$j+1;
	}
	$str=str_replace("[OPT-LINE]",$tmp,$str);

	$j=0;
	$tmp="";
	$fieldname1="find_station";
	$StrSQL="SELECT CD4, N3 FROM DAT_ROSEN WHERE PREFCD = '".$cd1."' group by CD4, N3 order by CD4";
	$rs=mysqli_query(ConnDB(), $StrSQL);
	while ($item = mysqli_fetch_assoc($rs)) {
		$checked="";
		$val=$item['N3']."駅";
		if(strpos($find_station,$val)!==false){
			$checked="checked";
		}
		$tmp.="<li><input ".$checked." id=\"".$fieldname1.$j."\" type=\"radio\" class=\"".$fieldname1."\" name=\"".$fieldname1."\" value=\"".$val."\"><label for=\"".$fieldname1.$j."\">".$val."</label></li>";
		$j=$j+1;
	}
	$str=str_replace("[OPT-STATION]",$tmp,$str);

	if($SEARCH_STR==""){
		$SEARCH_STR="なし";
	}
	$str=str_replace("[SEARCH_STR]",$SEARCH_STR,$str);

	$str=str_replace("[find_price1]",$find_price1,$str);
	$str=str_replace("[find_price2]",$find_price2,$str);
	$str=str_replace("[find_ritu1]",$find_ritu1,$str);
	$str=str_replace("[find_ritu2]",$find_ritu2,$str);
	$str=str_replace("[find_pref]",$find_pref,$str);
	$str=str_replace("[find_line]",$find_line,$str);
	$str=str_replace("[find_station]",$find_station,$str);

	$str=str_replace("[KEY]",$key,$str);
	$str=str_replace("[BASE_URL]",BASE_URL,$str);


	// CSRFトークン生成
	$token=htmlspecialchars(session_id().date("YmdHis") . substr(explode(".", microtime(true))[1], 0, 3));
	$_SESSION['token'] = $token;

	$str=str_replace("[TOKEN]",$token,$str);



	print $str;

	


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
