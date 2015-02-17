<div class="fleft top10header" style='border-bottom: 1px silver solid;margin:0 0 5px 0;width:100%;'>
	<div style='font-size:1.5em;' title='A&#183;nal&#183;o&#183;gi [uh-nal-uh-jee] Noun. A similarity between like features of two things, on which a comparison may be based.'>
		<span style='color:rgb(0,84,130)'>Ana</span><span style='color:rgb(237,28,36); font-style:italic;font-weight: bold'>Log</span><span style='color:rgb(0,84,130);'>i</span>
		<!--div class='tiny' style=''>
			<span class="tiny">A&#183;nal&#183;o&#183;gi [uh-nal-uh-jee] Noun. A similarity between like features of two things, on which a comparison may be based.</span>
		</div-->
	</div>

	<div class="tiny fright" style='display:inline-table;margin:0px 30px 0px 0;padding:0;' >
		<?php
			//fixed: undefined var: $glb_ossecdb
			include './config.php';
			if(count($glb_ossecdb)>1){
				echo "
				<form action='./index.php'>
					<select name='glb_ossecdb' onchange='document.cookie=\"ossecdbjs=\"+glb_ossecdb.options[selectedIndex].value ; location.reload(true)'>";

					foreach ($glb_ossecdb as $name => $file){
						if($_COOKIE['ossecdbjs'] == $name){
							$glb_ossecdb_selected=" SELECTED ";
						}else{
							$glb_ossecdb_selected="";
						}
						$glb_ossecdb_option.="<option value='".$name."' ".$glb_ossecdb_selected." >".$name." (".DB_NAME_O.", ".DB_HOST_O.")</option>";
					}
					echo $glb_ossecdb_option;
				echo "</select>
				</form>";
			}
		?>
		<a class='tinyblack' href='./index.php?'>Home</a> &nbsp;
		<a class='tinyblack' href='./newsfeed.php?'>NewsFeed</a> &nbsp;
		<a class='tinyblack' href='./massmonitoring.php?'>Monitor</a> &nbsp;
		<a class='tinyblack' href='./detail.php?from=<?php echo date("Hi dmy", (time()-(3600*24*30))) ?>'>Query</a> &nbsp;
		<a class='tinyblack' href='./ip_info.php?'>IP Search</a> &nbsp;
		<a class='tinyblack' onclick='alert("Warning : Due to the complexity of the code, this page may take a few minute to load."); window.location="./management.php"' href='#' >Management</a> &nbsp;
		<a class='tinyblack' href='./about.php'>About</a> &nbsp;
		<a class='tinyblack' href='https://github.com/ChrisDeFreitas/analogi-c/blob/indexphp/README.txt'>analogi-c</a> &nbsp;
		<?php
		if(isset($wallboard_url))	//fixed: undefined var $wallboard_url
			echo $wallboard_url;
		?>
	</div>
</div>
