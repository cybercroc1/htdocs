<?php
if(isset($_POST['formach'])){
if($_POST['passtry'] == '15986'){
$pin = $_POST['pin'];        
$fio_new = $_POST['fio_new'];        
}
else{
    echo"�������� ������!";
}
}
?>
<form action="" method="post">
    ������� ���<input type="text" name="pin"><br />
    ������� ����� ���<input type="text" name="fio_new"><br />
    ������� ������<input type="password" name="passtry"><br />
    <input type="submit" name="formach" value="��������"><br />
</form>    

