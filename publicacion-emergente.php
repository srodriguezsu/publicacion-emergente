<?php
/**
 * Plugin Name: Publicacion Emergente
 * Description: Muestra la última entrada marcada como "Mostrar en la ventana emergente" en una ventana emergente en la página de inicio.
 * Version: 1.2
 * Author: Sebastian Rodriguez
 */

if (!defined('ABSPATH')) exit;

// Add meta box to posts
add_action('add_meta_boxes', function () {
    add_meta_box('popup_post_meta', 'Ventana Emergente', function ($post) {
        $checked = get_post_meta($post->ID, '_show_in_popup', true) ? 'checked' : '';
        echo '<label><input type="checkbox" name="show_in_popup" value="1" ' . $checked . '> Mostrar en la ventana emergente</label>';
    }, 'post', 'side');
});

// Save checkbox value
add_action('save_post', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (get_post_type($post_id) !== 'post') return;

    if (isset($_POST['show_in_popup'])) {
        // Set current post as selected
        update_post_meta($post_id, '_show_in_popup', '1');

        // Unset other posts that were previously marked
        $args = [
            'post_type'      => 'post',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'post__not_in'   => [$post_id],
            'meta_key'       => '_show_in_popup',
            'meta_value'     => '1'
        ];
        $query = new WP_Query($args);
        foreach ($query->posts as $post) {
            delete_post_meta($post->ID, '_show_in_popup');
        }
    } else {
        // Allow disabling popup for this post
        delete_post_meta($post_id, '_show_in_popup');
    }
});


// Register plugin settings
add_action('admin_init', function () {
    register_setting('popup_options_group', 'popup_settings');
    add_settings_section('popup_main', 'Ajustes de la Ventana Emergente', null, 'popup-settings');

    add_settings_field('popup_heading', 'Título del popup', function () {
        $val = get_option('popup_settings')['popup_heading'] ?? 'You might like';
        echo "<input type='text' name='popup_settings[popup_heading]' value='" . esc_attr($val) . "' style='width:300px'>";
    }, 'popup-settings', 'popup_main');

    add_settings_field('popup_primary_color', 'Color primario', function () {
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

    add_settings_field('popup_frequency', 'Mostrar de nuevo después de (minutos)', function () {
        $val = get_option('popup_settings')['popup_frequency'] ?? '60';
        echo "<input type='number' name='popup_settings[popup_frequency]' value='$val' min='1'>";
    }, 'popup-settings', 'popup_main');
});

// Add top-level admin menu
add_action('admin_menu', function () {
    add_menu_page(
        'Ventana Emergente',
        'Publicación Emergente',
        'manage_options',
        'popup-settings',
        function () {
            ?>
            <div class="wrap">
                <h1>Configuración de Publicación Emergente</h1>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('popup_options_group');
                    do_settings_sections('popup-settings');
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        },
        'dashicons-format-aside'
    );
});

// Inject popup in footer
add_action('wp_footer', function () {
    if (!is_front_page()) return;

    $settings = get_option('popup_settings');
    $primary = esc_attr($settings['popup_primary_color'] ?? '#0073aa');
    $text    = esc_attr($settings['popup_text_color'] ?? '#ffffff');
    $bg      = esc_attr($settings['popup_bg_color'] ?? '#ffffff');
    $radius  = intval($settings['popup_radius'] ?? 10);
    $minutes = intval($settings['popup_frequency'] ?? 60);
    $heading = esc_html($settings['popup_heading'] ?? 'You might like');

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
                <h1 style="margin:0; font-size:1.5rem;"><?php echo $heading; ?></h1>
            </div>
            <div style="padding:1.5rem; text-align:center;">
                <h1 style="font-size:clamp(1.5rem,5vw,2.5rem); margin:1rem 0; text-shadow:1px 1px 3px rgba(0,0,0,0.5);"><?php echo esc_html($title); ?></h1>
                <p style="color:#333; margin:1em 0;"><?php echo esc_html($excerpt); ?></p>
                <a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener" style="display:inline-block; padding:0.8rem 2rem; background:<?php echo $primary; ?>; color:<?php echo $text; ?>; text-decoration:none; border-radius:50px; font-weight:600; letter-spacing:0.5px;">Leer más</a>
            </div>
            <button id="close-popup" style="position:absolute; top:10px; right:10px; background:none; border:none; font-size:1.5rem; cursor:pointer;">✖</button>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const lastShown = localStorage.getItem('popupLastShown');
                const now = Date.now();
                const minutes = <?php echo $minutes; ?>;
                const msInterval = minutes * 60 * 1000;

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
