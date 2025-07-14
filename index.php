<?php
/**
 * Plugin Name: Publicacion Emergente
 * Description: Displays the latest post marked "Show this post in popup?" in a popup on the homepage.
 * Version: 1.0
 * Author: Sebastian Rodriguez
 */

if (!defined('ABSPATH')) exit;

// Register meta box in post editor
add_action('add_meta_boxes', function () {
    add_meta_box('popup_post_meta', 'Popup Settings', function ($post) {
        $checked = get_post_meta($post->ID, '_show_in_popup', true) ? 'checked' : '';
        echo '<label><input type="checkbox" name="show_in_popup" value="1" ' . $checked . '> Mostrar en la ventana emergente</label>';
    }, 'post', 'side');
});

// Save meta box value
add_action('save_post', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['show_in_popup'])) {
        update_post_meta($post_id, '_show_in_popup', '1');
    } else {
        delete_post_meta($post_id, '_show_in_popup');
    }
});

// Enqueue script and popup output in footer
add_action('wp_footer', function () {
    if (!is_front_page()) return;

    // Query latest post with meta key
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
        <div id="custom-popup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); z-index:9999; background:white; max-width:90%; width:600px; box-shadow:0 10px 30px rgba(0,0,0,0.3); border-radius:10px; overflow:hidden;">
            <div style="padding:2rem; background-color:#0073aa; color:white; text-align:center;">
                <h1 style="margin:0; font-size:1.5rem;">Informes Institucionales</h1>
            </div>
            <div style="padding:1.5rem; text-align:center;">
                <h1 style="font-size:clamp(1.5rem,5vw,2.5rem); margin:1rem 0; text-shadow:1px 1px 3px rgba(0,0,0,0.5);"><?php echo esc_html($title); ?></h1>
                <p style="color:#333; margin:1em 0;"><?php echo esc_html($excerpt); ?></p>
                <a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener" style="display:inline-block; padding:0.8rem 2rem; background:#0073aa; color:#fff; text-decoration:none; border-radius:50px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Leer más</a>
            </div>
            <button id="close-popup" style="position:absolute; top:10px; right:10px; background:none; border:none; font-size:1.5rem; cursor:pointer;">✖</button>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (!localStorage.getItem('popupShown')) {
                    setTimeout(function () {
                        const popup = document.getElementById('custom-popup');
                        const closeBtn = document.getElementById('close-popup');
                        if (popup) popup.style.display = 'block';

                        closeBtn.addEventListener('click', function () {
                            popup.style.display = 'none';
                            localStorage.setItem('popupShown', 'true');
                        });
                    }, 2000);
                }
            });
        </script>
        <?php
    }
});
