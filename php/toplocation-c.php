<?php
/*
 * Copyright (c) 2012 Andy 'Rimmer' Shepherd <andrew.shepherd@ecsc.co.uk> (ECSC Ltd).
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

if($glb_debug==1){
	$starttime_toplocchart = microtime();
	$startarray_toplocchart = explode(" ", $starttime_toplocchart);
	$starttime_toplocchart = $startarray_toplocchart[1] + $startarray_toplocchart[0];
}

# To filter on 'Category' (SSHD) extra table needs adding, but they slow down the query for other things, so lets only put them into the SQL if needed....
if(strlen($wherecategory)>1){
        $wherecategory_tables=", signature_category_mapping, category";
        $wherecategory_and="and alert.rule_id=signature_category_mapping.rule_id
        and signature_category_mapping.cat_id=category.cat_id";
}else{
        $wherecategory_tables="";
        $wherecategory_and="";
}


//global $apputils;

//global data object passed to javascript
$data = array();

//first make a list of locations
$query= <<< EOT
SELECT DISTINCT	SUBSTRING_INDEX(SUBSTRING_INDEX(NAME, ' ', 1), '->', 1) dname
FROM location ORDER BY dname
EOT;
$result=mysql_query($query, $db_ossec);
$hostnames = array();
while($row = @mysql_fetch_assoc($result)){
	$hostnames[] = $row['dname'];
}


$query="SELECT count(alert.id) as res_cnt, SUBSTRING_INDEX(SUBSTRING_INDEX(location.name, ' ', 1), '->', 1) as res_name
	FROM alert, location, signature ".$wherecategory_tables."
	WHERE alert.location_id = location.id
	AND alert.rule_id = signature.rule_id
	".$wherecategory_and."
	AND signature.level>='".$inputlevel."'
	AND alert.timestamp>'".(time()-($inputhours*60*60))."'
	".$wherecategory."
	".$glb_notrepresentedwhitelist_sql."
	GROUP BY res_name
	ORDER BY res_cnt DESC
	LIMIT ".$glb_indexsubtablelimit;

$mainstring="";
if(!$result=mysql_query($query, $db_ossec)){
	$mainstring.= "SQL Error: ".$query;

}elseif($glb_debug==1){
	$mainstring="<div style='font-size:24px; color:red;font-family: Helvetica,Arial,sans-serif;'>Debug</div>";
	$mainstring.=$query;

	$endtime_toplocchart = microtime();
	$endarray_toplocchart = explode(" ", $endtime_toplocchart);
	$endtime_toplocchart = $endarray_toplocchart[1] + $endarray_toplocchart[0];
	$totaltime_toplocchart = $endtime_toplocchart - $starttime_toplocchart;
	$mainstring.="<br>Took ".round($totaltime_toplocchart,1)." seconds";

}else{

	$from = date("Hi dmy", (time()-($inputhours*3600)));
	$detailshours = (isset($_GET['level']) ? $detailshours="&level=".$inputlevel : "");

	while($row = @mysql_fetch_assoc($result)){
		$host = htmlspecialchars(preg_replace($glb_hostnamereplace, "", $row['res_name']));
		$data[] = array( number_format($row['res_cnt']),
										"<a href='./detail.php?source=".$row['res_name']."&level=".$inputlevel."&from=".$from.$detailshours."&breakdown=rule_id'>"
										.$host
										."</a>"
										);
		$key = array_search($row['res_name'], $hostnames, false);
		if($key !== false)
			array_splice($hostnames, $key, 1);
	}
	foreach($hostnames as $rawhost) {
		$host = htmlspecialchars(preg_replace($glb_hostnamereplace, "", $rawhost));
		$data[] = array( '0',
										"<a href='./detail.php?source=".$rawhost."&level=".$inputlevel."&from=".$from.$detailshours."&breakdown=rule_id'>"
										.$host
										."</a>"
										);
	}
}
?>

<script type="text/javascript">
	var data = [];
	$(document).ready(function(){
<?php
	//convert php data to javascript
	$stmp = ($mainstring != "")
				?	json_encode($mainstring, JSON_HEX_AMP |JSON_HEX_APOS |JSON_BIGINT_AS_STRING |JSON_HEX_QUOT |JSON_HEX_TAG )
				:	json_encode($data, JSON_HEX_AMP |JSON_HEX_APOS |JSON_BIGINT_AS_STRING |JSON_HEX_QUOT |JSON_HEX_TAG );
	echo "\ndata = $stmp;";
	echo "\nsql = ".json_encode($query, JSON_HEX_AMP |JSON_HEX_APOS |JSON_BIGINT_AS_STRING |JSON_HEX_QUOT |JSON_HEX_TAG ).";\n";
	echo "\nvar caption = 'Host Traffic'\n\n";
/*	echo "\nvar caption = 'Hosts - <span class=\"tw\">"
				.$inputhours
				."</span> Hrs, Lvl <span class=\"tw\">"
				.$inputlevel."+</span>';\n";
*/
?>
		//generate controls in a string
		var str = apputils.tableGen({ caption: caption,
																 data: data,
																 colHeads:  ['#Alerts','Host'],
															 	 ctrlPrefix: 'topHosts',		//table.id = topHostsTable
															 	 sql: sql
																});
		$("#divTopHosts").html(str);
	});
</script>
<div id="divTopHosts"></div>
