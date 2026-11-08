<?php
if (!defined('ABSPATH')) {
    exit;
}

/* ---------- Màn chuyển cảnh ---------- */
function hcmus_auto_render_curtain() {
    ?>
    <div class="hcmus-curtain" aria-hidden="true"><span>HCMUS AUTO</span></div>
    <?php
}

/* ---------- Header + menu trượt ---------- */
function hcmus_auto_render_header() {
    $cart_count = 0;
    if (function_exists('WC') && WC() && isset(WC()->cart) && WC()->cart) {
        $cart_count = (int) WC()->cart->get_cart_contents_count();
    }
    $cars      = array_slice(hcmus_auto_get_cars(10), 0, 10);
    $home_url  = home_url('/');
    $cars_url  = hcmus_auto_cars_url();
    $cost_url  = hcmus_auto_cost_url();
    ?>
    <header class="hcmus-site-header">
        <div class="hcmus-header__inner">
            <div class="hcmus-header__left">
                <button class="hcmus-menu-btn" type="button" aria-controls="hcmus-menu" aria-expanded="false">
                    <span class="hcmus-menu-btn__icon" aria-hidden="true"><i></i><i></i><i></i></span>
                    <span class="hcmus-menu-btn__text">MENU</span>
                </button>
                <a class="hcmus-logo" href="<?php echo esc_url($home_url); ?>">
                    <span class="hcmus-logo__mark">HA</span>
                    <span class="hcmus-logo__text"><?php echo esc_html(get_bloginfo('name')); ?></span>
                </a>
            </div>
        </div>
    </header>

    <div class="hcmus-menu" id="hcmus-menu" aria-hidden="true">
        <div class="hcmus-menu__panel">
            <button class="hcmus-menu__close" type="button"><span aria-hidden="true">✕</span> ĐÓNG</button>
            <div class="hcmus-menu__levels">
                <ul class="hcmus-menu__list is-active" data-level="main">
                    <li><a class="hcmus-menu__item" href="<?php echo esc_url($home_url); ?>">GIỚI THIỆU</a></li>
                    <li><button type="button" class="hcmus-menu__item" data-open="models">DÒNG XE</button></li>
                    <li><a class="hcmus-menu__item" href="<?php echo esc_url($cost_url); ?>">DỰ TOÁN CHI PHÍ</a></li>
                    <li><a class="hcmus-menu__item" href="<?php echo esc_url(home_url('/#showroom')); ?>">SHOWROOM</a></li>
                    <li><a class="hcmus-menu__item" href="<?php echo esc_url(hcmus_auto_wc_url('cart')); ?>">GIỎ HÀNG<?php if ($cart_count > 0) : ?> <span class="hcmus-menu__count"><?php echo esc_html($cart_count); ?></span><?php endif; ?></a></li>
                    <li><a class="hcmus-menu__item" href="<?php echo esc_url(hcmus_auto_wc_url('checkout')); ?>">THANH TOÁN</a></li>
                    <li><a class="hcmus-menu__item" href="<?php echo esc_url(hcmus_auto_wc_url('myaccount')); ?>">TÀI KHOẢN</a></li>
                </ul>
                <ul class="hcmus-menu__list" data-level="models">
                    <li><button type="button" class="hcmus-menu__item hcmus-menu__back" data-open="main"><span aria-hidden="true">←</span> DÒNG XE</button></li>
                    <?php foreach ($cars as $car) : ?>
                        <li>
                            <a class="hcmus-menu__item hcmus-menu__car"
                               href="<?php echo esc_url(get_permalink($car->get_id())); ?>"
                               data-name="<?php echo esc_attr($car->get_name()); ?>"
                               data-price="<?php echo esc_attr(hcmus_auto_plain_price($car)); ?>"
                               data-img="<?php echo esc_url(hcmus_auto_car_image_url($car)); ?>">
                                <?php echo esc_html($car->get_name()); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li class="hcmus-menu__gap"><a class="hcmus-menu__item" href="<?php echo esc_url($cars_url); ?>">TẤT CẢ XE</a></li>
                    <li><a class="hcmus-menu__item" href="<?php echo esc_url($cost_url); ?>">DỰ TOÁN CHI PHÍ</a></li>
                </ul>
            </div>
        </div>
        <div class="hcmus-menu__preview">
            <img class="hcmus-menu__img" alt="" src="">
            <div class="hcmus-menu__caption">
                <p class="hcmus-menu__name"></p>
                <p class="hcmus-menu__price"></p>
                <a class="hcmus-menu__discover" href="#">XEM XE</a>
            </div>
        </div>
    </div>
    <?php
}

/* ---------- Footer ---------- */
function hcmus_auto_render_footer() {
    $privacy = function_exists('get_privacy_policy_url') ? get_privacy_policy_url() : '';
    $content = hcmus_auto_content();
    ?>
    <footer class="hcmus-site-footer">
        <div class="hcmus-wrap hcmus-footer__grid">
            <div class="hcmus-footer__brand">
                <a class="hcmus-logo" href="<?php echo esc_url(home_url('/')); ?>">
                    <span class="hcmus-logo__mark">HA</span>
                    <span class="hcmus-logo__text"><?php echo esc_html(get_bloginfo('name')); ?></span>
                </a>
                <p>Nền tảng khám phá và lựa chọn ô tô thuận tiện, với thông tin xe rõ ràng và nhiều dòng xe đa dạng.</p>
            </div>
            <div>
                <h4>KHÁM PHÁ</h4>
                <a href="<?php echo esc_url(hcmus_auto_cars_url()); ?>">Tất cả xe</a>
                <?php foreach (hcmus_auto_get_categories(4) as $term) : ?>
                    <a href="<?php echo esc_url(hcmus_auto_cars_url(array('loai' => $term->slug))); ?>"><?php echo esc_html($term->name); ?></a>
                <?php endforeach; ?>
                <a href="<?php echo esc_url(hcmus_auto_cost_url()); ?>">Dự toán chi phí</a>
            </div>
            <div>
                <h4>TÀI KHOẢN</h4>
                <a href="<?php echo esc_url(hcmus_auto_wc_url('myaccount')); ?>">Đăng nhập / Đăng ký</a>
                <a href="<?php echo esc_url(hcmus_auto_wc_url('cart')); ?>">Giỏ hàng</a>
                <a href="<?php echo esc_url(hcmus_auto_wc_url('checkout')); ?>">Thanh toán</a>
            </div>
            <div>
                <h4>THÔNG TIN</h4>
                <a href="<?php echo esc_url(home_url('/#showroom')); ?>">Showroom</a>
                <?php if ($privacy) : ?>
                    <a href="<?php echo esc_url($privacy); ?>">Chính sách bảo mật</a>
                <?php endif; ?>
                <?php if ($content['address'] !== '') : ?>
                    <span class="hcmus-footer__line"><?php echo esc_html($content['address']); ?></span>
                <?php endif; ?>
                <?php if ($content['phone'] !== '') : ?>
                    <span class="hcmus-footer__line"><?php echo esc_html($content['phone']); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="hcmus-footer__bottom">
            <div class="hcmus-wrap">© <?php echo esc_html(wp_date('Y')); ?> HCMUS Auto. All rights reserved.</div>
        </div>
    </footer>
    <?php
}
