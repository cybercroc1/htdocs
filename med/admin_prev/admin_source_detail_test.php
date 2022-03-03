<?php 
session_name('medc');
session_start();
extract($_REQUEST);

require_once '../funct.php';

if (!isset($_SESSION['auth']) or $_SESSION['auth'] <> md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])) {
    echo "<b style='color: red'>Доступ запрещен</b>";
    exit();
}
if (!isset($_SESSION['user_role']) || USER_ADMIN != $_SESSION['user_role']) {
    if (USER_SUPER != $_SESSION['user_role'] ||
        (84 != $_SESSION['login_id_med'] && 11 != $_SESSION['login_id_med'])) { // только для Грачевой и Алибековой?
        echo '<p style="font-size: 26px; font-weight: bold; color: red;">Cтраница недоступна!</p>';
        exit();
    }
}

if (!isset($S_Auto)) $S_Auto='';

if (isset($save) && 3 == $fr and $S_Auto<>'' && isset($source_det_ids)) {
	foreach($source_det_ids as $source_det_id => $fucking_val) {
		//если есть хотя бы 
		if(isset($GLOBALS["on_".$source_det_id])) {
			//меняем услуги для источника
			$service_ids=implode(",",$GLOBALS["on_".$source_det_id]);
			//echo $S_Auto." - ".$source_det_id." - ". $service_ids."<br>";
		}
		else {
			$service_ids='';
		}
		
		$updatestr = "UPDATE SOURCE_AUTO_DETAIL SET SERVICE_IDS = '".$service_ids."' WHERE ID = '".$source_det_id."' and SOURCE_AUTO_ID='".$S_Auto."'";

		if (DB_OCI) {
			$query = OCIParse(GetData::GetConnect(), $updatestr);
			$query_result = OCIExecute($query);
			oci_free_statement($query);
		} else {
			$query_result = mysqli_query(GetData::GetConnect(), $updatestr);
		}
	}
}

if (isset($del_id) && 3 == $fr) {
    if (DB_OCI) {
        $deletestr = "UPDATE SOURCE_AUTO_DETAIL SET DELETED = to_date('" . date("d-m-Y  H:i:s") . "','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$del_id}'";
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    } else {
        $deletestr = "UPDATE SOURCE_AUTO_DETAIL SET DELETED = '" . date("Y-m-d H:i:s") . "' WHERE ID = '{$del_id}'";
        $query_result = mysqli_query(GetData::GetConnect(), $deletestr);
    }
    if ($query_result) {
        print "<script language='Javascript'>
                parent.adm_src_det_fr3.reload(); setTimeout('reload()', 100);
				</script>";
        echo "<p style='color: green'>Строка изменена. Идет перезагрузка данных...</p>";
    } else {
        echo "<p style='color: red'>Произошла ошибка удаления записи.</p>";
    }
}

//Восстанавливаем удаленное
if (isset($restore_id) && 3 == $fr) {
    $deletestr = "UPDATE SOURCE_AUTO_DETAIL SET DELETED = NULL WHERE ID = '{$restore_id}'";
    if (DB_OCI) {
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    } else {
        $query_result = mysqli_query(GetData::GetConnect(), $deletestr);
    }
    if ($query_result) {
        print "<script language='Javascript'>
                parent.adm_src_det_fr3.reload(); setTimeout('reload()', 100);
                </script>";

        echo "<p style='color: green'>Строка восстановлена. Идет перезагрузка данных...</p>";
    } else {
        echo "<p style='color: red'>Произошла ошибка восстановления записи.</p>";
    }
}

//ФРЕЙМСЕТ
if(!isset($fr)) {
	echo "<!DOCTYPE html>
	<html>
	<head>
	<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
	</head>
	<frameset frameborder=no rows='65,115,*'>
	<frame name='adm_src_det_fr1' id='adm_src_det_fr1' src=?fr=1>
	<frame name='adm_src_det_fr2' id='adm_src_det_fr2' src=?fr=2>
	<frame name='adm_src_det_fr3' id='adm_src_det_fr3' src=?fr=3>
	</frameset><noframes></noframes>";
}
//
else {
	echo '<head>';
	echo '<link rel="stylesheet" type="text/css" href="../billing.css">';
	if (TRUE == ENCODE_UTF)
	    echo '<meta http-equiv=Content-Type content="text/html; charset=utf-8"/>';
	else echo '<meta http-equiv=Content-Type content="text/html; charset=windows-1251"/>';
	echo '<title>Детализация Источников рекламы</title>';
	echo '<meta name="description" content="Детализация Источников рекламы">';
	echo '</head>';
	
	//ФРЕЙМ 1
	if (1 == $fr) {
        /*$source_auto_arr=array(); //Cписок автоматических источников, в зависимости от прав доступа

        if(USER_ADMIN != $_SESSION['user_role']) {
        $q=OCIParse(GetData::GetConnect(),"select distinct sa.id, st.name||' - '||sa.name name
        from USERS u, USER_DEP_ALLOC uda, ACCESS_DEP ad, SOURCE_AUTO sa, SOURCE_TYPE st
        where u.id='".$_SESSION['login_id_med']."' and uda.user_id=u.id
        and ad.departament_id=uda.dep_id
        and sa.id>0 and sa.id=decode(ad.source_auto_id,-1,sa.id,ad.source_auto_id)
        and sa.source_type=decode(ad.source_type_id,-1,sa.source_type,ad.source_type_id)
        and st.id=sa.source_type
        and uda.deleted is null and sa.deleted is null
        order by st.name||' - '||sa.name");}
        else {
        $q=OCIParse(GetData::GetConnect(),"select distinct sa.id,st.name||' - '||sa.name name
        from SOURCE_AUTO sa,SOURCE_TYPE st
        where st.id=sa.source_type and sa.deleted is null
        and sa.id>0
        order by st.name||' - '||sa.name");}
        OCIExecute($q);
        while (OCIFetch($q)) {$source_auto_arr[OCIResult($q,"ID")]=OCIResult($q,"NAME");}*/

		echo '<head>';
		echo '<link rel="stylesheet" type="text/css" href="../billing.css">';
		if (TRUE == ENCODE_UTF)
			echo '<meta http-equiv=Content-Type content="text/html; charset=utf-8"/>';
		else echo '<meta http-equiv=Content-Type content="text/html; charset=windows-1251"/>';
		echo '<title>Детализация Источников рекламы</title>';
		echo '<meta name="description" content="Детализация Источников рекламы">';
		echo '</head>';
		echo '<h3 style="margin: 0;">Детализация Источников рекламы</h3>';
		echo "<label id='S_AutoT' for='S_Auto'>Источник (авто):&nbsp;</label>";
		
		echo "<select id='S_Auto' name='S_Auto' onchange='parent.adm_src_det_fr2.location=\"?fr=2&S_Auto=\"+this.value;parent.adm_src_det_fr3.location=\"?fr=3&S_Auto=\"+this.value'>";	
		echo "<option value=''>Выберите автоматический источник рекламы</option>";
        $sources_auto = GetData::GetSourceAuto(NULL, NULL, (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE));
		//foreach($source_auto_arr as $source_auto_id => $source_auto_name) {echo "<option value='".$source_auto_id."'>".$source_auto_name."</option>";}
		foreach($_POST['array_source_auto'] as $key => $value) {
		    echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
		}
		echo "</select>";
		exit();
	}
	//
	//Cписок услуг в зависимости от прав доступа
	$service_arr=array();
	if(USER_ADMIN != $_SESSION['user_role']) {
	$q=OCIParse(GetData::GetConnect(),"select distinct s.id, s.name from USERS u, USER_DEP_ALLOC uda, ACCESS_DEP ad, SERVICES s
	where u.id='".$_SESSION['login_id_med']."' and uda.user_id=u.id 
	and ad.departament_id=uda.dep_id
	and s.id=decode(ad.service_id,-1,s.id,ad.service_id)
	and uda.deleted is null and s.deleted is null and s.id>0
	order by s.name");}
	else {
	$q=OCIParse(GetData::GetConnect(),"select distinct s.id,s.name 
	from SERVICES s where s.deleted is null and s.id>0 order by s.name");}
	OCIExecute($q);
	while (OCIFetch($q)) {$service_arr[OCIResult($q,"ID")]=OCIResult($q,"NAME");}
	//ФРЕЙМ 2
	if (2 == $fr && $S_Auto <> '') {
        $nServices = GetData::GetServices(FALSE,FALSE,NULL, (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE));
		echo '<table class="white_table"><tr><th style="width: 300px;">Выберите источник</th>';
		//foreach($service_arr as $service_id => $service_name) {echo '<th style="width: 101px;">'.$service_name.'</th>';}
		foreach($_POST['array_services'] as $key => $value) {echo '<th style="width: 101px;">'.$value['NAME'].'</th>';}

		echo '<th style="border-bottom: none"></th></tr>';
		echo '<tr style="vertical-align: middle">';
		echo '<td style="text-align: center; width: 300px">';	
		echo "<select id='Reservoir' name='Reservoir'>";
		echo "<option value='" . SOURCE_NOT . "'>Новый источник</option>";
		if (GetData::GetIstochnik(FALSE, FALSE, NULL, FALSE) > 0) {
			if (DB_OCI) {
				foreach ($_POST['array_istochnik'] as $key => $value) {
					if (TRUE == ENCODE_UTF)
						$value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
					printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
				}
			} else {
				foreach ($_POST['array_istochnik'] as $key => $value) {
					if (TRUE == ENCODE_UTF)
						$value[1] = iconv('utf-8', 'windows-1251', $value[1]);
					printf("<option value='%s'>%s</option>", $value[0], $value[1]);
				}
			}
		}
		echo "</select>";
		echo "<script type = 'text/javascript'> var sel = document.getElementById('Reservoir');
		if (sel) {
			sel.onchange = function() {
				if (" . SOURCE_NOT . " == sel.value) { // новый источник
					document.getElementById('NameIstT').style.visibility = 'visible';
					document.getElementById('NameIst').style.visibility = 'visible';
					document.getElementById('NameIstT').style.position = 'inherit';
					document.getElementById('NameIst').style.position = 'inherit';
				}
				else {
					document.getElementById('NameIstT').style.visibility = 'hidden';
					document.getElementById('NameIst').style.visibility = 'hidden';
					document.getElementById('NameIstT').style.position = 'absolute';
					document.getElementById('NameIst').style.position = 'absolute';
				}}
		}
		</script>";
		echo "<br><label for='NameIst' id='NameIstT' style='font-weight: bold'>или введите новый:&nbsp;</label>";
		echo '<input type="text" name="NameIst" id="NameIst" style="width: 250px" placeholder="Источник рекламы">';
		echo '</td>';
		foreach($service_arr as $service_id => $service_name) {
			echo '<td style="text-align: center;"><input type="checkbox" name="newservice['.$service_id.']"/></td>';
		}
		echo '<td style="border-top: none;"><input type="submit" name="Adding" value="Добавить источник" class="add_button"></td>';
		echo '</tr></table>';
		exit();
	}
	//
	//ФРЕЙМ 3
	if (3 == $fr && $S_Auto <> '') {
        echo "<form method=post>";
        /*if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
            strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) {
            echo "<table class='scrolling-table_ie'>";
        } else {
            echo "<table class='scrolling-table'>";
        }*/
        echo "<table class=white_table>";
        echo "<tr>";
        echo "<th style='width: 36px;'>ID</th>";
        echo "<th style='width: 260px;'>Источник</th>";

        foreach($service_arr as $service_id => $service_name) {echo "<th style='width: 101px;'>".$service_name."</th>";}

        //echo "<th style='width: 121px;'>Удалена</th>";
        //echo "<th style='width: 101px;'>Действие</th>";
        echo "</thead>";
        echo "<tbody>";

        $nSourceAutoDet = GetData::GetSourceAutoDetail(FALSE, TRUE, "source_auto_id=".$S_Auto, (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE));
        foreach ($_POST['array_sa_detail'] as $key => $value) {
            $id = $value['ID'];
            $name = $value['NAME'];
            $checked_service_ids = $value['SERVICE_IDS'];
            $deleted = $value['DELETED'];
            $str_deleted = ($deleted ? $deleted : "нет");
            if (TRUE == ENCODE_UTF)
                $name = iconv('windows-1251', 'utf-8', $name);

            $checked_service_arr=array();
            $checked_service_arr=explode(',',$checked_service_ids);
            foreach($checked_service_arr as $checked_id) {$checked_service_arr[$checked_id]=$checked_id;}

            echo "<tr><td style='text-align: center; width: 35px'>" . $id . "</td>";
            echo "<td style='text-align: center; width: 260px'>" . $name . "</td>";

            foreach($service_arr as $service_id => $service_name) {
                if(isset($checked_service_arr[$service_id])) {
                    $colors = "background-color: springgreen";
                    $checked = " checked";
                }
                else {
                    $colors = "background-color: red";
                    $checked = "";
                }
                echo "<td style='text-align: center; width: 100px;".$colors."'><input type=hidden name=source_det_ids[".$id."]><input type='checkbox' name='on_".$id."[]' value='".$service_id."'".$checked."/></td>";
            }

            /*echo "<td style='text-align: center; width: 120px'>" . $str_deleted . "</td>";
            if ($deleted)
                echo "<td style='text-align: center; width: 100px'>
<input type='submit' name='restore_id' value='Восстановить'/><input type='hidden' name='id_s' value='".$id."'/>
                </td>";
            else echo "<td style='text-align: center; width: 100px'><a href='?del_id=".$id."&fr=3'>Удалить</a></td>";*/
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "<hr>";
        echo "<input type='submit' name='save' value='Сохранить изменения'/>";
        echo "</form>";
	}
	exit();
}