<?php
/*
 * Copyright (c) 2012 Andy 'Rimmer' Shepherd <andrew.shepherd@ecsc.co.uk> (ECSC Ltd).
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
//global data object passed to javascript
$data = array();

if($glb_debug==1){
	$starttime_toprarechart = microtime();
	$startarray_toprarechart = explode(" ", $starttime_toprarechart);
	$starttime_toprarechart = $startarray_toprarechart[1] + $startarray_toprarechart[0];
}


# This will not be pretty.  A SQL command was made that worked, but due to indexing design flaws with the OSSEC MYSQL schema the command took 10 minutes to run on a relatively new/empty database.
# A better version of this interface is planned that will redesign the databse and made this nicer.

$query="select distinct(alert.rule_id)
	from alert, signature, signature_category_mapping, category
	where alert.timestamp>".(time()-($inputhours*3600))."
	and alert.rule_id=signature.rule_id
	and alert.rule_id=signature_category_mapping.rule_id
	and signature_category_mapping.cat_id=category.cat_id
	and signature.level>".$inputlevel."
	".$wherecategory."";


if(!$result=mysql_query($query, $db_ossec)){
	echo "SQL Error:".$query;
}

$lastrare =  array();

while($row = @mysql_fetch_assoc($result)){

	$ruleid=$row['rule_id'];

	$querylast="select max(alert.timestamp) as time, signature.description as descr, signature.level
		from alert, signature
		where alert.rule_id=".$ruleid."
		and alert.rule_id=signature.rule_id
		and alert.timestamp<".(time()-($inputhours*3600));
	$resultlast=mysql_query($querylast, $db_ossec);
	$rowlast = @mysql_fetch_assoc($resultlast);
	$lastrare[$ruleid]=$rowlast['time']."||{$rowlast['level']}||$ruleid||{$rowlast['descr']}";
	//$lastrare[$ruleid]=$rowlast['time']."||".$rowlast['descr'];
}


if($glb_debug==1){
	$mainstring="<div style='font-size:24px; color:red;font-family: Helvetica,Arial,sans-serif;'>Debug</div>";
	$mainstring.=$query;

	$endtime_toprarechart = microtime();
	$endarray_toprarechart = explode(" ", $endtime_toprarechart);
	$endtime_toprarechart = $endarray_toprarechart[1] + $endarray_toprarechart[0];
	$totaltime_toprarechart = $endtime_toprarechart - $starttime_toprarechart;
	$mainstring.="<br>Took ".round($totaltime_toprarechart,1)." seconds";

}else{
	asort($lastrare);
	$i=0;
	$mainstring="";
	foreach ($lastrare as $key => $val) {
		if($i<$glb_indexsubtablelimit && trim($val)!="||"){
			$display=explode("||", $val);
			if($display[0]==""){
				$displaydate="New";
			}else{
				$displaydate=date("dS M H:i", $display[0]);
			}
			$i++;
			$stmp = htmlspecialchars( $display[3] );
			$data[] = array( $displaydate,
											$display[1],
											$display[2],
											"<a href='./detail.php?rule_id=".$key."&breakdown=source'>".$stmp."</a>"
											);
		}
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
	echo "\nvar caption = 'Rare Rules'\n\n";
?>
		//generate controls in a string
		var str = apputils.tableGen({ caption: caption,
																 data: data,
																 colHeads:  ['Date','Level','RuleID','Description'],
															 	 ctrlPrefix: 'topRare',		//table.id = topRare
															 	 sql: sql
																});
		$("#divTopRare").html(str);
	});
</script>
<div id="divTopRare"></div>
