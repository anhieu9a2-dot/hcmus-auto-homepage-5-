<?php
/**
 * Mẫu trang HCMUS Auto (thay thế header/footer của giao diện).
 */
if (!defined('ABSPATH')) {
    exit;
}
$hcmus_view = hcmus_auto_view();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('hcmus-standalone'); ?>>
<?php
if (function_exists('wp_body_open')) {
    wp_body_open();
}
hcmus_auto_render_curtain();
hcmus_auto_render_header();
if ($hcmus_view !== 'woo' && function_exists('wc_print_notices')) {
    ob_start();
    wc_print_notices();
    $hcmus_notices = trim(ob_get_clean());
    if ($hcmus_notices !== '') {
        echo '<div class="hcmus-wrap hcmus-notices">' . $hcmus_notices . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
if ($hcmus_view === 'product') {
    echo hcmus_auto_render_product(); // phpcs:ignore WordPress.Security.EscapeOutput
} elseif ($hcmus_view === 'woo') {
    echo hcmus_auto_render_woo(); // phpcs:ignore WordPress.Security.EscapeOutput
} elseif ($hcmus_view === 'page') {
    foreach (hcmus_auto_page_shortcodes() as $hcmus_tag) {
        echo do_shortcode('[' . $hcmus_tag . ']'); // phpcs:ignore WordPress.Security.EscapeOutput
    }
} else {
    echo hcmus_auto_home_shortcode(); // phpcs:ignore WordPress.Security.EscapeOutput
}
hcmus_auto_render_footer();
wp_footer();
?>
</body>
</html>
