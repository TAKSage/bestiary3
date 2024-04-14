<!DOCTYPE html>
<html>
<head>
	<title>Maximus presents... The Total Annihilation: Kingdoms Bestiary</title>
</head>

<body leftmargin="20" topmargin="0" rightmargin="20" marginheight="0" marginwidth="0">

<!-- google analytics code -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-41970612-1', 'takingdoms.net');
  ga('send', 'pageview');

</script>
<!-- ends google analytics -->

<style type="text/css">
	body {
		background-color:white;
		color:black;
		font:10pt verdana,sans-serif;
		background-image: url(../images/bg.gif);
		/* scrollbar-face-color: #232323;
		scrollbar-highlight-color: #7D7D7D;
		scrollbar-shadow-color: #5A5A5A;
		scrollbar-3dlight-color: #DDDDDD;
		scrollbar-arrow-color: #E9D000;
		scrollbar-track-color: #000000;
		scrollbar-darkshadow-color: #000000; */
	}

	/* hr	{ height: 8px; border: double #000000; border-top-width: 1px;} */

	td {
		color:black;
		font:8pt verdana,sans-serif;
	}
	a:link,a:visited,a:active { BACKGROUND: none; FONT-WEIGHT: bold; COLOR: #1F44CD; FONT-SIZE: 10pt; FONT-FAMILY: "Times New Roman",Times,Serif; TEXT-DECORATION: none}
	a:hover {
		color:#0000FF; text-decoration:underline;
	}
	table {
		width:100%;
		font:8pt;
		background-color:#555555;
	}
	table tr td {
		background-color:white;
		background-image: url(../images/bg.gif);
	}
	.storytitle  {BACKGROUND: none; COLOR: #00009E; FONT-SIZE: 10pt; FONT-WEIGHT: bold; FONT-FAMILY: "Times New Roman",Times,Serif; TEXT-DECORATION: none}
</style>

<center><a href="index.php"><img src="../images/zhondrake.gif" alt="Click the Beast to send you back Home" width="700" height="133" border="0"></a></center>
<div align="center">
<font size="1">Maximus presents...</font><br /><br />
<font size="3"><u>The Total Annihilation: Kingdoms Bestiary</u></font><br /><br />Version: 3.0<br /><br /></div>


<div align="center">
	<!-- google adds -->
		<script type="text/javascript"><!--
			google_ad_client = "ca-pub-9736993425945945";
			/* takingdoms_net1 */
			google_ad_slot = "3329110711";
			google_ad_width = 468;
			google_ad_height = 60;
			//-->
		</script>
		<script type="text/javascript"
			src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
		</script>
	<!-- ends google adds -->
</div>


<?php
function getmicrotime(){  
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);  
}
$time_start = getmicrotime();
if(!@$lib) require("library.php");
// parse query string
$lib->get = $_GET;
function strip_slashes(&$item,$key) {
	if(is_array($item))
		return array_walk($item, 'strip_slashes');
	$item = stripslashes($item);
}
array_walk($lib->get, 'strip_slashes'); 
//$lib->parse_get();
// end of parse
if(@$lib->get['opt']['notice'])error_reporting(E_ALL);
else error_reporting(E_ALL & ~E_NOTICE);

if(@$lib->get['opt'])
	foreach(@$lib->get['opt'] as $key=>$opt)
		$lib->get['opt'][$opt] = 1;
$panel_mode = @$lib->get['panel_mode'];

// parse all directories
$lib->parse_directories();
// end of parsing all directories

if(!@$lib->get['opt']['no_panel'] == 1)include("bestiary_panel.php");

// fbi debug
if(@$lib->get['opt']['debug_fbi']) $lib->debug_code = true;
// end of
// limit filter
$mods = 0;
$races = 0;
$selected = "Selected Sets/Mods/Races:<br><Br>\n\n";
$race_select = @count($lib->get['race']);

if($race_select != 0 && $lib->get['race'][0] == "All") $race_select = 5;
$len = @count($lib->get["path"]);
for($i = 0; $i < $len; $i++) {
	$path = $lib->get["path"][$i];
	$paths = explode("/",$path);
	if($paths[0] == "sets" || $paths[0] == "mods") {
		$races += $race_select;
		$selected .= "$path [$race_select race(s)]<Br>\n";
		$mods++;
	}
	else if($paths[0] == "races") {
		$races++;
		$selected .= "$path: [1 race]<Br>\n";
	}
}
if($races > $lib->races_limit || $mods > $lib->mods_limit) {
	echo ($races>$lib->races_limit?"<b>Too many races selected [$races] must be {$lib->races_limit} or less!</b><br><Br>\n\n":"").($mods>$lib->mods_limit?"<b>Too many sets/mods selected [$mods] must be {$lib->mods_limit} or less!</b><Br><br>\n\n":"").$selected."<br><br>";
	@$lib->get['path'] = "";
}
// end of

// parse race filter
$race_filter = "";
if( @$lib->get['race'] && $lib->get['race'][0] != "All" )
	foreach($lib->get['race'] as $key=>$race)
		$race_filter .= "\$unit['UNITINFO']['description'] == '$race' || ";
if(!$race_filter) $race_filter .= "true || ";
if( @$lib->get['path'] )
	foreach($lib->get['path'] as $key=>$path) {
		if( strpos($path,'race') !== false ) {
			$race_filter .= "strpos(\$name,'$path') !== false || ";
		}
	}
if($race_filter) $lib->get['filter'][] = "( ".substr($race_filter,0,strlen($race_filter)-3)." )";
// end of

$set_paths = @$lib->get['path'];

if($set_paths || @$lib->get['opt']['downloads'] == 1) {
	

	// scandir clone -- for downloads
	if(@$lib->get['opt']['downloads'] == 1) {
		echo "<hr><br /><b><u><font size=3>All Downloads</font></u></b><Br><br>";
	}

	$downloads_dir  = opendir($lib->downloads); 
	$downloads_file = array();
	$downloads_name = array();
	$downloads_path = array();
	while (false !== ($type = readdir($downloads_dir))) { 
		if($type!="."&&$type!="..") {
			if(@$lib->get['opt']['downloads'] == 1)
				echo "<b><u>$type</u></b><br />";
			$downloads_type  = opendir($lib->downloads."/".$type); 
			while (false !== ($file = readdir($downloads_type))) { 
				if($file!="."&&$file!="..") {
					if(@$lib->get['opt']['downloads'] == 1) {
						$filename = str_replace("_", " ", $file);
						$name = ucwords(substr($filename,0,strrpos($filename,".")));
						echo "- <a href='{$lib->downloads}/{$type}/$file'>$name [".$lib->fsize("{$lib->downloads}/{$type}/$file")."]</a><br />";
					}
					else {
						$downloads_file[] = $file; 
						$downloads_name[] = substr($file,0,strrpos($file,"."));
					}
				}
			}
			closedir($downloads_type);
		}
	}
	closedir($downloads_dir);
	@include("downloads_special.php"); // special download filenames

	if(@$lib->get['opt']['downloads'] == 1 && $set_paths) {
		echo "<br />";
		//exit;
	}
	else if(!$set_paths)
		exit;

	// end of s

	// load cavedog files
	$lib->buildpics["{$lib->sets}/{$lib->cavedog}"] = $lib->load_directory("{$lib->sets}/{$lib->cavedog}/{$lib->buildpic}");
	$lib->weaponpics["{$lib->sets}/{$lib->cavedog}"] = $lib->load_directory("{$lib->sets}/{$lib->cavedog}/{$lib->weaponpic}");
	$lib->canbuilds["{$lib->sets}/{$lib->cavedog}"] = @$lib->load_directory("{$lib->sets}/{$lib->cavedog}/{$lib->canbuild}");
	$lib->canbuildcbs["{$lib->sets}/{$lib->cavedog}"] = @$lib->load_directory("{$lib->sets}/{$lib->cavedog}/{$lib->canbuildcb}");
	//end of load

	// new get sets/mods
	foreach($set_paths as $key=>$path) {
		if(is_dir($path)) {
			$units_path = $path;
			$units_dir = substr( $path, strrpos( $path, "/" )+1 );
			$set_path = substr( $path, 0, strrpos( $path, "/" ) );
			$res_path = $lib->sets."/".$lib->cavedog;
			$set_dirs = $lib->set_dirs["$set_path"];
			if(!@$lib->buildpics[$set_path])
				$lib->buildpics[$set_path] = @$lib->load_directory("$set_path/".@$set_dirs['buildpic']);;
			if(!@$lib->weaponpics[$set_path])
				$lib->weaponpics[$set_path] = @$lib->load_directory("$set_path/".@$set_dirs['weaponpic']);
			if(!@$lib->canbuilds[$set_path]&&$units_dir=="units") {
				$lib->canbuilds[$set_path] = @$lib->load_directory("$set_path/".@$set_dirs['canbuild']);
			}
			if(!@$lib->canbuildcbs[$set_path]&&$units_dir=="unitscb") {
				$lib->canbuildcbs[$set_path] = @$lib->load_directory("$set_path/".@$set_dirs['canbuildcb']);
			}
			//$lib->set_paths[$units_path] = array("units"=>$units_path,"set"=>$set_path,"res"=>$res_path);	
		}
		else {
			$lib->error("'$path' is not one of the included in this bestiary.");
		}
	}
	// end of
	
	foreach($set_paths as $key=>$set_path) {
		// include units from dir
		if($lib->use_saves) @include("{$lib->saves}/".str_replace("/",".",$set_path).".php");
		else {
			$lib->parse_path($set_path);
			if($lib->parse) $lib->system(sizeof($lib->parse)." units manually parsed from '{$set_path}'.");
		}

		// download set --- needs to be improved
		$type = substr($set_path,0,strpos($set_path,"/"));
		$set = substr($set_path,strlen($type)+1,strrpos($set_path,"/")-strlen($type)-1);
		$underscore_set = str_replace(" ","_",$set);
		$file_exists = array_search($underscore_set,$downloads_name);
		$special_exists = @$lib->downloads_special[$underscore_set];
		if($file_exists !== false) {
			$file_path = $lib->downloads."/$type/".$downloads_file[$file_exists];
			$filesize = $lib->fsize($file_path);
			$lib->download("<a href='$file_path'>".ucwords($set)." [".$downloads_file[$file_exists]." ".$filesize."]</a>");
		}
		if($special_exists&&in_array($underscore_set,$downloads_name)) {
			$file_path = $lib->downloads."/".$lib->downloads_special[$underscore_set];
			$filesize = $lib->fsize($file_path);
			$lib->download("<a href='$file_path'>".ucwords($set)." [".$lib->downloads_special[$underscore_set]." ".$filesize."]</a>");
		}
		// end of
	}
	
	if(!$lib->use_saves) $lib->keys = array_keys($lib->list);

	$lib->system(count($lib->list)." units loaded in ".(getmicrotime()-$time_start)." seconds.");

	// filters
	//$filter = array();//array("\$unit['WEAPON1']","!\$unit['WEAPON2']","!\$unit['WEAPON3']");
	$if_filter = "";
	foreach($lib->get['filter'] as $ndx=>$fil) $if_filter .= "($fil)&&";
	$if_filter .= "true";//substr($if_filter,2,strlen($if_filter)-2);
	//$if_filter = "true"; // until i fix other stuff
	// end of


	$num = 0;
	//uasort($lib->list, (($_GET['direction']) ? "za19" : "az91"));
	//while( list( $name, $unit ) = each( $lib->list ) ) {
	foreach($lib->list as $name=>$unit) {

		// filters
		if(!eval("return $if_filter;")) continue;
		// end of
		
		$file = basename(strtolower($name),".fbi");
		$files = explode("/",$name);
		$units_path = substr($name,0,strrpos($name,"/"));
		$set_path = substr($units_path,0,strrpos($units_path,"/"));
		$res_path = "{$lib->sets}/{$lib->cavedog}"; //cheating but leave for now
		$set_dirs = $lib->set_dirs[$set_path];
		$set_paths = $lib->set_paths[$set_path];
		$lib->set_path = array($set_path,$res_path);
		$set_path = $set_paths;


		//$files = explode("/",$name);
		
		

		/*$lib->set_path[] = $files[0]."/".$files[1];
		if( $files[0] != $lib->sets )
			$lib->set_path[] = $lib->sets."/".$lib->cavedog;
		$set = ucwords($files[1]);*/
		/*if( $files[0] == $lib->sets ) {
			$lib->set_path[] = $lib->sets."/".$files[1];
			$set = ucwords($files[1]);
		}
		else {
			$lib->set_path[] = $lib->mods."/".$files[1]."/".$files[2];
			$lib->set_path[] = $lib->sets."/".$files[1];
			$set = ucwords($files[2]);
		}*/
		$unitinfo = $unit['UNITINFO'];
		$balance = ($files[sizeof($files)-2]==@$set_dirs["unitscb"]) ? "Crusades Balance" : "Original TAK";
		if(!@$lib->get['opt']['no_images']) {
			$buildpic = $lib->file_in_dir("$file.jpg"/*"$file.jpg"*/,'buildpic');
			if(!$buildpic) {
				$buildpic = $lib->file_in_dir("{$unitinfo['unitname']}.jpg"/*"$file.jpg"*/,'buildpic');
				if($buildpic)$lib->error("'<a href=\"#{$units_path}/$file\">{$units_path}/$file</a>' fbi name != buildpic = unitname.");
			}
			if(!$buildpic) {
				$lib->error("'<a href=\"#{$units_path}/$file\">{$units_path}/$file</a>' has no buildpic.");
			}
			if($buildpic) $buildpic = "<img src=\"$buildpic\" width='64' height='48' border='0' alt=\"{$set_path}/".$files[count($files)-2]."/{$unitinfo['unitname']}\">";
		}
		else if(@$lib->get['opt']['no_images']) $buildpic = "[{$unitinfo['unitname']}]";

		// main template
		//$main_template = preg_replace("/#([^#]+)#/e","@ucwords(\$unit['UNITINFO']['\\1'])",$lib->templates['main']);
		//$main_template = preg_replace("/@([^@]+)@/e","@\$\\1;",$main_template);

		$html = "<a name=\"{$units_path}/$file\"><b>{$unitinfo['name']}</b></a>";
		///// check 3rd party units
		if( strpos($name, $lib->thirdpartypath) !== false ) {
			$underscore_name = str_replace(" ","_",strtolower($unitinfo['description']."_".$unitinfo['name']));
			$file_exists = array_search($underscore_name, $downloads_name);
			if($file_exists !== false) {
				$file_path = $lib->downloads."/".$lib->thirdpartydownloads."/".$downloads_file[$file_exists];
				$filesize = $lib->fsize($file_path);
				$html .= " <a href='$file_path'>[Download Unit ".$filesize."]</a>";
			}
		}
		/////
		$main_template = "<br /><table cellpadding='5' cellspacing='1' border='0' width='100%'><tr><td rowspan='2' width='64'>$buildpic</td><td>Unit No:</td><td>Unit Name:</td><td>Set:</td><td>Balance:</td><td>Side:</td><td>Type:</td><td>Cost:</td><td>Time:</td><td>Health:</td><td>Speed:</td></tr><tr><td>{$unitinfo['unitnumber']}</td><td>{$unitinfo['unitname']}</td><td>$set</td><td>$balance</td><td>{$unitinfo['description']}</td><td>".@$unitinfo['damagecategory']."</td><td>".@$unitinfo['buildcost']."</td><td>".@$unitinfo['buildtime']."</td><td>{$unitinfo['maxdamage']}</td><td>".@$unitinfo['maxvelocity']."</td></tr></table><br /><table cellpadding='5' cellspacing='1' border='0' width='100%'><tr><td>Radar:</td><td>Sight:</td><td>Mana Storage:</td><td>Mana Regen:</td><td>Mogrium Storage:</td><td>Mogrium Gen:</td><td>Health Regen Time:</td><td>Experience Points:</td></tr><tr><td>".@$unitinfo['radardistance']."</td><td>".@$unitinfo['sightdistance']."</td><td>".@$unitinfo['maxmana']."</td><td>".@$unitinfo['manarechargerate']."</td><td>".@$unitinfo['mogriumstorage']."</td><td>".@$unitinfo['mogriumincome']."</td><td>".@$unitinfo['healtime']."</td><td>".@$unitinfo['experiencepoints']."</td></tr></table><br />";
		//$main_template = preg_replace(array("/#([^#]+)#/e","/@([^@]+)@/e"),array("@ucwords(\$unit['UNITINFO']['\\1'])","@\$\\1;"),$lib->templates['main']);
		$html .= $main_template;
		// end of
		if(@$unit["WEAPON1"] || @$unit["WEAPON2"] || @$unit["WEAPON3"]) {
			$weapons = array();
			for($i = 1; $i < 4; $i++) {
				$weap = "WEAPON{$i}";
				if(@$unit[$weap]) {
					$weapon = $unit[$weap];
					$Damage = $weapon['DAMAGE'];
					if(!@$lib->get['opt']['no_images']) {
						$weaponpic = $lib->file_in_dir(@$weapon['buttonimageup'].".jpg",'weaponpic');
						if($weaponpic&&!@$lib->get['opt']['no_images'])$weaponpic="<img src=\"$weaponpic\" border='0' width='32' height='32'>";
						else if(@$weapon['buttonimageup']) {
							$lib->error("'$weap' of '<a href=\"#{$units_path}/$file\">{$units_path}/$file</a>' has a 'buttonimageup' but image '".@$weapon['buttonimageup'].".jpg' does not exist.");
						}
					}
					else if(@$lib->get['opt']['no_images']) $weaponpic = "";
	
					// damage mods
					$mods = "";
					//$default = "default"; // stupid reserved word -- remember
					foreach($Damage as $type=>$dmg) {
						if($type=="default") continue;
						//$mods .= preg_replace("/!([^!]+)!/e","@eval('return \\1;')",$lib->templates['damage_mods']);
						$mods .= round($dmg*$Damage['default'])." (".($dmg*100)."%) : ".ucwords($type)."<br />";
					}
					if(!$mods) $mods = ""; //"None";
					// end of

					// damage template
					//$damage = preg_replace(array("/#([^#]+)#/e","/@([^@]+)@/e"),array("@\$unit[$weap]['DAMAGE']['\\1']","@\$\\1"),$lib->templates['damage']);
					//$damage = "<table cellpadding='3' cellspacing='1' border='0'><tr><td width='50' align=center>{$Damage['default']}</td><td nowrap='nowrap'>$mods</td></tr></table>";
					/// end of

					// weapon template
					//$weapons[] = preg_replace(array("/#([^#]+)#/e","/@([^@]+)@/e"),array("@ucwords(\$unit[$weap]['\\1'])","@\$\\1"),$lib->templates['weapon']);
					$type = "";
					
					if(strtolower($weapon['type']) == "remote effect" && @$weapon['ringcount']) {
						$type = "<table cellpadding='3' cellspacing='1' border='0' width='100%'><tr><td>";
						$type .= "Buildup Time: {$weapon['builduptime']}<br>";
						$type .= "Decay Time: {$weapon['decaytime']}<br>";
						$type .= "Ring Count: {$weapon['ringcount']}<br>";
						$type .= "Ring Duration: {$weapon['ringduration']}<br>";
						$type .= "Ring Delay: {$weapon['ringdelay']}<br>";
						$type .= "Edge Effectiveness: ".($weapon['edgeeffectiveness']*100)."%<br>";
						$type .= "</td></tr></table>";
					}
					else if(strtolower($weapon['type']) == "remote effect" && @$weapon['particlespersecond']) {
						$type = "<table cellpadding='3' cellspacing='1' border='0' width='100%'><tr><td>";
						$type .= "Buildup Time: {$weapon['builduptime']}<br>";
						$type .= "Decay Time: {$weapon['decaytime']}<br>";
						$type .= "Duration: {$weapon['duration']}<br>";
						$type .= "Particles/s: {$weapon['particlespersecond']}<br>";
						$type .= "Edge Effectiveness: ".($weapon['edgeeffectiveness']*100)."%<br>";
						$type .= "</td></tr></table>";
					}
					else if(strtolower($weapon['type']) == "wandering") {
						$type = "<table cellpadding='3' cellspacing='1' border='0' width='100%'><tr><td>";
						$type .= "Buildup Time: {$weapon['builduptime']}<br>";
						$type .= "Decay Time: {$weapon['decaytime']}<br>";
						$type .= "Duration: {$weapon['duration']}<br>";
						$type .= "Edge Effectiveness: ".($weapon['edgeeffectiveness']*100)."%<br>";
						$type .= "</td></tr></table>";
						//foreach($weapon as $weapontype=>$valuetype) {
						//	$type .= $weapontype."=".$valuetype."<Br />";
						//}
					}
					else if(strtolower($weapon['type'])) {
						//foreach($weapon as $weapontype=>$valuetype) {
						//	$type .= $weapontype."=".$valuetype."<Br />";
						//}
					}
					$weapons[] = "<tr><td>$weaponpic</td><td>{$weapon['name']}</td><td>{$weapon['type']}<br />$type</td><td>{$Damage['default']}</td><td>$mods</td><td>{$weapon['range']}</td><td>".@$weapon['weaponvelocity']."</td><td>{$weapon['reloadtime']}</td><td>".@$weapon['areaofeffect']."</td><td>".@$weapon['manapershot']."</td></tr>";
					///
				}
			}
			//$lib->html .= preg_replace("/@([^@]+)@/e","@\$\\1",$lib->templates['secondary']);
			$html .= "<table cellpadding='3' cellspacing='1' border='0' width='100%'><tr><td>Pic:</td><td>Weapon:</td><td>Type:</td><td>Damage:</td><td>Damage Modifiers:</td><td>Range:</td><td>Velocity:</td><td>Reload Time:</td><td>Area of Effect:</td><td>Mana Cost:</td></tr>".@$weapons[0].@$weapons[1].@$weapons[2]."</table><br />";
		}
		// heal aura (AdjustJoy) -- seems complete
		$adjusts = array("Joy","Attack","Armor");
		foreach($adjusts as $key=>$adjust) {
			if(@$unit['UNITINFO']["Adjust$adjust"]) {
				$Adjust = $unit['UNITINFO']["Adjust$adjust"];
				//$auras = preg_replace("/#([^#]+)#/e","\$unit['UNITINFO']['Adjust$adjust']['\\1']",$lib->templates['auras']);
				//$auras = preg_replace("/@([^@]+)@/e","\$\\1",$auras);
				//$lib->html .= preg_replace("/!([^!]+)!/e","eval('return \\1;')",$auras);
				$html .= "<table cellpadding='5' cellspacing='1' border='0' width='100%'><tr><td colspan=4><b>$adjust Aura:</b></td></tr><tr><td>$adjust Adjustment:</td><td>Radius of Effect:</td><td>Affects Enemies:</td><td>Edge Effectiveness:</td></tr><tr><td>".@($Adjust['adjustment']*($adjust=='Joy'?1:100)).($adjust == 'Joy'?' Mogrium -> Health':'%')."</td><td>{$Adjust['radius']}</td><td>{$Adjust['affectsenemy']}</td><td>".($Adjust['edgeeffectiveness']*100)."%</td></tr></table><br />";
			}
		}
		// end of
		// can build -- decimal priority? -- canbuild is universal?
		if(@$lib->get['opt']['canbuild']) {
			$cb = (strtolower($files[sizeof($files)-2])=="unitscb") ? 'cb' : "";
			$builds_dir = $set_dirs["canbuild{$cb}"];
			//echo $set_dirs." = $builds_dir/{$file}<br>";
			if($lib->file_in_dir("$file","canbuild{$cb}")) {
				$buildpics = "";
				foreach($lib->set_path as $key=>$path) {
					$builds_dir = $lib->set_dirs[$path]["canbuild{$cb}"];
					$build = $lib->file_in_dir("$file","canbuild{$cb}",$path);
					if(!$build) continue;
					$build_dir = opendir($build);
					while($tree=readdir($build_dir)) {
						$tree = basename(strtolower($tree),".tdf");
						if($tree == "." || $tree == ".." || strpos($buildpics,"/$tree.jpg")) continue;
						$index = array_search("{$units_path}/$tree.fbi",$lib->keys);
						if(!@$lib->get['opt']['no_images']) {
							$buildpic = $lib->file_in_dir("$tree.jpg",'buildpic');
							if(!$buildpic) {
								if(!$lib->file_exists("$tree.fbi",$set_dirs["units{$cb}"]))
									$lib->error("'$path/$builds_dir/$tree' in '<a href=\"#{$units_path}/$file\">$build</a>' does not exist.");
								else
									$lib->error("'$path/$builds_dir/$tree' fbi name != buildpic or has no buildpic.");
							}
							if(!$buildpic)
								$buildpics .= "[/$tree.jpg] ";
							else if(!@$lib->get['opt']['no_images']) {
								if( $index !== false )
									$buildpics .= "<a href=\"#".substr($lib->keys[$index],0,strrpos($lib->keys[$index],'.'))."\"><img src=\"$buildpic\" border='0' alt=\"$build/$tree.jpg\" width='64' height='48' /></a> ";
								else
									$buildpics .= "<img src=\"$buildpic\" border='0' alt=\"$build/$tree.jpg\" width='64' height='48' /> ";
							}
						}
						else if (@$lib->get['opt']['no_images']) {
							if( $index !== false ) {
								$buildpics .= "<a href=\"#".substr($lib->keys[$index],0,strrpos($lib->keys[$index],'.'))."\">[".$lib->list[$lib->keys[$index]]['UNITINFO']['name']."]</a> ";
							}
							else
								$buildpics .= "[$tree] ";
						}
					}
					closedir($build_dir);
				}
				if(!$buildpics) $buildpics = "[None]: Empty build menu folder.";
				//$lib->html .= preg_replace("/@([^@]+)@/e","\$\\1",$lib->templates['buildmenu']);
				$html .= "<table cellpadding='3' cellspacing='1' border='0' width='100%'><tr><td><b>Can Build:</b> (may be incorrect and are based on the files in their build folders but TAK seems to interpret them differently)</td></tr><tr><td>$buildpics</td></tr></table><br />";
			}
		}
		// end of
		$html .= "<hr><br />\n";
		$lib->html[] = $html;
		$lib->order[] = eval("return @\$unit{$lib->get['order']};");
		$num++;
	}
	//$unit_serial = serialize($lib->list);
	//$fp = fopen("units_serialize.php", "a");
	//fwrite($fp, $unit_serial);
	//fclose($fp);

	//$s = implode("", @file("units_serialize.php"));
	//$a = unserialize($s);
	//print_r( $a);
	
	if($lib->system&&@$lib->get['opt']['system']||$lib->error&&@$lib->get['opt']['error']||$lib->download) {
	?>
	<hr /><br />
	<?php
	}
	$lib->system("$num units displayed in total.");

	$lib->download_out();

	if(@$lib->get['opt']['system'])$lib->system_out();

	if(@$lib->get['opt']['error'])$lib->error_out();

	?>
	<hr /><br />
	<b><u>Selected Units:</u></b><br /><br />
	<?php
	
	$final = "";
	uasort($lib->order, $lib->get['direction'] ? "za19" : "az91");
	foreach($lib->order as $key=>$value){
		$final .= $lib->html[$key];
	}
	echo $final;
	//$units_dir
	//print $lib->html;
}
//else {
//	$lib->error("No set was selected!");
//}

?>

<div align=center><fieldset style="text-align:left;width:100%;">
<legend>RX Website Skin By: <a href="mailto:ffuentealba@comcast.net">AxlRose-RX</a></legend>

<?php echo "<small>Page generated in ".(getmicrotime()-$time_start)." seconds</small><br />"; ?>

</fieldset></div>

</body>
</html>