<?php

	ini_set( 'display_errors', 0 );

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
	session_regenerate_id(true);
	// ---------------------------------------------------------------------

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function MakeHTML($tmp,$mode,$lid)
{
	//extract($GLOBALS);
	eval(globals());

	if ($lid=="99999"){
		$_SESSION['LID']="";
		$lid="";
	}

	if ($mode=="1"){
		if ((strpos($lid,",") ? strpos($lid,",")+1 : 0)>0){
			$l=explode(",",$lid);
			$id=$l[0];
			$pass=$l[1];


			$ConnDB=mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWD, DB_DBNAME);

			$StrSQL="SELECT * FROM DAT_ADMIN WHERE (ADMIN_ID = '".$id."'AND ADMIN_PASSWORD='".$pass."') or (DEV_ID = '".$id."'AND DEV_PASSWORD='".$pass."')";
			$rs=mysqli_query($ConnDB,$StrSQL);
			$cnt=mysqli_num_rows($rs);
			if ($cnt>0){
//mkFa!fGi32(A!S#6f4y8!9%
				$fp=$DOCUMENT_ROOT."../html_a/in.html";
				$_SESSION['LID']=$id;
			} else {
				$fp=$DOCUMENT_ROOT."../html_a/out.html";
				$tmp="ログインIDが正しくありません";
			} 
		} else {
			if ($_SESSION['LID']!="") {
				// if ($_SESSION['LID']=="root"){
					$fp=$DOCUMENT_ROOT."../html_a/in.html";
				// } else {
				// 	$fp=$DOCUMENT_ROOT."../html_a/out.html";
				// 	$_SESSION['LID']="";
				// } 
			} else {
				$fp=$DOCUMENT_ROOT."../html_a/out.html";
			} 
		} 
	} else {
		$fp=$DOCUMENT_ROOT."../html_a/in.html";
	} 


	// $fso is of type "Scripting.FileSystemObject"
//	$tso=fopen($fp,"r");
//	$str=fgets($tso,65535);;
//	fclose($tso);
	$str=@file_get_contents($fp);

	$str=str_replace("[MAIN]",$tmp,$str);

	if ((strpos($tmp,"ckeditor") ? strpos($tmp,"ckeditor")+1 : 0)==0){
		$str=str_replace("[METAF]","<!--",$str);
		$str=str_replace("[METAT]","-->",$str);
	} else {
		$str=str_replace("[METAF]","",$str);
		$str=str_replace("[METAT]","",$str);
	} 

	 $str=str_replace("[CSSDATE]",date('YmdHis'),$str);

	$function_ret=$str;

	return $function_ret;
} 

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function DispParam($str, $tn)
{

	$str=str_replace("[S-".$tn."]","",$str);
	$str=str_replace("[E-".$tn."]","",$str);

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

	$cnt=0;
	while($cnt<20){
		if(strstr($str, "[S-".$tn."]") !== false){
			$a=mb_strpos($str, "[S-".$tn."]");
			$b=mb_strpos($str, "[E-".$tn."]");
			$l=mb_strlen($str);
			$t=mb_strlen($tn);
			if(is_numeric($a) && is_numeric($b)){
				$str=mb_substr($str, 0, $a).mb_substr($str, $b+$t+4, $l);
			}
		}
		$cnt++;
	}
	return $str;

}

//=========================================================================================================
//名前
//機能
//引数
//戻値
//=========================================================================================================
function globals(){
	// PHP 8.1+ 対応: $GLOBALSやスーパーグローバルを除外
	$exclude = array('GLOBALS', '_SERVER', '_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_REQUEST', '_ENV', 'this');
	$vars = array();
	foreach(array_keys($GLOBALS) as $k){
		if (!in_array($k, $exclude)) {
			$vars[] = "$".$k;
		}
	}
	if (empty($vars)) {
		return ";";
	}
	return "global ".join(",", $vars).";";
}

//=========================================================================================================
//名前
//機能
//引数
//戻値
//=========================================================================================================
function pic_resize($orig_file, $resize_width, $resize_height)
{
	// GDライブラリがインストールされているか
	if (!extension_loaded('gd')) {
	    // エラー処理
	    return false;
	}

	$exif = exif_read_data($orig_file,'IFD0'); //Exif読み込み
	$orientation = $exif['Orientation']; //Orientation取得


	switch($orientation){ //Orientationの値によって分ける
	case 1:
		$rotate = 0; //1はそのまま
		break;
	case 3:
		$rotate = 180; //3は180度回転
		break;
	case 6:
		$rotate = 270; //6は右に90度(左に270度)
		break;
	case 8:
		$rotate = 90; //8は右に270度(左に90度)
		break;
	default:
		$rotate = 0; //他は無視
	}

	// 画像情報取得
	$result = getimagesize($orig_file);
	list($orig_width, $orig_height, $image_type) = $result;
	
	// 画像をコピー
	switch ($image_type) {
	    // 1 IMAGETYPE_GIF
	    // 2 IMAGETYPE_JPEG
	    // 3 IMAGETYPE_PNG
            case 1: $im = imagecreatefromgif($orig_file); break;
            case 2: $im = imagecreatefromjpeg($orig_file);  break;
            case 3: $im = imagecreatefrompng($orig_file); break;
            default: //エラー処理 
                return false;
        }

	if($orig_width>$orig_height){
		$resize_height=$orig_height*($resize_width/$orig_width);
	} else {
		$resize_width=$orig_width*($resize_height/$orig_height);
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
	if (($image_type == 1) OR ($image_type==3)) {
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

	if($rotate){ //$rotateが0じゃなかったら
		$new_image = imagerotate($new_image, $rotate, 0); //$rotateだけ回転させてから
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
//名前 パスワードをハッシュ化する
//機能 
//引数 
//戻値 
//=========================================================================================================
function pwd_hash($pwd){
	return password_hash($pwd, PASSWORD_DEFAULT);
}
?>
