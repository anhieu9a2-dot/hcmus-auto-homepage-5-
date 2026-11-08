<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Giao diện HCMUS cho Giỏ hàng, Thanh toán, Tài khoản (WooCommerce).
 * Nội dung bên trong vẫn do WooCommerce tạo, plugin chỉ bao khung và tạo kiểu.
 */
function hcmus_auto_woo_context() {
    if (!function_exists('is_cart')) {
        return '';
    }
    if (is_cart()) {
        return 'cart';
    }
    if (is_checkout()) {
        return (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) ? 'received' : 'checkout';
    }
    if (function_exists('is_account_page') && is_account_page()) {
        return 'account';
    }
    return '';
}

function hcmus_auto_woo_head($ctx) {
    $logged = is_user_logged_in();
    $map    = array(
        'cart'     => array('YOUR SELECTION', 'GIỎ HÀNG', 'Kiểm tra lại các xe đã chọn trước khi thanh toán.'),
        'checkout' => array('SECURE CHECKOUT', 'THANH TOÁN', 'Điền thông tin nhận xe và chọn phương thức thanh toán.'),
        'received' => array('ORDER CONFIRMED', 'ĐẶT HÀNG THÀNH CÔNG', 'Cảm ơn bạn. Thông tin đơn hàng nằm bên dưới.'),
        'account'  => array('MY ACCOUNT', 'TÀI KHOẢN', $logged ? 'Quản lý đơn hàng, địa chỉ và thông tin tài khoản của bạn.' : 'Đăng nhập hoặc tạo tài khoản để theo dõi đơn hàng.'),
    );
    $h = isset($map[$ctx]) ? $map[$ctx] : $map['cart'];

    $steps = array();
    if (in_array($ctx, array('cart', 'checkout', 'received'), true)) {
        $order = array('cart', 'checkout', 'received');
        $now   = array_search($ctx, $order, true);
        $items = array(
            array('GIỎ HÀNG', hcmus_auto_wc_url('cart')),
            array('THANH TOÁN', hcmus_auto_wc_url('checkout')),
            array('HOÀN TẤT', ''),
        );
        foreach ($items as $i => $item) {
            $state = $i < $now ? 'is-done' : ($i === $now ? 'is-current' : '');
            $steps[] = array('label' => $item[0], 'url' => ($i < $now && $ctx !== 'received') ? $item[1] : '', 'state' => $state, 'n' => $i + 1);
        }
    }
    ob_start();
    ?>
    <section class="hcmus-page-head hcmus-page-head--woo">
        <div class="hcmus-wrap">
            <p class="hcmus-eyebrow"><?php echo esc_html($h[0]); ?></p>
            <h1><?php echo esc_html($h[1]); ?></h1>
            <p class="hcmus-page-head__desc"><?php echo esc_html($h[2]); ?></p>
            <?php if ($steps) : ?>
                <ol class="hcmus-steps" aria-label="Các bước đặt xe">
                    <?php foreach ($steps as $s) : ?>
                        <li class="<?php echo esc_attr($s['state']); ?>"<?php echo $s['state'] === 'is-current' ? ' aria-current="step"' : ''; ?>>
                            <?php if ($s['url']) : ?>
                                <a class="hcmus-step" href="<?php echo esc_url($s['url']); ?>"><b><?php echo esc_html($s['n']); ?></b><span><?php echo esc_html($s['label']); ?></span></a>
                            <?php else : ?>
                                <span class="hcmus-step"><b><?php echo esc_html($s['n']); ?></b><span><?php echo esc_html($s['label']); ?></span></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

function hcmus_auto_render_woo() {
    $ctx = hcmus_auto_woo_context();
    ob_start();
    ?>
    <main class="hcmus-home hcmus-page hcmus-woo hcmus-woo--<?php echo esc_attr($ctx ? $ctx : 'page'); ?>">
        <?php echo hcmus_auto_woo_head($ctx); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <section class="hcmus-woo__body">
            <div class="hcmus-wrap">
                <?php
                while (have_posts()) {
                    the_post();
                    the_content();
                }
                ?>
            </div>
        </section>
    </main>
    <?php
    return ob_get_clean();
}

/* Liên kết "Tiếp tục xem xe" dưới bảng giỏ hàng */
function hcmus_auto_cart_continue_link() {
    echo '<a class="hcmus-continue" href="' . esc_url(hcmus_auto_cars_url()) . '">&larr; TIẾP TỤC XEM XE</a>';
}
add_action('woocommerce_after_cart_table', 'hcmus_auto_cart_continue_link');

/* Giỏ trống: nút quay lại trỏ thẳng về trang Dòng xe */
function hcmus_auto_return_to_shop_url($url) {
    return hcmus_auto_cars_url();
}
add_filter('woocommerce_return_to_shop_redirect', 'hcmus_auto_return_to_shop_url');

function hcmus_auto_return_to_shop_text($text) {
    return 'Xem dòng xe';
}
add_filter('woocommerce_return_to_shop_text', 'hcmus_auto_return_to_shop_text');
