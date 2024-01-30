<?php
/*
* 这个简单的类只负责检查和更新当前插件
* Author: Mango Fish
*/

if (!class_exists('UpdateChecker')) {
    class UpdateChecker
    {
        public $plugin_slug;
        public $version;
        public $cache_key;
        public $cache_allowed;
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
            // 标识(远程获取更新信息，本地缓存使用)
            $this->cache_key = $bool ? $init['cache_key'] : $this->plugin_slug;
            // 缓存超时时长
            $this->timeout = $bool ? $init['timeout'] : 60;
            // 启用缓存
            $this->cache_allowed = true;
            // 获取当前插件版本号
            $this->version = get_option($this->plugin_slug . '_version');

            // 插件入口文件
            $plugin_entrance = $this->plugin_slug . '/' . $this->plugin_slug . '.php';
            $this->plugin_entrance = $plugin_entrance ? $plugin_entrance : plugin_basename(__FILE__);


            file_put_contents(AUTOLODE_DIR . '/look.json', 1, FILE_APPEND);
            add_action('admin_menu', array($this, 'check_for_update'));
            add_filter('site_transient_update_plugins', array($this, 'check_for_plugin_update'));
            // add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_plugin_update'));
        }
        public function check_for_update() {
            // 手动触发插件更新检查
            file_put_contents(AUTOLODE_DIR . '/look.json', 2, FILE_APPEND);
            do_action('wp_update_plugins');
        }

        public function check_for_plugin_update($transient)
        {
            file_put_contents(AUTOLODE_DIR . '/look.json', json_encode(isset($transient->checked[$this->plugin_slug])).PHP_EOL, FILE_APPEND);
            if (empty($transient->checked) || !isset($transient->checked[$this->plugin_slug])) {
                return $transient;
            }
            if (self::$responseBody) {
                $transient->response[$this->plugin_slug] = self::$responseBody;
                return $transient;
            }
            // 向 GitHub API 发送请求，检查是否有新版本
            $github_api_url = 'https://api.github.com/repos/MangoFish163/wptplus/releases/138856852';
            $personal_access_token = json_decode(file_get_contents(AUTOLODE_DIR.'../../text-config.json'))->token;
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
                'User-Agent: '.$Agents[array_rand($Agents)], // 替换为您自己的 User Agent 名称
            );
            $response = wp_remote_get($github_api_url, array('headers' => $headers));
            $body = wp_remote_retrieve_body($response);

            if ($body) {
                $release_info = json_decode($body, true);
                $latest_version = $release_info['tag_name'];
                // 检查最新版本与当前版本的比较
                if (version_compare($transient->checked[$this->plugin_entrance], $latest_version, '<')) {
                    // 有新版本可用，设置更新信息
                    $obj = new stdClass();
                    $obj->slug = $this->plugin_slug;
                    $obj->new_version = $latest_version;
                    $obj->url = $release_info['html_url'];
                    $obj->package = $release_info['zipball_url'];
                    $transient->response[$obj->slug] = $obj;
                    self::$responseBody = $obj;
                }
            }
            file_put_contents(AUTOLODE_DIR . '/look.json', json_encode($transient->response), FILE_APPEND);
            return $transient;
        }

        public function purge($upgrader, $options)
        {

            if (
                $this->cache_allowed
                && 'update' === $options['action']
                && 'plugin' === $options['type']
            ) {
                // 当安装新插件版本时，清理缓存
                delete_transient($this->cache_key);
            }
        }
    }
}
// {
//     "url": "https:\/\/api.github.com\/repos\/MangoFish163\/wptplus\/releases\/138856852",
//     "assets_url": "https:\/\/api.github.com\/repos\/MangoFish163\/wptplus\/releases\/138856852\/assets",
//     "upload_url": "https:\/\/uploads.github.com\/repos\/MangoFish163\/wptplus\/releases\/138856852\/assets{?name,label}",
//     "html_url": "https:\/\/github.com\/MangoFish163\/wptplus\/releases\/tag\/0.1.0%E7%89%88%E6%9C%AC",
//     "id": 138856852,
//     "author": {
//         "login": "MangoFish163",
//         "id": 118880779,
//         "node_id": "U_kgDOBxX6Cw",
//         "avatar_url": "https:\/\/avatars.githubusercontent.com\/u\/118880779?v=4",
//         "gravatar_id": "",
//         "url": "https:\/\/api.github.com\/users\/MangoFish163",
//         "html_url": "https:\/\/github.com\/MangoFish163",
//         "followers_url": "https:\/\/api.github.com\/users\/MangoFish163\/followers",
//         "following_url": "https:\/\/api.github.com\/users\/MangoFish163\/following{\/other_user}",
//         "gists_url": "https:\/\/api.github.com\/users\/MangoFish163\/gists{\/gist_id}",
//         "starred_url": "https:\/\/api.github.com\/users\/MangoFish163\/starred{\/owner}{\/repo}",
//         "subscriptions_url": "https:\/\/api.github.com\/users\/MangoFish163\/subscriptions",
//         "organizations_url": "https:\/\/api.github.com\/users\/MangoFish163\/orgs",
//         "repos_url": "https:\/\/api.github.com\/users\/MangoFish163\/repos",
//         "events_url": "https:\/\/api.github.com\/users\/MangoFish163\/events{\/privacy}",
//         "received_events_url": "https:\/\/api.github.com\/users\/MangoFish163\/received_events",
//         "type": "User",
//         "site_admin": false
//     },
//     "node_id": "RE_kwDOLK3mNs4IRsmU",
//     "tag_name": "0.1.0\u7248\u672c",
//     "target_commitish": "main",
//     "name": "wptplus",
//     "draft": false,
//     "prerelease": true,
//     "created_at": "2024-01-29T05:14:55Z",
//     "published_at": "2024-01-29T05:58:56Z",
//     "assets": [],
//     "tarball_url": "https:\/\/api.github.com\/repos\/MangoFish163\/wptplus\/tarball\/0.1.0\u7248\u672c",
//     "zipball_url": "https:\/\/api.github.com\/repos\/MangoFish163\/wptplus\/zipball\/0.1.0\u7248\u672c",
//     "body": "wptplus \u7ebf\u4e0a\u66f4\u65b0\u529f\u80fd\r\n![VRgood](https:\/\/github.com\/MangoFish163\/wptplus\/assets\/118880779\/082826fe-646d-4ed3-93ac-19cbe4032359)\r\n"
// }

// "response": {
//     "airwallex-online-payments-gateway\/airwallex-online-payments-gateway.php": {
//         "id": "w.org\/plugins\/airwallex-online-payments-gateway",
//         "slug": "airwallex-online-payments-gateway",
//         "plugin": "airwallex-online-payments-gateway\/airwallex-online-payments-gateway.php",
//         "new_version": "1.5.1",
//         "url": "https:\/\/wordpress.org\/plugins\/airwallex-online-payments-gateway\/",
//         "package": "https:\/\/downloads.wordpress.org\/plugin\/airwallex-online-payments-gateway.1.5.1.zip",
//         "icons": {
//             "2x": "https:\/\/ps.w.org\/airwallex-online-payments-gateway\/assets\/icon-256\u00d7256.png?rev=2514923",
//             "1x": "https:\/\/ps.w.org\/airwallex-online-payments-gateway\/assets\/icon-128\u00d7128.png?rev=2514923"
//         },
//         "banners": {
//             "2x": "https:\/\/ps.w.org\/airwallex-online-payments-gateway\/assets\/banner-1544\u00d7500.jpg?rev=2514923",
//             "1x": "https:\/\/ps.w.org\/airwallex-online-payments-gateway\/assets\/banner-772\u00d7250.jpg?rev=2514923"
//         },
//         "banners_rtl": [],
//         "requires": "4.5",
//         "tested": "6.4.2",
//         "requires_php": false
//     },
//     "astra-sites\/astra-sites.php": {
//         "id": "w.org\/plugins\/astra-sites",
//         "slug": "astra-sites",
//         "plugin": "astra-sites\/astra-sites.php",
//         "new_version": "4.0.7",
//         "url": "https:\/\/wordpress.org\/plugins\/astra-sites\/",
//         "package": "https:\/\/downloads.wordpress.org\/plugin\/astra-sites.4.0.7.zip",
//         "icons": {
//             "1x": "https:\/\/ps.w.org\/astra-sites\/assets\/icon.svg?rev=2772059",
//             "svg": "https:\/\/ps.w.org\/astra-sites\/assets\/icon.svg?rev=2772059"
//         },
//         "banners": {
//             "2x": "https:\/\/ps.w.org\/astra-sites\/assets\/banner-1544x500.jpg?rev=2652631",
//             "1x": "https:\/\/ps.w.org\/astra-sites\/assets\/banner-772x250.jpg?rev=2652631"
//         },
//         "banners_rtl": [],
//         "requires": "4.4",
//         "tested": "6.4.2",
//         "requires_php": "7.4"
//     },
//     "woocommerce\/woocommerce.php": {
//         "id": "w.org\/plugins\/woocommerce",
//         "slug": "woocommerce",
//         "plugin": "woocommerce\/woocommerce.php",
//         "new_version": "8.5.2",
//         "url": "https:\/\/wordpress.org\/plugins\/woocommerce\/",
//         "package": "https:\/\/downloads.wordpress.org\/plugin\/woocommerce.8.5.2.zip",
//         "icons": {
//             "2x": "https:\/\/ps.w.org\/woocommerce\/assets\/icon-256x256.gif?rev=2869506",
//             "1x": "https:\/\/ps.w.org\/woocommerce\/assets\/icon-128x128.gif?rev=2869506"
//         },
//         "banners": {
//             "2x": "https:\/\/ps.w.org\/woocommerce\/assets\/banner-1544x500.png?rev=3000842",
//             "1x": "https:\/\/ps.w.org\/woocommerce\/assets\/banner-772x250.png?rev=3000842"
//         },
//         "banners_rtl": [],
//         "requires": "6.3",
//         "tested": "6.4.2",
//         "requires_php": "7.4"
//     }
// },