<?php 
require_once 'med/check_auth.php';
extract($_REQUEST);
if(!isset($report_id)) $report_id='';
if(!isset($start_date)) $start_date=date('d.m.Y');
if(!isset($end_date)) $end_date=date('d.m.Y');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
        "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<HEAD>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
</HEAD>

<frameset frameborder=yes rows='80,*,50'>
<frame name=rep_main_menu src='rep_menu.php?report_id=<?=$report_id?>&start_date=<?=$start_date?>&end_date=<?=$end_date?>'>
<frame name=rep_filter src='_blank_page.php'>
<frame name=rep_result src='_blank_page.php'>
</frameset><noframes></noframes>

</html>
