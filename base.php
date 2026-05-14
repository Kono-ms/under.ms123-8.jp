<?php

ini_set('display_errors', 0);
date_default_timezone_set('Asia/Tokyo');

// 2022.08.19 yamamoto 脆弱性対応セット
// ---------------------------------------------------------------------
// JavaScriptインジェクション対策1（http経由のみアクセス可能にする）
ini_set('session.cookie_httponly', 1);
// JavaScriptインジェクション対策2（不正なセッションIDを拒否）
ini_set('session.user_strict_mode', 1);
// セッションIDインジェクション対策（透過セッションID禁止）
ini_set('session.use_trans_sid', 0);
// httpsのみ許可する
ini_set('session.cookie_secure', 1);

// クリックジャッキング対策
header("X-Frame-Options: SAMEORIGIN");
header("Content-Security-Policy: frame-ancestors 'self'");

// ここより前にsession_startが実行されないように、
// 各index.phpのsession_startはすべて削除する
// (そうしないと上記設定が有効にならない)
session_start();

// セッションIDの強制変更
// session_regenerate_id(true);
// ---------------------------------------------------------------------

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function MakeHTML($str, $mode, $lid)
{
	//extract($GLOBALS);
	eval (globals());

	if ($lid == "99999") {
		$_SESSION['MATT'] = "";
		$_SESSION['M-ID'] = "";
		$_SESSION['HID'] = "";
		$_SESSION['MID'] = "";
		$lid = "";
	}

	// プラン関連
/*
	if($_SESSION['HID']!="" && $_SESSION['PLAN']==""){
		$url=$_SERVER['REQUEST_URI'];
		if($url=="/v_iine/" || $url=="/v_search/" || $url=="/searchv/" || $url=="/v_fav/" || $url=="/v_message/" || $url=="/v_editj/"){
			$StrSQL="SELECT ETC06 from DAT_HOSPITAL where HID='".$_SESSION['HID']."';";
			$rs=mysqli_query(ConnDB(),$StrSQL);
			$item = mysqli_fetch_assoc($rs);
			if($item['ETC06']==""){
				$fp="../planerr0.html";
			} else {
				$fp="../planerr1.html";
			}
			$str=@file_get_contents($fp);
		}
	}
*/
	// プラン関連


	// $statuslist = STATUS_ARRAY;

	// 	for($i=0; $i<count($statuslist); $i++){
// 		echo "<!--i:".$i.":".$statuslist[$i]."-->";
// 	}
//$_SESSION['MATT']でテンプレートを制御

	$cnt = 0;

	if ($_SESSION['MATT'] != "") {
		$str = str_replace("header.html", "header" . $_SESSION['MATT'] . ".html", $str);
	}

	while (strpos($str, "<!--template=") > 0 && $cnt < 20) {
		$tmpl = GetTemplateFileName($str, strpos($str, "<!--template="));

		$url = explode("/", $_SERVER["REQUEST_URI"]);

		if (count($url) == 2) {
			$fp = str_replace("/common/", "./common/", $tmpl);
		} elseif (count($url) == 3) {
			$fp = str_replace("/common/", "../common/", $tmpl);
		} elseif (count($url) == 4) {
			$fp = str_replace("/common/", "../common/", $tmpl);
		} elseif (count($url) == 5) {
			$fp = str_replace("/common/", "../common/", $tmpl);
		} else {
			$fp = str_replace("/common/", "../../common/", $tmpl);
		}

		$tmp = @file_get_contents($fp);
		$str = str_replace("<!--template=" . $tmpl . "-->", $tmp, $str);
		$cnt = $cnt + 1;
	}

	// NOTE: 20230916 タイムスタンプを取得します。CSS、JS、imgファイルなどのキャッシュクリア目的で、URLにパラメータを追加する際に使用します。
	$str = str_replace("[DEBUG-TIMESTAMP]", time(), $str);

	if ($_SESSION['MID'] == "") {
		$str = str_replace("[LOGINM-S]", "<!--", $str);
		$str = str_replace("[LOGINM-E]", "-->", $str);
		$str = str_replace("[LOGOUTM-S]", "", $str);
		$str = str_replace("[LOGOUTM-E]", "", $str);
		$str = str_replace("[L-M-ID]", "", $str);
		$str = str_replace("[L-MID]", "", $str);
		$str = str_replace("[L-MNAME]", "", $str);
		$str = str_replace("[L-MCOMPANY]", "会社名未設定", $str);
	} else {
		$str = str_replace("[LOGINM-S]", "", $str);
		$str = str_replace("[LOGINM-E]", "", $str);
		$str = str_replace("[LOGOUTM-S]", "<!--", $str);
		$str = str_replace("[LOGOUTM-E]", "-->", $str);
		$loginMName = $_SESSION['MNAME'];
		if ($_SESSION['MATT'] == "2") {
			$StrSQL = "SELECT M2_DVAL01, M2_DVAL02 FROM DAT_M2 where MID='" . $_SESSION['MID'] . "' and ENABLE='ENABLE:公開中' order by ID desc LIMIT 1";
			$rs = mysqli_query(ConnDB(), $StrSQL);
			if ($rs && mysqli_num_rows($rs) > 0) {
				$item = mysqli_fetch_assoc($rs);
				$loginMName = trim($item['M2_DVAL01']) . trim($item['M2_DVAL02']);
			}
			if ($loginMName == "") {
				$loginMName = "ユーザー名未設定";
			}
		}

		$str = str_replace("[L-M-ID]", $_SESSION['M-ID'], $str);
		$str = str_replace("[L-MID]", $_SESSION['MID'], $str);
		$str = str_replace("[L-MNAME]", htmlspecialchars($loginMName, ENT_QUOTES, 'UTF-8'), $str);

		$loginCompanyName = trim($_SESSION['MNAME']);
		if ($_SESSION['MATT'] == "1" && $_SESSION['MID'] != "") {
			$StrSQL = "SELECT M1_DVAL01 FROM DAT_M1 where MID='" . mysqli_real_escape_string(ConnDB(), $_SESSION['MID']) . "' LIMIT 0,1;";
			$rs = mysqli_query(ConnDB(), $StrSQL);
			if ($rs && mysqli_num_rows($rs) > 0) {
				$item = mysqli_fetch_assoc($rs);
				$loginCompanyName = trim($item['M1_DVAL01']);
			}
		}
		if ($loginCompanyName == "") {
			$loginCompanyName = "会社名未設定";
		}
		$str = str_replace("[L-MCOMPANY]", htmlspecialchars($loginCompanyName, ENT_QUOTES, 'UTF-8'), $str);

		$StrSQL = "SELECT ID FROM DAT_MESSAGE where AID like '%" . $_SESSION['MID'] . "%' and RID<>'" . $_SESSION['MID'] . "' and (NOREAD is null or NOREAD='') ";
		//2020/12/28 gaosan ADD START
		$StrSQL .= " AND NOT EXISTS (SELECT * FROM DAT_BL WHERE DAT_BL.MID1 = '" . $_SESSION['MID'] . "' and DAT_BL.MID2 = SUBSTRING(DAT_MESSAGE.AID,1,7)) ";
		$StrSQL .= " AND NOT EXISTS (SELECT * FROM DAT_BL WHERE DAT_BL.MID1 = '" . $_SESSION['MID'] . "' and DAT_BL.MID2 = SUBSTRING(DAT_MESSAGE.AID,9,7)) ";
		//2020/12/28 gaosan ADD END

		$rs = mysqli_query(ConnDB(), $StrSQL);
		$item = mysqli_num_rows($rs);
		if ($item > 0) {
			$str = str_replace("[MIDOKU]", $item, $str);
			$str = DispParam($str, "MIDOKU");
		} else {
			$str = str_replace("[MIDOKU]", "", $str);
			$str = DispParamNone($str, "MIDOKU");
		}

		$StrSQL = "SELECT ID FROM DAT_IINE where MIDT='" . $_SESSION['MID'] . "' ";
		// 2021.03.15 yamamoto いいね既読フラグ導入
		$StrSQL .= " and (ETC01 is null or ETC01 != '既読') ";
		//2020/12/28 gaosan ADD START
		$StrSQL .= " and NOT EXISTS (SELECT * FROM DAT_BL WHERE DAT_BL.MID1 = '" . $_SESSION['MID'] . "' and DAT_BL.MID2 = DAT_IINE.MID); ";
		//2020/12/28 gaosan ADD END

		$rs = mysqli_query(ConnDB(), $StrSQL);
		$item = mysqli_num_rows($rs);
		if ($item > 0) {
			$str = str_replace("[IINECNT]", $item, $str);
			$str = DispParam($str, "IINECNT");
		} else {
			$str = str_replace("[IINECNT]", "", $str);
			$str = DispParamNone($str, "IINECNT");
		}
	}

	// プラン関連
/*
	if(strstr($_SESSION['PLAN'], "Basic") !== false){
		$str=DispParam($str, "PLANB");
		$str=DispParamNone($str, "PLANP");

		$su1=0;
		$su2=0;
	} else {
		$str=DispParamNone($str, "PLANB");
		$str=DispParam($str, "PLANP");

		$d1=date("d");
		$d2=substr($_SESSION['LDATE'],-2,2);
		if(intval($d1)>=intval($d2)){
			$ds=date("Y/m")."/".$d2;
			$de=date("Y/m", strtotime('+1 month'))."/".$d2;
		} else {
			$ds=date("Y/m", strtotime('-1 month'))."/".$d2;
			$de=date("Y/m")."/".$d2;
		}
		$StrSQL="SELECT ID FROM DAT_IINE where HID='".$_SESSION['HID']."' and NEWDATE>='".$ds."' and NEWDATE<'".$de."';";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item=mysqli_num_rows($rs);
		$su1=30-$item;

		$StrSQL="SELECT ID FROM DAT_SCOUT where HID='".$_SESSION['HID']."' and NEWDATE>='".$ds."' and NEWDATE<'".$de."';";
		$rs=mysqli_query(ConnDB(),$StrSQL);
		$item=mysqli_num_rows($rs);
		$su2=10-$item;

	}
	$str=str_replace("[IINEZAN]",$su1,$str);
	$str=str_replace("[SCOUTZAN]",$su2,$str);

	$str=str_replace("<li><a href=\"/v_search/\" class=\"active\">残りいいね数 30回（更新日毎月日）</a></li>","",$str);
	$str=str_replace("<li><a href=\"/v_search/\" class=\"active\">有効期限 </a></li>","",$str);
*/
	// プラン関連

	// ご利用の流れを3タイプから選択
	if (CheckSession(1)) {
		$str = DispParamNone($str, "FLOW");
		$str = DispParam($str, "FLOW1");
		$str = DispParamNone($str, "FLOW2");
	} else if (CheckSession(2)) {
		$str = DispParamNone($str, "FLOW");
		$str = DispParamNone($str, "FLOW1");
		$str = DispParam($str, "FLOW2");
	} else {
		$str = DispParam($str, "FLOW");
		$str = DispParamNone($str, "FLOW1");
		$str = DispParamNone($str, "FLOW2");
	}

	$str = str_replace("[CSSDATE]", date('YmdHis'), $str);

	$function_ret = $str;

	return $function_ret;
}

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function ChatText($str)
{
	//extract($GLOBALS);
	eval (globals());

	$text = htmlspecialchars($str);
	//	$text = str_replace("\r\n","",$text);
	$text = str_replace("\n", "<br />", $str);
	//	$text = mb_ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", "<a href=\"\\0\" target=\"_blank\">\\0</a>", $text);
//	$text = mb_ereg_replace("[[:alnum:]]+@[[:alnum:]]+.+[[:alnum:]]", "<a href=\"mailto:\\0\">\\0</a>", $text);

	return $text;
}

//=========================================================================================================
//名前
//機能
//引数
//戻値
//=========================================================================================================
function GetTemplateFileName($tmp, $pos)
{

	$pos = $pos + 13;

	$str = "";
	for ($i = 0; $i < 99; $i++) {
		$s = substr($tmp, $pos + $i, 1);
		$s2 = substr($tmp, $pos + $i + 1, 1);
		if ($s != "-" || $s2 != "-") {
			$str = $str . $s;
		} else {
			break;
		}
	}

	$function_ret = $str;

	return $function_ret;

}

//=========================================================================================================
//名前
//機能
//引数
//戻値
//=========================================================================================================
function globals()
{
	// PHP 8.1+ 対応: $GLOBALSやスーパーグローバルを除外
	$exclude = array('GLOBALS', '_SERVER', '_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_REQUEST', '_ENV', 'this');
	$vars = array();
	foreach (array_keys($GLOBALS) as $k) {
		if (!in_array($k, $exclude)) {
			$vars[] = "$" . $k;
		}
	}
	if (empty($vars)) {
		return ";";
	}
	return "global " . join(",", $vars) . ";";
}

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function LeftText($str, $n)
{
	if (mb_strlen($str, "UTF-8") > $n) {
		return mb_substr($str, 0, $n, "UTF-8") . "…";
	} else {
		return $str;
	}

}

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function DispParam($str, $tn)
{

	$str = str_replace("[S-" . $tn . "]", "", $str);
	$str = str_replace("[E-" . $tn . "]", "", $str);

	return $str;

}

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function DispParamNone($str, $tn)
{

	$cnt = 0;
	while ($cnt < 20) {
		if (strstr($str, "[S-" . $tn . "]") !== false) {
			$a = mb_strpos($str, "[S-" . $tn . "]");
			$b = mb_strpos($str, "[E-" . $tn . "]");
			$l = mb_strlen($str);
			$t = mb_strlen($tn);
			if (is_numeric($a) && is_numeric($b)) {
				$str = mb_substr($str, 0, $a) . mb_substr($str, $b + $t + 4, $l);
			}
		}
		$cnt++;
	}
	return $str;

}


function pic_resize($orig_file, $resize_width, $resize_height)
{
	// GDライブラリがインストールされているか
	if (!extension_loaded('gd')) {
		// エラー処理
		return false;
	}

	// 画像情報取得
	$result = getimagesize($orig_file);
	list($orig_width, $orig_height, $image_type) = $result;

	// 画像をコピー
	switch ($image_type) {
		// 1 IMAGETYPE_GIF
		// 2 IMAGETYPE_JPEG
		// 3 IMAGETYPE_PNG
		case 1:
			$im = imagecreatefromgif($orig_file);
			break;
		case 2:
			$im = imagecreatefromjpeg($orig_file);
			break;
		case 3:
			$im = imagecreatefrompng($orig_file);
			break;
		default: //エラー処理
			return false;
	}

	if ($orig_width > $orig_height) {
		$resize_height = $orig_height * ($resize_width / $orig_width);
	} else {
		$resize_width = $orig_width * ($resize_height / $orig_height);
	}

	// コピー先となる空の画像作成
	$new_image = imagecreatetruecolor($resize_width, $resize_height);
	if (!$new_image) {
		// エラー処理
		// 不要な画像リソースを保持するメモリを解放する
		imagedestroy($im);
		return false;
	}

	// GIF、PNGの場合、透過処理の対応を行う
	if (($image_type == 1) OR ($image_type == 3)) {
		imagealphablending($new_image, false);
		imagesavealpha($new_image, true);
		$transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
		imagefilledrectangle($new_image, 0, 0, $resize_width, $resize_height, $transparent);
	}

	// コピー画像を指定サイズで作成
	if (!imagecopyresampled($new_image, $im, 0, 0, 0, 0, $resize_width, $resize_height, $orig_width, $orig_height)) {
		// エラー処理
		// 不要な画像リソースを保持するメモリを解放する
		imagedestroy($im);
		imagedestroy($new_image);
		return false;
	}

	ImageJPEG($new_image, $orig_file);
	// コピー画像を保存
	// $new_image : 画像データ
	// $new_fname : 保存先と画像名
	// クオリティ

	switch ($image_type) {
		// 1 IMAGETYPE_GIF
		// 2 IMAGETYPE_JPEG
		// 3 IMAGETYPE_PNG
//            case 1: $result = imagegif($new_image, $new_fname, $quality); break;
		//          case 2: $result = imagejpeg($new_image, $new_fname, $quality); break;
		//        case 3: $result = imagepng($new_image, $new_fname, $quality); break;
		default: //エラー処理
			return false;
	}

	if (!$result) {
		// エラー処理
		// 不要な画像リソースを保持するメモリを解放する
		imagedestroy($im);
		imagedestroy($new_image);
		return false;
	}

	// 不要になった画像データ削除
	imagedestroy($im);
	imagedestroy($new_image);

}

//=========================================================================================================
//名前 
//機能 メールテンプレートを取得して定数を埋め込む
//引数 
//戻値 
//=========================================================================================================
function GetMailTemplate($mailname)
{
	eval (globals());

	$maildata = array();

	$StrSQL = "SELECT TITLE,BODY from DAT_MAIL where MAILNAME='MAILNAME:" . $mailname . "';";
	$rs = mysqli_query(ConnDB(), $StrSQL);
	$item = mysqli_fetch_assoc($rs);
	if ($item !== null && $item !== false) {
		$maildata['TITLE'] = $item['TITLE'];
		$maildata['BODY'] = $item['BODY'];

		$maildata['BODY'] = str_replace("[BASE_URL]", BASE_URL, $maildata['BODY']);
		$maildata['BODY'] = str_replace("[SENDER_EMAIL]", SENDER_EMAIL, $maildata['BODY']);
		$maildata['BODY'] = str_replace("[SENDER_NAME]", SENDER_NAME, $maildata['BODY']);
		$maildata['BODY'] = str_replace("[WEBSITE_NAME]", "UNDER", $maildata['BODY']);
		$maildata['BODY'] = str_replace("[COMPANY_NAME]", COMPANY_NAME, $maildata['BODY']);
		$maildata['BODY'] = str_replace("[M1_CAPTION]", M1_CAPTION, $maildata['BODY']);
		$maildata['BODY'] = str_replace("[M2_CAPTION]", M2_CAPTION, $maildata['BODY']);

		$maildata['TITLE'] = str_replace("[WEBSITE_NAME]", "UNDER", $maildata['TITLE']);
		$maildata['TITLE'] = str_replace("[O1_CAPTION]", O1_CAPTION, $maildata['TITLE']);
		$maildata['TITLE'] = str_replace("[O2_CAPTION]", O2_CAPTION, $maildata['TITLE']);
		$maildata['TITLE'] = str_replace("[M1_CAPTION]", M1_CAPTION, $maildata['TITLE']);
		$maildata['TITLE'] = str_replace("[M2_CAPTION]", M2_CAPTION, $maildata['TITLE']);
	}

	return $maildata;
}


//=========================================================================================================
//名前 
//機能 メールテンプレートを取得して定数を埋め込む
//引数 
//戻値 
//=========================================================================================================
function GetMailTemplateStatus($mailname, $status)
{
	eval (globals());

	$maildata = array();

	$status = str_replace("STATUS:", "", $status);

	//ステータス含みで検索を行い、なければ無しバージョンで検索
	$StrSQL = "SELECT TITLE,BODY from DAT_MAIL where MAILNAME='MAILNAME:" . $mailname . "（" . $status . "）';";
	$rs = mysqli_query(ConnDB(), $StrSQL);
	$item = mysqli_fetch_assoc($rs);
	if ($item["TITLE"] == "") {
		$StrSQL = "SELECT TITLE,BODY from DAT_MAIL where MAILNAME='MAILNAME:" . $mailname . "';";
		$rs = mysqli_query(ConnDB(), $StrSQL);
		$item = mysqli_fetch_assoc($rs);
	}

	$maildata['TITLE'] = $item['TITLE'];
	$maildata['BODY'] = $item['BODY'];

	$maildata['BODY'] = str_replace("[BASE_URL]", BASE_URL, $maildata['BODY']);
	$maildata['BODY'] = str_replace("[SENDER_EMAIL]", SENDER_EMAIL, $maildata['BODY']);
	$maildata['BODY'] = str_replace("[SENDER_NAME]", SENDER_NAME, $maildata['BODY']);
	$maildata['BODY'] = str_replace("[WEBSITE_NAME]", "UNDER", $maildata['BODY']);
	$maildata['BODY'] = str_replace("[COMPANY_NAME]", COMPANY_NAME, $maildata['BODY']);
	$maildata['BODY'] = str_replace("[M1_CAPTION]", M1_CAPTION, $maildata['BODY']);
	$maildata['BODY'] = str_replace("[M2_CAPTION]", M2_CAPTION, $maildata['BODY']);

	$maildata['TITLE'] = str_replace("[WEBSITE_NAME]", "UNDER", $maildata['TITLE']);
	$maildata['TITLE'] = str_replace("[O1_CAPTION]", O1_CAPTION, $maildata['TITLE']);
	$maildata['TITLE'] = str_replace("[O2_CAPTION]", O2_CAPTION, $maildata['TITLE']);
	$maildata['TITLE'] = str_replace("[M1_CAPTION]", M1_CAPTION, $maildata['TITLE']);
	$maildata['TITLE'] = str_replace("[M2_CAPTION]", M2_CAPTION, $maildata['TITLE']);


	return $maildata;
}


//=========================================================================================================
//名前 
//機能 セッションチェック（空かどうかではなくセッションのMIDが有効かどうか）
//引数 
//戻値 
//=========================================================================================================
function CheckSession($side)
{
	eval (globals());

	$StrSQL = "SELECT ID from DAT_M" . $side . " where MID='" . $_SESSION['MID'] . "';";

	$rs = mysqli_query(ConnDB(), $StrSQL);
	$item = mysqli_fetch_assoc($rs);
	if ($item !== null && $item !== false) {
		return true;
	} else {
		return false;
	}

}
//=========================================================================================================
//名前 
//機能 年齢取得
//引数 $birthday "1990-07-01"
//戻値 
//=========================================================================================================
function getAge($birthday)
{

	// 現在日付
	$now = date('Ymd');

	// 誕生日
	// $birthday = "1990-07-01";
	$birthday = str_replace("-", "", $birthday);

	// 年齢
	$age = floor(($now - $birthday) / 10000);
	if ($age > 0) {
		return $age;
	} else {
		return 0;
	}


}
//=========================================================================================================
//名前 パスワードをハッシュ化する
//機能 
//引数 
//戻値 
//=========================================================================================================
function pwd_hash($pwd)
{
	return password_hash($pwd, PASSWORD_DEFAULT);
}
//=========================================================================================================
//名前 文字列暗号化
//機能 プログラムのメイン関数
//引数 なし
//戻値 なし
//=========================================================================================================
function strEncrypt($cid)
{
	// 暗号化キー
	$key = 'ms123_aes';
	// 暗号化方式
	$method = 'aes-128-cbc';
	// OPENSSL_RAW_DATA と OPENSSL_ZERO_PADDING を指定可
	$options = 0;
	// IV
	$iv_string = "fq5ctJw8ZJfGrpOFYABw5w==";
	$iv = base64_decode($iv_string);

	$encrypted = bin2hex(openssl_encrypt($cid, $method, $key, OPENSSL_RAW_DATA, $iv));

	return $encrypted;
}
//=========================================================================================================
//名前 文字列複合化
//機能 プログラムのメイン関数
//引数 なし
//戻値 なし
//=========================================================================================================
function strDecrypt($encrypted)
{
	// 暗号化キー
	$key = 'ms123_aes';
	// 暗号化方式
	$method = 'aes-128-cbc';
	// OPENSSL_RAW_DATA と OPENSSL_ZERO_PADDING を指定可
	$options = 0;
	// IV
	$iv_string = "fq5ctJw8ZJfGrpOFYABw5w==";
	$iv = base64_decode($iv_string);

	$decrypted = openssl_decrypt(hex2bin($encrypted), $method, $key, OPENSSL_RAW_DATA, $iv);

	return $decrypted;
}
?>