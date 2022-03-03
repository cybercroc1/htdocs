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
//session_name('medc');
session_start();

extract($_REQUEST);

if (!isset($_SESSION['count'])) {
    $_SESSION['count'] = 0;
} else {
    $_SESSION['count']++;
}
require_once 'funct.php';
GetData::CreateConnect();
?>

<?php if ( TRUE != GetData::is_registered() ) { ?>
    <div class="login">
        <form action="" method="post" style="width: 40em; margin-top: 10px;">
            <input type="text" name="login" placeholder="Логин"/>
            <input type="password" name="password" placeholder="Пароль"/>
            <div style="float: right; ">
                <input type="submit" name="Enter" value="Войти" style="font-weight: bold; *margin-top: -41px">
            </div>
        </form>
    </div>
<?php } else { ?>
    <iframe src="<?=PATH?>/main_menu.php" name="admMainTopFrame" id="admTopFrame" title="admTopFrame" width="100%" height="35" frameborder="no" scrolling="no"></iframe>
    <hr style="margin: 0;">
    <!--frameset id=admFrameset rows="40,*">
    <frame src="<?=PATH?>/main_menu.php" name="admMainTopFrame" id="admTopFrame" title="admTopFrame" noresize="noresize" scrolling="no"-->
    <?php if ($_SESSION['user_role'] == USER_ADMIN) { ?>
        <!--frameset COLS="25%,75%" id=admBottomFrame frameborder="YES" border="2"-->
            <iframe src="<?=PATH?>/index-f.php" name="admLeftFrame" id="admLeftFrame" title="admLeftFrame" width="19.5%" height="600" frameborder="no" scrolling="1"></iframe>
            <iframe name="admRightFrame" id="admRightFrame" title="admRightFrame" width="80%" height="600" frameborder="0"></iframe>
            <!--frame src="< ?=PATH?>/index-f.php" name="admLeftFrame" id="admLeftFrame" title="admLeftFrame"></frame>
            <frame name="admRightFrame" id="admRightFrame" title="admRightFrame"></frame-->
        <!--/frameset-->
    <?php } else { ?>
        <iframe src="<?=PATH?>/call_view/med_call_view.php" name="admRightFrame" id="admRightFrame" title="admRightFrame" width="100%" height="600" frameborder="no"></iframe>
        <!--frame src="<?=PATH?>/call_view/med_call_view.php" name="admLeftFrame" id="admLeftFrame" title="admLeftFrame"></frame-->
    <?php } ?>
    <!--/frameset><noframes></noframes-->
<?php } ?>

<!--script type="application/javascript">
    function LoginDate() {
        alert("Login");
        < ?php GetData::UpdateActivity($_SESSION['login_id'], TRUE); ?>
    }
</script-->

</html>