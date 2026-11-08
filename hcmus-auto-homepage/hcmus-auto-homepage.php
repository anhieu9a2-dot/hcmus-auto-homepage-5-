<?php
/**
 * Plugin Name: HCMUS Auto Homepage
 * Description: Giao diện HCMUS Auto nhiều trang: trang chủ giới thiệu, dòng xe, dự toán chi phí, trang chi tiết xe chia nhiều mục, giỏ hàng, thanh toán, tài khoản, menu trượt và hiệu ứng chuyển cảnh.
 * Version: 2.2.0
 * Author: HCMUS Auto
 * Text Domain: hcmus-auto
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HCMUS_AUTO_VERSION', '2.2.0');
define('HCMUS_AUTO_URL', plugin_dir_url(__FILE__));
define('HCMUS_AUTO_PATH', plugin_dir_path(__FILE__));

foreach (array('settings', 'core', 'chrome', 'cost', 'cars', 'home', 'product', 'woo') as $hcmus_auto_file) {
    require_once HCMUS_AUTO_PATH . 'inc/' . $hcmus_auto_file . '.php';
}

register_activation_hook(__FILE__, 'hcmus_auto_ensure_pages');
