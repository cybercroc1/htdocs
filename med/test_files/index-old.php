<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">

<head>
<link rel="shortcut icon" href="./favicon.ico" type="image/x-icon"/>
<link rel="stylesheet" type="text/css" href="./billing.css">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
<title>Медицина</title>
<base href="/">
<meta name="description" content="Медицина">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<?php
ini_set('session.use_cookies','1');
session_name('medc');
session_start();

if (!isset($_SESSION['count'])) {
    $_SESSION['count'] = 0;
} else {
    $_SESSION['count']++;
}
extract($_REQUEST);
require_once 'funct.php';
GetData::CreateConnect();
?>
<?php
if(isset($_GET['exit'])) {
    GetData::UpdateActivity($_SESSION['login_id_med'],FALSE);

    echo "<script>parent.parent.location.href=(document.location.pathname);</script>";
    echo "<script>parent.parent.location.reload();</script>";
    session_destroy();
}
?>

<?php if (TRUE != GetData::is_registered() ) { ?>
    <?php
    //var_dump($_SERVER);
    if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_SESSION['ip_addr']) && NULL != $_SESSION['ip_addr']) {
        //if ($_SERVER['REMOTE_ADDR'] != $_SESSION['ip_addr'])
            echo "<h2 style='color: magenta'>Ошибка! Данный пользователь уже подключен на компьютере с адресом ".$_SESSION['ip_addr'].".</br></h2>";
        //else echo "<h2 style='color: magenta'>Ошибка! Подождите 30 секунд и перезагрузите страницу.</br></h2>";
    }
    ?>
    <form action="" method="POST" name="login_frm">
        <table border="0" width="200" cellspacing="0" cellpadding="8" align="center">
            <tr>
                <td colspan="1" align="center" style="position: absolute; margin-left: -5em;"><img src="<?=PATH?>/images/ws-logo.png" alt="Wilstream"></td>
                <td colspan="1" align="center;"><img src="<?=PATH?>/images/icon175x175.png" style="padding-left: 2em;" alt="Logo"></td>
            </tr>
            <tr>
                <td width="20%" height="25"><p style="color: black; font-weight: bold">Пользователь</p></td>
                <td width="20%" align="center" height="25"><input type="text" name="login" size="20" placeholder="Логин" ></td>
            </tr>
            <tr>
                <td width="20%" height="25"><p style="color: black; font-weight: bold">Пароль</p></td>
                <td width="20%" align="center" height="25"><input type="password" name="password" size="20" placeholder="Пароль"></td>
            </tr>
            <tr align="center">
                <td colspan=2 align="center" height="65"><input type="submit" name="Enter" value="Войти"></td>
            </tr>
        </table>
    </form>
<?php } else { ?>
    <iframe src="<?=PATH?>/main_menu.php" name="admMainTopFrame" id="admTopFrame" title="admTopFrame" width="100%" height="35" frameborder="no" scrolling="no"></iframe>
    <hr style="margin: 0;">
    <!--frameset id=admFrameset rows="40,*">
    <frame src="< ?=PATH?>/main_menu.php" name="admMainTopFrame" id="admTopFrame" title="admTopFrame" noresize="noresize" scrolling="no"-->
    <?php if (USER_ADMIN == $_SESSION['user_role']) { ?>
        <!--frameset COLS="25%,75%" id=admBottomFrame frameborder="YES" border="2"-->
            <iframe src="<?=PATH?>/index-f.php" name="admLeftFrame" id="admLeftFrame" title="admLeftFrame" width="13%" height="600" frameborder="no" scrolling="0"></iframe>
            <iframe name="admRightFrame" id="admRightFrame" title="admRightFrame" width="86%" height="600" frameborder="0"></iframe>
            <!--frame src="< ?=PATH?>/index-f.php" name="admLeftFrame" id="admLeftFrame" title="admLeftFrame"></frame>
            <frame name="admRightFrame" id="admRightFrame" title="admRightFrame"></frame-->
        <!--/frameset-->
    <?php } else { ?>
        <!--frame src="< ?=PATH?>/call_view/med_call_view.php" name="admLeftFrame" id="admLeftFrame" title="admLeftFrame"></frame-->
        <?php if (DB_OCI) { ?>
        <iframe src="<?=PATH?>/call_view/med_call_view.php" name="admRightFrame" id="admRightFrame" title="admRightFrame" width="100%" height="600" frameborder="no"></iframe>
        <?php } else { ?>
        <iframe src="<?=PATH?>/call_view/med_call_view_my.php" name="admRightFrame" id="admRightFrame" title="admRightFrame" width="100%" height="600" frameborder="no"></iframe>
        <?php } ?>
    <?php } ?>
    <!--/frameset><noframes></noframes-->
<?php } ?>

</html>