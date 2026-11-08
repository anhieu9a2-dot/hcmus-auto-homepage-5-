<?php
if (!defined('ABSPATH')) {
    exit;
}

/* ---------- Thẻ xe ---------- */
function hcmus_auto_render_product_card($product, $brand_tax) {
    $product_id = $product->get_id();
    $link       = get_permalink($product_id);
    $image      = $product->get_image('woocommerce_single', array('class' => 'hcmus-product-card__image'));
    $label      = 'HCMUS AUTO';
    $brands     = get_the_terms($product_id, $brand_tax);
    if (!empty($brands) && !is_wp_error($brands)) {
        $label = $brands[0]->name;
    }
    ?>
    <article class="hcmus-product-card hcmus-reveal">
        <a class="hcmus-product-card__media" href="<?php echo esc_url($link); ?>">
            <?php echo wp_kses_post($image); ?>
        </a>
        <div class="hcmus-product-card__body">
            <p class="hcmus-product-card__label"><?php echo esc_html(hcmus_auto_upper($label)); ?></p>
            <h3><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($product->get_name()); ?></a></h3>
            <div class="hcmus-product-card__bottom">
                <span class="hcmus-price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                <a class="hcmus-arrow-link" href="<?php echo esc_url($link); ?>" aria-label="<?php echo esc_attr('Xem ' . $product->get_name()); ?>">↗</a>
            </div>
            <a class="hcmus-card-cost" href="<?php echo esc_url(hcmus_auto_cost_url($product_id)); ?>">DỰ TOÁN LĂN BÁNH →</a>
        </div>
    </article>
    <?php
}

/* ---------- Bộ lọc ---------- */
function hcmus_auto_read_filters() {
    $f = array('q' => '', 'hang' => '', 'gia' => '', 'loai' => '');
    foreach (array_keys($f) as $key) {
        if (isset($_GET[$key]) && !is_array($_GET[$key])) {
            $f[$key] = sanitize_text_field(wp_unslash($_GET[$key]));
        }
    }
    $f['hang'] = $f['hang'] !== '' ? sanitize_title($f['hang']) : '';
    $f['loai'] = $f['loai'] !== '' ? sanitize_title($f['loai']) : '';
    if ($f['gia'] !== '' && !array_key_exists($f['gia'], hcmus_auto_price_segments())) {
        $f['gia'] = '';
    }
    return $f;
}

function hcmus_auto_query_cars($f, $paged = 1, $limit = 12) {
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'paged'          => max(1, (int) $paged),
    );
    if ($f['q'] !== '') {
        $args['s'] = $f['q'];
    }
    $tax = array();
    if ($f['loai'] !== '') {
        $tax[] = array('taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $f['loai']);
    }
    if ($f['hang'] !== '') {
        $tax[] = array('taxonomy' => hcmus_auto_brand_taxonomy(), 'field' => 'slug', 'terms' => $f['hang']);
    }
    $hidden = array('exclude-from-catalog');
    if ($f['q'] !== '') {
        $hidden[] = 'exclude-from-search';
    }
    if (get_option('woocommerce_hide_out_of_stock_items') === 'yes') {
        $hidden[] = 'outofstock';
    }
    $tax[] = array('taxonomy' => 'product_visibility', 'field' => 'name', 'terms' => $hidden, 'operator' => 'NOT IN');
    $tax['relation'] = 'AND';
    $args['tax_query'] = $tax;
    if ($f['gia'] !== '') {
        $parts = explode('-', $f['gia']);
        $min   = isset($parts[0]) ? $parts[0] : '';
        $max   = isset($parts[1]) ? $parts[1] : '';
        if ($min !== '' && $max !== '') {
            $args['meta_query'] = array(array('key' => '_price', 'value' => array((float) $min, (float) $max), 'type' => 'NUMERIC', 'compare' => 'BETWEEN'));
        } elseif ($min !== '') {
            $args['meta_query'] = array(array('key' => '_price', 'value' => (float) $min, 'type' => 'NUMERIC', 'compare' => '>='));
        }
    }
    $query = new WP_Query($args);
    if (!$query->have_posts() && $args['paged'] > 1 && $query->found_posts > 0) {
        $args['paged'] = 1;
        $query         = new WP_Query($args);
    }
    $products = array();
    foreach ($query->posts as $post) {
        $product = wc_get_product($post->ID);
        if ($product) {
            $products[] = $product;
        }
    }
    wp_reset_postdata();
    return array(
        'products' => $products,
        'total'    => (int) $query->found_posts,
        'pages'    => (int) $query->max_num_pages,
        'paged'    => (int) $args['paged'],
    );
}

/* ---------- Gom xe theo loại hình ---------- */
function hcmus_auto_collect_sections($per_category) {
    $sections = array();
    $fallback = null;
    if (!class_exists('WooCommerce') || !taxonomy_exists('product_cat')) {
        return $sections;
    }
    $default_cat = (int) get_option('default_product_cat');
    $cats        = get_terms(array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'parent'     => 0,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ));
    if (is_wp_error($cats)) {
        return $sections;
    }
    foreach ($cats as $cat) {
        $items = wc_get_products(array_merge(array(
            'status'   => 'publish',
            'limit'    => $per_category,
            'category' => array($cat->slug),
            'orderby'  => 'date',
            'order'    => 'DESC',
        ), hcmus_auto_catalog_args()));
        if (empty($items)) {
            continue;
        }
        $is_default = ((int) $cat->term_id === $default_cat);
        $row        = array(
            'slug'     => $cat->slug,
            'name'     => $is_default ? 'Xe khác' : $cat->name,
            'products' => $items,
        );
        if ($is_default) {
            $fallback = $row;
        } else {
            $sections[] = $row;
        }
    }
    if ($fallback) {
        $sections[] = $fallback;
    }
    return $sections;
}

/* ---------- Trang Dòng xe: [hcmus_auto_cars] ---------- */
function hcmus_auto_cars_shortcode($atts = array()) {
    $atts = shortcode_atts(array('per_category' => 4), $atts, 'hcmus_auto_cars');
    $per_category = max(1, min(12, absint($atts['per_category'])));
    $brand_tax    = hcmus_auto_brand_taxonomy();
    $brand_terms  = hcmus_auto_brand_terms();
    $filters      = hcmus_auto_read_filters();
    $active       = ($filters['q'] !== '' || $filters['hang'] !== '' || $filters['gia'] !== '' || $filters['loai'] !== '');
    $cars_url     = hcmus_auto_cars_url();
    $segments     = hcmus_auto_price_segments();
    $cars_id      = hcmus_auto_page_id('cars');
    $ugly_links   = !get_option('permalink_structure');

    $results  = array();
    $sections = array();
    $total    = 0;
    $pages    = 0;
    $paged    = 1;
    if ($active) {
        $requested = isset($_GET['trang']) ? max(1, absint($_GET['trang'])) : 1;
        $found     = hcmus_auto_query_cars($filters, $requested);
        $results   = $found['products'];
        $total     = $found['total'];
        $pages     = $found['pages'];
        $paged     = $found['paged'];
    } else {
        $sections = hcmus_auto_collect_sections($per_category);
    }

    ob_start();
    ?>
    <main class="hcmus-home hcmus-page hcmus-cars">
        <section class="hcmus-page-head">
            <div class="hcmus-wrap">
                <p class="hcmus-eyebrow">THE COLLECTION</p>
                <h1>DÒNG XE</h1>
                <p class="hcmus-page-head__desc">Tìm xe theo tên, hãng và mức giá, hoặc xem theo từng loại hình.</p>
            </div>
        </section>

        <section class="hcmus-filter" id="tim-xe">
            <div class="hcmus-wrap">
                <form class="hcmus-filter__form" method="get" action="<?php echo esc_url($cars_url); ?>">
                    <?php if ($ugly_links && $cars_id) : ?>
                        <input type="hidden" name="page_id" value="<?php echo esc_attr($cars_id); ?>">
                    <?php endif; ?>
                    <?php if ($filters['loai'] !== '') : ?>
                        <input type="hidden" name="loai" value="<?php echo esc_attr($filters['loai']); ?>">
                    <?php endif; ?>
                    <label>
                        <span>TÊN XE / TỪ KHÓA</span>
                        <input type="search" name="q" value="<?php echo esc_attr($filters['q']); ?>" placeholder="Ví dụ: Civic, SUV, xe gia đình...">
                    </label>
                    <label>
                        <span>HÃNG XE</span>
                        <select name="hang">
                            <option value="">Tất cả hãng xe</option>
                            <?php foreach ($brand_terms as $term) : ?>
                                <option value="<?php echo esc_attr($term->slug); ?>"<?php selected($filters['hang'], $term->slug); ?>><?php echo esc_html($term->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>PHÂN KHÚC GIÁ</span>
                        <select name="gia">
                            <option value="">Mọi mức giá</option>
                            <?php foreach ($segments as $value => $text) : ?>
                                <option value="<?php echo esc_attr($value); ?>"<?php selected($filters['gia'], $value); ?>><?php echo esc_html($text); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="hcmus-button hcmus-button--gold" type="submit">TÌM XE <span aria-hidden="true">→</span></button>
                </form>
            </div>
        </section>

        <section class="hcmus-section hcmus-collection" id="bo-suu-tap">
            <div class="hcmus-wrap">
                <?php if (!class_exists('WooCommerce')) : ?>
                    <div class="hcmus-empty-state">
                        <h3>WooCommerce chưa được kích hoạt</h3>
                        <p>Kích hoạt WooCommerce để hiển thị danh sách xe và giá bán tự động.</p>
                    </div>
                <?php elseif ($active) : ?>
                    <div class="hcmus-results-bar">
                        <p><strong><?php echo esc_html($total); ?></strong> xe phù hợp</p>
                        <div class="hcmus-chips">
                            <?php if ($filters['q'] !== '') : ?><span>“<?php echo esc_html($filters['q']); ?>”</span><?php endif; ?>
                            <?php if ($filters['loai'] !== '') :
                                $loai_term = get_term_by('slug', $filters['loai'], 'product_cat');
                                ?><span>Loại: <?php echo esc_html($loai_term ? $loai_term->name : $filters['loai']); ?></span><?php endif; ?>
                            <?php if ($filters['hang'] !== '') :
                                $hang_term = get_term_by('slug', $filters['hang'], $brand_tax);
                                ?><span>Hãng: <?php echo esc_html($hang_term ? $hang_term->name : $filters['hang']); ?></span><?php endif; ?>
                            <?php if ($filters['gia'] !== '') : ?><span><?php echo esc_html($segments[$filters['gia']]); ?></span><?php endif; ?>
                            <a class="hcmus-text-link" href="<?php echo esc_url($cars_url); ?>">XÓA BỘ LỌC</a>
                        </div>
                    </div>
                    <?php if (!empty($results)) : ?>
                        <div class="hcmus-product-grid">
                            <?php foreach ($results as $product) {
                                hcmus_auto_render_product_card($product, $brand_tax);
                            } ?>
                        </div>
                        <?php if ($pages > 1) : ?>
                            <nav class="hcmus-pager" aria-label="Phân trang">
                                <?php for ($p = 1; $p <= $pages; $p++) :
                                    $page_args = array_filter($filters, 'strlen');
                                    if ($p > 1) {
                                        $page_args['trang'] = $p;
                                    }
                                    ?>
                                    <a href="<?php echo esc_url(hcmus_auto_cars_url($page_args)); ?>" class="<?php echo $p === $paged ? 'is-active' : ''; ?>"<?php echo $p === $paged ? ' aria-current="page"' : ''; ?>><?php echo esc_html($p); ?></a>
                                <?php endfor; ?>
                            </nav>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="hcmus-empty-state">
                            <h3>Không tìm thấy xe phù hợp</h3>
                            <p>Hãy thử đổi từ khóa hoặc bỏ bớt bộ lọc.</p>
                            <a class="hcmus-button hcmus-button--outline" href="<?php echo esc_url($cars_url); ?>">XEM TẤT CẢ XE</a>
                        </div>
                    <?php endif; ?>
                <?php elseif (empty($sections)) : ?>
                    <div class="hcmus-empty-state">
                        <h3>Danh sách xe đang được cập nhật</h3>
                        <p>Hãy thêm sản phẩm xe và gán danh mục (Sedan, SUV, CUV, MPV...) trong WooCommerce để hiển thị tại đây.</p>
                    </div>
                <?php else : ?>
                    <nav class="hcmus-tabs" aria-label="Loại hình xe">
                        <a href="#bo-suu-tap" class="hcmus-tab is-active" data-filter="all">TẤT CẢ</a>
                        <?php foreach ($sections as $sec) : ?>
                            <a href="#loai-<?php echo esc_attr($sec['slug']); ?>" class="hcmus-tab" data-filter="<?php echo esc_attr($sec['slug']); ?>">
                                <?php echo esc_html(hcmus_auto_upper($sec['name'])); ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                    <?php foreach ($sections as $i => $sec) : ?>
                        <div class="hcmus-type" id="loai-<?php echo esc_attr($sec['slug']); ?>" data-cat="<?php echo esc_attr($sec['slug']); ?>">
                            <div class="hcmus-type__head">
                                <div>
                                    <span class="hcmus-type__number"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                                    <h3><?php echo esc_html(hcmus_auto_upper($sec['name'])); ?></h3>
                                </div>
                                <a class="hcmus-ghost-link" href="<?php echo esc_url(hcmus_auto_cars_url(array('loai' => $sec['slug']))); ?>">
                                    XEM TẤT CẢ <?php echo esc_html(hcmus_auto_upper($sec['name'])); ?> <span aria-hidden="true">↗</span>
                                </a>
                            </div>
                            <div class="hcmus-product-grid">
                                <?php foreach ($sec['products'] as $product) {
                                    hcmus_auto_render_product_card($product, $brand_tax);
                                } ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <?php
    return ob_get_clean();
}
add_shortcode('hcmus_auto_cars', 'hcmus_auto_cars_shortcode');
