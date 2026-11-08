<?php
if (!defined('ABSPATH')) {
    exit;
}

function hcmus_auto_product_tabs($product) {
    $reviews = method_exists($product, 'get_review_count') ? (int) $product->get_review_count() : 0;
    return array(
        'tong-quan' => 'TỔNG QUAN',
        'mo-ta'     => 'MÔ TẢ',
        'thong-so'  => 'THÔNG SỐ',
        'chi-phi'   => 'CHI PHÍ LĂN BÁNH',
        'danh-gia'  => 'ĐÁNH GIÁ (' . $reviews . ')',
    );
}

function hcmus_auto_product_tab_url($product_id, $tab) {
    $url = get_permalink($product_id);
    return $tab === 'tong-quan' ? $url : add_query_arg('hcmus_tab', $tab, $url);
}

/* ---------- Thư viện ảnh ---------- */
function hcmus_auto_render_gallery($product) {
    $ids = array();
    if ($product->get_image_id()) {
        $ids[] = (int) $product->get_image_id();
    }
    foreach ($product->get_gallery_image_ids() as $gid) {
        $ids[] = (int) $gid;
    }
    ob_start();
    ?>
    <div class="hcmus-gallery">
        <div class="hcmus-gallery__main">
            <?php if ($ids) : ?>
                <?php echo wp_get_attachment_image($ids[0], 'large', false, array('class' => 'hcmus-gallery__img')); ?>
            <?php else : ?>
                <?php echo wp_kses_post(function_exists('wc_placeholder_img') ? wc_placeholder_img('large') : ''); ?>
            <?php endif; ?>
        </div>
        <?php if (count($ids) > 1) : ?>
            <div class="hcmus-gallery__thumbs">
                <?php foreach ($ids as $gid) : ?>
                    <a class="hcmus-gallery__thumb" href="<?php echo esc_url(wp_get_attachment_image_url($gid, 'full')); ?>" data-full="<?php echo esc_url(wp_get_attachment_image_url($gid, 'large')); ?>">
                        <?php echo wp_get_attachment_image($gid, 'thumbnail'); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/* ---------- Thông số ---------- */
function hcmus_auto_spec_rows($product) {
    $rows = array();
    $id   = $product->get_id();
    $brands = wp_get_post_terms($id, hcmus_auto_brand_taxonomy(), array('fields' => 'names'));
    if (!is_wp_error($brands) && $brands) {
        $rows['Hãng xe'] = implode(', ', $brands);
    }
    $cats = wp_get_post_terms($id, 'product_cat', array('fields' => 'names'));
    if (!is_wp_error($cats) && $cats) {
        $rows['Loại hình'] = implode(', ', $cats);
    }
    if ($product->get_sku()) {
        $rows['Mã xe (SKU)'] = $product->get_sku();
    }
    foreach ($product->get_attributes() as $attr) {
        if (is_object($attr) && method_exists($attr, 'get_visible') && !$attr->get_visible()) {
            continue;
        }
        $name  = is_object($attr) ? $attr->get_name() : (string) $attr;
        $value = $product->get_attribute($name);
        if ($value !== '') {
            $rows[wc_attribute_label($name)] = $value;
        }
    }
    if ($product->get_weight()) {
        $rows['Khối lượng'] = $product->get_weight() . ' ' . get_option('woocommerce_weight_unit');
    }
    if ($product->has_dimensions()) {
        $rows['Kích thước'] = wc_format_dimensions($product->get_dimensions(false));
    }
    return $rows;
}

/* ---------- Đánh giá ---------- */
function hcmus_auto_render_reviews($product) {
    $html = '';
    if (comments_open($product->get_id())) {
        ob_start();
        comments_template();
        $html = ob_get_clean();
    }
    if (trim($html) === '') {
        $html = '<div class="hcmus-empty-state"><h3>Chưa có đánh giá</h3><p>Sản phẩm này chưa có đánh giá nào.</p></div>';
    }
    return '<div class="hcmus-reviews">' . $html . '</div>';
}

/* ---------- Trang chi tiết xe ---------- */
function hcmus_auto_render_product() {
    global $post, $product;
    $post_id = get_queried_object_id();
    $post    = get_post($post_id);
    if ($post) {
        setup_postdata($post);
    }
    $product = function_exists('wc_get_product') ? wc_get_product($post_id) : false;
    if (!$product) {
        return '';
    }
    $tabs = hcmus_auto_product_tabs($product);
    $tab  = 'tong-quan';
    if (isset($_GET['hcmus_tab'])) {
        $requested = sanitize_key(wp_unslash($_GET['hcmus_tab']));
        if (isset($tabs[$requested])) {
            $tab = $requested;
        }
    }
    $brands = wp_get_post_terms($post_id, hcmus_auto_brand_taxonomy(), array('fields' => 'names'));
    $label  = (!is_wp_error($brands) && $brands) ? $brands[0] : 'HCMUS AUTO';
    $in_stock = $product->is_in_stock();
    $qty      = $product->get_manage_stock() ? $product->get_stock_quantity() : null;

    ob_start();
    ?>
    <main class="hcmus-home hcmus-page hcmus-product">
        <section class="hcmus-page-head hcmus-page-head--product">
            <div class="hcmus-wrap">
                <nav class="hcmus-breadcrumb" aria-label="Breadcrumb">
                    <a href="<?php echo esc_url(home_url('/')); ?>">TRANG CHỦ</a> /
                    <a href="<?php echo esc_url(hcmus_auto_cars_url()); ?>">DÒNG XE</a> /
                    <span><?php echo esc_html(hcmus_auto_upper($product->get_name())); ?></span>
                </nav>
                <p class="hcmus-eyebrow"><?php echo esc_html(hcmus_auto_upper($label)); ?></p>
                <h1><?php echo esc_html($product->get_name()); ?></h1>
                <p class="hcmus-product__price"><?php echo wp_kses_post($product->get_price_html()); ?></p>
            </div>
        </section>

        <div class="hcmus-subnav-wrap">
            <nav class="hcmus-subnav hcmus-wrap" aria-label="Nội dung xe">
                <?php foreach ($tabs as $key => $text) : ?>
                    <a href="<?php echo esc_url(hcmus_auto_product_tab_url($post_id, $key)); ?>" class="<?php echo $key === $tab ? 'is-active' : ''; ?>"><?php echo esc_html($text); ?></a>
                <?php endforeach; ?>
            </nav>
        </div>

        <section class="hcmus-section hcmus-product__body">
            <div class="hcmus-wrap">
                <?php if ($tab === 'tong-quan') : ?>
                    <div class="hcmus-product__overview">
                        <?php echo hcmus_auto_render_gallery($product); // phpcs:ignore ?>
                        <div class="hcmus-product__info">
                            <?php if ($product->get_short_description()) : ?>
                                <div class="hcmus-product__short"><?php echo wp_kses_post(wpautop($product->get_short_description())); ?></div>
                            <?php endif; ?>
                            <p class="hcmus-product__stock <?php echo $in_stock ? 'is-in' : 'is-out'; ?>">
                                <?php
                                if (!$in_stock) {
                                    echo 'Hết hàng';
                                } elseif ($qty !== null) {
                                    echo esc_html('Còn ' . (int) $qty . ' xe trong kho');
                                } else {
                                    echo 'Còn hàng';
                                }
                                ?>
                            </p>
                            <div class="hcmus-product__actions">
                                <?php if ($in_stock && $product->is_purchasable()) : ?>
                                    <a class="hcmus-button hcmus-button--gold" href="<?php echo esc_url($product->add_to_cart_url()); ?>">THÊM VÀO GIỎ HÀNG <span aria-hidden="true">→</span></a>
                                <?php endif; ?>
                                <a class="hcmus-button hcmus-button--dark" href="<?php echo esc_url(hcmus_auto_product_tab_url($post_id, 'chi-phi')); ?>">DỰ TOÁN LĂN BÁNH <span aria-hidden="true">→</span></a>
                            </div>
                            <?php
                            $specs = array_slice(hcmus_auto_spec_rows($product), 0, 4, true);
                            if ($specs) :
                                ?>
                                <dl class="hcmus-product__quick">
                                    <?php foreach ($specs as $name => $value) : ?>
                                        <dt><?php echo esc_html(hcmus_auto_upper($name)); ?></dt><dd><?php echo esc_html($value); ?></dd>
                                    <?php endforeach; ?>
                                </dl>
                                <a class="hcmus-text-link" href="<?php echo esc_url(hcmus_auto_product_tab_url($post_id, 'thong-so')); ?>">XEM ĐẦY ĐỦ THÔNG SỐ <span aria-hidden="true">↗</span></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($tab === 'mo-ta') : ?>
                    <div class="hcmus-product__text">
                        <?php
                        $desc = $product->get_description();
                        if (trim($desc) !== '') {
                            echo wp_kses_post(apply_filters('the_content', $desc));
                        } else {
                            echo '<p>Chưa có mô tả chi tiết cho xe này.</p>';
                        }
                        ?>
                    </div>
                <?php elseif ($tab === 'thong-so') : ?>
                    <?php $rows = hcmus_auto_spec_rows($product); ?>
                    <?php if ($rows) : ?>
                        <table class="hcmus-spec-table">
                            <tbody>
                                <?php foreach ($rows as $name => $value) : ?>
                                    <tr><th><?php echo esc_html($name); ?></th><td><?php echo esc_html($value); ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <div class="hcmus-empty-state"><h3>Chưa có thông số</h3><p>Thêm thuộc tính cho sản phẩm trong WooCommerce để hiển thị tại đây.</p></div>
                    <?php endif; ?>
                <?php elseif ($tab === 'chi-phi') : ?>
                    <?php echo hcmus_auto_render_cost($post_id, true); // phpcs:ignore ?>
                <?php else : ?>
                    <?php echo hcmus_auto_render_reviews($product); // phpcs:ignore ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <?php
    return ob_get_clean();
}
