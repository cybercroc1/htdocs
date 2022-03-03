<?php
if(isset($_POST['formach'])){
if($_POST['passtry'] == '15986'){
$pin = $_POST['pin'];        
$fio_new = $_POST['fio_new'];        
}
else{
    echo"Неверный пароль!";
}
}
?>
<form action="" method="post">
    Введите пин<input type="text" name="pin"><br />
    Введите новое фио<input type="text" name="fio_new"><br />
    Введите пароль<input type="password" name="passtry"><br />
    <input type="submit" name="formach" value="изменить"><br />
</form>    

