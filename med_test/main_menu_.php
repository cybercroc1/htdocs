<?php
ini_set('session.use_cookies','1');
//ini_set('session.use_trans_sid','0');

session_name('medc');
session_start();

extract($_REQUEST);
require_once 'base.php';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
	<link rel="stylesheet" type="text/css" href="./billing.css">
	<title>Входящие заявки</title>
</head>

<body style="margin-top: 0">
<table width=25% style="float: left; margin-top: -3px">
    <tr><td>
    <img alt="Медицина" class="logo" src="./images/icon175x175.png" style="width: 3em; position: absolute;">
    <h3 class="heading" style="margin-left: 20px; margin-top: 5px; position: absolute;"><?php echo (isset($_COOKIE['business']) ? $_COOKIE['business'] : '')?></h3>
        </td>
    </tr>
</table>
<table width=75% style="float: right; margin-top: 3px">
    <tr><td align=left></td>
        <td></td>
        <td style="width: 77%; float:right"><strong style="font-size: 18px; ">Пользователь:
                <?php echo (isset($_SESSION['login_name']) ? $_SESSION['login_name'] : '').
                    "<span style='color:red'>".($_SESSION['on_duty_today'] ? ' (Дежурный)' : '')."</span>"?>
            </strong></td>
        <td align='right'><a href="<?=PATH?>/?exit" id="ExitLink" target="_parent" onclick="return UpdateDate();" style="font-size: 2em; color: red">Выход</a></td>
    </tr>
</table>

<script>
    function UpdateDate() {
        return (confirm('Выходите из системы?'));
    }
</script>

</body>