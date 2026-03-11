<?php
/**
 * Plugin Name: Publicacion Emergente
 * Description: Muestra las últimas 5 entradas marcadas como "Mostrar en la ventana emergente" en una ventana emergente en la página de inicio.
 * Version: 1.8
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
        // Marcar el post actual como seleccionado
        update_post_meta($post_id, '_show_in_popup', '1');

        // Obtener los otros posts ya marcados (excluyendo el actual), ordenados por fecha ascendente (más antiguos primero)
        $args = [
                'post_type'      => 'post',
                'post_status'    => 'any',
                'posts_per_page' => -1,
                'post__not_in'   => [$post_id],
                'meta_key'       => '_show_in_popup',
                'meta_value'     => '1',
                'orderby'        => 'date',
                'order'          => 'ASC'
        ];
        $query = new WP_Query($args);

        // Si hay más de 4 (más el actual serían 5), desmarcar los más antiguos
        if (count($query->posts) > 4) {
            $posts_to_unset = array_slice($query->posts, 0, count($query->posts) - 4);
            foreach ($posts_to_unset as $post) {
                delete_post_meta($post->ID, '_show_in_popup');
            }
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

    add_settings_field('popup_header_color', 'Color del título', function () {
        $val = get_option('popup_settings')['popup_header_color'] ?? '#fff';
        echo "<input type='color' name='popup_settings[popup_header_color]' value='$val'>";
    }, 'popup-settings', 'popup_main');

    add_settings_field('popup_primary_color', 'Color primario', function () {
        $val = get_option('popup_settings')['popup_primary_color'] ?? '#0073aa';
        echo "<input type='color' name='popup_settings[popup_primary_color]' value='$val'>";
    }, 'popup-settings', 'popup_main');

    add_settings_field('popup_text_color', 'Color del texto', function () {
        $val = get_option('popup_settings')['popup_text_color'] ?? '#000';
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
    $contrast = esc_attr($settings['popup_header_color'] ?? '#ffffff');
    $text    = esc_attr($settings['popup_text_color'] ?? '#000');
    $bg      = esc_attr($settings['popup_bg_color'] ?? '#ffffff');
    $radius  = intval($settings['popup_radius'] ?? 10);
    $minutes = intval($settings['popup_frequency'] ?? 60);
    $heading = esc_html($settings['popup_heading'] ?? 'You might like');

    $args = [
            'post_type'      => 'post',
            'posts_per_page' => 5,
            'meta_key'       => '_show_in_popup',
            'meta_value'     => '1',
            'orderby'        => 'date',
            'order'          => 'DESC'
    ];
    $popup_query = new WP_Query($args);

    if ($popup_query->have_posts()) {
        $posts = [];
        while ($popup_query->have_posts()) {
            $popup_query->the_post();
            $posts[] = [
                    'title' => get_the_title(),
                    'excerpt' => get_the_excerpt(),
                    'link' => get_permalink()
            ];
        }
        wp_reset_postdata();

        $post_count = count($posts);
        ?>
        <div id="custom-popup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); z-index:9999; background:<?php echo $bg; ?>; max-width:90%; width:<?php echo $post_count === 1 ? '600px' : '700px'; ?>; max-height:90vh; box-shadow:0 10px 30px rgba(0,0,0,0.3); border-radius:<?php echo $radius; ?>px; overflow:hidden;">
            <div style="padding:2rem; background-color:<?php echo $primary; ?>; text-align:center;">
                <h1 style="margin:0; font-size:1.5rem; color:<?php echo $contrast; ?>;"><?php echo $heading; ?></h1>
            </div>

            <?php if ($post_count === 1): ?>
                <!-- Single post layout -->
                <div style="padding:1.5rem; text-align:center;">
                    <h2 style="font-size:clamp(1.5rem,5vw,2.5rem); margin:1rem 0; text-shadow:1px 1px 3px rgba(0,0,0,0.5); color:<?php echo $text; ?>;"><?php echo esc_html($posts[0]['title']); ?></h2>
                    <p style="color:<?php echo $text ?>; margin:1em 0;"><?php echo esc_html($posts[0]['excerpt']); ?></p>
                    <a href="<?php echo esc_url($posts[0]['link']); ?>" target="_blank" rel="noopener" style="display:inline-block; padding:0.8rem 2rem; background:<?php echo $primary; ?>; color:<?php echo $contrast; ?>; text-decoration:none; border-radius:<?php echo $radius; ?>px; font-weight:600; letter-spacing:0.5px;">Leer más</a>
                </div>
            <?php else: ?>
                <!-- Multiple posts layout -->
                <div style="padding:1.5rem 2rem; max-height:60vh; overflow-y:auto;">
                    <?php foreach ($posts as $index => $post): ?>
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:1.5rem; padding:1.25rem 0; <?php echo $index < $post_count - 1 ? 'border-bottom:1px solid rgba(0,0,0,0.1);' : ''; ?>">
                            <div style="flex:1; text-align:left;">
                                <h3 style="margin:0 0 0.5rem 0; font-size:1.25rem; color:<?php echo $text; ?>; line-height:1.4; font-weight:600;"><?php echo esc_html($post['title']); ?></h3>
                                <p style="color:<?php echo $text; ?>; margin:0; font-size:0.95rem; line-height:1.5; opacity:0.75;"><?php echo esc_html(wp_trim_words($post['excerpt'], 15)); ?></p>
                            </div>
                            <div style="flex-shrink:0;">
                                <a href="<?php echo esc_url($post['link']); ?>" target="_blank" rel="noopener" style="display:inline-block; padding:0.6rem 1.5rem; background:<?php echo $primary; ?>; color:<?php echo $contrast; ?>; text-decoration:none; border-radius:<?php echo max(5, $radius - 5); ?>px; font-weight:600; font-size:0.9rem; white-space:nowrap;">Leer más</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <button id="close-popup" style="position:absolute; top:10px; color:<?php echo $contrast; ?>; right:10px; background:none; border:none; font-size:1.5rem; cursor:pointer; z-index:1;">✖</button>
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
                        const overlay = document.createElement('div');
                        overlay.id = 'popup-overlay';
                        overlay.style.cssText = `
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background-color: rgba(0, 0, 0, 0.5);
                            z-index: 9998;
                        `;
                        document.body.appendChild(overlay);
                        popup.style.display = 'block';
                        document.body.style.overflow = 'hidden';

                        function closePopup() {
                            popup.style.display = 'none';
                            localStorage.setItem('popupLastShown', Date.now());
                            document.body.style.overflow = '';
                            document.getElementById('popup-overlay')?.remove();
                        }

                        document.getElementById('close-popup').addEventListener('click', closePopup);

                        // Close on overlay click
                        overlay.addEventListener('click', closePopup);
                    }, 2000);
                }
            });
        </script>

        <?php
    }
});