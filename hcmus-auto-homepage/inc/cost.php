<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Bảng dự toán chi phí lăn bánh.
 * $selected: ID xe chọn sẵn. $embedded: true khi nhúng trong trang chi tiết xe (không có tiêu đề).
 */
function hcmus_auto_render_cost($selected = 0, $embedded = false) {
    $config      = hcmus_auto_cost_settings();
    $cars        = hcmus_auto_get_cars(50);
    $selected    = (int) $selected;
    $preset_ids  = array();
    $preset_price = 0;
    foreach ($cars as $car) {
        $preset_ids[] = $car->get_id();
    }
    if ($selected && !in_array($selected, $preset_ids, true) && function_exists('wc_get_product')) {
        $extra = wc_get_product($selected);
        if ($extra) {
            array_unshift($cars, $extra);
        }
    }
    ob_start();
    ?>
    <section class="hcmus-cost-section<?php echo $embedded ? ' hcmus-cost-section--embedded' : ''; ?>" id="du-toan">
        <div class="hcmus-wrap">
            <?php if (!$embedded) : ?>
                <div class="hcmus-section-heading">
                    <div>
                        <p class="hcmus-eyebrow">OWNERSHIP COST</p>
                        <h2>DỰ TOÁN CHI PHÍ LĂN BÁNH</h2>
                    </div>
                </div>
            <?php endif; ?>
            <div class="hcmus-cost" data-config="<?php echo esc_attr(wp_json_encode($config)); ?>">
                <div class="hcmus-cost__form">
                    <label class="full">
                        <span>CHỌN XE</span>
                        <select name="car">
                            <option value="">Nhập giá xe khác</option>
                            <?php foreach ($cars as $car) :
                                $price = (float) $car->get_price();
                                if ($price <= 0) {
                                    continue;
                                }
                                $is_selected = ($selected && (int) $car->get_id() === $selected);
                                if ($is_selected) {
                                    $preset_price = (int) round($price);
                                }
                                ?>
                                <option value="<?php echo esc_attr($car->get_id()); ?>" data-price="<?php echo esc_attr((string) round($price)); ?>"<?php echo $is_selected ? ' selected' : ''; ?>>
                                    <?php echo esc_html($car->get_name()); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="full">
                        <span>GIÁ XE (ĐỒNG)</span>
                        <input type="text" name="price" inputmode="numeric" placeholder="Ví dụ: 850.000.000" value="<?php echo $preset_price ? esc_attr(number_format($preset_price, 0, ',', '.')) : ''; ?>">
                    </label>
                    <label>
                        <span>NƠI ĐĂNG KÝ</span>
                        <select name="area">
                            <option value="hn">Hà Nội</option>
                            <option value="hcm" selected>TP. Hồ Chí Minh</option>
                            <option value="city">Thành phố khác</option>
                            <option value="other">Khu vực còn lại</option>
                        </select>
                    </label>
                    <label>
                        <span>LOẠI XE</span>
                        <select name="type">
                            <option value="car">Xe xăng / dầu (≤ 9 chỗ)</option>
                            <option value="ev">Xe điện</option>
                            <option value="pickup">Xe bán tải</option>
                        </select>
                    </label>
                    <label class="full hcmus-cost__check">
                        <input type="checkbox" name="hull">
                        <span>Thêm bảo hiểm vật chất (tự nguyện, ước tính)</span>
                    </label>
                    <p class="hcmus-cost__subtitle full">TRẢ GÓP (THAM KHẢO)</p>
                    <label>
                        <span>VAY (% GIÁ XE)</span>
                        <input type="number" name="loan_pct" min="0" max="100" step="1" value="<?php echo esc_attr($config['loan_pct']); ?>">
                    </label>
                    <label>
                        <span>THỜI HẠN (NĂM)</span>
                        <input type="number" name="loan_years" min="1" max="10" step="1" value="<?php echo esc_attr($config['loan_years']); ?>">
                    </label>
                    <label class="full">
                        <span>LÃI SUẤT (%/NĂM)</span>
                        <input type="number" name="loan_rate" min="0" step="0.1" value="<?php echo esc_attr($config['loan_rate']); ?>">
                    </label>
                </div>
                <div class="hcmus-cost__result">
                    <div class="hcmus-cost__row"><span>Giá xe</span><strong data-out="price">0 ₫</strong></div>
                    <div class="hcmus-cost__row"><span>Lệ phí trước bạ <em data-out="taxrate"></em></span><strong data-out="tax">0 ₫</strong></div>
                    <div class="hcmus-cost__row"><span>Phí cấp biển số</span><strong data-out="plate">0 ₫</strong></div>
                    <div class="hcmus-cost__row"><span>Phí đăng kiểm</span><strong data-out="inspection">0 ₫</strong></div>
                    <div class="hcmus-cost__row"><span>Phí bảo trì đường bộ (1 năm)</span><strong data-out="road">0 ₫</strong></div>
                    <div class="hcmus-cost__row"><span>Bảo hiểm TNDS bắt buộc (1 năm)</span><strong data-out="tnds">0 ₫</strong></div>
                    <div class="hcmus-cost__row" data-row="hull"><span>Bảo hiểm vật chất (ước tính)</span><strong data-out="hull">0 ₫</strong></div>
                    <p class="hcmus-cost__ev" data-out="evnote" hidden></p>
                    <div class="hcmus-cost__row hcmus-cost__row--total"><span>TỔNG CHI PHÍ LĂN BÁNH</span><strong data-out="total">0 ₫</strong></div>
                    <div class="hcmus-cost__loan">
                        <div class="hcmus-cost__row"><span>Số tiền vay</span><strong data-out="loan">0 ₫</strong></div>
                        <div class="hcmus-cost__row"><span>Trả hằng tháng (ước tính)</span><strong data-out="monthly">0 ₫</strong></div>
                        <div class="hcmus-cost__row"><span>Tổng lãi dự kiến</span><strong data-out="interest">0 ₫</strong></div>
                        <div class="hcmus-cost__row"><span>Cần chuẩn bị ban đầu</span><strong data-out="upfront">0 ₫</strong></div>
                    </div>
                    <p class="hcmus-cost__note">Số liệu mang tính tham khảo. Lệ phí trước bạ thực tế tính trên giá theo bảng giá của cơ quan thuế, chưa gồm phí dịch vụ đại lý và phụ kiện. Mức thu có thể thay đổi theo quy định.</p>
                </div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

/* Trang riêng: [hcmus_auto_cost] */
function hcmus_auto_cost_shortcode() {
    $selected = isset($_GET['xe']) ? absint($_GET['xe']) : 0;
    ob_start();
    ?>
    <main class="hcmus-home hcmus-page">
        <section class="hcmus-page-head">
            <div class="hcmus-wrap">
                <p class="hcmus-eyebrow">OWNERSHIP COST</p>
                <h1>DỰ TOÁN CHI PHÍ LĂN BÁNH</h1>
                <p class="hcmus-page-head__desc">Ước tính tổng chi phí sở hữu xe và khoản trả góp hằng tháng trước khi quyết định.</p>
            </div>
        </section>
        <?php echo hcmus_auto_render_cost($selected, false); // phpcs:ignore ?>
    </main>
    <?php
    return ob_get_clean();
}
add_shortcode('hcmus_auto_cost', 'hcmus_auto_cost_shortcode');
