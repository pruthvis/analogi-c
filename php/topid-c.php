<?php
/*
 * Copyright (c) 2012 Andy 'Rimmer' Shepherd <andrew.shepherd@ecsc.co.uk> (ECSC Ltd).
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
//global $apputils;

//global data object passed to javascript
$data = array();

if($glb_debug==1){
	$starttime_topidchart = microtime();
	$startarray_topidchart = explode(" ", $starttime_topidchart);
	$starttime_topidchart = $startarray_topidchart[1] + $startarray_topidchart[0];
}

# To filter on 'Category' (SSHD) extra table needs adding, but they slow down the query for other things, so lets only put them into the SQL if needed....
if(strlen($wherecategory)>5){
	$wherecategory_tables=", signature_category_mapping, category";
	$wherecategory_and="and alert.rule_id=signature_category_mapping.rule_id
        and signature_category_mapping.cat_id=category.cat_id";
}else{
	$wherecategory_tables="";
	$wherecategory_and="";
}
$timestamp = (time()-($inputhours*60*60));
$query= <<< EOT
SELECT count(alert.id) as res_cnt, alert.rule_id as res_id, signature.description as res_desc, signature.rule_id as res_rule, signature.level as rule_lvl
	FROM alert, signature $wherecategory_tables
	WHERE alert.timestamp > "$timestamp"
	and alert.rule_id=signature.rule_id
	$wherecategory_and
	AND signature.level >= $inputlevel
	$glb_notrepresentedwhitelist_sql
	$wherecategory
	GROUP BY res_id, res_desc, res_rule
	ORDER BY count(alert.id) DESC
	LIMIT $glb_indexsubtablelimit;
EOT;

$mainstring="";
if(!$result=mysql_query($query, $db_ossec)){
	$mainstring= "SQL Error: ".$query;

}elseif($glb_debug==1){
	$mainstring="<div style='font-size:24px; color:red;font-family: Helvetica,Arial,sans-serif;'>Debug</div>";
	$mainstring.=$query;

	$endtime_topidchart = microtime();
	$endarray_topidchart = explode(" ", $endtime_topidchart);
	$endtime_topidchart = $endarray_topidchart[1] + $endarray_topidchart[0];
	$totaltime_topidchart = $endtime_topidchart - $starttime_topidchart;
	$mainstring.="<br>Took ".round($totaltime_topidchart,1)." seconds";
}else{

	# Keep this in the same format that detail.php already uses
	$from=date("Hi dmy", (time()-($inputhours*3600)));

	while($row = @mysql_fetch_assoc($result)){
		$stmp = htmlspecialchars( $row['res_desc'] );
		$data[] = array(number_format($row['res_cnt']),
										$row['rule_lvl'],
										$row['res_rule'],
										"<a href='./detail.php?rule_id=".$row['res_rule']."&from=".$from."&breakdown=source'>".$stmp."</a>"
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
				: json_encode($data, JSON_HEX_AMP |JSON_HEX_APOS |JSON_BIGINT_AS_STRING |JSON_HEX_QUOT |JSON_HEX_TAG );
	echo "\ndata = $stmp;";
	echo "\nsql = ".json_encode($query, JSON_HEX_AMP |JSON_HEX_APOS |JSON_BIGINT_AS_STRING |JSON_HEX_QUOT |JSON_HEX_TAG ).";\n";
	echo "\nvar caption = 'Rule Traffic'\n\n";
	echo "\nvar hint = '$glb_indexsubtablelimit least frequently seen rules in this query'\n\n";
?>
		//generate display controls
		var html = apputils.tableGen({caption: caption,
																 	data: data,
																 	colHeads:  ['#Alerts','Level','RuleID','Description'],
																	ctrlPrefix: 'topRules',		//table.id = topRulesTable
															 	 	sql: sql,
															 	 	hint: hint
																});
		$("#divTopRule").html(html);

	});
</script>
<div id="divTopRule"></div>