<?php
/*
 * Plugin Name: Random Text Color – 随机彩色文字
 * Plugin URI: https://github.com/2547729123/random-text-color
 * Description: 为加粗文字、段落、小标题添加彩色样式，支持深色模式、自定义渐变配置，所有模块可单独开关控制。
 * Version: 1.0
 * Author: 码铃薯
 * Author URI: https://www.tudoucode.cn
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: random-text-color
 * Domain Path: /languages/
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.0
 *
 * 国际化说明：
 * 本插件支持多语言，语言文件存放于 /languages/ 目录。
 * 请确保语言包文件 (.mo/.po) 已正确放置。
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 后台设置菜单
add_action('admin_menu', function() {
    add_options_page(
        __('Random Text Color 设置', 'random-text-color'),
        __('文字随机色设置', 'random-text-color'),
        'manage_options',
        'rbtc-settings',
        'rbtc_render_settings_page'
    );
});

// 数据净化回调
function rbtc_sanitize_checkbox($input) {
    return $input === '1' ? 1 : 0;
}

function rbtc_sanitize_gradient_colors($input) {
    $colors = explode(',', $input);
    $valid_colors = [];
    foreach ($colors as $color) {
        $color = trim($color);
        if (preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $color)) {
            $valid_colors[] = strtoupper($color);
        }
    }
    if (count($valid_colors) === 0) {
        return '#FF0000,#FF9900,#33CC33';
    }
    return implode(',', array_slice($valid_colors, 0, 10));
}

function rbtc_sanitize_max_paragraphs($input) {
    $num = intval($input);
    if ($num < 1) $num = 1;
    if ($num > 20) $num = 20;
    return $num;
}

// 注册设置
add_action('admin_init', function() {
    register_setting('rbtc_settings_group', 'rbtc_enable_plugin', ['sanitize_callback' => 'rbtc_sanitize_checkbox']);
    register_setting('rbtc_settings_group', 'rbtc_enable_bold_color', ['sanitize_callback' => 'rbtc_sanitize_checkbox']);
    register_setting('rbtc_settings_group', 'rbtc_enable_heading_gradient', ['sanitize_callback' => 'rbtc_sanitize_checkbox']);
    register_setting('rbtc_settings_group', 'rbtc_enable_paragraph_color', ['sanitize_callback' => 'rbtc_sanitize_checkbox']);
    register_setting('rbtc_settings_group', 'rbtc_enable_dark_mode_style', ['sanitize_callback' => 'rbtc_sanitize_checkbox']);
    register_setting('rbtc_settings_group', 'rbtc_custom_gradient_colors', ['sanitize_callback' => 'rbtc_sanitize_gradient_colors']);
    register_setting('rbtc_settings_group', 'rbtc_max_colored_paragraphs', ['sanitize_callback' => 'rbtc_sanitize_max_paragraphs']);
});

// 设置页面渲染
function rbtc_render_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die( esc_html__( '你无权限访问该页面。', 'random-text-color' ) );
    }
    ?>
    <div class="wrap rbtc-wrap">
        <h1><?php esc_html_e('Random Text Color 插件设置', 'random-text-color'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('rbtc_settings_group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('启用插件功能', 'random-text-color'); ?></th>
                    <td><input type="checkbox" name="rbtc_enable_plugin" value="1" <?php checked(1, get_option('rbtc_enable_plugin', 0)); ?> />
                    <?php esc_html_e('启用插件整体功能', 'random-text-color'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('加粗文字随机颜色', 'random-text-color'); ?></th>
                    <td><input type="checkbox" name="rbtc_enable_bold_color" value="1" <?php checked(1, get_option('rbtc_enable_bold_color', 0)); ?> /> <?php esc_html_e('启用', 'random-text-color'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('小标题彩虹渐变', 'random-text-color'); ?></th>
                    <td><input type="checkbox" name="rbtc_enable_heading_gradient" value="1" <?php checked(1, get_option('rbtc_enable_heading_gradient', 0)); ?> /> <?php esc_html_e('启用', 'random-text-color'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('段落随机颜色', 'random-text-color'); ?></th>
                    <td><input type="checkbox" name="rbtc_enable_paragraph_color" value="1" <?php checked(1, get_option('rbtc_enable_paragraph_color', 0)); ?> /> <?php esc_html_e('启用', 'random-text-color'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('启用暗黑模式配色', 'random-text-color'); ?></th>
                    <td><input type="checkbox" name="rbtc_enable_dark_mode_style" value="1" <?php checked(1, get_option('rbtc_enable_dark_mode_style', 0)); ?> /> <?php esc_html_e('启用', 'random-text-color'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('自定义渐变色（英文逗号分隔）', 'random-text-color'); ?></th>
                    <td><input type="text" name="rbtc_custom_gradient_colors" value="<?php echo esc_attr(get_option('rbtc_custom_gradient_colors', '#FF0000,#FF9900,#33CC33')); ?>" size="70" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('最多着色段落数', 'random-text-color'); ?></th>
                    <td><input type="number" name="rbtc_max_colored_paragraphs" value="<?php echo esc_attr(get_option('rbtc_max_colored_paragraphs', 5)); ?>" min="1" max="20" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <hr>

        <h2><?php esc_html_e('开发者信息', 'random-text-color'); ?></h2>
        <p><?php esc_html_e('插件由码铃薯开发维护，', 'random-text-color'); ?>
             <a href="https://www.tudoucode.cn/" target="_blank" rel="noopener">
             <?php esc_html_e('访问我的官方网站', 'random-text-color'); ?>
             </a>
        </p>
		
        <h2><?php esc_html_e('使用注意事项', 'random-text-color'); ?></h2>
        <ul>
            <li><?php esc_html_e('启用插件后请清理缓存，确保效果生效。', 'random-text-color'); ?></li>
            <li><?php esc_html_e('深色模式兼容需开启对应选项。', 'random-text-color'); ?></li>
            <li><?php esc_html_e('如遇问题，请先查看常见问题或联系我们。', 'random-text-color'); ?></li>
        </ul>

        <h2><?php esc_html_e('升级到 Pro 版本，享受更多高级功能', 'random-text-color'); ?></h2>
        <div class="rbtc-pro-cards">
            <div class="rbtc-pro-card">
                <div class="icon">🌈</div>
                <h3><?php esc_html_e('多彩高级渐变', 'random-text-color'); ?></h3>
                <p><?php esc_html_e('支持无限自定义渐变色，打造独一无二的彩虹文字效果。', 'random-text-color'); ?></p>
            </div>
            <div class="rbtc-pro-card">
                <div class="icon">⚡</div>
                <h3><?php esc_html_e('自动颜色优化', 'random-text-color'); ?></h3>
                <p><?php esc_html_e('智能调整文字颜色，确保在各种背景下都清晰可见。', 'random-text-color'); ?></p>
            </div>
            <div class="rbtc-pro-card">
                <div class="icon">🔧</div>
                <h3><?php esc_html_e('专业客户支持', 'random-text-color'); ?></h3>
                <p><?php esc_html_e('优先响应你的问题和需求，专属服务更高效。', 'random-text-color'); ?></p>
            </div>
            <div class="rbtc-pro-card">
                <div class="icon">🚀</div>
                <h3><?php esc_html_e('自动更新', 'random-text-color'); ?></h3>
                <p><?php esc_html_e('Pro 版本自动推送最新功能和安全补丁，无需手动操作。', 'random-text-color'); ?></p>
            </div>
        </div>
        <a href="https://www.tudoucode.cn/plugins/wp-plugins/random-text-color/" target="_blank" class="rbtc-pro-upgrade-btn"><?php esc_html_e('立即升级 Pro 版本', 'random-text-color'); ?></a>
    </div>
    <?php
}

// 前端加载 CSS 和 JS，并传递设置数据
add_action('wp_enqueue_scripts', function() {
    if (!is_single() || (function_exists('is_amp_endpoint') && is_amp_endpoint())) return;
    if (!get_option('rbtc_enable_plugin')) return;

    wp_enqueue_style('rbtc_style', plugins_url('assets/css/style.css', __FILE__), [], '1.0');
    wp_enqueue_script('rbtc_main_js', plugins_url('assets/js/main.js', __FILE__), [], '1.0', true);

    $enable_bold = get_option('rbtc_enable_bold_color', 0) ? true : false;
    $enable_headings = get_option('rbtc_enable_heading_gradient', 0) ? true : false;
    $enable_para = get_option('rbtc_enable_paragraph_color', 0) ? true : false;
    $max_para = intval(get_option('rbtc_max_colored_paragraphs', 5));
    $max_para = max(1, min(20, $max_para));
    $custom_colors = get_option('rbtc_custom_gradient_colors', '#FF0000,#FF9900,#33CC33');
    $gradient_array = explode(',', $custom_colors);
    $gradient_array = array_map('trim', $gradient_array);
    $gradient_array = array_filter($gradient_array, function($color) {
        return preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $color);
    });
    $gradient_array = array_slice($gradient_array, 0, 10);
    if (empty($gradient_array)) {
        $gradient_array = ['#FF0000', '#FF9900', '#33CC33'];
    }

    wp_localize_script('rbtc_main_js', 'rbtc_vars', [
        'enable_bold' => $enable_bold,
        'enable_headings' => $enable_headings,
        'enable_para' => $enable_para,
        'max_para' => $max_para,
        'gradientColors' => array_values($gradient_array),
    ]);
});

add_action('admin_enqueue_scripts', function($hook) {
    // 只在插件设置页面加载
    if ($hook !== 'settings_page_rbtc-settings') return;

    wp_enqueue_style('rbtc_admin_style', plugins_url('assets/css/admin.css', __FILE__), [], '1.0');
});

// 插件列表页的设置和升级链接
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_url = esc_url(admin_url('options-general.php?page=rbtc-settings'));
    $settings_link = '<a href="' . $settings_url . '">' . esc_html__('设置', 'random-text-color') . '</a>';
    $pro_url = esc_url('https://www.tudoucode.cn/plugins/wp-plugins/random-text-color/');
    $pro_link = '<a href="' . $pro_url . '" target="_blank" style="color:#d54e21;">' . esc_html__('Pro版', 'random-text-color') . '</a>';
    array_unshift($links, $settings_link);
    $links[] = $pro_link;
    return $links;
});
