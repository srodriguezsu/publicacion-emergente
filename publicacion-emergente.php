<?php
/**
 * Plugin Name: Publicacion Emergente
 * Description: Displays the latest post marked "Mostrar en la ventana emergente" in a popup on the homepage.
 * Version: 1.1
 * Author: Sebastian Rodriguez
 */

if (!defined('ABSPATH')) exit;

// Add meta box
add_action('add_meta_boxes', function () {
    add_meta_box('popup_post_meta', 'Ventana Emergente', function ($post) {
        $checked = get_post_meta($post->ID, '_show_in_popup', true) ? 'checked' : '';
        echo '<label><input type="checkbox" name="show_in_popup" value="1" ' . $checked . '> Mostrar en la ventana emergente</label>';
    }, 'post', 'side');
});

// Save meta box
add_action('save_post', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['show_in_popup'])) {
        update_post_meta($post_id, '_show_in_popup', '1');
    } else {
        delete_post_meta($post_id, '_show_in_popup');
    }
});

// Register settings
add_action('admin_init', function () {
    register_setting('popup_options_group', 'popup_settings');
    add_settings_section('popup_main', 'Ajustes de la Ventana Emergente', null, 'popup-settings');

    add_settings_field('popup_primary_color', 'Color Primario', function () {
        $val = get_option('popup_settings')['popup_primary_color'] ?? '#0073aa';
        echo "<input type='color' name='popup_settings[popup_primary_color]' value='$val'>";
    }, 'popup-settings', 'popup_main');

    add_settings_field('popup_text_color', 'Color del texto', function () {
        $val = get_option('popup_settings')['popup_text_color'] ?? '#ffffff';
        echo "<input type='color' name='popup_settings[popup_text_color]' value='$val'>";
    }, 'popup-settings', 'popup_main');

    add_settings_field('popup_bg_color', 'Color de fondo', function () {
        $val = get_option('popup_settings')['popup_bg_color'] ?? '#ffffff';
        echo "<input type='color' name='popup_settings[popup_bg_color]' value='$val'>";
    }, 'popup-settings', 'popup_main');

    add_settings_field('popup_radius', 'Borde redondeado (px)', function () {
        $val = get_option('popup_settings')['popup_radius'] ?? '10';
        echo "<input type='number' name='popup_settings[popup_radius]' value='$val' min='0'>";
    }, 'popup-settings', 'popup_main');

    add_settings_field('popup_frequency', 'Mostrar de nuevo después de (horas)', function () {
        $val = get_option('popup_settings')['popup_frequency'] ?? '24';
        echo "<input type='number' name='popup_settings[popup_frequency]' value='$val' min='1'>";
    }, 'popup-settings', 'popup_main');
});

// Add admin menu
add_action('admin_menu', function () {
    add_options_page('Ventana Emergente', 'Ventana Emergente', 'manage_options', 'popup-settings', function () {
        ?>
        <div class="wrap">
            <h1>Ajustes de la Ventana Emergente</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('popup_options_group');
                do_settings_sections('popup-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    });
});

// Frontend popup
add_action('wp_footer', function () {
    if (!is_front_page()) return;

    $settings = get_option('popup_settings');
    $primary = esc_attr($settings['popup_primary_color'] ?? '#0073aa');
    $text    = esc_attr($settings['popup_text_color'] ?? '#ffffff');
    $bg      = esc_attr($settings['popup_bg_color'] ?? '#ffffff');
    $radius  = intval($settings['popup_radius'] ?? 10);
    $hours   = intval($settings['popup_frequency'] ?? 24);

    $args = [
        'post_type'      => 'post',
        'posts_per_page' => 1,
        'meta_key'       => '_show_in_popup',
        'meta_value'     => '1',
        'orderby'        => 'date',
        'order'          => 'DESC'
    ];
    $popup_query = new WP_Query($args);

    if ($popup_query->have_posts()) {
        $popup_query->the_post();
        $title = get_the_title();
        $excerpt = get_the_excerpt();
        $link = get_permalink();
        wp_reset_postdata();
        ?>
        <div id="custom-popup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); z-index:9999; background:<?php echo $bg; ?>; max-width:90%; width:600px; box-shadow:0 10px 30px rgba(0,0,0,0.3); border-radius:<?php echo $radius; ?>px; overflow:hidden;">
            <div style="padding:2rem; background-color:<?php echo $primary; ?>; color:<?php echo $text; ?>; text-align:center;">
                <h1 style="margin:0; font-size:1.5rem;">Informes Institucionales</h1>
            </div>
            <div style="padding:1.5rem; text-align:center;">
                <h1 style="font-size:clamp(1.5rem,5vw,2.5rem); margin:1rem 0; text-shadow:1px 1px 3px rgba(0,0,0,0.5);"><?php echo esc_html($title); ?></h1>
                <p style="color:#333; margin:1em 0;"><?php echo esc_html($excerpt); ?></p>
                <a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener" style="display:inline-block; padding:0.8rem 2rem; background:<?php echo $primary; ?>; color:<?php echo $text; ?>; text-decoration:none; border-radius:50px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Leer más</a>
            </div>
            <button id="close-popup" style="position:absolute; top:10px; right:10px; background:none; border:none; font-size:1.5rem; cursor:pointer;">✖</button>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const lastShown = localStorage.getItem('popupLastShown');
                const now = Date.now();
                const hours = <?php echo $hours; ?>;
                const msInterval = hours * 60 * 60 * 1000;

                if (!lastShown || (now - parseInt(lastShown)) > msInterval) {
                    setTimeout(() => {
                        const popup = document.getElementById('custom-popup');
                        const closeBtn = document.getElementById('close-popup');
                        popup.style.display = 'block';

                        closeBtn.addEventListener('click', function () {
                            popup.style.display = 'none';
                            localStorage.setItem('popupLastShown', Date.now());
                        });
                    }, 2000);
                }
            });
        </script>
        <?php
    }
});
