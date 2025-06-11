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

// 加载插件语言包，支持国际化翻译
function rbtc_load_textdomain() {
    load_plugin_textdomain(
        'random-text-color',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
}
add_action('plugins_loaded', 'rbtc_load_textdomain');

// 后台设置页面
add_action('admin_menu', function() {
    add_options_page(__('Random Text Color 设置', 'random-text-color'), __('文字随机色设置', 'random-text-color'), 'manage_options', 'rbtc-settings', 'rbtc_render_settings_page');
});

// 数据净化回调函数定义

// Checkbox 净化，返回 1 或 0
function rbtc_sanitize_checkbox($input) {
    return $input === '1' ? 1 : 0;
}

// 渐变色字符串净化，允许以英文逗号分隔的 #RRGGBB 或 #RGB 颜色，过滤非法内容
function rbtc_sanitize_gradient_colors($input) {
    $colors = explode(',', $input);
    $valid_colors = [];
    foreach ($colors as $color) {
        $color = trim($color);
        // 简单匹配 #RGB 或 #RRGGBB 格式
        if (preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $color)) {
            $valid_colors[] = strtoupper($color);
        }
    }
    // 最少一个有效颜色，最多10个
    if (count($valid_colors) === 0) {
        // 默认渐变色
        return '#FF0000,#FF9900,#33CC33';
    }
    return implode(',', array_slice($valid_colors, 0, 10));
}

// 最大段落数净化，确保是 1~20 之间的整数
function rbtc_sanitize_max_paragraphs($input) {
    $num = intval($input);
    if ($num < 1) {
        $num = 1;
    } elseif ($num > 20) {
        $num = 20;
    }
    return $num;
}

// 注册设置项，并绑定对应的净化回调
add_action('admin_init', function() {
    register_setting('rbtc_settings_group', 'rbtc_enable_plugin', ['sanitize_callback' => 'rbtc_sanitize_checkbox']);
    register_setting('rbtc_settings_group', 'rbtc_enable_bold_color', ['sanitize_callback' => 'rbtc_sanitize_checkbox']);
    register_setting('rbtc_settings_group', 'rbtc_enable_heading_gradient', ['sanitize_callback' => 'rbtc_sanitize_checkbox']);
    register_setting('rbtc_settings_group', 'rbtc_enable_paragraph_color', ['sanitize_callback' => 'rbtc_sanitize_checkbox']);
    register_setting('rbtc_settings_group', 'rbtc_enable_dark_mode_style', ['sanitize_callback' => 'rbtc_sanitize_checkbox']);
    register_setting('rbtc_settings_group', 'rbtc_custom_gradient_colors', ['sanitize_callback' => 'rbtc_sanitize_gradient_colors']);
    register_setting('rbtc_settings_group', 'rbtc_max_colored_paragraphs', ['sanitize_callback' => 'rbtc_sanitize_max_paragraphs']);
});

// 渲染设置页面
function rbtc_render_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die( esc_html__( '你无权限访问该页面。', 'random-text-color' ) );
    }
    ?>
    <style>
    /* 基础样式 */
    .rbtc-wrap h1 {
        margin-bottom: 24px;
        color: #222;
        font-weight: 700;
    }
    .rbtc-wrap hr {
        margin: 40px 0;
        border: none;
        border-top: 1px solid #ddd;
    }
    .rbtc-wrap h2 {
        margin-top: 40px;
        margin-bottom: 16px;
        font-weight: 600;
        color: #333;
    }
    .rbtc-wrap p, .rbtc-wrap ul {
        font-size: 14px;
        color: #444;
        line-height: 1.6;
    }
    .rbtc-wrap ul {
        padding-left: 20px;
    }

    /* Pro 功能卡片容器 */
    .rbtc-pro-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 20px;
    }

    /* 单个卡片 */
    .rbtc-pro-card {
        flex: 1 1 280px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(34, 113, 177, 0.15);
        padding: 20px;
        color: #222;
        transition: box-shadow 0.3s ease;
        cursor: default;
    }
    .rbtc-pro-card:hover {
        box-shadow: 0 12px 30px rgba(34, 113, 177, 0.3);
    }
    .rbtc-pro-card h3 {
        margin-top: 0;
        margin-bottom: 12px;
        color: #2271b1;
        font-weight: 700;
        font-size: 18px;
    }
    .rbtc-pro-card p {
        font-size: 14px;
        color: #555;
    }
    .rbtc-pro-card .icon {
        font-size: 32px;
        margin-bottom: 12px;
        color: #2271b1;
    }

    /* 按钮 */
    .rbtc-pro-upgrade-btn {
        display: inline-block;
        margin-top: 24px;
        padding: 10px 28px;
        background: #2271b1;
        color: white;
        font-weight: 600;
        text-decoration: none;
        border-radius: 30px;
        box-shadow: 0 6px 12px rgba(34,113,177,0.4);
        transition: background 0.3s ease;
    }
    .rbtc-pro-upgrade-btn:hover {
        background: #135e96;
        box-shadow: 0 8px 18px rgba(19,94,150,0.6);
    }
    </style>

    <div class="wrap rbtc-wrap">
        <h1><?php esc_html_e('Random Text Color 插件设置', 'random-text-color'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('rbtc_settings_group'); ?>
            <table class="form-table">
                <tr><th scope="row"><?php esc_html_e('启用插件功能', 'random-text-color'); ?></th><td><input type="checkbox" name="rbtc_enable_plugin" value="1" <?php checked(1, get_option('rbtc_enable_plugin', 0)); ?> /><?php esc_html_e('启用插件整体功能', 'random-text-color'); ?></td></tr>
                <tr><th scope="row"><?php esc_html_e('加粗文字随机颜色', 'random-text-color'); ?></th><td><input type="checkbox" name="rbtc_enable_bold_color" value="1" <?php checked(1, get_option('rbtc_enable_bold_color', 0)); ?> /> <?php esc_html_e('启用', 'random-text-color'); ?></td></tr>
                <tr><th scope="row"><?php esc_html_e('小标题彩虹渐变', 'random-text-color'); ?></th><td><input type="checkbox" name="rbtc_enable_heading_gradient" value="1" <?php checked(1, get_option('rbtc_enable_heading_gradient', 0)); ?> /> <?php esc_html_e('启用', 'random-text-color'); ?></td></tr>
                <tr><th scope="row"><?php esc_html_e('段落随机颜色', 'random-text-color'); ?></th><td><input type="checkbox" name="rbtc_enable_paragraph_color" value="1" <?php checked(1, get_option('rbtc_enable_paragraph_color', 0)); ?> /> <?php esc_html_e('启用', 'random-text-color'); ?></td></tr>
                <tr><th scope="row"><?php esc_html_e('启用暗黑模式配色', 'random-text-color'); ?></th><td><input type="checkbox" name="rbtc_enable_dark_mode_style" value="1" <?php checked(1, get_option('rbtc_enable_dark_mode_style', 0)); ?> /> <?php esc_html_e('启用', 'random-text-color'); ?></td></tr>
                <tr><th scope="row"><?php esc_html_e('自定义渐变色（英文逗号分隔）', 'random-text-color'); ?></th>
                    <td><input type="text" name="rbtc_custom_gradient_colors" value="<?php echo esc_attr(get_option('rbtc_custom_gradient_colors', '#FF0000,#FF9900,#33CC33')); ?>" size="70" /></td>
                </tr>
                <tr><th scope="row"><?php esc_html_e('最多着色段落数', 'random-text-color'); ?></th>
                    <td><input type="number" name="rbtc_max_colored_paragraphs" value="<?php echo esc_attr(get_option('rbtc_max_colored_paragraphs', 5)); ?>" min="1" max="20" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <hr>

        <h2><?php esc_html_e('开发者信息', 'random-text-color'); ?></h2>
        <?php /* translators: %s 是官网链接 */ ?>
		<p><?php printf(wp_kses(__('插件由 <a href="%s" target="_blank" rel="noopener">码铃薯</a> 开发维护，欢迎访问官网获取更多支持。', 'random-text-color'), ['a' => ['href' => [], 'target' => [], 'rel' => []]]), esc_url('https://www.tudoucode.cn/')); ?></p>
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
        <a href="https://www.tudoucode.cn/" target="_blank" class="rbtc-pro-upgrade-btn"><?php esc_html_e('立即升级 Pro 版本', 'random-text-color'); ?></a>
    </div>
    <?php
}

// 插件前端输出逻辑：在页面底部插入样式和脚本
add_action('wp_footer', function() {
    // 只在单篇文章页启用，且 AMP 页面不执行
    if (!is_single() || (function_exists('is_amp_endpoint') && is_amp_endpoint())) return;

    // 如果插件未启用则不输出任何内容
    if (!get_option('rbtc_enable_plugin')) return;

    // 获取插件设置项
    $enable_bold = get_option('rbtc_enable_bold_color'); // 是否启用粗体变色
    $enable_headings = get_option('rbtc_enable_heading_gradient'); // 是否启用标题渐变色
    $enable_para = get_option('rbtc_enable_paragraph_color'); // 是否启用段落文字随机色
    $enable_dark = get_option('rbtc_enable_dark_mode_style'); // 是否启用暗色模式优化样式

    // 获取自定义渐变颜色设置，默认提供三个基础颜色
    $custom_colors = get_option('rbtc_custom_gradient_colors', '#FF0000,#FF9900,#33CC33');

    // 分解颜色为数组，并限制最大颜色数量为10个
    $gradient_array = explode(',', $custom_colors);
    $gradient_array = array_map('trim', $gradient_array);
    $gradient_array = array_filter($gradient_array, function($color) {
        return preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $color);
    });
    $gradient_array = array_slice($gradient_array, 0, 10);

    // 转为 JS 格式的颜色字符串数组
    $gradient_colors_js = json_encode(array_values($gradient_array));

    // 设置最大着色段落数（默认最多处理 5 个段落）
    $max_para = intval(get_option('rbtc_max_colored_paragraphs', 5));
    if ($max_para < 1) $max_para = 1;
    if ($max_para > 20) $max_para = 20;

    // 输出样式代码（包括暗黑模式兼容 + 标题渐变样式）
    echo "<style>";
    if ($enable_dark) {
        // 暗黑模式下的兼容样式
        echo "@media (prefers-color-scheme: dark) {
            .entry-content, .post-content, .article-content, article { color: #e0e0e0 !important; }
            .rainbow-gradient-text { color: transparent !important; }
            p { color: #d0d0d0 !important; }
            strong, b { color: #ffcc00 !important; }
        }";
    }

    // 渐变文字基础样式定义
    echo ".rainbow-gradient-text {
        background-clip: text; -webkit-background-clip: text;
        -webkit-text-fill-color: transparent; text-fill-color: transparent;
        font-weight: bold; display: inline;
    }</style>";
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const content = document.querySelector('.entry-content, .post-content, .article-content, article');
        if (!content) return;

        const isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

        // 获取所有粗体文字、标题和段落
        const bolds = content.querySelectorAll('strong, b');
        const headings = content.querySelectorAll('h2, h3, h4, h5, h6');
        const paras = content.querySelectorAll('p');

        // 渐变颜色数组
		const gradientColors = <?php echo wp_json_encode($gradient_array); ?>;



        // 获取随机颜色函数，避免过亮或过暗
        function getRandomColor() {
            function rand(min = 0, max = 255) {
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }
            let r, g, b, bright;
            do {
                r = rand(); g = rand(); b = rand();
                bright = r + g + b;
                if (!isDarkMode && bright > 600) continue; // 普通模式不太亮
                if (isDarkMode && bright < 200) continue; // 暗模式不太暗
                break;
            } while (true);
            return `rgb(${r},${g},${b})`;
        }

        // === 渲染粗体文字 ===
        <?php if ($enable_bold): ?>
        bolds.forEach(el => {
            el.style.color = getRandomColor();
        });
        <?php endif; ?>

        // === 渲染标题为渐变色 ===
        <?php if ($enable_headings): ?>
        headings.forEach((el, i) => {
            // 使用当前索引和下一个颜色拼接渐变
            const c1 = gradientColors[i % gradientColors.length];
            const c2 = gradientColors[(i + 1) % gradientColors.length];
            el.style.backgroundImage = `linear-gradient(to right, ${c1}, ${c2})`;
            el.style.backgroundSize = '100% 100%';
            el.classList.add('rainbow-gradient-text');
        });
        <?php endif; ?>

        // === 渲染段落为随机颜色（最多处理 N 个段落）===
        <?php if ($enable_para): ?>
        let count = 0;
        let paraArray = Array.from(paras);

        // 洗牌随机顺序，避免总是前面几段着色
        for (let i = paraArray.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [paraArray[i], paraArray[j]] = [paraArray[j], paraArray[i]];
        }

		paraArray.forEach(p => {
            if (count >= <?php echo wp_json_encode(intval($max_para)); ?>) return;
            if (p.querySelector('strong, b')) return; // 有粗体跳过，避免冲突

            p.style.color = getRandomColor();
            count++;
        });
        <?php endif; ?>
    });
    </script>

    <?php
});

// 插件列表页的设置和升级链接
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_url = esc_url(admin_url('options-general.php?page=rbtc-settings'));
    $settings_link = '<a href="' . $settings_url . '">' . esc_html__('设置', 'random-text-color') . '</a>';
    $pro_url = esc_url('https://www.tudoucode.cn/');
    $pro_link = '<a href="' . $pro_url . '" target="_blank" style="color:#d54e21;">' . esc_html__('Pro版', 'random-text-color') . '</a>';
    array_unshift($links, $settings_link);
    $links[] = $pro_link;
    return $links;
});
