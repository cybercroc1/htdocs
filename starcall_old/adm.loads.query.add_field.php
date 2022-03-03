<?php 
//используется в:
//adm.loads.fields.php
//adm.loads.load_data.php
include("../../conf/starcall_conf/session.cfg.php");

if($_SESSION['user']['rw_src_bd']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

include("../../conf/starcall_conf/conn_string.cfg.php");
$q=OCIParse($c,"select SEQ_STC_FIELDS_ID.nextval from dual");
OCIExecute($q, OCI_DEFAULT); OCIFetch($q);
$new_field_id=OCIResult($q,"NEXTVAL"); 

if(!isset($new_field_id) or $new_field_id=='') exit();
echo "<script>parent.admBottomFrame.add_field('".$new_field_id."');</script>";
?>