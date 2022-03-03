<?php
require_once 'oktell_vg_connect.php';

if(isset($_POST['form_set']))
{
if ($_POST['kod'] == 15986)
{
    $pin = $_POST['pin'];
    $newfio = $_POST['newfio'];
    $query = $vg_okt->query("
    update [oktell_settings].[dbo].[A_Users]
    set Name='[1905] ".$newfio."' where Login='1905-".$pin."'
    ");	
    echo "Выполнено"; 
	exit();   
}
else
{
echo 'Неверные данные';
}
}
else
{
echo
'<h2>Только для Админов<h2>
<p>Меняет фио оператора(пока только в октелле Волгоградки)</p><br />
Сменить ФИО<form method="post"></br>
Введите код: <input type="text" name="kod" /><br />
Введите пин: <input type="text" name="pin" /><br />
Введите новое ФИО: <input type="text" name="newfio" /><br />
<input type="submit" value="Изменить имя" name="form_set" />
</form>';
}
?>