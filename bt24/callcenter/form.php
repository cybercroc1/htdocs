<?php

extract($_GET);

//задать кодировку браузеру, без нее открывает как попало
header('Content-Type: text/html; charset=utf-8');

if (!isset($aon) || empty($aon)) {
    exit('Номер телефона не указан');
}



echo '

<form id="my_form" action="ajax.php" method=post class="form-style-9">
<input type="hidden" name="aon" value="'.$aon.'">
<input type="hidden" name="lead_id" value="'.$lead_id.'">
<div style="text-align:center">Контакт не существует, форма для создания лида</div><br>
<ul>
<li>
Имя:<br><input type="text" name="name" placeholder="Имя" required>
</li>
<li>
Телефон:<br><input type="text" name="phone" placeholder="Телефон" required value="'.$aon.'">
</li>
<li>
Телефон контакта:<br><input type="text" name="second_phone" placeholder="Телефон" value="'.$aon.'">
</li>
<li>
email:<br><input type="email" name="email" placeholder="E-mail">
</li>
<li>
Комментарий<br>
<textarea class="field-style" name="comment" placeholder="Комментарий" ></textarea>
</li>
<li>
<div class="form_response"></div>
</li>
<li>
<input name="source" type="hidden" value="'.$source.'">
<input class="sub_form" type="submit" value="Создать">
</li>
</ul>
</form>







<style type="text/css">
input[type="text"], input[type="email"]{
    width: 100%;
    height: 30px;
    padding: 5px;
}
.form-style-9{
    max-width: 450px;
    background: #FAFAFA;
    padding: 30px;
    margin: 50px auto;
    box-shadow: 1px 1px 25px rgba(0, 0, 0, 0.35);
    border-radius: 10px;
    border: 2px solid #305A72;
}
.form-style-9 ul{
    padding:0;
    margin:0;
    list-style:none;
}
.form-style-9 ul li{
    display: block;
    margin-bottom: 10px;
    min-height: 35px;
}
.form-style-9 ul li  .field-style{
    box-sizing: border-box; 
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box; 
    padding: 8px;
    outline: none;
    border: 1px solid #B0CFE0;
    -webkit-transition: all 0.30s ease-in-out;
    -moz-transition: all 0.30s ease-in-out;
    -ms-transition: all 0.30s ease-in-out;
    -o-transition: all 0.30s ease-in-out;

}.form-style-9 ul li  .field-style:focus{
    box-shadow: 0 0 5px #B0CFE0;
    border:1px solid #B0CFE0;
}
.form-style-9 ul li .field-split{
    width: 49%;
}
.form-style-9 ul li .field-full{
    width: 100%;
}
.form-style-9 ul li input.align-left{
    float:left;
}
.form-style-9 ul li input.align-right{
    float:right;
}
.form-style-9 ul li textarea{
    width: 100%;
    height: 100px;
}
.form-style-9 ul li input[type="button"], 
.form-style-9 ul li input[type="submit"] {
    -moz-box-shadow: inset 0px 1px 0px 0px #3985B1;
    -webkit-box-shadow: inset 0px 1px 0px 0px #3985B1;
    box-shadow: inset 0px 1px 0px 0px #3985B1;
    background-color: #216288;
    border: 1px solid #17445E;
    display: inline-block;
    cursor: pointer;
    color: #FFFFFF;
    padding: 8px 18px;
    text-decoration: none;
    font: 12px Arial, Helvetica, sans-serif;
    float: right;
}
.form-style-9 ul li input[type="button"]:hover, 
.form-style-9 ul li input[type="submit"]:hover {
    background: linear-gradient(to bottom, #2D77A2 5%, #337DA8 100%);
    background-color: #28739E;
}
.sub_form[disabled] {
    opacity: 0.5;
}
</style>

<script type="text/javascript" src="jquery.min.js"></script>

<script>

$(document).ready(function(){

    $("form").on("submit", function(e){
        $(".sub_form").prop("disabled", "disabled");
        e.preventDefault();
        var form_data = $(this).serialize();
        $.ajax({
            type: "POST",
            url: "ajax.php",
            data: form_data,
            success: function(data)
            {
           
            $(".form_response").text(data);
            },
            error: function(data){
                $(".sub_form").prop("disabled", "");
            }
            });
    });
});


</script>

';
?>