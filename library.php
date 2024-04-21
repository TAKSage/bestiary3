<?php

class lib {

// conf

var $use_saves = false;
var $debug_code = false;
var $races_limit = 6;
var $mods_limit = 2;

var $sets = "sets";
var $mods = "mods";
var $races = "races";
var $thirdpartypath = "mods/3rd party units";
var $thirdpartydownloads = "3rd_party_units";
var $extras;
var $cavedog = "cavedog";
var $saves = "saves";
var $units = "units";
var $unitscb = "unitscb";
var $buildpic = "anims/buildpic";
var $buildpics = array();
var $weaponpic = "anims/weaponpic";
var $weaponpics = array();
var $canbuild = "canbuild";
var $canbuilds = array();
var $canbuildcb = "canbuildcb";
var $canbuildcbs = array();
var $downloads = "downloads";

function lib() {
	$this->extras = array($this->mods,$this->races);
}

// end of

var $unit;
var $set_path;
var $set_paths;
var $set_dirs;
var $parse;
var $unparse;
var $get;
var $list = array();
var $keys = array();
var $html = array();
var $order = array();
var $templates = array( /// not in use for speed reasons :(
	"main" => "#name#<br /><table cellpadding='5' cellspacing='1' border='0' width='100%'><tr><td rowspan='2' width='64'><img src=\"@buildpic@\" width='64' height='48' border='0' alt=\"@lib->set_path[0]@/@files[count(\$files)-2]@/#unitname#\"></td><td>Unit No:</td><td>Set:</td><td>Balance:</td><td>Side:</td><td>Type:</td><td>Cost:</td><td>Time:</td><td>Health:</td><td>Speed:</td></tr><tr><td>#unitnumber#</td><td>@set@</td><td>@balance@</td><td>#description#</td><td>#damagecategory#</td><td>#buildcost#</td><td>#buildtime#</td><td>#maxdamage#</td><td>#maxvelocity#</td></tr></table><br /><table cellpadding='5' cellspacing='1' border='0' width='100%'><tr><td>Radar:</td><td>Sight:</td><td>Mana Storage:</td><td>Mana Regen:</td><td>Mogrium Storage:</td><td>Mogrium Gen:</td><td>Health Regen Time:</td><td>Experience Points:</td></tr><tr><td>#radardistance#</td><td>#sightdistance#</td><td>#maxmana#</td><td>#manarechargerate#</td><td>#mogriumstorage#</td><td>#mogriumincome#</td><td>#healtime#</td><td>#experiencepoints#</td></tr></table><br />",
	
	"damage" => "<table cellpadding='3' cellspacing='1' border='0'><tr><td width='50' align=center>#default#</td><td nowrap='nowrap'>@mods@</td></tr></table>",
	
	"damage_mods" => "!round(\$dmg*\$unit[\$weap]{'DAMAGE'}{'default'})! (!\$dmg*100!%) : !ucwords(\$type)!<br />",
	
	"weapon" => "<tr><td>@weaponpic@</td><td>#name#</td><td>#type#</td><td>@damage@</td><td>#range#</td><td>#weaponvelocity#</td><td>#reloadtime#</td><td>#areaofeffect#</td><td>#manapershot#</td></tr>",
	
	"secondary" => "<table cellpadding='3' cellspacing='1' border='0' width='100%'><tr><td>Pic:</td><td>Weapon:</td><td>Type:</td><td>Damage:</td><td>Range:</td><td>Velocity:</td><td>Reload Time:</td><td>Area of Effect:</td><td>Mana Cost:</td></tr>@weapons[0]@@weapons[1]@@weapons[2]@</table><br />",
	
	"buildmenu" => "<table cellpadding='3' cellspacing='1' border='0' width='100%'><tr><td><b>Can Build:</b></td></tr><tr><td>@buildpics@</td></tr></table><br />",
	
	"auras" => "<table cellpadding='5' cellspacing='1' border='0' width='100%'><tr><td colspan=4><b>@adjust@ Aura:</b></td></tr><tr><td>@adjust@ Adjustment:</td><td>Radius of Effect:</td><td>Affect Enemies:</td><td>Edge Effectiveness:</td></tr><tr><td>!#adjustment#*('@adjust@'=='Joy'?1:100)!!('@adjust@'=='Joy'?' Mogrium -> Health':'%')!</td><td>#radius#</td><td>#affectsenemy#</td><td>#edgeeffectiveness#</td></tr></table><br />"
);

function parse_fbi($file) {
	$open = fopen($file, "r");
	$read = fread($open, filesize($file));
	fclose($open);
	
	$read = preg_replace(
		array(
			"/\s*\/\/.*\n\s*|\s*\n\s*|\s*\t\s*|\s*\r\s*/",
			"/[\s\t]*=[\s\t]*/",
			"/;/",
			"/\]\{/",
			"/\}\[/",
			"/\[/",
			"/\}|,\"[})]/"
		),
		array(
			"",
			'"=>"',
			'","',
			'"=>array("',
			'),"',
			'',
			')'
		),
		$read);
	$read = "array(\"$read,\"MODIFIED\"=>\"".filemtime($file)."\");";
	//echo $read;
	@eval("\$unit = $read");

	// debug search
	// (missing {) | (missing ;) | (missing ; on last item)
	if($this->debug_code) {
		preg_match_all("/(\"[^,\]]*\][^\"]*\")|(=>[^,(]*=>)|(=>\"[^\"\)]*\)[^\",\)]*[,\)])/",$read,$matches);
			$err_str = "Known error matches: ".sizeof($matches[0])."<br>";
			foreach($matches[0] as $key=>$match) $err_str .= $match."<br>";
	}
	if(!$unit||@$matches[0]) {
		$this->error("Unit '$file' one of more syntax errors in it.");
		if(!@$matches[0]) $this->error("Unknown error, printing unit conversion code...<br>$read<br>");
		else if(@$matches[0]) $this->error($err_str);
		return;
	}
	else {
		$this->parse[$file] = "\$lib->list[\"$file\"] = $read";
		$this->list[strtolower($file)] = $unit;
	}
	//if(sizeof($this->list) == 1) print_r($unit);
	return $unit;
}

function fsize($file) {
       $a = array("B", "KB", "MB", "GB", "TB", "PB");
       $pos = 0;
       $size = filesize($file);
       while ($size >= 1024) {
               $size /= 1024;
               $pos++;
       }
       return round($size,2)." ".$a[$pos];
}

function parse_get(){
	$query = explode('&', urldecode($_SERVER['QUERY_STRING']));
	$this->get=array("filter"=>array(),"path"=>array(),"race"=>array());
	foreach($query as $key=>$val) {
		$param = explode('=', $val);
		if(@is_array($this->get[$param[0]]))
			$this->get[$param[0]][] = $param[1];
		else if(sizeof($param)==2) {
			$this->get[$param[0]] = $param[1];
		}
	}
}

function file_exists($file, $ext="") {
	if( func_num_args() == 2) $args = $this->set_path;
	else $args = array_slice(func_get_args(),2);
	foreach($args as $key=>$path) {
		if($ext)$path.="/$ext";
		if(file_exists("$path/$file")) return "$path/$file";
	}
	return "";
}

function file_in_dir($file, $dir, $set_path="") {
	$file = strtolower($file);
	$args = $set_path?array($set_path):$this->set_path;
	foreach($args as $key=>$path) {
		if(@$this->{$dir."s"}[$path][$file]) return $this->{$dir."s"}[$path][$file];
	}
	return false;
}

function path_exists($ext="") {
	if( func_num_args() == 1) $args = $this->set_path;
	else $args = array_slice(func_get_args(),1);
	foreach($args as $key=>$path) {
		if($ext)$path.="/$ext";
		if(file_exists($path)) return $path;
	}
	return "";
}

function unparse_path($units_path="") {
	//if($units_path) {$lib=$this;@include("{$units_path}.php");}
	$this->unparse = array();
	foreach($this->list as $unit_path=>$unit) { // remove first -- smaller array
		if(!file_exists($unit_path)||filemtime($unit_path)!=$this->list[$unit_path]['MODIFIED']) {
			unset($this->list[$unit_path]);
			$this->unparse[$unit_path] = ""; }
	}
}

function parse_path($units_path="") {
	// compare and read manually if necessary
	$this->parse = array();
	if(!$units_path) $units_path = $this->set_path['units'];
	$set_dir = opendir($units_path);
	while($file = readdir($set_dir)) {
		$file_name = substr( $file,0, strrpos($file,".") );
		$unit_path = "{$units_path}/{$file}";
		if( $file != $file_name."." && (!@$this->list[$unit_path] || filemtime($unit_path) != $this->list[$unit_path]['MODIFIED']) )
			$this->unit = $this->parse_fbi($unit_path);
	}
	closedir($set_dir);
	// end of
}

function write_path_file($units_path="") {
	//new write file
	if( $this->parse || $this->unparse ) {
		$serialize = "<?php\n";
		if(!$units_path) $units_path = $this->set_path['units'];
		foreach($this->list as $unit_path=>$unit) {
			if(strpos("/".$unit_path,$units_path."/")==1)
				$serialize .= "\$lib->list[\"$unit_path\"] = ".$this->serialize_unit($unit).";\n";
		}
		$dot_units_path = str_replace("/",".",$units_path);
		$units_file = fopen("{$this->saves}/{$dot_units_path}.php", "w");
		fwrite($units_file, $serialize."?>");
		fclose($units_file);
		touch("{$this->saves}/{$dot_units_path}.php",filemtime("{$units_path}"));
		if($serialize == "<?php\n") unlink("{$this->saves}/{$dot_units_path}.php");
		if($this->parse) $this->system(sizeof($this->parse)." units parsed manually from '$units_path'.");
		if($this->unparse) $this->system(sizeof($this->unparse)." units unparsed manually from '$units_path'.");
		return true;
	}
	// end of
}

function load_directory($dir,$arr = array()) {
	$dir_handle = opendir($dir);
	while(false !== ($file = readdir($dir_handle))) {
		if($file!="."&&$file!="..")
			$arr[strtolower("$file")] = "$dir/$file";
	}
	closedir($dir_handle);
	return $arr;
}

var $panel_path_string = "<div id=\"%s\" style='display:%s'><input type='checkbox' name=\"path[]\" value=\"%s\" %s><a href='#' onclick=\"check('%s');return false;\">%s</a></div>\n";
var $panel_path_bad_string = "<div id=\"%s\" style='display:%s;visibility:hidden;'><input type='checkbox' name=\"path[]\" value=\"%s\"></div>\n";
var $panel_units = "";
var $panel_unitscb = "";
var $panel_text = "";

function parse_directories() {
	//echo "<font>Page generated in ".(getmicrotime()-$GLOBALS['time_start'])." seconds<br />";

	// crawl sets and mods
	$types_dirs = array($this->sets,$this->mods,$this->races);
	$units_paths = array();
	$units_dirs = array($this->units,$this->unitscb);
	foreach($types_dirs as $key=>$types_dir) {
		$type_dirs = opendir($types_dir);
		foreach($units_dirs as $key=>$units_dir) {
			$this->{"panel_$units_dir"} .= "<br /><u>".ucwords($types_dir)."</u><br>";	
		}
		$selectedunits = false;
		$selectedunitscb = false;
		while($type_dir = readdir($type_dirs)) {
			if($type_dir!="." && $type_dir !="..") {
				// get real dir names
				$set_dir = opendir("{$types_dir}/$type_dir");
				while(false !== ($res_dir = readdir($set_dir))) {
					if(is_dir("{$types_dir}/$type_dir/$res_dir") && $res_dir != "." && $res_dir != ".." ) {
						$low_res_dir = strtolower($res_dir);
						$this->set_dirs["{$types_dir}/$type_dir"][$low_res_dir] = $res_dir;
						if( $low_res_dir == "anims" ) {
							$anims_dir = opendir("{$types_dir}/$type_dir/$res_dir");
							while(false !== ($pic_dir = readdir($anims_dir))) {
								if(is_dir("{$types_dir}/$type_dir/$res_dir/$pic_dir") && $pic_dir != "." && $pic_dir != "..") {
									//$lib->set_dirs[$path][$low_res_dir."/".strtolower($pic_dir)] = "$res_dir/$pic_dir";
									$this->set_dirs["{$types_dir}/$type_dir"][strtolower($pic_dir)] = "$res_dir/$pic_dir";
								}
							}
							closedir($anims_dir);
						}
					}
				}
				closedir($set_dir);
				// end of
				foreach($units_dirs as $key=>$low_units_dir) {
					@$units_dir = $this->set_dirs["{$types_dir}/$type_dir"][$low_units_dir];
					$units_path = "{$types_dir}/$type_dir/$units_dir";
					$is_checked = @in_array($units_path,$this->get['path']);
					$block = $is_checked && @$this->get['panel_mode'] || !@$this->get['panel_mode'] ? "block" : "none";
					if(is_dir($units_path)&&$units_dir) {
						$units_paths[] = $units_path;
						$is_checked = @in_array($units_path,$this->get['path']);
						$checked = $is_checked?"checked":"";
						$this->{"panel_$low_units_dir"} .= sprintf($this->panel_path_string,$units_path."/div",$block,$units_path,$checked,addslashes("$type_dir/$units_dir"),ucwords($type_dir));
						if($is_checked&&$key) $selectedunitscb = true;
						else if($is_checked) $selectedunits = true;
					}
					else {
						$this->{"panel_$low_units_dir"} .= sprintf($this->panel_path_bad_string,$units_path."$low_units_dir/div",$block,$units_path."$low_units_dir");;
					}
				}
			}
		}
		foreach($units_dirs as $key=>$units_dir) {
			$this->{"panel_$units_dir"} .= "<div id=\"$types_dir/$units_dir/div\" style='display:".(!${"selected$units_dir"}&&@$this->get['panel_mode']?'block':'none').";'>None Selected</div>";	
		}
	}

	if($this->use_saves) {
		//compare modify times and parse if necessary
		foreach($units_paths as $key=>$units_path) {
			$dot_units_path = "{$this->saves}/".str_replace("/",".",$units_path).".php";
			$file_time = @filemtime($dot_units_path);
			$dir_time = filemtime($units_path);
			//echo $file_time." = ".$dir_time." = ".$units_path."<br/>";
			if( $file_time != $dir_time ) {
				$lib = $this;
				@include($dot_units_path);
				$this->list = $lib->list;
				if($this->use_saves) $this->unparse_path($units_path);
				$this->parse_path($units_path);
				if($this->use_saves && $this->write_path_file($units_path))
					$this->system("'{$dot_units_path}' was written.");
				$this->list = array();
				
			}
		}
			
		//check for removed directories
		$saves_dir = opendir("{$this->saves}");
		while($save = readdir($saves_dir)) {
			if($save!="."&&$save!="..") {
				$dot_units_path = substr($save,0,strlen($save)-4);
				$units_path = str_replace(".","/",$dot_units_path);
				if(!is_dir($units_path)) {
					if(!$this->delete_file("{$this->saves}/{$dot_units_path}.php"))
						echo "Cannot Delete File: {$this->saves}/{$dot_units_path}.php";
					//unlink("{$this->saves}/{$dot_units_path}.php");
					$this->system("'$units_path' has been removed.");
				}
			}
		}
		closedir($saves_dir);
		$this->keys = array_keys($this->list);
	}
}

function delete_file($file){ 
  $delete = @unlink($file); 
  clearstatcache();
  if (@file_exists($file)) { 
     $filesys = eregi_replace("/","\\",$file); 
     $delete = @system("del $filesys");
     clearstatcache();
     if (@file_exists($file)) { 
        $delete = @chmod ($file, 0775); 
        $delete = @unlink($file); 
        $delete = @system("del $filesys");
     }
  }
  clearstatcache();
  if (@file_exists($file)){
     return false;
     }
     else{
           return true;
           }
}  // end function

function serialize_unit($unit) {
	$str = "Array(";
	foreach($unit as $key=>$val) {
		$str .= "\"$key\"=>";
		if($val."" == "Array") $str .= $this->serialize_unit($val).",";
		else $str .= "\"$val\",";
	}
	return substr($str,0,strlen($str)-1).")";
}

function is_mod($set, $mod) {
	if(is_dir("mods/$set/$mod"))
		return true;
	return false;
}

function is_set($set) {
	if(is_dir("sets/$set"))
		return true;
	return false;
}

var $download = "";

function download($str) {
	$this->download .= "$str<br />\n";
}

function download_out() {
	if($this->download) echo "<b>Selected Downloadable Mods:</b><br />\n".$this->download."<br />";
	$this->download = "";
}

var $error = "";

function error($str) {
	$this->error .= "$str<br />\n";
}

function error_out() {
	if($this->error) echo "<b>Error:</b><br />\n".$this->error."<br />";
	$this->error = "";
}

var $system = "";

function system($str) {
	$this->system .= "$str<br />\n";
}

function system_out() {
	if($this->system) echo "<b>System:</b><br>\n".$this->system."<br />";
	$this->system = "";
}

function is_prop($arr) {
	$args = func_get_args();
	for($i = 1; $i < sizeof($args); $i++) {
		if(!@$arr[$args[$i]]) return false;
	}
	return true;
}

function is_int($arr, $str) {
	if(@$arr{$str}) $arr{$str} = 0;
	return $arr{$str};
}

function is_str($arr, $str) {
	if(@$arr{$str}) $arr{$str} = "None";
	return ucwords(strtolower($arr{$str}));
}

}

$lib = new lib;

/*function az91($a, $b) {
	global $lib;
	$a = eval("return @\$a{$lib->get['order']};");
	$b = eval("return @\$b{$lib->get['order']};");
	if ( $a == $b ) return 0;
	return ( ( is_numeric( $a ) || is_numeric( $b ) ) && $a < $b || !( is_numeric( $a ) || is_numeric( $b ) ) && $a > $b ) ? 1 : -1;
}

function za19($a, $b) {
	global $lib;
	$a = eval("return @\$a{$lib->get['order']};");
	$b = eval("return @\$b{$lib->get['order']};");
	if ($a == $b) return 0;
	return ( ( is_numeric( $a ) || is_numeric( $b ) ) && $a < $b || !( is_numeric( $a ) || is_numeric( $b ) ) && $a > $b ) ? -1 : 1;
}*/

function az91($a, $b) {
	if ( $a == $b ) return 0;
	return ( ( is_numeric( $a ) || is_numeric( $b ) ) && $a < $b || !( is_numeric( $a ) || is_numeric( $b ) ) && $a > $b ) ? 1 : -1;
}

function za19($a, $b) {
	if ($a == $b) return 0;
	return ( ( is_numeric( $a ) || is_numeric( $b ) ) && $a < $b || !( is_numeric( $a ) || is_numeric( $b ) ) && $a > $b ) ? -1 : 1;
}

?>