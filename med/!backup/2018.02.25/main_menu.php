<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
	<link rel="stylesheet" type="text/css" href="./billing.css">
	<title>Входящие звонки</title>
</head>
<?php
ini_set('session.use_cookies','1');
//ini_set('session.use_trans_sid','0');

//session_name('medc');
session_start();

require_once 'funct.php';

extract($_REQUEST);
?>
<body style="margin-top: 0">
<table width=25% style="float: left; margin-top: 5px;">
    <img alt="Медицина" class="logo" src="./images/icon175x175.png" style="width: 3em; position: absolute;">
    <h3 class="heading" style="margin-left: 20px; margin-top: 5px; position: absolute;"><?php echo (isset($_COOKIE['business']) ? $_COOKIE['business'] : '')?></h3>
</table>
<table width=75% style="float: right; margin-top: 5px;">
    <tr><td align=left></td>
        <td></td>
        <td style="width: 77%; float:right"><strong style="font-size: 18px; ">Пользователь: <?php echo (isset($_COOKIE['login']) ? $_COOKIE['login'] : '')?></strong></td>
        <!--td align='left'>
            <input type='text' name='User' value='<?php echo (isset($_COOKIE['login']) ? $_COOKIE['login'] : '')?>' disabled>
        </td-->
        <td align=right><a href="./" id="ExitLink" target=_parent onclick="UpdateDate(); return true;" style="font-size: 2em; color: red">Выход</a></td>
    </tr>
</table>

<script type="application/javascript">
    function UpdateDate() {
        //alert("UpdateDate");
        <?php GetData::UpdateActivity($_SESSION['login_id'], FALSE); ?>
    }
</script>
</body>