<?php
/*
 * @wordpress-plugin
 * Plugin Name:         Autolode
 * Description:         在线更新功能
 * Version:             0.0.5
 * Author:              Mango Fish
*/

if (!defined('AUTOLODE_DIR')) {
    define('AUTOLODE_DIR', plugin_dir_path(__FILE__));
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

/**
* 加载自动检查更新程序
*/
if (!defined('UPDATECHECKER_DATA')) {
    require_once(plugin_dir_path(__FILE__) . '/upload.php');
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    define('UPDATECHECKER_DATA', get_plugin_data(__FILE__));
    define('UPDATECHECKER_DIR_NAME', basename(plugin_dir_path(__FILE__)));
    define('UPDATECHECKER_DIR',plugin_dir_path(__FILE__));
    if (is_admin()) new UpdateChecker(plugin_basename(__FILE__));
}