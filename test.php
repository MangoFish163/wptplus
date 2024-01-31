<?php
/*
 * @wordpress-plugin
 * Plugin Name:         Autolode
 * Description:         在线更新功能测试
 * Version:             0.0.4
 * Author:              Taotens
*/

if (!defined('AUTOLODE_DIR')) {
    define('AUTOLODE_DIR', plugin_dir_path(__FILE__));

    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    define('AUTOLODE_DATA', get_plugin_data(__FILE__));
    define('AUTOLODE_DI_NAME', basename(AUTOLODE_DIR));
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
require_once(plugin_dir_path(__FILE__) . '/upload.php');

/**
 * 加载自动检查更新程序
 */
if (is_admin()) new UpdateChecker(plugin_basename(__FILE__));
