<form name="beastiary" action="<?=$_SERVER['PHP_SELF']?>" method="get" onsubmit="//if(!six_races()) return false;">
<style type="text/css"><!--
table.srStyles {
	width:100%;
	background-color:black;
}
table.srStyles td {
	text-align:left;
	vertical-align:top;
	font:10px verdana,sans-serif;
	color:white;
}
table.srStyles fieldset {
	padding:5px;
	padding-top:0px;
	border:1px solid #999999;
	height:110px;
}
table.srStyles legend {
	border:1px solid #999999;
	padding:3px;
	margin-bottom:5px;
}
table.srStyles select {
	font:10px verdana,sans-serif;
}
--></style>
<div align=center><fieldset style="text-align:left;width:100%;">
<legend>Show By: Set / Rebalance / Mod</legend>
<table cellpadding=0 cellspacing=0>
<tr>
	<td valign=top width="250"><u><b>Original TA:Kingoms</b></u> <a href="javascript:showNames('units',0)">[show]</a> <a href="javascript:showNames('units',1)">[hide]</a><br><br>
	<?=$lib->panel_units;?>
<?php //$lib->scan_sets('<input type="checkbox" name="path" value="%P">%W<br>',$lib->units); ?></td>
	<td valign=top width="250"><u><b>Crusades Balance</b></u> <a href="javascript:showNames('unitscb',0)">[show]</a> <a href="javascript:showNames('unitscb',1)">[hide]</a><br><br>
	<?=$lib->panel_unitscb;?>
<?php //$lib->scan_sets('<input type="checkbox" name="path" value="%P">%W<br>',$lib->unitscb); ?></td>
	<td valign=top width="400"><b><u>Race Selection:</u></b> <a href="javascript:showRaces(0)">[show]</a> <a href="javascript:showRaces(1)">[hide]</a><Br /><br />
	<div id="race_Desc/div" style="display:<?=@in_array("Aramon",$lib->get['race'])||@in_array("Veruna",$lib->get['race'])||@in_array("Taros",$lib->get['race'])||@in_array("Zhon",$lib->get['race'])||@in_array("Creon",$lib->get['race'])?"block":"block"?>">(If none are selected, all races are shown --<br />
	-- 3rd party races are enabled automatically)</div>
	<!--<?=(!@in_array("Aramon",$lib->get['race'])&&!@in_array("Veruna",$lib->get['race'])&&!@in_array("Taros",$lib->get['race'])&&!@in_array("Zhon",$lib->get['race'])&&!@in_array("Creon",$lib->get['race'])&&$panel_mode)||!@$lib->get['race']?"block":"none"?>-->
<div id="race_All/div" style="display:<?=@in_array("All",$lib->get['race'])||!@$lib->get['race']||!$panel_mode?"block":"none"?>"><input type="checkbox" name="race[]" value="All" <?=@in_array("All",$lib->get['race'])||!@$lib->get['race']?"checked":""?> onclick="clearRaces(true);">Select All</div>
<div id="race_Aramon/div" style="display:<?=@in_array("Aramon",$lib->get['race'])||!$panel_mode?"block":"none"?>"><input type="checkbox" name="race[]" value="Aramon" <?=@in_array("Aramon",$lib->get['race'])?"checked":""?> onclick="clearRaces(false);">Aramon</div>
<div id="race_Veruna/div" style="display:<?=@in_array("Veruna",$lib->get['race'])||!$panel_mode?"block":"none"?>"><input type="checkbox" name="race[]" value="Veruna" <?=@in_array("Veruna",$lib->get['race'])?"checked":""?> onclick="clearRaces(false);">Veruna</div>
<div id="race_Taros/div" style="display:<?=@in_array("Taros",$lib->get['race'])||!$panel_mode?"block":"none"?>"><input type="checkbox" name="race[]" value="Taros" <?=@in_array("Taros",$lib->get['race'])?"checked":""?> onclick="clearRaces(false);">Taros</div>
<div id="race_Zhon/div" style="display:<?=@in_array("Zhon",$lib->get['race'])||!$panel_mode?"block":"none"?>"><input type="checkbox" name="race[]" value="Zhon" <?=@in_array("Zhon",$lib->get['race'])?"checked":""?> onclick="clearRaces(false);">Zhon</div>
<div id="race_Creon/div" style="display:<?=@in_array("Creon",$lib->get['race'])||!$panel_mode?"block":"none"?>"><input type="checkbox" name="race[]" value="Creon" <?=@in_array("Creon",$lib->get['race'])?"checked":""?> onclick="clearRaces(false);">Creon</div>
<br />
<u><b>Display Options:</b></u> <a href="javascript:showOptions(0)">[show]</a> <a href="javascript:showOptions(1)">[hide]</a><br><br>
<div id="opt_order/div" style="display:<?=$panel_mode?"block":"block"?>">Order By: <select name="order" onclick="//this.onchange();" onchange="checkSIndex(this)">
<?php
	$orders = array(
		"Unit Name" => "['UNITINFO']['name']",
		"Unit Health" => "['UNITINFO']['maxdamage']",
		"Weapon 1 Damage" => "['WEAPON1']['DAMAGE']['default']",
	);
	foreach($orders as $text=>$value)
		echo "<option value=\"$value\" ".(@$lib->get['order'] == $value?"selected":"").">$text</option>\n";
?>
<option value="">Custom...</option>
<?php
	if( @$lib->get['order'] && !in_array(stripslashes($lib->get['order']),$orders) )
		echo "<option value=\"{$lib->get['order']}\" selected>{$lib->get['order']}</option>\n";
?>
</select> <a href="" onclick="alert('You can write a custom order filter by selecting \'Custom\'. You will need some knowledge of how the code of an FBI files looks to use this functions.\n\nThe data you must enter is as follows:\n\n\t[\'<FBI Object>\']...[\'<prop>\']\n\nFor example, to sort the units by name you would put this as the order code:\n\n\t[\'UNITINFO\'][\'name\']\n\nOr if you wanted to sort it by weapon 1 default damage, you would put the following code:\n\n\t[\'WEAPON1\'][\'DAMAGE\'][\'default\']');return false;">["Custom..." Explanation]</a>
<script language="JavaScript">
<!--
function checkSIndex(obj) {
	if(obj[obj.selectedIndex].value == "") {
		var c = prompt("Enter a custom order script:","");
		if(c) {
			obj[obj.length] = new Option(c,c,true,true);
		}
		else {
			obj.selectedIndex = 0;
		}
	}
}
//-->
</script>
<script language="JavaScript">
<!--
function check(text) {
	var paths = document.forms["beastiary"].elements["path[]"];
	for(var i = 0; i < paths.length; i++) {
		if(paths[i].value.indexOf("/"+text) > -1/* && paths[i].value.indexOf("/"+text) + text.length+1 == paths[i].value.length*/) {
			paths[i].checked = !paths[i].checked;
			break;
		}
	}
}
function showNames(unit,mode) {
	var paths = document.forms["beastiary"].elements["path[]"];
	var types = new Array();
	for(var i = 0; i < paths.length; i++) {
		if((paths[i].value.toLowerCase()+"/").indexOf("/"+unit+"/") > 0) {
			var display = !mode || mode == 1 && paths[i].checked ? "block" : "none";
			document.getElementById(paths[i].value+"/div").style.display = display;
			var type = paths[i].value.substring(0,paths[i].value.indexOf('/'))+"/"+paths[i].value.substring(paths[i].value.lastIndexOf('/')+1,paths[i].value.length).toLowerCase();
			types[type] = display == "block" || types[type] == true ? true : false;
		}
	}
	var num = 0;
	for(var i in types) {
		var type = document.getElementById(i+"/div");
		if(type) {
			type.style.display = types[i] ? "none" : "block";
		}
	}
}
function showRaces(mode) {
	var races = document.forms["beastiary"].elements["race[]"];
	var selected = false;
	for(var i = 0; i < races.length; i++) {
		var display = !mode || mode == 1 && races[i].checked ? "block" : "none";
		document.getElementById("race_"+races[i].value+"/div").style.display = display;
		selected = display == "block" || selected == true ? true : false;
	}
	//document.getElementById("race_Desc/div").style.display = selected ? "block" : "none";
	//document.getElementById("race_All/div").style.display = selected ? "none" : "block";
}
function clearRaces(clear) {
	var races = document.forms["beastiary"].elements["race[]"];
	for(var i = 1; i < races.length; i++) {
		if(races[i].checked == true && clear == true)
			races[i].checked = false;
				
	}
	races[0].checked = clear;
}
function showOptions(mode) {
	var opts = document.forms["beastiary"].elements["opt[]"];
	for(var i = 0; i < opts.length; i++) {
		var display = !mode || mode == 1 && opts[i].checked ? "block" : "none";
		document.getElementById("opt_"+opts[i].value+"/div").style.display = display;
	}
	document.getElementById("opt_order/div").style.display = !mode ? "block" : "block";
}
//-->
</script><br />
From <input type="radio" name="direction" value="0" <?=!@$lib->get['direction']?"checked":""?>>A-Z 9-1
<input type="radio" name="direction" value="1" <?=@$lib->get['direction']?"checked":""?>>Z-A 1-9<Br /></div>
<div id="opt_no_images/div" style="display:<?=@$lib->get['opt']['no_images']||!$panel_mode?"block":"none"?>"><input type="checkbox" name="opt[]" value="no_images" <?=@$lib->get['opt']['no_images']?"checked":""?>>Disable Images</div>
<div id="opt_canbuild/div" style="display:<?=@$lib->get['opt']['canbuild']||!$panel_mode?"block":"none"?>"><input type="checkbox" name="opt[]" value="canbuild" <?=@$lib->get['opt']['canbuild']?"checked":""?>>Enable Build Menus</div>
<div id="opt_system/div" style="display:<?=@$lib->get['opt']['system']||!$panel_mode?"block":"none"?>"><input type="checkbox" name="opt[]" value="system" <?=@$lib->get['opt']['system']?"checked":""?>>Enable System Messages</div>
<div id="opt_error/div" style="display:<?=@$lib->get['opt']['error']||!$panel_mode?"block":"none"?>"><input type="checkbox" name="opt[]" value="error" <?=@$lib->get['opt']['error']?"checked":""?>>Enable Error Messages</div>
<div id="opt_debug_fbi/div" style="display:<?=@$lib->get['opt']['debug_fbi']||!$panel_mode?"block":"none"?>"><input type="checkbox" name="opt[]" value="debug_fbi" <?=@$lib->get['opt']['debug_fbi']?"checked":""?>>Enable FBI Debugging [<a href="#" onclick="alert('This is a special mode which makes the beastiary run special checks on the FBI files it loads into memory. The purpose of this mode is for mod/race developers to check if their FBI files are written correctly and to minimize the chances of their work causeing TAK to crash.\n\nWhen this mode is enabled, the beastiary will report any errors it finds and attempt to give you the location of the error if the error is of a known type. If the error is of an unknown type, it will dump the FBI code which has been converted to PHP code so that the developer might be able to find the problem manually.\n\nNote that some FBI errors are not detected without this mode enabled. If this mode is not enabled, the beastiary will only report that it could not parse the unit data when it runs into obvious syntax errors. It will not show any detail as to how many errors there are and where you might find them.');return false;">What is this?</a>]</div>
<!--<div id="opt_notice/div" style="display:<?=@$lib->get['opt']['notice']||!$panel_mode?"block":"none"?>"><input type="checkbox" name="opt[]" value="notice" <?=@$lib->get['opt']['notice']?"checked":""?>>Enable PHP Notices [<a href="#" onclick="alert('This is a feature for bestiary masters to see any kind of \'notice\' type errors the bestiary may be generating as it\'s processing and displaying the units. In certain cases this feature can help unit developers with fbi debugging as well although the error messages will give you very limited information.');return false;">What is this?</a>]</div>-->
<div id="opt_downloads/div" style="display:<?=@$lib->get['opt']['downloads']||!$panel_mode?"block":"none"?>"><input type="checkbox" name="opt[]" value="downloads" <?=@$lib->get['opt']['downloads']?"checked":""?>>Show All Downloads</div>
<br />
<!--<u><b>Download Manager:</b></u> <a href="javascript:showOptions(0)">[show]</a> <a href="javascript:showOptions(1)">[hide]</a><br><br>
<div id="download_selected/div"><input type="checkbox" name="opt[]" value="debug_fbi" <?=@$lib->get['opt']['debug_fbi']?"checked":""?>>Selected Mods/Races</div>
<div id="download_selected/div"><input type="checkbox" name="opt[]" value="debug_fbi" <?=@$lib->get['opt']['debug_fbi']?"checked":""?>>All Downloads</div>
<div id="download_selected/div"><input type="checkbox" name="opt[]" value="debug_fbi" <?=@$lib->get['opt']['debug_fbi']?"checked":""?>>All Mods</div>
<div id="download_selected/div"><input type="checkbox" name="opt[]" value="debug_fbi" <?=@$lib->get['opt']['debug_fbi']?"checked":""?>>All Races</div>
<div id="download_selected/div"><input type="checkbox" name="opt[]" value="debug_fbi" <?=@$lib->get['opt']['debug_fbi']?"checked":""?>>Aramon 3rd Party Units</div>
<div id="download_selected/div"><input type="checkbox" name="opt[]" value="debug_fbi" <?=@$lib->get['opt']['debug_fbi']?"checked":""?>>Taros 3rd Party Units</div>
<div id="download_selected/div"><input type="checkbox" name="opt[]" value="debug_fbi" <?=@$lib->get['opt']['debug_fbi']?"checked":""?>>Veruna 3rd Party Units</div>
<div id="download_selected/div"><input type="checkbox" name="opt[]" value="debug_fbi" <?=@$lib->get['opt']['debug_fbi']?"checked":""?>>Zhon 3rd Party Units</div>
<div id="download_selected/div"><input type="checkbox" name="opt[]" value="debug_fbi" <?=@$lib->get['opt']['debug_fbi']?"checked":""?>>Creon 3rd Party Units</div>
<div id="download_button/div"><input type="submit" value="View Downloads List >>" onclick="this.form.action.value = 'downloads'"></div>
<br />-->
<IMG SRC="../images/letters/Y.gif" align="left">ou are only allowed to choose 6 <b>races</b> and/or 2 <b>sets/mods</b>  at one time. This means if you choose to show all the races from a given set/mod you are choosing 5 races.<br><br> For Mods that contain a lot of units such as <b>Vaeruns 3rd Party Mod</b> that you want to display them with the option <b>Enable Build Menus</b>, it is recommended to activate only one race at a time, or the Bestiary won't be able to process so many units at a time.<br><br><b>Important:</b> you need to download and install <a href="https://github.com/riquems/tak-enhanced">The Enhanced Launcher</a> to be able to play the new Units, Mods and Races that appear for download on this Bestiary.<br><br /><input type="hidden" value="default" name="action">
<input type="hidden" value="1" name="panel_mode">
<input type="submit" value="Go >>" />

</td>
</tr>
</table>
<script language="JavaScript">
<!--
function six_races() {
	mods = 0;
	races = 0;
	selected = "Selected Mods:\n\n";
	race_select = 0;
	for(var i = 0; i < document.forms["beastiary"].elements["race[]"].length; i++) {
		if(document.forms["beastiary"].elements["race[]"][i].checked)
			race_select++;
	}
	if(race_select == 0) race_select = 5;
	var len = document.forms["beastiary"].elements["path[]"].length;
	for(var i = 0; i < len; i++) {
		if(document.forms["beastiary"].elements["path[]"][i].checked) {
			var path = document.forms["beastiary"].elements["path[]"][i].value;
			var paths = path.split("/");
			if(paths[0] == "sets" || paths[0] == "mods") {
				races += race_select;
				selected += path+" ["+race_select+" race(s)]\n";
			}
			else if(paths[0] == "races") {
				races++;
				selected += path+": [1 race]\n";
			}
			mods++;
		}
	}
	if(races > 6 || mods > 6) {
		alert((races>6?"Too many races ["+races+" selected] must be 6 or less!\n\n":"")+(mods>6?"Too many sets/mods/races ["+mods+" selected] must be 6 or less!\n\n":"")+selected);
		return false;
	}
	return true;
}
//-->
</script>
</fieldset></div>
</form>