<?php
if (!defined('ABSPATH')) {
    exit;
}

/* ---------- CSS / JS ---------- */
function hcmus_auto_assets() {
    wp_enqueue_style('hcmus-auto-home', HCMUS_AUTO_URL . 'assets/home.css', array(), HCMUS_AUTO_VERSION);
    wp_enqueue_script('hcmus-auto-home', HCMUS_AUTO_URL . 'assets/home.js', array(), HCMUS_AUTO_VERSION, true);
}
add_action('wp_enqueue_scripts', 'hcmus_auto_assets');

/* ---------- Hàm hỗ trợ ---------- */
function hcmus_auto_upper($text) {
    return function_exists('mb_strtoupper') ? mb_strtoupper($text, 'UTF-8') : strtoupper($text);
}

function hcmus_auto_wc_url($page) {
    if (function_exists('wc_get_page_permalink')) {
        $url = wc_get_page_permalink($page);
        if ($url) {
            return $url;
        }
    }
    return home_url('/');
}

function hcmus_auto_price_segments() {
    return array(
        '0-500000000'           => 'Dưới 500 triệu',
        '500000000-1000000000'  => '500 triệu – 1 tỷ',
        '1000000000-2000000000' => '1 – 2 tỷ',
        '2000000000-5000000000' => '2 – 5 tỷ',
        '5000000000-'           => 'Trên 5 tỷ',
    );
}

/* ---------- Hãng xe (taxonomy) ---------- */
function hcmus_auto_register_brand() {
    if (taxonomy_exists('product_brand') || !post_type_exists('product')) {
        return;
    }
    register_taxonomy('hcmus_brand', array('product'), array(
        'labels' => array(
            'name'          => 'Hãng xe',
            'singular_name' => 'Hãng xe',
            'menu_name'     => 'Hãng xe',
            'add_new_item'  => 'Thêm hãng xe',
            'search_items'  => 'Tìm hãng xe',
        ),
        'public'            => false,
        'publicly_queryable' => false,
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'query_var'         => false,
        'rewrite'           => false,
    ));
}
add_action('init', 'hcmus_auto_register_brand', 20);

function hcmus_auto_brand_taxonomy() {
    return taxonomy_exists('product_brand') ? 'product_brand' : 'hcmus_brand';
}

function hcmus_auto_brand_terms() {
    $tax = hcmus_auto_brand_taxonomy();
    if (!taxonomy_exists($tax)) {
        return array();
    }
    $terms = get_terms(array('taxonomy' => $tax, 'hide_empty' => false, 'orderby' => 'name'));
    return is_wp_error($terms) ? array() : $terms;
}

function hcmus_auto_get_categories($number = 0) {
    if (!taxonomy_exists('product_cat')) {
        return array();
    }
    $args = array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
        'exclude'    => array((int) get_option('default_product_cat')),
    );
    if ($number > 0) {
        $args['number'] = $number;
    }
    $terms = get_terms($args);
    return is_wp_error($terms) ? array() : $terms;
}

/* ---------- Dữ liệu xe ---------- */
function hcmus_auto_catalog_args() {
    $args = array('visibility' => 'catalog');
    if (get_option('woocommerce_hide_out_of_stock_items') === 'yes') {
        $args['stock_status'] = 'instock';
    }
    return $args;
}

function hcmus_auto_get_cars($limit = 50) {
    static $cache = array();
    if (isset($cache[$limit])) {
        return $cache[$limit];
    }
    $cars = array();
    if (function_exists('wc_get_products')) {
        $cars = wc_get_products(array_merge(array(
            'status'  => 'publish',
            'limit'   => $limit,
            'orderby' => 'date',
            'order'   => 'DESC',
        ), hcmus_auto_catalog_args()));
    }
    $cache[$limit] = $cars;
    return $cars;
}

function hcmus_auto_plain_price($product) {
    return trim(html_entity_decode(wp_strip_all_tags($product->get_price_html()), ENT_QUOTES, 'UTF-8'));
}

function hcmus_auto_car_image_url($product) {
    $image_id = $product->get_image_id();
    if ($image_id) {
        $url = wp_get_attachment_image_url($image_id, 'large');
        if ($url) {
            return $url;
        }
    }
    return function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src('large') : '';
}

/* ---------- Các trang do plugin tạo ---------- */
function hcmus_auto_ensure_pages() {
    $defs = array(
        'cars' => array('title' => 'Dòng xe', 'slug' => 'dong-xe', 'shortcode' => 'hcmus_auto_cars'),
        'cost' => array('title' => 'Dự toán chi phí', 'slug' => 'du-toan-chi-phi', 'shortcode' => 'hcmus_auto_cost'),
    );
    $ids = get_option('hcmus_auto_pages', array());
    if (!is_array($ids)) {
        $ids = array();
    }
    foreach ($defs as $key => $def) {
        $id = isset($ids[$key]) ? (int) $ids[$key] : 0;
        if ($id && get_post_status($id) === 'publish') {
            continue;
        }
        $existing = get_page_by_path($def['slug']);
        if ($existing && $existing->post_status === 'publish' && has_shortcode($existing->post_content, $def['shortcode'])) {
            $ids[$key] = $existing->ID;
            continue;
        }
        $new_id = wp_insert_post(array(
            'post_title'   => $def['title'],
            'post_name'    => $def['slug'],
            'post_content' => '[' . $def['shortcode'] . ']',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ));
        if ($new_id && !is_wp_error($new_id)) {
            $ids[$key] = (int) $new_id;
        }
    }
    update_option('hcmus_auto_pages', $ids);
}

function hcmus_auto_maybe_ensure_pages() {
    if (get_option('hcmus_auto_pages_ver') === HCMUS_AUTO_VERSION) {
        return;
    }
    hcmus_auto_ensure_pages();
    update_option('hcmus_auto_pages_ver', HCMUS_AUTO_VERSION);
}
add_action('admin_init', 'hcmus_auto_maybe_ensure_pages');

function hcmus_auto_page_id($key) {
    $ids = get_option('hcmus_auto_pages', array());
    return (is_array($ids) && !empty($ids[$key])) ? (int) $ids[$key] : 0;
}

function hcmus_auto_page_url($key) {
    $id = hcmus_auto_page_id($key);
    if ($id) {
        $url = get_permalink($id);
        if ($url) {
            return $url;
        }
    }
    return home_url('/');
}

function hcmus_auto_cars_url($args = array()) {
    $url = hcmus_auto_page_url('cars');
    return $args ? add_query_arg($args, $url) : $url;
}

function hcmus_auto_cost_url($product_id = 0) {
    $url = hcmus_auto_page_url('cost');
    return $product_id ? add_query_arg('xe', (int) $product_id, $url) : $url;
}

/* ---------- Xác định trang nào dùng giao diện HCMUS ---------- */
function hcmus_auto_page_shortcodes() {
    static $found = null;
    if ($found !== null) {
        return $found;
    }
    $found = array();
    if (!is_singular()) {
        return $found;
    }
    $post = get_queried_object();
    if (!($post instanceof WP_Post)) {
        return $found;
    }
    $el = get_post_meta($post->ID, '_elementor_data', true);
    if (!is_string($el)) {
        $el = '';
    }
    foreach (array('hcmus_auto_home', 'hcmus_auto_cars', 'hcmus_auto_cost') as $tag) {
        if (has_shortcode($post->post_content, $tag) || ($el !== '' && strpos($el, $tag) !== false)) {
            $found[] = $tag;
        }
    }
    return $found;
}

function hcmus_auto_view() {
    static $view = null;
    if ($view !== null) {
        return $view;
    }
    $view = '';
    if (is_admin() || is_customize_preview() || isset($_GET['elementor-preview'])) {
        return $view;
    }
    if (is_singular('product')) {
        $view = 'product';
    } elseif (hcmus_auto_page_shortcodes()) {
        $view = 'page';
    } elseif (hcmus_auto_woo_context() !== '') {
        $view = 'woo';
    } elseif (is_front_page()) {
        $view = 'home';
    }
    return $view;
}

function hcmus_auto_template_include($template) {
    if (hcmus_auto_view() !== '') {
        $file = HCMUS_AUTO_PATH . 'templates/page.php';
        if (file_exists($file)) {
            return $file;
        }
    }
    return $template;
}
add_filter('template_include', 'hcmus_auto_template_include', 99);

function hcmus_auto_clean_theme_assets() {
    if (hcmus_auto_view() === '') {
        return;
    }
    $handles = array('wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'wc-blocks-style');
    if (hcmus_auto_view() === 'woo') {
        // Giỏ hàng/thanh toán dạng khối cần CSS khối của WordPress và WooCommerce.
        $handles = array('wp-block-library-theme', 'classic-theme-styles', 'global-styles');
    }
    foreach ($handles as $handle) {
        wp_dequeue_style($handle);
    }
}
add_action('wp_enqueue_scripts', 'hcmus_auto_clean_theme_assets', 100);

/* Trang cửa hàng / danh mục của WooCommerce -> trang Dòng xe của plugin */
function hcmus_auto_redirect_archives() {
    if (is_admin() || !function_exists('is_shop')) {
        return;
    }
    $cars_id = hcmus_auto_page_id('cars');
    if (!$cars_id || get_post_status($cars_id) !== 'publish' || is_page($cars_id)) {
        return;
    }
    if (is_shop() && !is_search()) {
        wp_safe_redirect(hcmus_auto_cars_url());
        exit;
    }
    if (is_product_category()) {
        $term = get_queried_object();
        if ($term instanceof WP_Term) {
            wp_safe_redirect(hcmus_auto_cars_url(array('loai' => $term->slug)));
            exit;
        }
    }
}
add_action('template_redirect', 'hcmus_auto_redirect_archives', 5);

/* Đánh dấu có JavaScript sớm để hiệu ứng không bị nháy */
function hcmus_auto_head_flag() {
    if (hcmus_auto_view() === '') {
        return;
    }
    echo "<script>document.documentElement.className+=' hcmus-js';</script>\n";
}
add_action('wp_head', 'hcmus_auto_head_flag', 1);
