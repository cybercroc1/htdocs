<form method=post>
<input type=submit name=next value="Получить следующий код">
</form>
<?php
extract($_POST);
if (isset($next) and ($_SERVER['REMOTE_ADDR']=='213.221.30.226' or substr($_SERVER['REMOTE_ADDR'],0,9)=='192.168.1')) {
include("../../sc_conf/sc_conn_string");
$q=OCIParse($c,"select SEQ_KASKO_CODE.nextval from dual");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$code="A";

for($i=1; $i<=3-strlen(OCIResult($q,"NEXTVAL")); $i++) {
$code.="0";
}
$code.=OCIResult($q,"NEXTVAL");

echo "<br><font size=4>Код: <b>".$code."</font></b>";

}
?>
