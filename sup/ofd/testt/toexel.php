<?php
require_once 'oktell_conn_string.php';
if(isset($_POST['formach'])){

    $toxlsquery = $_POST['toxlsquery'];
    /*
    "SELECT *
    FROM [oktell_settings].[dbo].[A_Users]
    Where Login='Ok_op_3064'";
    */
    $query = $c_okt->query("INSERT INTO OPENROWSET('Microsoft.ACE.OLEDB.12.0','Excel 12.0;Database=C:\TEMP\temp.xls;HDR=YES', [Sheet1$]) ".$toxlsquery);
    echo "Выполнено";
}
?>
<form action="" method="post">
    Введите запрос<input type="text" name="toxlsquery"><br />
    <input type="submit" name="formach" value="составить файл"><br />
</form>    