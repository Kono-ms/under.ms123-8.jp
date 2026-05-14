<?php
	session_start();
	require __DIR__."/config.php";
	require __DIR__."/base.php";
// ini_set( 'display_errors', 1 );
set_time_limit(7200);

Main();

//=========================================================================================================
//名前 
//機能\ 
//引数 
//戻値 
//=========================================================================================================
function Main()
{
	eval(globals());

	if($_POST['pref']==""){
		$pref=$_GET['pref'];
	} else {
		$pref=$_POST['pref'];
	}
	if($_POST['fieldname1']==""){
		$fieldname1=$_GET['fieldname1'];
	} else {
		$fieldname1=$_POST['fieldname1'];
	}
	if($_POST['cityval']==""){
		$cityval=$_GET['cityval'];
	} else {
		$cityval=$_POST['cityval'];
	}
	
	$j=0;
	$tmp="<option value=\"\">▼選択して下さい</option>";
	$StrSQL="SELECT N2,N3 FROM DAT_ADDRESS  ";
	$StrSQL.=" WHERE N1 = '".$pref."'";
	$StrSQL.="  order by cast(SORT as signed) asc";
	// echo $StrSQL;
	$rs=mysqli_query(ConnDB(), $StrSQL);
	while ($item = mysqli_fetch_assoc($rs)) {
		$selected="";
		$val=str_replace("\r", "", str_replace("\n", "", $item['N2'].$item['N3']));
		if($cityval==$val){
			$selected="selected";
		}
		
		$tmp.="<option ".$selected." value=\"".$fieldname1.":".$val."\">".$val."</option>";
		$j=$j+1;
	}
	
	print $tmp;
	
	return $function_ret;
} 

//=========================================================================================================
//【関数名】	:ConnDB()
//【機能\】	:データベースへの接続
//【引数】	:なし
//【戻り値】	:なし
//【備考】	:DB接続
//=========================================================================================================
function ConnDB()
{
	eval(globals());
	$ConnDB=mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWD, DB_DBNAME);
	return $ConnDB;
} 

?>
