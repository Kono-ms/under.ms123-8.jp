<?php

require "../config.php";
require "../base.php";
require "../common.php";
require '../a_mcontact/config.php';

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

	if($_SESSION['MID']==""){
		$url=BASE_URL . "/login2/";
		header("Location: {$url}");
		exit;
	}

	if(!isset($_POST['mode']) || $_POST['mode']===""){
		$mode=$_GET['mode'] ?? '';
		$sort=$_GET['sort'] ?? '';
		$word=$_GET['word'] ?? '';
		$key=$_GET['key'] ?? '';
		$page=$_GET['page'] ?? '';
		$lid=$_GET['lid'] ?? '';
		$token=$_GET['token'] ?? '';
		$sel1=$_GET['sel1'];
		$STATUS=$_GET['STATUS'];
	} else {
		$mode=$_POST['mode'] ?? '';
		$sort=$_POST['sort'] ?? '';
		$word=$_POST['word'] ?? '';
		$key=$_POST['key'] ?? '';
		$page=$_POST['page'] ?? '';
		$lid=$_POST['lid'] ?? '';
		$token=$_POST['token'] ?? '';
		$sel1=$_POST['sel1'];
		$STATUS=$_POST['STATUS'];
	}

	if ($mode==""){
		$mode="list";
	}

	if($mode=="export"){
		ExportData();
		exit;
	}
	if($mode=="status"){

		$StrSQL="UPDATE DAT_MCONTACT SET ";
		$StrSQL.=" STATUS='".$STATUS."'";
		$StrSQL.=" WHERE ID='".$key."'";
		if (!(mysqli_query(ConnDB(),$StrSQL))) {
			die;
		}
		
		$StrSQL="SELECT MID,MIDT FROM DAT_MCONTACT where ID='".$key."'";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item = mysqli_fetch_assoc($rs);

		$mid1=$item["MID"];
		$mid2=$item["MIDT"];
		SendMail($mid1,$mid2,$STATUS);

		$mode="list";
	}


	if ($page==""){
		$page=1;
	} 

	DispData($mode,$sort,$word,$key,$page,$lid,$token,$sel1);

	return $function_ret;
} 
//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function SendMail($mid1,$mid2,$status)
{

	eval(globals());

	$status = str_replace("STATUS:","",$status);

	$StrSQL="SELECT * FROM DAT_M1 where MID='".$mid1."'";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$itemM1 = mysqli_fetch_assoc($rs);

	$StrSQL="SELECT * FROM DAT_M2 where MID='".$mid2."'";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$itemM2 = mysqli_fetch_assoc($rs);

	$maildata = GetMailTemplateStatus('ステータス通知（M1）',$status);
	$MailBody = $maildata['BODY'];
	$subject = $maildata['TITLE'];
	$subject=str_replace("[M2_DVAL01]",$itemM2["M2_DVAL01"],$subject);
	$subject=str_replace("[STATUS]",$status,$subject);
	$MailBody=str_replace("[STATUS]",$status,$MailBody);

	$MailBody=str_replace("[M1_DVAL01]",$itemM1["M1_DVAL01"],$MailBody);

	$mailtoM1=$itemM1['EMAIL'];
	// $mailtoM1="toretoresansan99@gmail.com";
	// $subject.=$itemM1['EMAIL'];
	mb_language("Japanese");
	mb_internal_encoding("UTF-8");
	mb_send_mail($mailtoM1, $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 

	// $maildata = GetMailTemplate('ステータス通知（M2）');
	// $MailBody = $maildata['BODY'];
	// $subject = $maildata['TITLE'];
	// $subject=str_replace("[M1_DVAL01]",$itemM1["M1_DVAL01"],$subject);
	// $subject=str_replace("[STATUS]",$status,$subject);
	// $MailBody=str_replace("[STATUS]",$status,$MailBody);

	// $mailtoM2=$itemM2['EMAIL'];
	// // $mailtoM2="toretoresansan11@gmail.com";
	// // $subject.=$itemM2['EMAIL'];
	// mb_language("Japanese");
	// mb_internal_encoding("UTF-8");
	// mb_send_mail($mailtoM2, $subject, $MailBody, "From:".mb_encode_mimeheader(mb_convert_encoding(SENDER_NAME,"ISO-2022-JP","AUTO"))."<".SENDER_EMAIL.">"); 


	
	
	
}
//=========================================================================================================
//名前 画面表示処理
//機能 Modeによって画面表示
//引数 $mode,$sort,$word,$key,$page,$lid
//戻値 なし
//=========================================================================================================
function DispData($mode,$sort,$word,$key,$page,$lid,$token,$sel1)
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


	$hid="";
	// SQLインジェクション対策

	$StrSQL=" SELECT ";
	$StrSQL.="  * ";
	$StrSQL.=" FROM ";
	$StrSQL.="  DAT_O1 ";
	$StrSQL.=" WHERE ";
	$StrSQL.="  MID = '".$_SESSION['MID']."' ";
	

	if($word!=""){

		$StrSQL.="  AND ( ";

		$StrSQL.="     O1_DVAL01 like '%".$word."%'";
		$StrSQL.="  OR O1_DTXT01 like '%".$word."%'";
		$StrSQL.="  OR O1_DTXT02 like '%".$word."%'";
		$StrSQL.="  OR O1_DTXT03 like '%".$word."%'";
		$StrSQL.="  OR O1_DTXT04 like '%".$word."%'";
		$StrSQL.="  OR O1_DTXT05 like '%".$word."%'";
		$StrSQL.="  OR O1_DTXT06 like '%".$word."%'";
		$StrSQL.="  OR O1_MSEL01 like '%".$word."%'";
		$StrSQL.="  OR O1_MSEL02 like '%".$word."%'";

		$StrSQL.="  ) ";
	}

	$StrSQL.="  ORDER BY NEWDATE DESC ";
// var_dump($StrSQL);
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item=mysqli_num_rows($rs);
	if($item==0) {
		$reccount=0;
		$pagestr="";
		$strMain="該当データがありません。";
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
				$str=$str." <a href=\"".$aspname."?mode=list&lid=".$lid."&sort=".$sort."&word=".$word."&sel1=".$sel1."&page=".$i."\" class=\"inactive\">".$i."</a>";
			} 
		}
		$pagestr=$str;



		$sum_jyuryo=0;
		$sum_tanka=0;
		$sum_soryo=0;
		$sum_kingk=0;
		$CurrentRecord=1;
		$strMain="";
		while ($item = mysqli_fetch_assoc($rs)) {

			$str=$strM;

			$str=str_replace("[O1_DVAL01]",$item['O1_DVAL01'],$str);
			$str=str_replace("[O1_DTXT01]",str_replace("\r\n","<br>",htmlspecialchars($item["O1_DTXT01"])),$str);
			$str=str_replace("[O1_DTXT02]",htmlspecialchars($item["NEWDATE"]),$str);
			$str=str_replace("[O1_MSEL01]",str_replace("\r\n","<br>",str_replace("O1_MSEL01:","",htmlspecialchars($item["O1_MSEL01"]))),$str);
			$str=str_replace("[O1_MSEL02]",str_replace("\r\n","<br>",str_replace("O1_MSEL02:","",htmlspecialchars($item["O1_MSEL02"]))),$str);
			$str=str_replace("[O1_ID]",$item['ID'],$str); //案件詳細

			$str=str_replace("[AID]","",$str);
			$str=str_replace("[MID1]","",$str);
			$str=str_replace("[MID2]","",$str);
			$str=str_replace("[ETC02]","",$str);
			$str=str_replace("[ETC03]","",$str);

			$str=str_replace("[O2_DVAL01]",$item['O1_DVAL01'],$str);
			$str=str_replace("[O2_DTXT01]",str_replace("\r\n","<br>",htmlspecialchars($item["O1_DTXT01"])),$str);
			$str=str_replace("[O2_DTXT02]",str_replace("\r\n","<br>",htmlspecialchars($item["O1_DTXT02"])),$str);
			$str=str_replace("[O2_DTXT03]",str_replace("\r\n","<br>",htmlspecialchars($item["O1_DTXT03"])),$str);
			$str=str_replace("[O2_DTXT04]",str_replace("\r\n","<br>",htmlspecialchars($item["O1_DTXT04"])),$str);
			$str=str_replace("[O2_DTXT05]",str_replace("\r\n","<br>",htmlspecialchars($item["O1_DTXT05"])),$str);
			$str=str_replace("[O2_DTXT06]",str_replace("\r\n","<br>",htmlspecialchars($item["O1_DTXT06"])),$str);
			$str=str_replace("[O2_MSEL01]",str_replace("\r\n","<br>",str_replace("O1_MSEL01:","",htmlspecialchars($item["O1_MSEL01"]))),$str);
			$str=str_replace("[O2_MSEL02]",str_replace("\r\n","<br>",str_replace("O1_MSEL02:","",htmlspecialchars($item["O1_MSEL02"]))),$str);

			$str=DispParamNone($str, "TOP_MIDOKU");

			$strMain=$strMain.$str.chr(13);

			$CurrentRecord=$CurrentRecord+1; //CurrentRecordの更新

			if ($CurrentRecord>$PageSize){
				break;
			}
		} 
	} 

	
	$str=$strU.$strMain.$strD;

	$str = MakeHTML($str,0,$lid);




	// ステータス
	$tmp="";
	$sel=explode("::", FIRST_STATUS."::".STATUS_LIST);
	for($i=0; $i<count($sel); $i++){
		if($sel[$i]!=""){
			if(strstr($sel1, $sel[$i]) !== false){
				$tmp.="<option value=\"STATUS:".$sel[$i]."\" selected>".$sel[$i]."</option>";
			} else {
				$tmp.="<option value=\"STATUS:".$sel[$i]."\">".$sel[$i]."</option>";
			}
		}
	}
	$str=str_replace("[SEL_S1]",$tmp,$str);

	$str=str_replace("[PAGING]",$pagestr,$str);
	$str=str_replace("[SORT]",$sort,$str);
	$str=str_replace("[WORD]",$word,$str);
	$str=str_replace("[PAGE]",$page,$str);
	$str=str_replace("[KEY]",$key,$str);
	$str=str_replace("[LID]",$lid,$str);
	$str=str_replace("[RECCOUNT]",$reccount,$str);
	$str=str_replace("[hiduke]",$hiduke,$str);
	$str=str_replace("[TOP_MESSAGE_LIST]", GetTopMessageListHtml(), $str);

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
//名前 サイドメッセージ一覧取得
//機能 マイページ右側のメッセージ一覧HTMLを生成する
//引数 なし
//戻値 HTML文字列
//=========================================================================================================
function GetTopMessageListHtml()
{
	eval(globals());

	$list_html = '';
	$limit = 5;

	$StrSQL = "SELECT AID, ETC02, ifnull(ETC03,'') as ETC03, max(NEWDATE) as LDATE ";
	$StrSQL .= "FROM DAT_MESSAGE ";
	$StrSQL .= "WHERE AID like '%".$_SESSION['MID']."%' ";
	$StrSQL .= "GROUP BY AID, ETC02, ifnull(ETC03,'') ";
	$StrSQL .= "ORDER BY LDATE DESC ";
	$StrSQL .= "LIMIT ".$limit.";";
	$rs = mysqli_query(ConnDB(), $StrSQL);

	if($rs == false || mysqli_num_rows($rs) == 0){
		return '<li class="mypage-message__list-item"><p>まだメッセージはありません。</p></li>';
	}

	while($item = mysqli_fetch_assoc($rs)) {
		$ids = explode('-', $item['AID']);
		$mid1 = $ids[0];
		$mid2 = $ids[1];

		if($mid1 == $_SESSION['MID']){
			$partner_mid = $mid2;
		} else {
			$partner_mid = $mid1;
		}

		$partner_m2_name = '';
		$StrSQL_M2="SELECT M2_DVAL01, M2_DVAL02,M2_ETC02 FROM DAT_M2 where MID='".$partner_mid."';";
		$rs_m2=mysqli_query(ConnDB(),$StrSQL_M2);
		if($rs_m2){
			$item_m2 = mysqli_fetch_assoc($rs_m2);
			if($item_m2){
				$sei = isset($item_m2['M2_DVAL01']) ? trim($item_m2['M2_DVAL01']) : '';
				$mei = isset($item_m2['M2_DVAL02']) ? trim($item_m2['M2_DVAL02']) : '';
				$auth = isset($item_m2['M2_ETC02']) ? trim($item_m2['M2_ETC02']) : '';
				$partner_m2_name = trim($sei.' '.$mei);
				if($auth!=""){
					//顧客認証が紐づいている場合、メッセージ相手の名前の後ろに顧客認証名を表示
					$partner_m2_name.="(".$auth.")"; 
				}
			}
		}

		$title = '';
		$StrSQL_TITLE="SELECT COMMENT FROM DAT_MESSAGE where AID='".$item['AID']."' and ETC02='".$item['ETC02']."' and ifnull(ETC03,'')='".$item['ETC03']."' and COMMENT like '[タイトル変更]%' order by ID desc;";
		$rs_title=mysqli_query(ConnDB(),$StrSQL_TITLE);
		if($rs_title){
			$item_title = mysqli_fetch_assoc($rs_title);
			if($item_title && $item_title['COMMENT'] != ''){
				$preg = preg_match_all('/タイトル「.+?」/i', $item_title['COMMENT'], $match);
				for($i = 0; $i < count($match[0]); $i++) {
					$tmp = str_replace('タイトル「', '', $match[0][$i]);
					$title = str_replace('」', '', $tmp);
				}
			}
		}

		if($title == '') {
			$StrSQL_O1="SELECT DAT_O1.O1_DVAL01 FROM DAT_O1 join DAT_IINE on DAT_O1.OID = DAT_IINE.OIDT where DAT_IINE.MID='".$_SESSION['MID']."' and DAT_IINE.MIDT='".$partner_mid."';";
			$rs_o1=mysqli_query(ConnDB(),$StrSQL_O1);
			if($rs_o1){
				$item_o1 = mysqli_fetch_assoc($rs_o1);
				$title = $item_o1['O1_DVAL01'];
			}
		}
		if($title == ''){
			$title = '(タイトル未設定)';
		}

		$ldate = htmlspecialchars($item['LDATE']);
		$timestamp = strtotime($item['LDATE']);
		if($timestamp !== false){
			$ldate = date('Y.m.d H:i', $timestamp);
		}

		$link = '/m_chat2/?mode=list&word='.$item['AID'].'&mid1='.$mid1.'&mid2='.$mid2.'&etc02='.$item['ETC02'].'&etc03='.$item['ETC03'];
		$list_html .= '<li class="mypage-message__list-item">';
		$list_html .= '<a href="'.$link.'">';
		$list_html .= '<p class="mypage-message__list-item-ttl">';
		$list_html .= '<span class="mypage-message__list-item-ttl-lead">'.htmlspecialchars($title).'</span>';
		$list_html .= '<span class="mypage-message__list-item-ttl-ico"><img src="/common/images/link__icom_window.svg" alt=""></span>';
		$list_html .= '</p>';
		$list_html .= '<p class="mypage-message__list-item-company">'.htmlspecialchars($partner_m2_name).'</p>';
		$list_html .= '<p class="mypage-message__list-item-date">'.$ldate.'</p>';
		$list_html .= '</a>';
		$list_html .= '</li>';
	}

	return $list_html;
}

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function ExportData()
{
	eval(globals());

	$csv_data = "";

	$StrSQL=" SELECT ";
	$StrSQL.="  DATE_FORMAT(  ";
    $StrSQL.="      STR_TO_DATE(DAT_MCONTACT.NEWDATE, '%Y/%m/%d %H:%i:%s') ";
    $StrSQL.="      , '%Y/%m/%d' ";
    $StrSQL.="  ) as ymd ";

	$StrSQL.="  , DAT_M1.M1_DVAL02 ";
	$StrSQL.="  , DAT_M1.M1_DSEL01 ";
	$StrSQL.="  , DAT_M1.M1_DVAL12 ";
	$StrSQL.="  , DAT_M1.M1_DVAL13 ";
	$StrSQL.="  , DAT_M1.M1_DVAL01 ";
	$StrSQL.="  , DAT_M1.M1_DVAL15 ";
	$StrSQL.="  , DAT_M1.EMAIL as M1_EMAIL";
	$StrSQL.="  , CONCAT(CONCAT(REPLACE(DAT_M1.M1_DSEL01,'M1_DSEL01:',''),DAT_M1.M1_DVAL12),DAT_M1.M1_DVAL13) as M1_ADDRESS ";


    $StrSQL.="  , DAT_M2.M2_DVAL01 ";
    $StrSQL.="  , DAT_M2.M2_DVAL02 ";
    $StrSQL.="  , DAT_M2.M2_DSEL01 ";
    $StrSQL.="  , DAT_M2.M2_DVAL03 ";
    $StrSQL.="  , DAT_M2.M2_DVAL04 ";
    $StrSQL.="  , DAT_M2.M2_DVAL05 ";
    $StrSQL.="  , DAT_M2.EMAIL as M2_EMAIL";
	$StrSQL.="  , CONCAT(CONCAT(REPLACE(DAT_M2.M2_DSEL01,'M2_DSEL01:',''),DAT_M2.M2_DVAL03),DAT_M2.M2_DVAL04) as M2_ADDRESS ";

	$StrSQL.="  , DAT_O1.O1_DVAL01 ";
    $StrSQL.="  , DAT_O1.O1_MSEL03 ";
    $StrSQL.="  , DAT_O1.O1_ETC02 ";
	$StrSQL.="  , DAT_O1.O1_ETC03 ";
	

	$StrSQL.="  , DAT_MCONTACT.ETC02 ";
    $StrSQL.="  , DAT_MCONTACT.ETC04 ";
	$StrSQL.="  , DAT_MCONTACT.ETC06 ";
    $StrSQL.="  , DAT_MCONTACT.STATUS  ";
	$StrSQL.="  , DAT_MCONTACT.MID  ";
	$StrSQL.="  , DAT_MCONTACT.MIDT  ";
	$StrSQL.="  FROM ";
	$StrSQL.="      DAT_MCONTACT  ";
	$StrSQL.="      INNER JOIN DAT_M1  ";
	$StrSQL.="          ON DAT_MCONTACT.MID = DAT_M1.MID  ";
	$StrSQL.="      INNER JOIN DAT_M2  ";
	$StrSQL.="          ON DAT_MCONTACT.MIDT = DAT_M2.MID  ";
	$StrSQL.="      INNER JOIN DAT_O1  ";
	$StrSQL.="          ON DAT_MCONTACT.ETC07 = DAT_O1.OID  ";
	$StrSQL.="  WHERE ";
	$StrSQL.="      DAT_MCONTACT.MID = '".$_SESSION['MID']."' ";
	$StrSQL.="      AND ifnull(DAT_MCONTACT.ETC07,'') !='' ";
	$StrSQL.="  ORDER BY DAT_MCONTACT.NEWDATE DESC ";

	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item=mysqli_num_rows($rs);

	//送付元と送付先の情報（郵便番号、住所、連絡先、名前）、商品、商品金額、送料、合計金額、送料タイプ

	$FieldName1="M1_DVAL02::M1_ADDRESS::M1_DVAL15::M1_EMAIL::";
	$FieldLabel1="送付元郵便番号::送付元住所::送付元電話番号::送付元メールアドレス::";

	$FieldName1.="M2_DVAL02::M2_ADDRESS::M2_DVAL05::M2_EMAIL::";
	$FieldLabel1.="送付先郵便番号::送付先住所::送付先電話番号::送付先メールアドレス::";

	$FieldName1.="O1_DVAL01::O1_ETC02::ETC04::ETC06::O1_ETC03";
	$FieldLabel1.="商品::商品金額::送料::合計金額::送料タイプ";


	// $FieldName1="ymd::M2_DVAL02::M2_DSEL01::M2_DVAL03::M2_DVAL04::M2_DVAL01::M2_DVAL05::EMAIL::O1_DVAL01::O1_MSEL03::O1_ETC02::ETC04::ETC06::STATUS";
	// $FieldLabel1="注文日::郵便番号::都道府県::市町村::番地等::ニックネーム::M2_DVAL05::メールアドレス::商品名::重量::単価::送料::合計金額::ステータス";

	$FieldNameArray = explode("::",$FieldName1);
	$FieldLabelArray = explode("::",$FieldLabel1);


	$soryoParam1="送料A::送料B::送料C::送料D::送料E::送料F::送料G::送料H::送料I::送料J::送料K::送料L::送料無料";
	$soryoParams1=explode("::",$soryoParam1);

	$soryoParam2=POSTAGE_A."::".POSTAGE_B."::".POSTAGE_C."::".POSTAGE_D."::".POSTAGE_E."::".POSTAGE_F."::".POSTAGE_G."::".POSTAGE_H."::".POSTAGE_I."::".POSTAGE_J."::".POSTAGE_K."::".POSTAGE_L."::".POSTAGE_NONE; //送料タイプ
	$soryoParams2=explode("::",$soryoParam2);


	if($item<>0) {
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=member".date('Ymd').".csv");

		$str="";
		for ($j=0; $j<count($FieldLabelArray); $j=$j+1){
			$StrSQL=$StrSQL."`".$FieldLabelArray[$j]."`";
			if ($str!=""){
				$str.=",";
			} 
			$str=$str."\"".$FieldLabelArray[$j]."\"";
		}
		$str=$str."\r\n";
		$csv_data = $str;
		$csv_data = mb_convert_encoding($csv_data, "SJIS-win", "UTF-8");
		echo($csv_data);
		while ($item = mysqli_fetch_assoc($rs)) {
			$str="";
			for ($i=0; $i<count($FieldNameArray); $i=$i+1){
				if ($i!=0){
					$str.=",";
				}

				$val=$item[$FieldNameArray[$i]];
				$val=str_replace($FieldNameArray[$i].":","",$val);

				if($FieldLabelArray[$i]=="送料タイプ"){
					for ($j=0; $j<count($soryoParams1); $j=$j+1) {
						if($val==$soryoParams1[$j]){
							$val=$soryoParams2[$j];
							break;
						}
					}
				}

				$str.="\"".str_replace("\r\n", "[rn]", str_replace("\r", "[r]", str_replace("\n", "[n]", str_replace("\t", "[t]", str_replace($FieldNameArray[$i].":","",$val)))))."\"";
			}
			$csv_data = $str."\r\n";
			$csv_data = mb_convert_encoding($csv_data, "SJIS-win", "UTF-8");
			echo($csv_data);
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
