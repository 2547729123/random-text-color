<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}
$settings = [
    'rbtc_enable_plugin',
    'rbtc_enable_bold_color',
    'rbtc_enable_heading_gradient',
    'rbtc_enable_paragraph_color',
    'rbtc_enable_dark_mode_style',
    'rbtc_custom_gradient_colors',
    'rbtc_max_colored_paragraphs'
];
foreach ($settings as $setting) {
    delete_option($setting);
}
