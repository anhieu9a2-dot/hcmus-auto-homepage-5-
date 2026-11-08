<?php
if (!defined('ABSPATH')) {
    exit;
}

/* ---------- Chi phí lăn bánh ---------- */
function hcmus_auto_cost_defaults() {
    return array(
        'tax_hn'      => 10,
        'tax_hcm'     => 10,
        'tax_other'   => 10,
        'pickup_pct'  => 60,
        'tax_ev'      => 0,
        'ev_until'    => '2027-02-28',
        'ev_after_pct' => 100,
        'plate_hn'    => 14000000,
        'plate_hcm'   => 14000000,
        'plate_city'  => 1000000,
        'plate_other' => 200000,
        'inspection'  => 340000,
        'road_fee'    => 1560000,
        'tnds'        => 480700,
        'hull_pct'    => 1.5,
        'loan_pct'    => 70,
        'loan_years'  => 5,
        'loan_rate'   => 8.5,
    );
}

function hcmus_auto_cost_labels() {
    return array(
        'tax_hn'      => 'Lệ phí trước bạ – Hà Nội (%)',
        'tax_hcm'     => 'Lệ phí trước bạ – TP. Hồ Chí Minh (%)',
        'tax_other'   => 'Lệ phí trước bạ – nơi khác (%)',
        'pickup_pct'  => 'Xe bán tải: tỷ lệ so với xe con (%)',
        'tax_ev'      => 'Lệ phí trước bạ xe điện trong thời gian ưu đãi (%)',
        'ev_until'    => 'Xe điện: ưu đãi trước bạ đến hết ngày (để trống = không giới hạn)',
        'ev_after_pct' => 'Xe điện: sau khi hết ưu đãi, tỷ lệ so với xe xăng/dầu (%)',
        'plate_hn'    => 'Phí cấp biển số – Hà Nội (đ)',
        'plate_hcm'   => 'Phí cấp biển số – TP. Hồ Chí Minh (đ)',
        'plate_city'  => 'Phí cấp biển số – thành phố khác (đ)',
        'plate_other' => 'Phí cấp biển số – khu vực còn lại (đ)',
        'inspection'  => 'Phí đăng kiểm ban đầu (đ)',
        'road_fee'    => 'Phí bảo trì đường bộ, 1 năm (đ)',
        'tnds'        => 'Bảo hiểm TNDS bắt buộc, 1 năm (đ)',
        'hull_pct'    => 'Bảo hiểm vật chất ước tính (% giá xe)',
        'loan_pct'    => 'Trả góp: tỷ lệ vay mặc định (%)',
        'loan_years'  => 'Trả góp: số năm vay mặc định',
        'loan_rate'   => 'Trả góp: lãi suất mặc định (%/năm)',
    );
}

function hcmus_auto_cost_settings() {
    $defaults = hcmus_auto_cost_defaults();
    $saved    = get_option('hcmus_auto_cost', array());
    if (!is_array($saved)) {
        $saved = array();
    }
    return array_merge($defaults, array_intersect_key($saved, $defaults));
}

function hcmus_auto_cost_sanitize($input) {
    $out = array();
    foreach (hcmus_auto_cost_defaults() as $key => $default) {
        if ($key === 'ev_until') {
            $raw = (is_array($input) && isset($input[$key])) ? sanitize_text_field(wp_unslash($input[$key])) : null;
            if ($raw !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
                $out[$key] = $raw;
            } elseif ($raw === '') {
                $out[$key] = '';
            } else {
                $out[$key] = $default;
            }
            continue;
        }
        $out[$key] = (is_array($input) && isset($input[$key])) ? max(0, (float) $input[$key]) : $default;
    }
    return $out;
}

/* ---------- Nội dung giới thiệu ---------- */
function hcmus_auto_content_schema() {
    return array(
        'mission'        => array('Sứ mệnh', 'Giúp mỗi khách hàng tìm được chiếc xe phù hợp với nhu cầu và ngân sách, với thông tin rõ ràng và quy trình mua xe thuận tiện.', 'textarea'),
        'vision'         => array('Tầm nhìn', 'Trở thành địa chỉ đáng tin cậy để khám phá và sở hữu ô tô, nơi mỗi mẫu xe đều được giới thiệu minh bạch.', 'textarea'),
        'value1_title'   => array('Triết lý 1 – tiêu đề', 'Minh bạch', 'text'),
        'value1_text'    => array('Triết lý 1 – nội dung', 'Giá bán, thông số và chi phí sở hữu được trình bày rõ ràng để khách hàng yên tâm quyết định.', 'textarea'),
        'value2_title'   => array('Triết lý 2 – tiêu đề', 'Chọn lọc', 'text'),
        'value2_text'    => array('Triết lý 2 – nội dung', 'Mỗi mẫu xe được chọn lọc kỹ, ưu tiên chất lượng và mức độ phù hợp với người dùng.', 'textarea'),
        'value3_title'   => array('Triết lý 3 – tiêu đề', 'Đồng hành', 'text'),
        'value3_text'    => array('Triết lý 3 – nội dung', 'Tư vấn tận tâm trước, trong và sau khi mua xe, để mỗi hành trình đều trọn vẹn.', 'textarea'),
        'showroom_title' => array('Showroom – tiêu đề', 'Showroom HCMUS Auto', 'text'),
        'showroom_text'  => array('Showroom – giới thiệu', 'Không gian trưng bày để khách hàng xem xe, trao đổi cùng đội ngũ tư vấn và trải nghiệm trực tiếp trước khi quyết định.', 'textarea'),
        'showroom_image' => array('Showroom – ảnh (đường dẫn ảnh, lấy từ Thư viện media)', '', 'url'),
        'address'        => array('Địa chỉ showroom', '', 'text'),
        'hours'          => array('Giờ mở cửa', '', 'text'),
        'phone'          => array('Số điện thoại', '', 'text'),
        'email'          => array('Email', '', 'text'),
    );
}

function hcmus_auto_content() {
    $saved  = get_option('hcmus_auto_content', array());
    $values = array();
    if (!is_array($saved)) {
        $saved = array();
    }
    foreach (hcmus_auto_content_schema() as $key => $def) {
        $values[$key] = (isset($saved[$key]) && $saved[$key] !== '') ? $saved[$key] : $def[1];
    }
    return $values;
}

function hcmus_auto_content_sanitize($input) {
    $out = array();
    foreach (hcmus_auto_content_schema() as $key => $def) {
        $raw = (is_array($input) && isset($input[$key])) ? wp_unslash($input[$key]) : '';
        if ($def[2] === 'textarea') {
            $out[$key] = sanitize_textarea_field($raw);
        } elseif ($def[2] === 'url') {
            $out[$key] = esc_url_raw($raw);
        } else {
            $out[$key] = sanitize_text_field($raw);
        }
    }
    return $out;
}

/* ---------- Trang cài đặt ---------- */
function hcmus_auto_register_settings() {
    register_setting('hcmus_auto_cost_group', 'hcmus_auto_cost', array('sanitize_callback' => 'hcmus_auto_cost_sanitize'));
    register_setting('hcmus_auto_content_group', 'hcmus_auto_content', array('sanitize_callback' => 'hcmus_auto_content_sanitize'));
}
add_action('admin_init', 'hcmus_auto_register_settings');

function hcmus_auto_settings_menu() {
    add_options_page('HCMUS Auto – Chi phí', 'HCMUS Auto – Chi phí', 'manage_options', 'hcmus-auto-cost', 'hcmus_auto_cost_page');
    add_options_page('HCMUS Auto – Nội dung', 'HCMUS Auto – Nội dung', 'manage_options', 'hcmus-auto-content', 'hcmus_auto_content_page');
}
add_action('admin_menu', 'hcmus_auto_settings_menu');

function hcmus_auto_cost_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    $values = hcmus_auto_cost_settings();
    ?>
    <div class="wrap">
        <h1>HCMUS Auto – Chi phí lăn bánh</h1>
        <p>Các mức thu dưới đây dùng cho trang Dự toán chi phí. Quy định có thể thay đổi, hãy đối chiếu với văn bản mới nhất rồi chỉnh tại đây.</p>
        <form method="post" action="options.php">
            <?php settings_fields('hcmus_auto_cost_group'); ?>
            <table class="form-table" role="presentation">
                <?php foreach (hcmus_auto_cost_labels() as $key => $label) : ?>
                    <tr>
                        <th scope="row"><label for="hcmus-cost-<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
                        <td><input type="<?php echo $key === 'ev_until' ? 'date' : 'number'; ?>" <?php echo $key === 'ev_until' ? '' : 'step="any" min="0"'; ?> class="regular-text" id="hcmus-cost-<?php echo esc_attr($key); ?>" name="hcmus_auto_cost[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($values[$key]); ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function hcmus_auto_content_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    $values = hcmus_auto_content();
    ?>
    <div class="wrap">
        <h1>HCMUS Auto – Nội dung trang chủ</h1>
        <p>Sửa nội dung giới thiệu (sứ mệnh, triết lý kinh doanh, showroom) hiển thị ở trang chủ. Các ô thông tin liên hệ để trống sẽ không hiện ra.</p>
        <form method="post" action="options.php">
            <?php settings_fields('hcmus_auto_content_group'); ?>
            <table class="form-table" role="presentation">
                <?php foreach (hcmus_auto_content_schema() as $key => $def) : ?>
                    <tr>
                        <th scope="row"><label for="hcmus-content-<?php echo esc_attr($key); ?>"><?php echo esc_html($def[0]); ?></label></th>
                        <td>
                            <?php if ($def[2] === 'textarea') : ?>
                                <textarea class="large-text" rows="3" id="hcmus-content-<?php echo esc_attr($key); ?>" name="hcmus_auto_content[<?php echo esc_attr($key); ?>]"><?php echo esc_textarea($values[$key]); ?></textarea>
                            <?php else : ?>
                                <input type="<?php echo $def[2] === 'url' ? 'url' : 'text'; ?>" class="large-text" id="hcmus-content-<?php echo esc_attr($key); ?>" name="hcmus_auto_content[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($values[$key]); ?>">
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
