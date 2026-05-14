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
	if($_POST['lineval']==""){
		$lineval=$_GET['lineval'];
	} else {
		$lineval=$_POST['lineval'];
	}
	if($_POST['stationval']==""){
		$stationval=$_GET['stationval'];
	} else {
		$stationval=$_POST['stationval'];
	}
	$StrSQL="SELECT CD1 FROM DAT_ADDRESS WHERE N1='".$pref."'";
	$rs=mysqli_query(ConnDB(),$StrSQL);
	$item = mysqli_fetch_assoc($rs);
	$cd1=$item["CD1"];

	$j=0;
	$StrSQL="SELECT CD4, N3 FROM DAT_ROSEN  ";
	$StrSQL.=" WHERE PREFCD = '".$cd1."'";
	$StrSQL.="  AND N2 = '".$lineval."'";
	$StrSQL.=" group by CD4, N3 order by CD4";
	// echo $StrSQL;
	$rs=mysqli_query(ConnDB(), $StrSQL);
	while ($item = mysqli_fetch_assoc($rs)) {
		$checked="";
		$val=$item['N3']."駅";
		if(strpos($stationval,$val)!==false){
			$checked="checked";
		}
		$tmp.="<li><input ".$checked." id=\"".$fieldname1.$j."\" type=\"radio\" class=\"".$fieldname1."\" name=\"".$fieldname1."\" value=\"".$fieldname1.":".$val."\"><label for=\"".$fieldname1.$j."\">".$val."</label></li>";
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
