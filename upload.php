<?php
/*
* 负责检查和更新当前插件
* Author: Mango Fish
*/

if (!class_exists('UpdateChecker')) {
    class UpdateChecker
    {
        public $plugin_slug;
        public $version;
        public $cache_key;
        public $interval;
        public $updateNum = 0;
        public $plugin_entrance;
        public $timeout;
        static $responseBody;

        public function __construct($init = ['plugin_slug' => false, 'cache_key' => false, 'timeout' => 60])
        {
            // 如果不是管理员页面，什么也不做
            if (!is_admin()) return false;
            $bool = is_array($init);
            $this->plugin_slug = $bool ? $init['plugin_slug'] : $init;
            // 标识
            $this->cache_key = $bool ? $init['cache_key'] : $this->plugin_slug;
            $this->interval = $bool && isset($init['cache_key'])? $init['cache_key'] : false;
            // 缓存时长
            $this->timeout = $bool ? $init['timeout'] : 60;
            // 获取当前插件版本号
            $this->version = AUTOLODE_DATA['Version'];
            // 插件入口文件
            $this->plugin_entrance = $this->plugin_slug;
            if ($this->cache_key == false || get_transient($this->cache_key) === false) {
                add_filter('site_transient_update_plugins', array($this, 'check_for_plugin_update'));
                add_filter('upgrader_source_selection', array($this, 'custom_upgrade_directory'), 10, 3);
            }
        }

        public function check_for_plugin_update($transient)
        {
            if (empty($transient->checked) || !isset($transient->checked[$this->plugin_slug])) {
                return $transient;
            }
            if (self::$responseBody) {
                if (version_compare($transient->checked[$this->plugin_entrance], self::$responseBody->new_version, '<')) {
                    $transient->response[$this->plugin_slug] = self::$responseBody;
                };
                return $transient;
            }
            // 向 GitHub API 发送请求，检查是否有新版本 地址/token换成自己的就行
            $github_api_url = 'https://api.github.com/repos/MangoFish163/wptplus/releases/latest';
            $personal_access_token = json_decode(file_get_contents(WP_CONTENT_DIR . '/text-config.json'))->token;
            $Agents = array(
                'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.71 Safari/537.1 LBBROWSER',
                'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; QQDownload 732; .NET4.0C; .NET4.0E)',
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11',
                'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.133 Safari/534.16',
                'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
                'Mozilla/5.0 (Windows NT 5.1; U; en; rv:1.8.1) Gecko/20061208 Firefox/2.0.0 Opera 9.50'
            );
            $headers = array(
                'Authorization: Bearer ' . $personal_access_token,
                'Accept: application/vnd.github+json',
                'User-Agent: ' . $Agents[array_rand($Agents)],
            );
            $response = wp_remote_get($github_api_url, array('headers' => $headers));
            $body = wp_remote_retrieve_body($response);

            if ($body) {
                $release_info = json_decode($body, true);
                $latest_version = $release_info['tag_name'];
                // 检查最新版本与当前版本的比较 设置更新信息
                $obj = new stdClass();
                $obj->slug = $this->plugin_slug;
                $obj->new_version = $latest_version ?? 0;
                $obj->url = $release_info['html_url'];
                $obj->package = $release_info['zipball_url'];
                self::$responseBody = $obj;
                if (version_compare($transient->checked[$this->plugin_entrance], $latest_version, '<')) {
                    $transient->response[$obj->slug] = $obj;
                }
            }

            return $transient;
        }
        // 过滤器回调函数
        public function custom_upgrade_directory($source, $remote_source, $upgrader)
        {
            if (strpos($source, 'MangoFish163') !== false) {
                $target_directory = WP_CONTENT_DIR . "/upgrade/" . AUTOLODE_DI_NAME . "/";
                return $this->move_folder_contents($source, $target_directory) ? $target_directory : $source;
            }
            self::$responseBody = false;
            if($this->interval){
                set_transient( $this->cache_key, $this->version, $this->timeout );
            }
            return $source;
        }
        // 移动文件到指定目录,避免默认移动到官方默认去重目录
        public function move_folder_contents($source, $destination)
        {
            if (!file_exists($source)) {
                return false;
            }
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $items = glob($source . '/*');
            foreach ($items as $item) {
                $new_item = $destination . '/' . basename($item);
                rename($item, $new_item);
            }
            return true;
        }

        public function purge($upgrader, $options)
        {
            
        }
    }
}
