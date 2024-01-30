<?php
/*
 * @wordpress-plugin
 * Plugin Name:         Autolode
 * Description:         自动共享功能测试
 * Version:             0.0.2
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


// add_action('init', 'add_plugin_update_filter');
// function add_plugin_update_filter() {
//     add_filter('pre_set_site_transient_update_plugins', 'check_for_plugin_update');
// }
// function check_for_plugin_update($transient) {
//     // 你的检查更新逻辑
//     return $transient;
// }
