<?php
/*
Plugin Name: HelpDen Free Live Support Chat
Plugin URI: http://www.helpden.com/add-ons.php
Description: Allows you to add live chat support to your WordPress site in a few clicks.
Version: 1.0.1
Author: Manas Kanti dey
Author URI: http://www.helpden.com/
*/



register_activation_hook(__FILE__, 'helpden_activation');
register_deactivation_hook(__FILE__, 'helpden_deactivation');

    if (is_admin()) {
	add_action('admin_menu', 'helpden_admin_menu');
        add_action( 'admin_menu', 'helpden_api_settings' );
    }

function helpden_admin_menu() {
	add_options_page('Helpden', 'Helpden', 'administrator', __FILE__, 'helpden_options_page');
}

function helpden_options_page() {
	echo'
	<div class="wrap">
		<h2>HelpDen Options</h2>
        <p>To find your HelpDen id please login to your HelpDen Dashboard and then go to "Account Management"</p>
		<form method="post" action="options.php">';
			wp_nonce_field('update-options');
			 echo '
			<table class="form-table">
				<tr valign="top">';
				$settings = helpden_settings_list();
				foreach ($settings as $setting) {
					echo '<th scope="row">'.$setting['display'].'</th>
					<td>';
					if ($setting['type']=='selectbox') {
						$str = explode(",",$setting['option']);
						echo '<select name="'.$setting['name'].'">';
						for($i=0;$i<count($str);$i++)
						{	
							$selected="";
							if(get_option($setting['name'])==$str[$i])
								$selected='selected';
							echo "<option value='".$str[$i]."' ".$selected." >".$str[$i]."</option>";
						}
						echo '</select>';
					}
					else if ($setting['type']=='radio') {
						echo 'Yes <input type="'.$setting['type'].'" name="'.$setting['name'].'" value="1" ';
						if (get_option($setting['name'])==1) { echo 'checked="checked" />'; } else { echo ' />'; }
						echo 'No <input type="'.$setting['type'].'" name="'.$setting['name'].'" value="0" ';
						if (get_option($setting['name'])==0) { echo 'checked="checked" />'; } else { echo ' />'; }
					} else { echo '<input type="'.$setting['type'].'" name="'.$setting['name'].'" value="'.get_option($setting['name']).'" />'; }
					echo ' (<em>'.$setting['hint'].'</em>)</td></tr>';
				}
			
			echo '</table>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="';
			foreach ($settings as $setting) {
				echo $setting['name'].',';
			}
			echo '" /><p class="submit"><input type="submit" class="button-primary" value="Save Changes" /></p>
		</form>';
	echo '</div>';
}

function helpden_settings_list() {
	$settings = array(
		array(
			'display' => 'HelpDen ID',
			'name'    => 'helpden_id',
			'value'   => '',
			'type'    => 'textbox',
            'hint'    => 'Enter your HelpDen id'
		),
		array(
			'display' => 'Position',
			'name'    => 'helpden_position',
			'value'   => '',
			'option'  => 'topleft,topright,right,left,bottomright,bottomleft',
			'type'    => 'selectbox',
            'hint'    => 'Select Position of Bulb'
		),
		array(
			'display' => 'Show HelpDen Bulb',
			'name'    => 'helpden_show_code',
			'value'   => '0',
			'type'    => 'radio',
            'hint'    => 'Display status inicator (Bulb)'
		),
	);
	return $settings;
}


function helpden_api_settings() {
	$settings = helpden_settings_list();
	foreach ($settings as $setting) {
		register_setting($setting['name'], $setting['value']);
	}
}

function helpden_activation() {
	$settings = helpden_settings_list();
	foreach ($settings as $setting) {
		update_option($setting['name'], $setting['value']);
	}
}

function helpden_deactivation() {
	$settings = helpden_settings_list();
	foreach ($settings as $setting) {
		delete_option($setting['name']);
	}
}

add_filter( 'page_template', 'helpden_redirect_template' );
function helpden_redirect_template($template) {
	//$templates = array('helpden-redirect.php');
	//$template = locate_plugin_template($templates);
	return $template;
}

function helpden_settings_link($links) {
$settings_link = '<a href="options-general.php?page=wordpress-helpden/wp-helpden.php">Settings</a>';
array_unshift($links, $settings_link);
return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'helpden_settings_link' );


add_action('wp_footer', 'helpden_wp_footer');
function helpden_wp_footer() {
    $is_show = get_option('helpden_show_code');
	$helpden_code = get_option('helpden_code');
    if($is_show==1)
	{
		$buffer = '<script type="text/javascript" src="http://helpden.com/client/helpden.js"></script>
<style type="text/css" media="screen, projection">
  @import url(http://helpden.com/client/helpden.css);
</style>
<script type="text/javascript">
	Helpden.init({
	  dropboxID:   "'.get_option('helpden_id').'",
	  url:         "http://helpden.com/code.php",
	  tabID:       "support",
	  tabPosition: "'.get_option('helpden_position').'"
	});
</script>
<!-- end Dropbox -->';
		ob_start();
		eval('?>' . $buffer);
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}
}