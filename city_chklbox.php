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

	if($_POST['prefs']==""){
		$prefs=$_GET['prefs'];
	} else {
		$prefs=$_POST['prefs'];
	}
	if($_POST['fieldname1']==""){
		$fieldname1=$_GET['fieldname1'];
	} else {
		$fieldname1=$_POST['fieldname1'];
	}
	if($_POST['cityvals']==""){
		$cityvals=$_GET['cityvals'];
	} else {
		$cityvals=$_POST['cityvals'];
	}
	
	$j=0;
	// $tmp="<option value=\"\">▼選択して下さい</option>";
	$StrSQL="SELECT N2,N3 FROM DAT_ADDRESS WHERE 1=1 ";
	$pref_array=explode(",",$prefs);
	if(get_count($pref_array)>0){
		$StrSQL.=" AND ( ";
		for ($i=0; $i<get_count($pref_array); $i=$i+1) {
			if($i!=0){
				$StrSQL.=" OR ";
			}
			$StrSQL.=" N1 LIKE '%".$pref_array[$i]."%'";
		}
		$StrSQL.=" ) ";
	}
	$StrSQL.="  order by cast(SORT as signed) asc";
	// echo $StrSQL;
	$rs=mysqli_query(ConnDB(), $StrSQL);
	while ($item = mysqli_fetch_assoc($rs)) {
		$checked="";
		$val=str_replace("\r", "", str_replace("\n", "", $item['N2'].$item['N3']));
		if(strpos($cityvals,"$val")!==false){
			$selected="checked";
		}
		$tmp.="<li><input ".$checked." id=\"".$fieldname1.$j."\" type=\"checkbox\" class=\"".$fieldname1."\" name=\"".$fieldname1."[]\" value=\"".$fieldname1.":".$val."\"><label for=\"".$fieldname1.$j."\">".$val."</label></li>";
		// $tmp.="<option value=\"".$fieldname1.":".$val."\">".$val."</option>";
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
