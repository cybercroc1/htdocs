<?php
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
session_start();
extract($_REQUEST);

if (!isset($_SESSION['auth']) or $_SESSION['auth']<>'y') {
echo "<font color=red><b>У Вас не прав для просмотра данной страницы или Вы не прошли авторизацию</b></font>";
exit();
}

if (!isset($start_date)) $start_date=date('d.m.Y',strtotime('-8 day'));
if (!isset($end_date)) $end_date=date('d.m.Y',strtotime('-1 day'));

include("../../sc_conf/sc_conn_string");
include("../../sc_conf/ab_conn_string");

if(isset($count)) {
header("Content-type: application/xls");
header("Content-Disposition: attachment; filename=\"calls.xls\""); 

echo '<html>
<head>
<meta charset=windows-1251" />
</head>';

	
	$q=OCIParse($abilling,"select YYYY, 
decode(count(w01),0,null,count(w01)) as \"1\",
decode(count(w02),0,null,count(w02)) as \"2\",
decode(count(w03),0,null,count(w03)) as \"3\",
decode(count(w04),0,null,count(w04)) as \"4\",
decode(count(w05),0,null,count(w05)) as \"5\",
decode(count(w06),0,null,count(w06)) as \"6\",
decode(count(w07),0,null,count(w07)) as \"7\",
decode(count(w08),0,null,count(w08)) as \"8\",
decode(count(w09),0,null,count(w09)) as \"9\",
decode(count(w10),0,null,count(w10)) as \"10\",
decode(count(w11),0,null,count(w11)) as \"11\",
decode(count(w12),0,null,count(w12)) as \"12\",
decode(count(w13),0,null,count(w13)) as \"13\",
decode(count(w14),0,null,count(w14)) as \"14\",
decode(count(w15),0,null,count(w15)) as \"15\",
decode(count(w16),0,null,count(w16)) as \"16\",
decode(count(w17),0,null,count(w17)) as \"17\",
decode(count(w18),0,null,count(w18)) as \"18\",
decode(count(w19),0,null,count(w19)) as \"19\",
decode(count(w20),0,null,count(w20)) as \"20\",
decode(count(w21),0,null,count(w21)) as \"21\",
decode(count(w22),0,null,count(w22)) as \"22\",
decode(count(w23),0,null,count(w23)) as \"23\",
decode(count(w24),0,null,count(w24)) as \"24\",
decode(count(w25),0,null,count(w25)) as \"25\",
decode(count(w26),0,null,count(w26)) as \"26\",
decode(count(w27),0,null,count(w27)) as \"27\",
decode(count(w28),0,null,count(w28)) as \"28\",
decode(count(w29),0,null,count(w29)) as \"29\",
decode(count(w30),0,null,count(w30)) as \"30\",
decode(count(w31),0,null,count(w31)) as \"31\",
decode(count(w32),0,null,count(w32)) as \"32\",
decode(count(w33),0,null,count(w33)) as \"33\",
decode(count(w34),0,null,count(w34)) as \"34\",
decode(count(w35),0,null,count(w35)) as \"35\",
decode(count(w36),0,null,count(w36)) as \"36\",
decode(count(w37),0,null,count(w37)) as \"37\",
decode(count(w38),0,null,count(w38)) as \"38\",
decode(count(w39),0,null,count(w39)) as \"39\",
decode(count(w40),0,null,count(w40)) as \"40\",
decode(count(w41),0,null,count(w41)) as \"41\",
decode(count(w42),0,null,count(w42)) as \"42\",
decode(count(w43),0,null,count(w43)) as \"43\",
decode(count(w44),0,null,count(w44)) as \"44\",
decode(count(w45),0,null,count(w45)) as \"45\",
decode(count(w46),0,null,count(w46)) as \"46\",
decode(count(w47),0,null,count(w47)) as \"47\",
decode(count(w48),0,null,count(w48)) as \"48\",
decode(count(w49),0,null,count(w49)) as \"49\",
decode(count(w50),0,null,count(w50)) as \"50\",
decode(count(w51),0,null,count(w51)) as \"51\",
decode(count(w52),0,null,count(w52)) as \"52\",
decode(count(w53),0,null,count(w53)) as \"53\"
from
(
select 
decode(to_char(offered_date,'WW'),'01','1',null) w01,
decode(to_char(offered_date,'WW'),'02','1',null) w02,
decode(to_char(offered_date,'WW'),'03','1',null) w03,
decode(to_char(offered_date,'WW'),'04','1',null) w04,
decode(to_char(offered_date,'WW'),'05','1',null) w05,
decode(to_char(offered_date,'WW'),'06','1',null) w06,
decode(to_char(offered_date,'WW'),'07','1',null) w07,
decode(to_char(offered_date,'WW'),'08','1',null) w08,
decode(to_char(offered_date,'WW'),'09','1',null) w09,
decode(to_char(offered_date,'WW'),'10','1',null) w10,
decode(to_char(offered_date,'WW'),'11','1',null) w11,
decode(to_char(offered_date,'WW'),'12','1',null) w12,
decode(to_char(offered_date,'WW'),'13','1',null) w13,
decode(to_char(offered_date,'WW'),'14','1',null) w14,
decode(to_char(offered_date,'WW'),'15','1',null) w15,
decode(to_char(offered_date,'WW'),'16','1',null) w16,
decode(to_char(offered_date,'WW'),'17','1',null) w17,
decode(to_char(offered_date,'WW'),'18','1',null) w18,
decode(to_char(offered_date,'WW'),'19','1',null) w19,
decode(to_char(offered_date,'WW'),'20','1',null) w20,
decode(to_char(offered_date,'WW'),'21','1',null) w21,
decode(to_char(offered_date,'WW'),'22','1',null) w22,
decode(to_char(offered_date,'WW'),'23','1',null) w23,
decode(to_char(offered_date,'WW'),'24','1',null) w24,
decode(to_char(offered_date,'WW'),'25','1',null) w25,
decode(to_char(offered_date,'WW'),'26','1',null) w26,
decode(to_char(offered_date,'WW'),'27','1',null) w27,
decode(to_char(offered_date,'WW'),'28','1',null) w28,
decode(to_char(offered_date,'WW'),'29','1',null) w29,
decode(to_char(offered_date,'WW'),'30','1',null) w30,
decode(to_char(offered_date,'WW'),'31','1',null) w31,
decode(to_char(offered_date,'WW'),'32','1',null) w32,
decode(to_char(offered_date,'WW'),'33','1',null) w33,
decode(to_char(offered_date,'WW'),'34','1',null) w34,
decode(to_char(offered_date,'WW'),'35','1',null) w35,
decode(to_char(offered_date,'WW'),'36','1',null) w36,
decode(to_char(offered_date,'WW'),'37','1',null) w37,
decode(to_char(offered_date,'WW'),'38','1',null) w38,
decode(to_char(offered_date,'WW'),'39','1',null) w39,
decode(to_char(offered_date,'WW'),'40','1',null) w40,
decode(to_char(offered_date,'WW'),'41','1',null) w41,
decode(to_char(offered_date,'WW'),'42','1',null) w42,
decode(to_char(offered_date,'WW'),'43','1',null) w43,
decode(to_char(offered_date,'WW'),'44','1',null) w44,
decode(to_char(offered_date,'WW'),'45','1',null) w45,
decode(to_char(offered_date,'WW'),'46','1',null) w46,
decode(to_char(offered_date,'WW'),'47','1',null) w47,
decode(to_char(offered_date,'WW'),'48','1',null) w48,
decode(to_char(offered_date,'WW'),'49','1',null) w49,
decode(to_char(offered_date,'WW'),'50','1',null) w50,
decode(to_char(offered_date,'WW'),'51','1',null) w51,
decode(to_char(offered_date,'WW'),'52','1',null) w52,
decode(to_char(offered_date,'WW'),'53','1',null) w53,
to_char(offered_date,'yyyy') as YYYY from cdr_calls
where called_num='7390614'
and offered_date > to_date('01.01.2007','DD.MM.YYYY')
)
group by YYYY
order by YYYY desc");
	OCIExecute($q,OCI_DEFAULT);

echo "<table border=1>";
echo "<tr><td><b>Общее количество входящих вызовов</b></td></tr>";
echo "<tr>
<td align=center><b></b></td>
<td align=center><b></b></td>
<td align=center colspan=5><b>янв</b></td>
<td align=center colspan=4><b>февр</b></td>
<td align=center colspan=4><b>март</b></td>
<td align=center colspan=5><b>апр</b></td>
<td align=center colspan=4><b>май</b></td>
<td align=center colspan=4><b>июнь</b></td>
<td align=center colspan=5><b>июль</b></td>
<td align=center colspan=4><b>авг</b></td>
<td align=center colspan=4><b>сент</b></td>
<td align=center colspan=5><b>окт</b></td>
<td align=center colspan=4><b>нояб</b></td>
<td align=center colspan=5><b>дек</b></td>
</tr>";
echo "<tr>
<td align=center><b></b></td>
<td align=center><b>№ нед.</b></td>
<td align=center><b>1</b></td>
<td align=center><b>2</b></td>
<td align=center><b>3</b></td>
<td align=center><b>4</b></td>
<td align=center><b>5</b></td>
<td align=center><b>6</b></td>
<td align=center><b>7</b></td>
<td align=center><b>8</b></td>
<td align=center><b>9</b></td>
<td align=center><b>10</b></td>
<td align=center><b>11</b></td>
<td align=center><b>12</b></td>
<td align=center><b>13</b></td>
<td align=center><b>14</b></td>
<td align=center><b>15</b></td>
<td align=center><b>16</b></td>
<td align=center><b>17</b></td>
<td align=center><b>18</b></td>
<td align=center><b>19</b></td>
<td align=center><b>20</b></td>
<td align=center><b>21</b></td>
<td align=center><b>22</b></td>
<td align=center><b>23</b></td>
<td align=center><b>24</b></td>
<td align=center><b>25</b></td>
<td align=center><b>26</b></td>
<td align=center><b>27</b></td>
<td align=center><b>28</b></td>
<td align=center><b>29</b></td>
<td align=center><b>30</b></td>
<td align=center><b>31</b></td>
<td align=center><b>32</b></td>
<td align=center><b>33</b></td>
<td align=center><b>34</b></td>
<td align=center><b>35</b></td>
<td align=center><b>36</b></td>
<td align=center><b>37</b></td>
<td align=center><b>38</b></td>
<td align=center><b>39</b></td>
<td align=center><b>40</b></td>
<td align=center><b>41</b></td>
<td align=center><b>42</b></td>
<td align=center><b>43</b></td>
<td align=center><b>44</b></td>
<td align=center><b>45</b></td>
<td align=center><b>46</b></td>
<td align=center><b>47</b></td>
<td align=center><b>48</b></td>
<td align=center><b>49</b></td>
<td align=center><b>50</b></td>
<td align=center><b>51</b></td>
<td align=center><b>52</b></td>
<td align=center><b>53</b></td>
</tr>";


	while(OCIFetch($q)) {
		
		
		echo "<tr>";
		echo "<td align=center><b>".OCIResult($q,"YYYY")."</b></td>";
echo "<td align=center>кол-во</td>";
echo "<td align=center>".OCIResult($q,"1")."</td>";
echo "<td align=center>".OCIResult($q,"2")."</td>";
echo "<td align=center>".OCIResult($q,"3")."</td>";
echo "<td align=center>".OCIResult($q,"4")."</td>";
echo "<td align=center>".OCIResult($q,"5")."</td>";
echo "<td align=center>".OCIResult($q,"6")."</td>";
echo "<td align=center>".OCIResult($q,"7")."</td>";
echo "<td align=center>".OCIResult($q,"8")."</td>";
echo "<td align=center>".OCIResult($q,"9")."</td>";
echo "<td align=center>".OCIResult($q,"10")."</td>";
echo "<td align=center>".OCIResult($q,"11")."</td>";
echo "<td align=center>".OCIResult($q,"12")."</td>";
echo "<td align=center>".OCIResult($q,"13")."</td>";
echo "<td align=center>".OCIResult($q,"14")."</td>";
echo "<td align=center>".OCIResult($q,"15")."</td>";
echo "<td align=center>".OCIResult($q,"16")."</td>";
echo "<td align=center>".OCIResult($q,"17")."</td>";
echo "<td align=center>".OCIResult($q,"18")."</td>";
echo "<td align=center>".OCIResult($q,"19")."</td>";
echo "<td align=center>".OCIResult($q,"20")."</td>";
echo "<td align=center>".OCIResult($q,"21")."</td>";
echo "<td align=center>".OCIResult($q,"22")."</td>";
echo "<td align=center>".OCIResult($q,"23")."</td>";
echo "<td align=center>".OCIResult($q,"24")."</td>";
echo "<td align=center>".OCIResult($q,"25")."</td>";
echo "<td align=center>".OCIResult($q,"26")."</td>";
echo "<td align=center>".OCIResult($q,"27")."</td>";
echo "<td align=center>".OCIResult($q,"28")."</td>";
echo "<td align=center>".OCIResult($q,"29")."</td>";
echo "<td align=center>".OCIResult($q,"30")."</td>";
echo "<td align=center>".OCIResult($q,"31")."</td>";
echo "<td align=center>".OCIResult($q,"32")."</td>";
echo "<td align=center>".OCIResult($q,"33")."</td>";
echo "<td align=center>".OCIResult($q,"34")."</td>";
echo "<td align=center>".OCIResult($q,"35")."</td>";
echo "<td align=center>".OCIResult($q,"36")."</td>";
echo "<td align=center>".OCIResult($q,"37")."</td>";
echo "<td align=center>".OCIResult($q,"38")."</td>";
echo "<td align=center>".OCIResult($q,"39")."</td>";
echo "<td align=center>".OCIResult($q,"40")."</td>";
echo "<td align=center>".OCIResult($q,"41")."</td>";
echo "<td align=center>".OCIResult($q,"42")."</td>";
echo "<td align=center>".OCIResult($q,"43")."</td>";
echo "<td align=center>".OCIResult($q,"44")."</td>";
echo "<td align=center>".OCIResult($q,"45")."</td>";
echo "<td align=center>".OCIResult($q,"46")."</td>";
echo "<td align=center>".OCIResult($q,"47")."</td>";
echo "<td align=center>".OCIResult($q,"48")."</td>";
echo "<td align=center>".OCIResult($q,"49")."</td>";
echo "<td align=center>".OCIResult($q,"50")."</td>";
echo "<td align=center>".OCIResult($q,"51")."</td>";
echo "<td align=center>".OCIResult($q,"52")."</td>";
echo "<td align=center>".OCIResult($q,"53")."</td>";
		echo "</tr>";
	}
	
	$q=OCIParse($c,"select YYYY, 
decode(count(w01),0,null,count(w01)) as \"1\",
decode(count(w02),0,null,count(w02)) as \"2\",
decode(count(w03),0,null,count(w03)) as \"3\",
decode(count(w04),0,null,count(w04)) as \"4\",
decode(count(w05),0,null,count(w05)) as \"5\",
decode(count(w06),0,null,count(w06)) as \"6\",
decode(count(w07),0,null,count(w07)) as \"7\",
decode(count(w08),0,null,count(w08)) as \"8\",
decode(count(w09),0,null,count(w09)) as \"9\",
decode(count(w10),0,null,count(w10)) as \"10\",
decode(count(w11),0,null,count(w11)) as \"11\",
decode(count(w12),0,null,count(w12)) as \"12\",
decode(count(w13),0,null,count(w13)) as \"13\",
decode(count(w14),0,null,count(w14)) as \"14\",
decode(count(w15),0,null,count(w15)) as \"15\",
decode(count(w16),0,null,count(w16)) as \"16\",
decode(count(w17),0,null,count(w17)) as \"17\",
decode(count(w18),0,null,count(w18)) as \"18\",
decode(count(w19),0,null,count(w19)) as \"19\",
decode(count(w20),0,null,count(w20)) as \"20\",
decode(count(w21),0,null,count(w21)) as \"21\",
decode(count(w22),0,null,count(w22)) as \"22\",
decode(count(w23),0,null,count(w23)) as \"23\",
decode(count(w24),0,null,count(w24)) as \"24\",
decode(count(w25),0,null,count(w25)) as \"25\",
decode(count(w26),0,null,count(w26)) as \"26\",
decode(count(w27),0,null,count(w27)) as \"27\",
decode(count(w28),0,null,count(w28)) as \"28\",
decode(count(w29),0,null,count(w29)) as \"29\",
decode(count(w30),0,null,count(w30)) as \"30\",
decode(count(w31),0,null,count(w31)) as \"31\",
decode(count(w32),0,null,count(w32)) as \"32\",
decode(count(w33),0,null,count(w33)) as \"33\",
decode(count(w34),0,null,count(w34)) as \"34\",
decode(count(w35),0,null,count(w35)) as \"35\",
decode(count(w36),0,null,count(w36)) as \"36\",
decode(count(w37),0,null,count(w37)) as \"37\",
decode(count(w38),0,null,count(w38)) as \"38\",
decode(count(w39),0,null,count(w39)) as \"39\",
decode(count(w40),0,null,count(w40)) as \"40\",
decode(count(w41),0,null,count(w41)) as \"41\",
decode(count(w42),0,null,count(w42)) as \"42\",
decode(count(w43),0,null,count(w43)) as \"43\",
decode(count(w44),0,null,count(w44)) as \"44\",
decode(count(w45),0,null,count(w45)) as \"45\",
decode(count(w46),0,null,count(w46)) as \"46\",
decode(count(w47),0,null,count(w47)) as \"47\",
decode(count(w48),0,null,count(w48)) as \"48\",
decode(count(w49),0,null,count(w49)) as \"49\",
decode(count(w50),0,null,count(w50)) as \"50\",
decode(count(w51),0,null,count(w51)) as \"51\",
decode(count(w52),0,null,count(w52)) as \"52\",
decode(count(w53),0,null,count(w53)) as \"53\"
from
(
select 
decode(to_char(date_call,'WW'),'01','1',null) w01,
decode(to_char(date_call,'WW'),'02','1',null) w02,
decode(to_char(date_call,'WW'),'03','1',null) w03,
decode(to_char(date_call,'WW'),'04','1',null) w04,
decode(to_char(date_call,'WW'),'05','1',null) w05,
decode(to_char(date_call,'WW'),'06','1',null) w06,
decode(to_char(date_call,'WW'),'07','1',null) w07,
decode(to_char(date_call,'WW'),'08','1',null) w08,
decode(to_char(date_call,'WW'),'09','1',null) w09,
decode(to_char(date_call,'WW'),'10','1',null) w10,
decode(to_char(date_call,'WW'),'11','1',null) w11,
decode(to_char(date_call,'WW'),'12','1',null) w12,
decode(to_char(date_call,'WW'),'13','1',null) w13,
decode(to_char(date_call,'WW'),'14','1',null) w14,
decode(to_char(date_call,'WW'),'15','1',null) w15,
decode(to_char(date_call,'WW'),'16','1',null) w16,
decode(to_char(date_call,'WW'),'17','1',null) w17,
decode(to_char(date_call,'WW'),'18','1',null) w18,
decode(to_char(date_call,'WW'),'19','1',null) w19,
decode(to_char(date_call,'WW'),'20','1',null) w20,
decode(to_char(date_call,'WW'),'21','1',null) w21,
decode(to_char(date_call,'WW'),'22','1',null) w22,
decode(to_char(date_call,'WW'),'23','1',null) w23,
decode(to_char(date_call,'WW'),'24','1',null) w24,
decode(to_char(date_call,'WW'),'25','1',null) w25,
decode(to_char(date_call,'WW'),'26','1',null) w26,
decode(to_char(date_call,'WW'),'27','1',null) w27,
decode(to_char(date_call,'WW'),'28','1',null) w28,
decode(to_char(date_call,'WW'),'29','1',null) w29,
decode(to_char(date_call,'WW'),'30','1',null) w30,
decode(to_char(date_call,'WW'),'31','1',null) w31,
decode(to_char(date_call,'WW'),'32','1',null) w32,
decode(to_char(date_call,'WW'),'33','1',null) w33,
decode(to_char(date_call,'WW'),'34','1',null) w34,
decode(to_char(date_call,'WW'),'35','1',null) w35,
decode(to_char(date_call,'WW'),'36','1',null) w36,
decode(to_char(date_call,'WW'),'37','1',null) w37,
decode(to_char(date_call,'WW'),'38','1',null) w38,
decode(to_char(date_call,'WW'),'39','1',null) w39,
decode(to_char(date_call,'WW'),'40','1',null) w40,
decode(to_char(date_call,'WW'),'41','1',null) w41,
decode(to_char(date_call,'WW'),'42','1',null) w42,
decode(to_char(date_call,'WW'),'43','1',null) w43,
decode(to_char(date_call,'WW'),'44','1',null) w44,
decode(to_char(date_call,'WW'),'45','1',null) w45,
decode(to_char(date_call,'WW'),'46','1',null) w46,
decode(to_char(date_call,'WW'),'47','1',null) w47,
decode(to_char(date_call,'WW'),'48','1',null) w48,
decode(to_char(date_call,'WW'),'49','1',null) w49,
decode(to_char(date_call,'WW'),'50','1',null) w50,
decode(to_char(date_call,'WW'),'51','1',null) w51,
decode(to_char(date_call,'WW'),'52','1',null) w52,
decode(to_char(date_call,'WW'),'53','1',null) w53,
to_char(date_call,'yyyy') as YYYY 
from SC_CALL_REPORT
where project_id=358 and form_id=7424
and date_call > to_date('01.01.2007','DD.MM.YYYY')
)
group by YYYY
order by YYYY desc");
	OCIExecute($q,OCI_DEFAULT);

echo "<table border=1>";
echo "<tr></tr>";
echo "<tr><td><b>Количество результативных звнков</b></td></tr>";
echo "<tr>
<td align=center><b></b></td>
<td align=center><b></b></td>
<td align=center colspan=5><b>янв</b></td>
<td align=center colspan=4><b>февр</b></td>
<td align=center colspan=4><b>март</b></td>
<td align=center colspan=5><b>апр</b></td>
<td align=center colspan=4><b>май</b></td>
<td align=center colspan=4><b>июнь</b></td>
<td align=center colspan=5><b>июль</b></td>
<td align=center colspan=4><b>авг</b></td>
<td align=center colspan=4><b>сент</b></td>
<td align=center colspan=5><b>окт</b></td>
<td align=center colspan=4><b>нояб</b></td>
<td align=center colspan=5><b>дек</b></td>
</tr>";
echo "<tr>
<td align=center><b></b></td>
<td align=center><b>№ нед.</b></td>
<td align=center><b>1</b></td>
<td align=center><b>2</b></td>
<td align=center><b>3</b></td>
<td align=center><b>4</b></td>
<td align=center><b>5</b></td>
<td align=center><b>6</b></td>
<td align=center><b>7</b></td>
<td align=center><b>8</b></td>
<td align=center><b>9</b></td>
<td align=center><b>10</b></td>
<td align=center><b>11</b></td>
<td align=center><b>12</b></td>
<td align=center><b>13</b></td>
<td align=center><b>14</b></td>
<td align=center><b>15</b></td>
<td align=center><b>16</b></td>
<td align=center><b>17</b></td>
<td align=center><b>18</b></td>
<td align=center><b>19</b></td>
<td align=center><b>20</b></td>
<td align=center><b>21</b></td>
<td align=center><b>22</b></td>
<td align=center><b>23</b></td>
<td align=center><b>24</b></td>
<td align=center><b>25</b></td>
<td align=center><b>26</b></td>
<td align=center><b>27</b></td>
<td align=center><b>28</b></td>
<td align=center><b>29</b></td>
<td align=center><b>30</b></td>
<td align=center><b>31</b></td>
<td align=center><b>32</b></td>
<td align=center><b>33</b></td>
<td align=center><b>34</b></td>
<td align=center><b>35</b></td>
<td align=center><b>36</b></td>
<td align=center><b>37</b></td>
<td align=center><b>38</b></td>
<td align=center><b>39</b></td>
<td align=center><b>40</b></td>
<td align=center><b>41</b></td>
<td align=center><b>42</b></td>
<td align=center><b>43</b></td>
<td align=center><b>44</b></td>
<td align=center><b>45</b></td>
<td align=center><b>46</b></td>
<td align=center><b>47</b></td>
<td align=center><b>48</b></td>
<td align=center><b>49</b></td>
<td align=center><b>50</b></td>
<td align=center><b>51</b></td>
<td align=center><b>52</b></td>
<td align=center><b>53</b></td>
</tr>";


	while(OCIFetch($q)) {
		
		
		echo "<tr>";
		echo "<td align=center><b>".OCIResult($q,"YYYY")."</b></td>";
echo "<td align=center>кол-во</td>";
echo "<td align=center>".OCIResult($q,"1")."</td>";
echo "<td align=center>".OCIResult($q,"2")."</td>";
echo "<td align=center>".OCIResult($q,"3")."</td>";
echo "<td align=center>".OCIResult($q,"4")."</td>";
echo "<td align=center>".OCIResult($q,"5")."</td>";
echo "<td align=center>".OCIResult($q,"6")."</td>";
echo "<td align=center>".OCIResult($q,"7")."</td>";
echo "<td align=center>".OCIResult($q,"8")."</td>";
echo "<td align=center>".OCIResult($q,"9")."</td>";
echo "<td align=center>".OCIResult($q,"10")."</td>";
echo "<td align=center>".OCIResult($q,"11")."</td>";
echo "<td align=center>".OCIResult($q,"12")."</td>";
echo "<td align=center>".OCIResult($q,"13")."</td>";
echo "<td align=center>".OCIResult($q,"14")."</td>";
echo "<td align=center>".OCIResult($q,"15")."</td>";
echo "<td align=center>".OCIResult($q,"16")."</td>";
echo "<td align=center>".OCIResult($q,"17")."</td>";
echo "<td align=center>".OCIResult($q,"18")."</td>";
echo "<td align=center>".OCIResult($q,"19")."</td>";
echo "<td align=center>".OCIResult($q,"20")."</td>";
echo "<td align=center>".OCIResult($q,"21")."</td>";
echo "<td align=center>".OCIResult($q,"22")."</td>";
echo "<td align=center>".OCIResult($q,"23")."</td>";
echo "<td align=center>".OCIResult($q,"24")."</td>";
echo "<td align=center>".OCIResult($q,"25")."</td>";
echo "<td align=center>".OCIResult($q,"26")."</td>";
echo "<td align=center>".OCIResult($q,"27")."</td>";
echo "<td align=center>".OCIResult($q,"28")."</td>";
echo "<td align=center>".OCIResult($q,"29")."</td>";
echo "<td align=center>".OCIResult($q,"30")."</td>";
echo "<td align=center>".OCIResult($q,"31")."</td>";
echo "<td align=center>".OCIResult($q,"32")."</td>";
echo "<td align=center>".OCIResult($q,"33")."</td>";
echo "<td align=center>".OCIResult($q,"34")."</td>";
echo "<td align=center>".OCIResult($q,"35")."</td>";
echo "<td align=center>".OCIResult($q,"36")."</td>";
echo "<td align=center>".OCIResult($q,"37")."</td>";
echo "<td align=center>".OCIResult($q,"38")."</td>";
echo "<td align=center>".OCIResult($q,"39")."</td>";
echo "<td align=center>".OCIResult($q,"40")."</td>";
echo "<td align=center>".OCIResult($q,"41")."</td>";
echo "<td align=center>".OCIResult($q,"42")."</td>";
echo "<td align=center>".OCIResult($q,"43")."</td>";
echo "<td align=center>".OCIResult($q,"44")."</td>";
echo "<td align=center>".OCIResult($q,"45")."</td>";
echo "<td align=center>".OCIResult($q,"46")."</td>";
echo "<td align=center>".OCIResult($q,"47")."</td>";
echo "<td align=center>".OCIResult($q,"48")."</td>";
echo "<td align=center>".OCIResult($q,"49")."</td>";
echo "<td align=center>".OCIResult($q,"50")."</td>";
echo "<td align=center>".OCIResult($q,"51")."</td>";
echo "<td align=center>".OCIResult($q,"52")."</td>";
echo "<td align=center>".OCIResult($q,"53")."</td>";
		echo "</tr>";
	}	
		echo "</table>";
exit();
}

//	between to_date('".$start_date."','DD.MM.YYYY') and to_date('".$end_date."','DD.MM.YYYY')+1


?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Выгрузка базы для проведения платежей</title>
</head>
<body>
<form method="post">
<?php

echo "<font size=3><b>Количество звнков в call-центр</b></font><input type=submit name=count value='кол-во звонков по неделям с 2007г'><br><br>";

echo "Период: с <td bgcolor=white><INPUT TYPE=TEXT NAME=start_date SIZE=10 value='".$start_date."' onClick='if(self.gfPop)gfPop.fPopCalendar(document.all.start_date);return false;' HIDEFOCUS> 
по <td bgcolor=white><INPUT TYPE=TEXT NAME=end_date SIZE=10 value='".$end_date."' onClick='if(self.gfPop)gfPop.fPopCalendar(document.all.end_date);return false;' HIDEFOCUS> 
(включительно)<br>";
//echo "<input type=submit name=show value='Показать'>";
echo "<hr>";

if(isset($show)) {
	echo "<table bgcolor=black cellspacing=1 cellpadding=1>";
	echo "<tr>
	<td bgcolor=white><b>DATE_REG_CODE</b></td>
	<td bgcolor=white><b>CODE</b></td>
	<td bgcolor=white><b>NOMINAL</b></td>
	<td bgcolor=white><b>NAME1</b></td>
	<td bgcolor=white><b>NAME2</b></td>
	<td bgcolor=white><b>NAME3</b></td>
	<td bgcolor=white><b>DATE_AGE</b></td>
	<td bgcolor=white><b>MOBILE_PHONE</b></td>
	<td bgcolor=white><b>ADRES</b></td>
	<td bgcolor=white><b>DATE_PAY</b></td>
	<td bgcolor=white><b>STATUS_PAY</b></td>
	</tr>";
	
	$q=OCIParse($c,"select to_char(DATE_REG_CODE,'DD.MM.YYYY') DATE_REG_CODE, CODE, NOMINAL, NAME1, NAME2, NAME3, to_char(DATE_AGE,'DD.MM.YYYY') DATE_AGE, MOBILE_PHONE, ADRES, to_char(DATE_PAY,'DD.MM.YYYY') DATE_PAY, STATUS_PAY
	from rolton_coupon_base 
	where DATE_REG_CODE between to_date('".$start_date."','DD.MM.YYYY') and to_date('".$end_date."','DD.MM.YYYY')+1
	order by DATE_REG_CODE");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		echo "<tr>
		<td bgcolor=white><b>".OCIResult($q,"DATE_REG_CODE")."</b></td>
		<td bgcolor=white><b>".OCIResult($q,"CODE")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"NOMINAL")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"NAME1")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"NAME2")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"NAME3")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"DATE_AGE")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"MOBILE_PHONE")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"ADRES")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"DATE_PAY")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"STATUS_PAY")."</b></b></td>
		</tr>";
	}
}

?>
</form>
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>
