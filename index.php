<?php
/*
 * Copyright (c) 2012 Andy 'Rimmer' Shepherd <andrew.shepherd@ecsc.co.uk> (ECSC Ltd).
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

//include "php/appUtils.inc";
//global $apputils;


require './top.php';


## filter criteria 'level'
if(isset($_GET['level']) && preg_match("/^[0-9]+$/", $_GET['level'])){
	$inputlevel=$_GET['level'];
}else{
	$inputlevel=$glb_level;
}
$query="SELECT distinct(level) FROM signature ORDER BY level";
$result=mysql_query($query, $db_ossec);
$filterlevel="";
while($row = @mysql_fetch_assoc($result)){
	$selected="";
	if($row['level']==$inputlevel){
		$selected=" SELECTED";
	}
	$filterlevel.="<option value='".$row['level']."'".$selected.">".$row['level']." +</option>";
}


## filter from
if(isset($_GET['hours']) && preg_match("/^[0-9]+$/", $_GET['hours'])){
	$inputhours=$_GET['hours'];
}else{
	$inputhours=$glb_hours;
}

## filter category
$inputcategoryname='';
if(isset($_GET['category']) && preg_match("/^[0-9]+$/", $_GET['category'])){
	$inputcategory=$_GET['category'];
	$wherecategory=" AND category.cat_id=".$inputcategory." ";
//throw new exception(print_r($_GET,true));
}else{
	$inputcategory="";
	$wherecategory=" ";
}
$query="SELECT *
	FROM category
	ORDER BY cat_name";
$result=mysql_query($query, $db_ossec);
$filtercategory="";
while($row = @mysql_fetch_assoc($result)){
	$selected="";
        if($row['cat_id']==$inputcategory){
					$selected=" SELECTED";
					$inputcategoryname = $row['cat_name'];
        }
	$filtercategory.="<option value='".$row['cat_id']."'".$selected.">".$row['cat_name']."</option>";
}


## filter
$radiosource="";
$radiopath="";
$radiolevel="";
$radiorule_id="";
$graphbreakdownname = $glb_graphbreakdown;
if(isset($_GET['field']) && $_GET['field']=='path'){
	$graphbreakdownname = $_GET['field'];
	$radiopath="checked";
}elseif(isset($_GET['field']) && $_GET['field']=='level'){
	$graphbreakdownname = $_GET['field'];
	$radiolevel="checked";
}elseif(isset($_GET['field']) && $_GET['field']=='rule_id'){
	$graphbreakdownname = $_GET['field'];
	$radiorule_id="checked";
}elseif(isset($_GET['field']) && $_GET['field']=='source'){
	$graphbreakdownname = $_GET['field'];
	$radiosource="checked";
}else{
	if($glb_graphbreakdown=="source"){
		$radiosource="checked";
	}elseif($glb_graphbreakdown=="path"){
		$radiopath="checked";
	}elseif($glb_graphbreakdown=="level"){
		$radiolevel="checked";
	}elseif($glb_graphbreakdown=="rule_id"){
		$radiorule_id="checked";
	}else{
		# default source
		$radiosource="checked";
	}
}

//<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
//<html xmlns="http://www.w3.org/1999/xhtml">
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>AnaLogi - OSSEC WUI</title>

<?php
include "page_refresh.php";
?>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
<link href="./style.css" rel="stylesheet" type="text/css" />
<script src="./amcharts/amcharts.js" type="text/javascript"></script>
<script src="./sortable.js" type="text/javascript"></script>
<script src="./js/apputils.js" type="text/javascript"></script>
<script type="text/javascript">

	$(document).ready(function(){
			$('.toggle').click(function(){
				id = $(this).parent().attr("id");
				toggled = $(this).parent().find(".toggled");

				toggled.slideToggle('fast', function(){
					cookie = (toggled.is(":hidden")) ? "0" : "1";
					setCookie("hideshow"+id, cookie, "100");
				});
			});
	});

	function setCookie(c_name,value,exdays){
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		document.cookie=c_name + "=" + c_value;
	}
	function get_cookies_array() {
		var cookies = { };
		if (document.cookie && document.cookie != '') {
			var split = document.cookie.split(';');
			for (var i = 0; i < split.length; i++) {
				var name_value = split[i].split("=");
				name_value[0] = name_value[0].replace(/^ /, '');
				cookies[decodeURIComponent(name_value[0])] = decodeURIComponent(name_value[1]);
			}
		}
		return cookies;
	}
	function databasetest(){
		<!--  If no data, alerts will be created in here  -->
		<?php #include './databasetest.php' ?>

	}



	var chart;

	<?php
	include './php/index_graph.php';
	?>


	AmCharts.ready(function () {
		// SERIAL CHART
		chart = new AmCharts.AmSerialChart();
		chart.dataProvider = chartData;
		chart.categoryField = "date";
		chart.startDuration = 0.5;
		chart.balloon.color = "#000000";
		chart.zoomOutOnDataUpdate=true;
		chart.pathToImages = "./images/";
		chart.zoomOutButton = {
			backgroundColor: '#000000',
			backgroundAlpha: 0.15
		};

		// listen for "dataUpdated" event (fired when chart is rendered) and call zoomChart method when it happens
		chart.addListener("dataUpdated", zoomChart);

		// AXES
		// category
		var categoryAxis = chart.categoryAxis;
		categoryAxis.fillAlpha = 1;
		categoryAxis.fillColor = "#FAFAFA";
		categoryAxis.gridAlpha = 0;
		categoryAxis.axisAlpha = 0;
		categoryAxis.gridPosition = "start";
		categoryAxis.position = "top";
		categoryAxis.parseDates = true;
		categoryAxis.minPeriod = "mm";

		<?php
		## See top.php for more info
		#include './php/index_graph_icinga.php';
		?>

		// value
		var valueAxis = new AmCharts.ValueAxis();
		chart.addValueAxis(valueAxis);
		valueAxis.logarithmic = <?php echo $glb_indexgraphlogarithmic; ?>;
		valueAxis.title = "Alerts";

		// this method is called when chart is first inited as we listen for "dataUpdated" event
		function zoomChart() {
			// replaced by chart.zoomOutOnDataUpdate
		}

		// SCROLLBAR
		var chartScrollbar = new AmCharts.ChartScrollbar();
		chartScrollbar.graph = graph0;
		chartScrollbar.scrollbarHeight = 40;
		chartScrollbar.color = "#000000";
		chartScrollbar.gridColor = "#000000";
		chartScrollbar.backgroundColor = "#FFFFFF";
		chartScrollbar.autoGridCount = true;
		chart.addChartScrollbar(chartScrollbar);


		<?php
		if($glb_indexgraphbubbletext==1){
		echo "
		// chartCursor
		var chartCursor = new AmCharts.ChartCursor();
          	chartCursor.cursorPosition = 'mouse';
                chartCursor.categoryBalloonDateFormat = 'JJ:NN, DD MMMM';
		chart.addChartCursor(chartCursor);
		";
		}
		?>

		// changes cursor mode from pan to select
		function setPanSelect() {
			if (document.getElementById("rb1").checked) {
				chartCursor.pan = false;
				chartCursor.zoomable = true;
			} else {
				chartCursor.pan = true;
			}
			chart.validateNow();
		}



		<?php
		echo $graphlines;
		echo $workinghoursguide
		?>

		<?php
		if($glb_indexgraphkey==1){
		echo "
		// LEGEND
		var legend = new AmCharts.AmLegend();
		legend.markerType = 'circle';
		chart.addLegend(legend);";
		}
		?>



		<?php
		echo $graphheight;
		?>
		// WRITE
		chart.write("chartdiv");

	});
</script>


</head>
<body onload="databasetest();">

<?php include './header.php'; ?>
<div class='clr'></div>

<!-- Filter Controls -->
<div id=filters>
	<div class='top10header toggle' style='width:100%;' title='Click to show/hide'>Filter:
	<?php
		$cat = $inputcategory != ''
				? ", Category \"$inputcategoryname\""
				: '';
		if($inputlevel != '')
			$cat = ", Level <span class='tw'>".$inputlevel."+</span>".$cat;
		echo "<span class='tw'>Last ".$inputhours." hrs</span>{$cat}, Breakdown \"$graphbreakdownname\"";
	?>
	</div>
	<div class='newboxes toggled' style='display:none;padding-left:10px'>
	<form method='GET' action='./index.php'>
		<div class='fleft filters'>
			Hours<br/>
			<input type='text' size='6' name='hours' value='<?php echo $inputhours; ?>' />
		</div>
		<div class='fleft filters'>
			Level<br/>
			<select name='level'>
				<option value=''>--</option>
				<?php echo $filterlevel; ?>
			</select>
		</div>
		<div class='fleft filters'>
			Category<br/>
			<select name='category'>
				<option value=''>--</option>
				<?php echo $filtercategory; ?>
			</select>
		</div>
		<div class='fleft filters'>
			Graph Breakdown<br/>
			<label><input type='radio' name='field' value='source' <?php echo $radiosource; ?> />Source</label>
			<label><input type='radio' name='field' value='path' <?php echo $radiopath; ?> />Path</label>
			<label><input type='radio' name='field' value='level' <?php echo $radiolevel; ?> />Level</label>
			<label><input type='radio' name='field' value='rule_id' <?php echo $radiorule_id; ?> />Rule ID</label>
		</div>
		<div class='fleft filters'>
			<br/>
			<input type='submit' value='Go' />
		</div>
	</form>
	<div class='clr'>&nbsp;</div>
	</div>
</div>

<!-- Chart -->
<div id=chart>
	<div class='top10header toggle' style="width:100%;"><b>Results</b></div>
	<div class='newboxes toggled' style='display: block;'>
		<div id="chartdiv" style="width:100%; height:500px;"><?php echo $nochartdata; ?></div>
	</div>
</div>

<!-- Tables -->
<table id="top10s" class="top10sTables"><tr>
<td class=top10sTd style='width: 14em'>
	<div style='height:25em;overflow-y:auto;'>
		<?php include './php/toplocation-c.php'; ?>
	</div>
</td><td class=top10sTd>
	<div style='height:25em;overflow-y:auto;'>
		<?php include './php/topid-c.php'; ?>
	</div>
</td><td class=top10sTd>
	<div style='height:25em;overflow-y:auto;'>
		<?php include './php/toprare-c.php'; ?>
	</div>
</td></tr></table>

<?php
include './footer.php';
?>
