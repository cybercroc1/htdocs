<?php
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
ini_set('max_input_vars','100000');
set_time_limit(120);
include("../../sc_conf/sc_session");
session_start();
set_error_handler('fError');

extract($_REQUEST);

if ($_SESSION['project']['id']==0 and $_SESSION['admin']<>1) exit();
if ($_SESSION['project']['ch_sc']<>1 or !isset($_SESSION['login_id'])) {echo "<font color=red>Страница недоступна!</font>"; exit();} 

include("../../sc_conf/sc_conn_string");

if(isset($del_table)) {
$del=OCIParse($c,"delete from sc_dynamic_table where id='".$del_table."' and project_id='".$_SESSION['project']['id']."'");
OCIExecute($del,OCI_DEFAULT);
OCICommit($c);
echo "<script>parent.location='edit_table.php'</script>";
exit();
}
if(isset($del_template)) {
$del=OCIParse($c,"delete from sc_dynamic_table where id='".$del_template."' and login_id='".$_SESSION['login_id']."'");
OCIExecute($del,OCI_DEFAULT);
OCICommit($c);
echo "<script>parent.location='edit_table.php'</script>";
exit();
}


if(!isset($table_id)) $table_id='';
if(!isset($template_id)) $template_id='';
if(!isset($table_name)) $table_name='';
//if(isset($edit_blog)) $edit_blog='&edit_blog'; else $edit_blog='';

echo "--".$table_id."-".$table_name."--";

if($save_as=='table') {
	if(isset($other_project_id) and $other_project_id<>$_SESSION['project']['id']) {
		$t=$table_id;
		$table_id='';
		$project_id=$other_project_id;
		echo $project_id;
		}
		else {		
			$project_id=$_SESSION['project']['id'];
		}	

	$login_id='';
	if($table_id=='') {$reload_page='y';} else {$reload_page='n';}
}
if($save_as=='template') {
	$project_id=''; 
	$login_id=$_SESSION['login_id'];
	
	if($table_id=='') {$reload_page='y';} else {$reload_page='n';}
	//$reload_page='n';
	$table_id=$template_id;
}


if($table_project_id==$_SESSION['project']['id']) {

if($table_id=='' and !isset($no_change)) {
	$q=OCIParse($c,"select SEQ_SC_DYNAMIC_TABLE_ID.nextval from dual");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$table_id=OCIResult($q,"NEXTVAL");
	if($table_name=='') $table_name=$table_id;
	
	$ins_table=OCIParse($c,"insert into sc_dynamic_table (id,project_id,name,attrib,style,row_count,col_count,login_id) 
	values ('".$table_id."','".$project_id."',:name,:attrib,:style,'".$row_count."','".$col_count."','".$login_id."')");

	OCIBindByName($ins_table,":name",$table_name);
	OCIBindByName($ins_table,":attrib",$table_attrib);
	OCIBindByName($ins_table,":style",$table_style);
	OCIExecute($ins_table, OCI_DEFAULT);
}
else {
	$q=OCIParse($c,"select id from sc_dynamic_table where id='".$table_id."' and (project_id='".$project_id."' or login_id='".$login_id."')");
	OCIExecute($q,OCI_DEFAULT);
	if(!OCIFetch($q)) {echo "<font color=red size=3>Эту таблицу изменить нельзя!</font>"; exit();}	
	
	if(isset($no_change)) {
		$upd_table=OCIParse($c,"update sc_dynamic_table 
		set name=nvl(:name,nvl(name,id))
		where id='".$table_id."' and (project_id='".$project_id."' or login_id='".$login_id."')");
	}
	else {
		$upd_table=OCIParse($c,"update sc_dynamic_table 
		set name=nvl(:name,nvl(name,id)), attrib=:attrib, style=:style, row_count='".$row_count."',col_count='".$col_count."'
		where id='".$table_id."' and (project_id='".$project_id."' or login_id='".$login_id."')");
	
		OCIBindByName($upd_table,":attrib",$table_attrib);
		OCIBindByName($upd_table,":style",$table_style);
	}
	OCIBindByName($upd_table,":name",$table_name);	
	OCIExecute($upd_table, OCI_DEFAULT);
}
if(!isset($no_change)) {
	$del=OCIParse($c,"delete from sc_dynamic_table_rows where table_id='".$table_id."'");
	OCIExecute($del,OCI_DEFAULT);

	$ins_row=OCIParse($c,"insert into sc_dynamic_table_rows (table_id,row_num,attrib,style,height, active_head_lvl) 
	values ('".$table_id."',:row_num,'',:style,:height,:active_head_lvl)");

	$ins_cell=OCIParse($c,"insert into sc_dynamic_table_cells (table_id,row_num,cell_num,attrib,style,html,display,faq_id,phones,width, height) 
	values ('".$table_id."',:row_num,:cell_num,:attrib,:style,EMPTY_CLOB(),:display,decode(:faq,'y',SEQ_SC_DYNAMIC_TABLE_FAQ_ID.nextval,:faq),:phones,:width,:height) RETURNING html INTO :html_clob");
	$html_clob = oci_new_descriptor($c, OCI_D_LOB);
	
	foreach($row_height as $rownum => $height) {

		OCIBindByName($ins_row,":row_num",$rownum);
		OCIBindByName($ins_row,":style",$row_style[$rownum]);
		OCIBindByName($ins_row,":height",$height);
		if(isset($row_active_head_lvl[$rownum]) and $row_active_head_lvl[$rownum]>0) $tmp_active_head_lvl=$row_active_head_lvl[$rownum]; else $tmp_active_head_lvl="";
		OCIBindByName($ins_row,":active_head_lvl",$tmp_active_head_lvl);
		OCIExecute($ins_row, OCI_DEFAULT);
		foreach($col_width as $cellnum => $width) {
			
			if($rownum>0 and $cellnum>0) {
				
				//if(!isset($cell_style[$rownum][$cellnum])) $cell_style[$rownum][$cellnum])='';
				//if(!isset($cell_attrib[$rownum][$cellnum])) $cell_attrib[$rownum][$cellnum]='';
				
				if(substr_count($cell_style[$rownum][$cellnum],'display:none')>0) $display='none'; else $display='';
				if(substr_count($cell_attrib[$rownum][$cellnum],'phones="y"')>0) $phones='y'; else $phones='';
			
				if(isset($cell_html[$rownum][$cellnum])) $cell_html[$rownum][$cellnum]=str_replace(" [href]="," href=",$cell_html[$rownum][$cellnum]);
				else $cell_html[$rownum][$cellnum]='';
			
				
				//if(isset($other_project_id) and $other_project_id<>$_SESSION['project']['id'] and $faq[$rownum][$cellnum]*1>0) 
				//	$faq[$rownum][$cellnum]='y'; 

				if(preg_match('/(?<=rowspan=")(([2-9]\d*)|(1\d+))/i',$cell_attrib[$rownum][$cellnum])) $height='';
				if(preg_match('/(?<=colspan=")(([2-9]\d*)|(1\d+))/i',$cell_attrib[$rownum][$cellnum])) $width='';			
			}
			else {
				$cell_style[$rownum][$cellnum]=''; $cell_attrib[$rownum][$cellnum]=''; $cell_html[$rownum][$cellnum]=''; 
				$faq[$rownum][$cellnum]=''; $display=''; $phones='';
			}
			
			OCIBindByName($ins_cell,":row_num",$rownum);
			OCIBindByName($ins_cell,":cell_num",$cellnum);
			OCIBindByName($ins_cell,":style",$cell_style[$rownum][$cellnum]);
			OCIBindByName($ins_cell,":attrib",$cell_attrib[$rownum][$cellnum]);		
			OCIBindByName($ins_cell, ":html_clob", $html_clob, -1, OCI_B_CLOB);
			//OCIBindByName($ins_cell,":html",$cell_html[$rownum][$cellnum]);
			OCIBindByName($ins_cell,":display",$display);
			OCIBindByName($ins_cell,":faq",$faq[$rownum][$cellnum]);			
			OCIBindByName($ins_cell,":phones",$phones);
			OCIBindByName($ins_cell,":width",$width);
			OCIBindByName($ins_cell,":height",$height);			
			OCIExecute($ins_cell, OCI_DEFAULT);
			$html_clob->save($cell_html[$rownum][$cellnum]);
		}
	}
}
}
//---------------------------------------------------------------------------------

if(isset($edit_blog) and $save_as=='table') {
	if(!isset($general) or $general<>'y') $general='n';
	//if($general=='n') $punkt_id='';
	if(!isset($shedule_id)) $shedule_id='';
	if(!isset($blog_id)) $blog_id='';
	$ordering=$ordering+1;

	if($blog_id=='') {
		$q=OCIParse($c,"select nvl(max(id),0)+1 blog_id from sc_body");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$blog_id=OCIResult($q,"BLOG_ID");
	
		$insupd=OCIParse($c,"insert into sc_body (id,type,ordering,project_id,punkt_id,general,shedule_id,table_id) 
		values (:blog_id,'DT',:ordering,:project_id,decode(:general,'n',:punkt_id,null),:general,:shedule_id,:table_id)");
	}
	else {
	
		$insupd=OCIParse($c,"update sc_body set 
		type='DT', punkt_id=decode(:general,'n',:punkt_id,null), general=:general, 
		ordering=
		decode(:general,general,:ordering,	
			decode(:general,'y',(select nvl(max(ordering),0)+1 from sc_body where project_id=:project_id and general=:general) ,
								(select nvl(min(ordering),0)-1 from sc_body where project_id=:project_id and nvl(punkt_id,0)=nvl(:punkt_id,0)))
		),
		shedule_id=:shedule_id, table_id=:table_id
		where id=:blog_id and project_id=:project_id");
	}
	OCIBindByName($insupd,":project_id",$project_id);
	OCIBindByName($insupd,":blog_id",$blog_id);
	OCIBindByName($insupd,":ordering",$ordering);
	OCIBindByName($insupd,":punkt_id",$punkt_id);
	OCIBindByName($insupd,":general",$general);
	OCIBindByName($insupd,":shedule_id",$shedule_id);
	OCIBindByName($insupd,":table_id",$table_id);
	
	OCIExecute($insupd,OCI_DEFAULT);

	$del=OCIParse($c,"delete from SC_BODY_CGPN where body_id='".$blog_id."'");
	OCIExecute($del,OCI_DEFAULT);

	if(isset($cgpns) and count($cgpns)<$cgpns_count) {
		$ins2=OCIParse($c,"insert into SC_BODY_CGPN (cgpn,body_id) values (:cgpn,'".$blog_id."')");
			foreach($cgpns as $val) {
			$val=trim($val);
			OCIBindByName($ins2,":cgpn",$val);
			OCIExecute($ins2,OCI_DEFAULT);
		}
	}
//------------------------

	$del=OCIParse($c,"delete from SC_BODY_DIRECTIONS where body_id='".$blog_id."'");
	OCIExecute($del,OCI_DEFAULT);
	
	if(isset($directions) and count($directions)<3) {
		$ins5=OCIParse($c,"insert into SC_BODY_DIRECTIONS (direction,body_id) values (:direction,'".$blog_id."')");
			foreach($directions as $key=>$val) {
			OCIBindByName($ins5,":direction",$key);
			OCIExecute($ins5,OCI_DEFAULT);
		}
	}	
	
//------------------------
	$del=OCIParse($c,"delete from SC_BODY_AONS where body_id='".$blog_id."'");
	OCIExecute($del,OCI_DEFAULT);
	
	if($aons<>'') {
		$aons_arr=explode(",",$aons);
		foreach($aons_arr as $val) {
			$val=trim($val);
			$ins3=OCIParse($c,"insert into SC_BODY_AONS (aon,body_id) values (:aons,'$blog_id')");
			OCIBindByName($ins3,":aons",$val);
			OCIExecute($ins3,OCI_DEFAULT);
		}
	}
	$del=OCIParse($c,"delete from SC_BODY_TONEDIAL where body_id='".$blog_id."'");
	OCIExecute($del,OCI_DEFAULT);
	
	if($tonedials<>'') {
		$td_arr=explode(",",$tonedials);
		foreach($td_arr as $val) {
			$val=trim($val);
			$ins4=OCIParse($c,"insert into SC_BODY_TONEDIAL (tonedial,body_id) values (:tonedials,'$blog_id')");
			OCIBindByName($ins4,":tonedials",$val);
			OCIExecute($ins4,OCI_DEFAULT);
		}
	}	

	$upd=OCIParse($c,"update sc_punkt
set with_blog= (select decode(count(*),0,null,1) from sc_body where punkt_id='".$punkt_id."' and project_id=".$project_id." and invisible is null)
where id='".$punkt_id."' and project_id='".$project_id."'"); 

	OCIExecute($upd,OCI_DEFAULT);
	
	$upd=OCIParse($c,"update sc_body set ordering=ordering+1
		where project_id='".$_SESSION['project']['id']."'
		and id<>'".$blog_id."' 
	    and general='".$general."'
		and nvl(punkt_id,0)=decode('".$general."','y',nvl(punkt_id,0),nvl('".$punkt_id."',0))
		and ordering>='".$ordering."'");
	OCIExecute($upd,OCI_DEFAULT);	

	OCICommit($c);	
echo "<script language='javascript'>
parent.location='body.php?punkt_id=".$punkt_id."&tree_id=".$tree_id."#".$blog_id."'</script>";

}
//-------------------------------------------------------------------------------------------------------------------

OCICommit($c);

if($save_as=='table' and (!isset($no_change) or (isset($other_project_id) and $other_project_id<>$_SESSION['project']['id']))) {
	if(isset($other_project_id) and $other_project_id<>$_SESSION['project']['id']) {
		$table_id=$t;
		echo "<script>alert('Таблица сохранена!');parent.vChanged='y';</script>";
	}
	else {
		echo "<script>alert('Таблица сохранена!');parent.vChanged=null;</script>";
	}
	if($reload_page=="y") {
		if(isset($edit_blog)) echo "<script>parent.location='edit_table.php?edit_blog&blog_id=".$blog_id."&table_id=".$table_id."&general=".$general."&punkt_id=".$punkt_id."&tree_id=".$tree_id."&ordering=".$ordering."&shedule_id=".$shedule_id."';</script>";
		else echo "<script>parent.location='edit_table.php?table_id=".$table_id."';</script>";
	}
}
if($save_as=='template' and !isset($no_change)) {
	echo "<script>alert('Шаблон сохранен!');</script>";
	if($reload_page=="y") {
		if(isset($edit_blog)) echo "<script>parent.location='edit_table.php?edit_blog&blog_id=".$blog_id."&template_id=".$table_id."&general=".$general."&punkt_id=".$punkt_id."&tree_id=".$tree_id."&ordering=".$ordering."&shedule_id=".$shedule_id."';</script>";
		else echo "<script>parent.location='edit_table.php?template_id=".$table_id."';</script>";
	}
}


function fError($errno,$errstr,$errfile,$errline) {
global $c;
	echo '<script>alert("ОШИБКА! таблица не сохранена!\n'.$errstr.'\nв '.str_replace('\\','\\\\',$errfile).' Строка: '.$errline.'");</script>';
	echo $errno.$errstr.$errfile.' Строка: '.$errline;
	OCIRollback($c);
	exit();
}
?>
<body>
</body>
</html>
