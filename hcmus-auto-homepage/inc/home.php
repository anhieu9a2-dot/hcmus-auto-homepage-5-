<?php
if (!defined('ABSPATH')) {
    exit;
}

/* ---------- Trang chủ giới thiệu: [hcmus_auto_home] ---------- */
function hcmus_auto_home_shortcode() {
    $c        = hcmus_auto_content();
    $cars_url = hcmus_auto_cars_url();
    $cost_url = hcmus_auto_cost_url();
    $values   = array(
        array($c['value1_title'], $c['value1_text']),
        array($c['value2_title'], $c['value2_text']),
        array($c['value3_title'], $c['value3_text']),
    );
    $has_info = ($c['address'] !== '' || $c['hours'] !== '' || $c['phone'] !== '' || $c['email'] !== '');

    ob_start();
    ?>
    <main class="hcmus-home">
        <section class="hcmus-hero">
            <div class="hcmus-hero__overlay"></div>
            <div class="hcmus-wrap hcmus-hero__content">
                <p class="hcmus-eyebrow">HCMUS AUTO · PREMIUM AUTOMOTIVE</p>
                <h1>KHẲNG ĐỊNH PHONG CÁCH<br><span>TRÊN MỌI HÀNH TRÌNH</span></h1>
                <p class="hcmus-hero__desc">
                    Khám phá những mẫu xe chất lượng, lựa chọn chiếc xe phù hợp với phong cách và nhu cầu của bạn.
                </p>
                <div class="hcmus-hero__actions">
                    <a class="hcmus-button hcmus-button--gold" href="<?php echo esc_url($cars_url); ?>">KHÁM PHÁ DÒNG XE <span aria-hidden="true">→</span></a>
                    <a class="hcmus-button hcmus-button--outline" href="<?php echo esc_url($cost_url); ?>">DỰ TOÁN CHI PHÍ <span aria-hidden="true">→</span></a>
                </div>
            </div>
            <div class="hcmus-hero__bottom">
                <span>DESIGNED FOR YOUR JOURNEY</span>
                <span>01 / HCMUS AUTO</span>
            </div>
        </section>

        <section class="hcmus-section hcmus-intro" id="muc-tieu">
            <div class="hcmus-wrap">
                <div class="hcmus-section-heading">
                    <div>
                        <p class="hcmus-eyebrow">OUR PURPOSE</p>
                        <h2>MỤC TIÊU &amp; SỨ MỆNH</h2>
                    </div>
                </div>
                <div class="hcmus-intro__grid">
                    <article class="hcmus-intro__card hcmus-reveal">
                        <span class="hcmus-intro__label">SỨ MỆNH</span>
                        <p><?php echo esc_html($c['mission']); ?></p>
                    </article>
                    <article class="hcmus-intro__card hcmus-reveal">
                        <span class="hcmus-intro__label">TẦM NHÌN</span>
                        <p><?php echo esc_html($c['vision']); ?></p>
                    </article>
                </div>
            </div>
        </section>

        <section class="hcmus-section hcmus-values" id="triet-ly">
            <div class="hcmus-wrap">
                <div class="hcmus-section-heading">
                    <div>
                        <p class="hcmus-eyebrow">OUR PHILOSOPHY</p>
                        <h2>TRIẾT LÝ KINH DOANH</h2>
                    </div>
                </div>
                <div class="hcmus-values__grid">
                    <?php foreach ($values as $i => $value) : ?>
                        <article class="hcmus-values__item hcmus-reveal">
                            <span class="hcmus-values__number"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                            <h3><?php echo esc_html(hcmus_auto_upper($value[0])); ?></h3>
                            <p><?php echo esc_html($value[1]); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="hcmus-section hcmus-showroom" id="showroom">
            <div class="hcmus-wrap hcmus-showroom__inner">
                <div class="hcmus-showroom__media hcmus-reveal"<?php echo $c['showroom_image'] !== '' ? ' style="background-image:url(' . esc_url($c['showroom_image']) . ')"' : ''; ?>>
                    <?php if ($c['showroom_image'] === '') : ?>
                        <span>SHOWROOM</span>
                    <?php endif; ?>
                </div>
                <div class="hcmus-showroom__content hcmus-reveal">
                    <p class="hcmus-eyebrow">VISIT US</p>
                    <h2><?php echo esc_html(hcmus_auto_upper($c['showroom_title'])); ?></h2>
                    <p><?php echo esc_html($c['showroom_text']); ?></p>
                    <?php if ($has_info) : ?>
                        <dl class="hcmus-showroom__info">
                            <?php if ($c['address'] !== '') : ?><dt>ĐỊA CHỈ</dt><dd><?php echo esc_html($c['address']); ?></dd><?php endif; ?>
                            <?php if ($c['hours'] !== '') : ?><dt>GIỜ MỞ CỬA</dt><dd><?php echo esc_html($c['hours']); ?></dd><?php endif; ?>
                            <?php if ($c['phone'] !== '') : ?><dt>ĐIỆN THOẠI</dt><dd><?php echo esc_html($c['phone']); ?></dd><?php endif; ?>
                            <?php if ($c['email'] !== '') : ?><dt>EMAIL</dt><dd><?php echo esc_html($c['email']); ?></dd><?php endif; ?>
                        </dl>
                    <?php endif; ?>
                    <a class="hcmus-text-link" href="<?php echo esc_url($cars_url); ?>">XEM CÁC MẪU XE <span aria-hidden="true">↗</span></a>
                </div>
            </div>
        </section>

        <section class="hcmus-contact-strip">
            <div class="hcmus-wrap hcmus-contact-strip__inner">
                <div>
                    <p class="hcmus-eyebrow">YOUR NEXT JOURNEY STARTS HERE</p>
                    <h2>SẴN SÀNG TÌM CHIẾC XE CỦA BẠN?</h2>
                </div>
                <a class="hcmus-button hcmus-button--gold" href="<?php echo esc_url($cars_url); ?>">XEM CÁC MẪU XE <span aria-hidden="true">→</span></a>
            </div>
        </section>
    </main>
    <?php
    return ob_get_clean();
}
add_shortcode('hcmus_auto_home', 'hcmus_auto_home_shortcode');
