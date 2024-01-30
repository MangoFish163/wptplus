<?php
class Your_Plugin {
    private $plugin_slug;

    public function __construct($plugin_slug) {
        $this->plugin_slug = $plugin_slug;

        add_action('admin_init', array($this, 'check_update'));
    }

    public function check_update() {
        // 获取当前插件版本号
        $current_version = get_option($this->plugin_slug . '_version');

        // 向 GitHub API 发送请求，检查是否有新版本
        $github_api_url = 'https://api.github.com/repos/MangoFish163/wptplus/releases/138856852';
        $personal_access_token = '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';
        $headers = array(
            'Authorization: Bearer ' . $personal_access_token,
            'Accept: application/vnd.github+json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0', // 替换为您自己的 User Agent 名称
        );
        $response = wp_remote_get($github_api_url, array('headers' => $headers));
        $body = wp_remote_retrieve_body($response);
        if ($body) {
            $release_info = json_decode($body, true);
            // 比较版本号
            echo '1111111111111111111111111';
            if ($release_info && version_compare($current_version, $release_info['tag_name'], '<')) {
                echo '22222222222222222222222222';
                // 有新版本可用，提供更新信息
                set_transient($this->plugin_slug . '_update', array(
                    'new_version'   => $release_info['tag_name'],
                    'package'       => $release_info['zipball_url'],
                    'url'           => $release_info['html_url'],
                    'tested'        => '',
                    'sections'      => array(
                        'description' => $release_info['body'],
                        'installation' => 'You can install this version using the built-in WordPress installer.',
                    ),
                ), 12 * HOUR_IN_SECONDS);
            }
        }
    }
}
