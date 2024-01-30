<?php
/*
 * @wordpress-plugin
 * Plugin Name:         Autolode
 * Description:         在线更新功能测试
 * Version:             0.0.3
 * Author:              Taotens
*/

if(!defined('AUTOLODE_DIR'))
{
	define( 'AUTOLODE_DIR', plugin_dir_path( __FILE__ ) );
}

function activate_Wp_Autolode_Plugin()
{
}
function deactivate_Wp_Autolode_Plugin()
{
}
try {
    register_activation_hook(__FILE__, 'activate_Wp_Autolode_Plugin');
    register_deactivation_hook(__FILE__, 'deactivate_Wp_Autolode_Plugin');
} catch (\Throwable $th) {
}
require_once(plugin_dir_path(__FILE__).'/upload.php');
/**
* Load the automatic check updater
*/
if (is_admin()) new UpdateChecker(plugin_basename(__FILE__));