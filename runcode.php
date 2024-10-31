<?php
/*
 Plugin Name: RunCode by Soncy
 Plugin URI: http://www.eyike.com/html/y2009/wordpress-runcode-soncy.html
 Description: Run html,css,javascript code in a textarea,u can control the size of textarea.
 Version: 1.1.5
 Author: Soncy
 Author URI: http://www.eyike.com
 */
add_action('wp_head','runcode_run');

function runcode_make_random_str($length) //generate random id
{
	$possible = "0123456789_" . "abcdefghijklmnopqrstuvwxyz". "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$str = "";
	while (strlen($str) < $length) {
		$str .= substr($possible, (rand() % strlen($possible)), 1);
	}
	return($str);
}

function runcode_run() {
echo <<<END
<script type="text/javascript">
function runcode_open_new(element)
{
	var code = document.getElementById(element).value;
	var win = window.open("", "", "");
	win.opener = null;
	win.document.write(code);
	win.document.close();
}
function saveCode(obj,filename)
{
	if(!document.all){
END;

echo "alert(\"".__('Your browser does not support this method.','runcode')."\");";
echo <<<END
	return;
	}
    var winname = window.open("", "", "top=10000,left=10000");
    winname.document.open("text/html", "replace");
    winname.document.write(document.getElementById(obj).value);
    winname.document.execCommand("saveas", "", filename + ".htm");
    winname.close();
}
function runcode_copy(element)
{
	var codeobj = document.getElementById(element);
	var meintext = codeobj.value;
	try {
	 if (window.clipboardData)
	   {
	  
	   // the IE-manier
	   window.clipboardData.setData("Text", meintext);
	  
	   // waarschijnlijk niet de beste manier om Moz/NS te detecteren;
	   // het is mij echter onbekend vanaf welke versie dit precies werkt:
	   }
	   else if (window.netscape)
	   {
	  
	   // dit is belangrijk maar staat nergens duidelijk vermeld:
	   // you have to sign the code to enable this, or see notes below
	   netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
	  
	   // maak een interface naar het clipboard
	   var clip = Components.classes['@mozilla.org/widget/clipboard;1']
					 .createInstance(Components.interfaces.nsIClipboard);
	   if (!clip) return;
	  
	   // maak een transferable
	   var trans = Components.classes['@mozilla.org/widget/transferable;1']
					  .createInstance(Components.interfaces.nsITransferable);
	   if (!trans) return;
	  
	   // specificeer wat voor soort data we op willen halen; text in dit geval
	   trans.addDataFlavor('text/unicode');
	  
	   // om de data uit de transferable te halen hebben we 2 nieuwe objecten
	   // nodig om het in op te slaan
	   var str = new Object();
	   var len = new Object();
	  
	   var str = Components.classes["@mozilla.org/supports-string;1"]
					.createInstance(Components.interfaces.nsISupportsString);
	  
	   var copytext=meintext;
	  
	   str.data=copytext;
	  
	   trans.setTransferData("text/unicode",str,copytext.length*2);
	  
	   var clipid=Components.interfaces.nsIClipboard;
	  
	   if (!clip) return false;
	  
	   clip.setData(trans,null,clipid.kGlobalClipboard);
	  
	   }
	} catch (e) {
END;

echo "alert('" . __('Because of security policy reasons, this feature has been banned by your browser.Close this window, and press "Ctrl+C" to copy the code.', 'runcode') . "');";

echo <<<END
		codeobj.focus();
	}
	codeobj.select();
   return false;
}
</script>
END;
}

$RunCode = new RunCode();
add_filter('the_content', array(&$RunCode, 'part_one'), -500);
add_filter('the_content', array(&$RunCode, 'part_two'),  500);
add_action('admin_menu', array(&$RunCode, 'runcode_menu'));

unset($RunCode);

class RunCode
{
    // The blocks array that holds the block ID's and their real code blocks
    var $blocks = array();


	function RunCode() {
		load_plugin_textdomain('runcode', 'wp-content/plugins/runcode-by-soncy');
	}


    /****************************************************************************
     * part_one
     *    > Replace the code blocks with the block IDs
     ****************************************************************************/
	function part_one($content)
    {
		$run_button = __('Run', 'runcode');
		$copy_button = __('Copy', 'runcode');
		$save_button = __('Save As', 'runcode');
		$run_tips = __('Tips:You can change the code before run.', 'runcode');
		$str_pattern = "/(\<runcode(.*?)\<\/runcode\>)/is";
		$str_pattern_ot = "/(\[runcode(.*?)\[\/runcode\])/is";
		
		if (preg_match_all($str_pattern, $content, $matches)) {
			for ($i = 0; $i < count($matches[0]); $i++) {
				$width = get_option('runcode_width');
				$height = "auto";
				$fsize = get_option('runcode_size');
				if ('' == $width) {$width = 600;}
				if ('' == $fsize) {$fsize = 12;}	
				$code = htmlspecialchars($matches[2][$i]);
				$hcontent = $matches[1][$i];
				
				$sc = preg_match('/(\<runcode(.*?)\>)/is', $hcontent, $matcht);
				if ($sc) {
					$htitle = $matcht[1];
				}
				if (preg_match('/width="(\d*)"/i', $htitle, $match)) {
					$width = $match[1];
				}
				if (preg_match('/height="(\w*)"/i', $htitle, $matchc)) {
					$height = $matchc[1];
				}else{
					$height = "auto";
				}
				if (preg_match('/size="(\d*)"/i', $htitle, $matchf)) {
					$fsize = $matchf[1];
				}
				if(substr( $code, 0, 4 ) != "&gt;"){ 
					if (preg_match('/(\S(.*?)\&gt;)/', $code, $matchtx)) {
						$codex = $matchtx[1];
						$code = str_replace($codex,"",$code);
					}
				}else{
					$code = "|runcode|".$code;
					$codex = "|runcode|&gt;";
				}
				if($codex){
					$code = str_replace($codex,"",$code);
				}
				$num = runcode_make_random_str(6);
				$id = "runcode_$num";
				$blockID = "<p>++RunCode_BLOCK_$num++</p>";
				$innertext = $this -> creatHtml($code,$height,$width,$fsize,$run_button,$copy_button,$save_button,$run_tips,$id);
				$this->blocks[$blockID] = $innertext;
				$content = str_replace($matches[0][$i], $blockID, $content);
			}
		}
		
		if (preg_match_all($str_pattern_ot, $content, $matches)) {
			for ($i = 0; $i < count($matches[0]); $i++) {
				$width = get_option('runcode_width');
				$height = "auto";
				$fsize = get_option('runcode_size');
				if ('' == $width) {$width = 600;}
				if ('' == $fsize) {$fsize = 12;}	
				$code = htmlspecialchars($matches[2][$i]);
				$hcontent = $matches[1][$i];
				
				$sc = preg_match('/(\[runcode(.*?)\])/is', $hcontent, $matcht);
				if ($sc) {
					$htitle = $matcht[1];
				}
				if (preg_match('/width="(\d*)"/i', $htitle, $match)) {
					$width = $match[1];
				}
				if (preg_match('/height="(\w*)"/i', $htitle, $matchc)) {
					$height = $matchc[1];
				}else{
					$height = "auto";
				}
				if (preg_match('/size="(\d*)"/i', $htitle, $matchf)) {
					$fsize = $matchf[1];
				}
				if(substr( $code, 0, 1 ) != "]"){ 
					if (preg_match('/(\S(.*?)\])/', $code, $matchtx)) {
						$codex = $matchtx[1];
						$code = str_replace($codex,"",$code);
					}
				}else{
					$code = "|runcode|".$code;
					$codex = "|runcode|]";
				}
				if($codex){
					$code = str_replace($codex,"",$code);
				}
				$num = runcode_make_random_str(6);
				$id = "runcode_$num";
				$blockID = "<p>++RunCode_BLOCK_$num++</p>";
				$innertext = $this -> creatHtml($code,$height,$width,$fsize,$run_button,$copy_button,$save_button,$run_tips,$id);
				$this->blocks[$blockID] = $innertext;
				$content = str_replace($matches[0][$i], $blockID, $content);
			}
		}
		return $content;
	}

	
	function creatHtml($code,$height,$width,$fsize,$run_button,$copy_button,$save_button,$run_tips,$id){
		$innertext = "<div class=\"runcode\">" . "\n";
		$code = preg_replace("/(\s*?\r?\n\s*?)+/", "\n", $code);
		if($height == "auto"){
			$innertext .= "<p><textarea name=\"runcode\" style=\"overflow-y:visible;width:".$width."px;font-size:".$fsize."px\" class=\"runcode_text\" id=\"" . $id . "\">" . $code . "</textarea></p>" . "\n";
			$innertext .= "<script type=\"text/javascript\">function changeTsize(){document.getElementById(\"".$id."\").style.height = document.getElementById(\"".$id."\").scrollHeight + \"px\";}window.setTimeout(changeTsize,0);</script>";
		}else{
			$innertext .= "<p><textarea name=\"runcode\" style=\"height:".$height."px;width:".$width."px;font-size:".$fsize."px\" class=\"runcode_text\" id=\"" . $id . "\">" . $code . "</textarea></p>" . "\n";
		}
		$innertext .= "<p><input type=\"button\" value=\"" . $run_button . "\" class=\"runcode_button\" onclick=\"runcode_open_new('" . $id . "');\"/> ";
		$innertext .= "<input type=\"button\" value=\"" . $copy_button . "\" class=\"runcode_button\" onclick=\"runcode_copy('" . $id . "');\"/> ";
		$innertext .= "<input type=\"button\" value=\"" . $save_button . "\" class=\"runcode_button\" onclick=\"saveCode('" . $id . "','" . $id . "');\"/> ";
		$innertext .= $run_tips . "</p>" . "\n";
		$innertext .= "</div>";
		return $innertext;
	}
	
    /****************************************************************************
     * part_two
     *    > Replace the block ID's from part one with the actual code blocks
     ****************************************************************************/
    function part_two($content)
    {
        if (count($this->blocks)) {
            $content = str_replace(array_keys($this->blocks), array_values($this->blocks), $content);
            $this->blocks = array();
        }

        return $content;
    }
	function runcode_menu(){
		add_options_page('Runcode', 'Runcode', 8, __FILE__, array(&$this, 'runcode_option'));	
	}
	function runcode_option(){
		?>
		<div class="wrap">
			<h2>
				<?php _e('Runcode Options', 'runcode') ?>
			</h2>

		<form name="form1" method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Default Width:','runcode' ); ?></th>
				<td><input type="text" name="runcode_width" value="<?php echo get_option('runcode_width'); ?>" /><?php _e('px' ); ?></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Default Font Size:','runcode' ); ?></th>
				<td><input type="text" name="runcode_size" value="<?php echo get_option('runcode_size'); ?>" /><?php _e('px' ); ?></td>
			</tr>


		</table>

		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="runcode_width,runcode_size" />

		<p class="submit">
		<input type="submit" class="button-primary" name="Submit" value="<?php _e('Save Changes' ) ?>" />
		</p>

		</form>
		</div>
		<?php
	}
}
?>
