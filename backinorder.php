<?php
/**
 * Plugin Name: Back In Stock Notificări + Overlay
 * Description: Notificări automate pentru produse care revin în stoc. Include formular OOS și overlay la vizita pe pagina de produs epuizat (o singură dată pe 55 zile).
 * Version: 1.2
 * Author: George Advertising
 */

if (! defined('ABSPATH')) {
    exit;
}

/* -------------------------------------------------------------------------- *
 * 1. CPT pentru abonamente
 * -------------------------------------------------------------------------- */
function register_backinstock_subscription_cpt() {
    $labels = [
        'name'               => 'Abonamente Notificare Stoc',
        'singular_name'      => 'Abonament Notificare Stoc',
        'menu_name'          => 'Notificări Stoc',
        'add_new'            => 'Adaugă abonament',
        'add_new_item'       => 'Adaugă abonament notificare',
        'new_item'           => 'Abonament nou',
        'edit_item'          => 'Editează abonament',
        'view_item'          => 'Vezi abonament',
        'all_items'          => 'Toate abonamentele',
        'search_items'       => 'Caută abonamente',
        'not_found'          => 'Nu s-au găsit abonamente',
        'not_found_in_trash' => 'Nu s-au găsit în coș',
    ];
    $args = [
        'labels'       => $labels,
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'supports'     => ['title'],
    ];
    register_post_type('backinstock_sub', $args);
}
add_action('init', 'register_backinstock_subscription_cpt');
// 1. Înregistrează coloana „Produs” în lista de abonamente
add_filter( 'manage_backinstock_sub_posts_columns', 'bis_add_product_column' );
function bis_add_product_column( $columns ) {
    // Poziționează coloana după Title
    $new_columns = [];
    foreach ( $columns as $key => $label ) {
        $new_columns[ $key ] = $label;
        if ( 'title' === $key ) {
            $new_columns['backinstock_product'] = __( 'Produs', 'text-domain' );
        }
    }
    return $new_columns;
}

// 2. Populează coloana cu titlul și link-ul produsului
add_action( 'manage_backinstock_sub_posts_custom_column', 'bis_show_product_column', 10, 2 );
function bis_show_product_column( $column, $post_id ) {
    if ( 'backinstock_product' !== $column ) {
        return;
    }
    $product_id = get_post_meta( $post_id, 'backinstock_product_id', true );
    if ( $product_id && $product = wc_get_product( $product_id ) ) {
        // Titlul produsului și link către editare produs
        $edit_link = get_edit_post_link( $product_id );
        echo '<a href="' . esc_url( $edit_link ) . '">' . esc_html( $product->get_name() ) . '</a>';
    } else {
        echo '—';
    }
}

// 3. (Opțional) fă coloana sortabilă după produs
add_filter( 'manage_edit-backinstock_sub_sortable_columns', 'bis_make_product_column_sortable' );
function bis_make_product_column_sortable( $columns ) {
    $columns['backinstock_product'] = 'backinstock_product';
    return $columns;
}

add_action( 'pre_get_posts', 'bis_product_column_orderby' );
function bis_product_column_orderby( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( 'backinstock_product' === $query->get( 'orderby' ) ) {
        // Sortăm după meta value (ID-ul produsului)
        $query->set( 'meta_key', 'backinstock_product_id' );
        $query->set( 'orderby', 'meta_value_num' );
    }
}

/* -------------------------------------------------------------------------- *
 * 2. Shortcode pentru formular doar dacă produsul epuizat
 * -------------------------------------------------------------------------- */
function back_in_stock_shortcode($atts) {
    $atts = shortcode_atts(['product_id' => ''], $atts, 'back_in_stock');
    if (empty($atts['product_id']) && function_exists('is_product') && is_product()) {
        global $product;
        if ($product) {
            $atts['product_id'] = $product->get_id();
        }
    }
    if (empty($atts['product_id'])) {
        return '';
    }
    $product_obj = wc_get_product($atts['product_id']);
    if (! $product_obj || $product_obj->get_stock_status() === 'instock') {
        return '';
    }

    ob_start();
    ?>
    <div id="backinstock-form-wrapper">
      <form id="backinstock-form" method="post" action="" style="margin:20px 0;padding:15px;background:#f8f9fa;border:1px solid #dee2e6;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
        <div style="display:flex;align-items:center;margin-bottom:12px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:10px;"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
          <h3 style="margin:0;color:#495057;font-size:16px;">Notificare stoc</h3>
        </div>
        <input type="hidden" name="backinstock_product_id" value="<?php echo esc_attr($atts['product_id']); ?>">
        <label for="backinstock_email" style="display:block;margin-bottom:8px;font-weight:600;color:#495057;">Email notificare pentru când produsul revine în stoc:</label>
        <div style="position:relative;">
          <input type="email" name="backinstock_email" id="backinstock_email" placeholder="Introdu adresa ta de email" required style="padding:10px 10px 10px 40px;width:100%;max-width:300px;margin-bottom:12px;border:1px solid #ced4da;border-radius:4px;font-size:14px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
        </div>
        <button type="submit" name="backinstock_submit" style="display:flex;align-items:center;padding:8px 16px;cursor:pointer;background:#28a745;color:#fff;border:none;border-radius:4px;font-weight:500;transition:background-color .3s;">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
          Notifică-mă
        </button>
      </form>
    </div>
    <?php
    if (isset($_POST['backinstock_submit'], $_POST['backinstock_email'], $_POST['backinstock_product_id'])) {
        $email      = sanitize_email($_POST['backinstock_email']);
        $product_id = intval($_POST['backinstock_product_id']);
        $post_id    = wp_insert_post([
            'post_title'  => 'Abonament: ' . $email,
            'post_status' => 'publish',
            'post_type'   => 'backinstock_sub',
        ]);
        if ($post_id) {
            update_post_meta($post_id, 'backinstock_email', $email);
            update_post_meta($post_id, 'backinstock_product_id', $product_id);
            // email confirmare HTML
            $subject = 'Abonare notificare produs';
            $message = '<html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;"><div style="text-align:center;margin-bottom:20px;"><img src="https://natural.360software.ro/wp-content/uploads/2025/02/f9d506b3-3a68-438f-b8a9-72b5db40d4d2-400x151.png" alt="Logo" style="max-width:250px;"></div><p>Bună,</p><p>Mulțumim că te-ai abonat! Te vom notifica când producții revin în stoc.</p></body></html>';
            wp_mail($email, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
            echo '<p style="color:green;">Mulțumim! Te-ai abonat cu succes.</p>';
        } else {
            echo '<p style="color:red;">A apărut o eroare. Încearcă din nou.</p>';
        }
    }
    return ob_get_clean();
}
add_shortcode('back_in_stock', 'back_in_stock_shortcode');

/* -------------------------------------------------------------------------- *
 * 3. Notificare la revenirea în stoc
 * -------------------------------------------------------------------------- */
function notify_backinstock_subscribers($product_id, $stock_status, $product) {
    if ($stock_status !== 'instock') {
        return;
    }
    $subs = get_posts([
        'post_type'   => 'backinstock_sub',
        'meta_query' => [[
            'key'   => 'backinstock_product_id',
            'value' => $product_id,
        ]],
        'numberposts' => -1,
    ]);
    foreach ($subs as $sub) {
        $email = get_post_meta($sub->ID, 'backinstock_email', true);
        if ($email) {
            $link    = get_permalink($product_id);
            $subject = 'Produsul a revenit în stoc';
            $message = '<html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;"><div style="text-align:center;margin-bottom:20px;"><img src="https://natural.360software.ro/wp-content/uploads/2025/02/f9d506b3-3a68-438f-b8a9-72b5db40d4d2-400x151.png" alt="Logo" style="max-width:250px;"></div><p>Bună,</p><p>Produsul este acum <strong>în stoc</strong>!</p><p style="text-align:center;"><a href="'.esc_url($link).'" style="background:#8bd300;padding:10px 20px;color:#fff;text-decoration:none;border-radius:5px;">Vezi produsul</a></p></body></html>';
            wp_mail($email, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
        }
        wp_delete_post($sub->ID, true);
    }
}
add_action('woocommerce_product_set_stock_status', 'notify_backinstock_subscribers', 10, 3);

/* -------------------------------------------------------------------------- *
 * 4. Personalizează email expeditor
 * -------------------------------------------------------------------------- */
add_filter('wp_mail_from_name', fn($name) => 'webiste.com');
add_filter('wp_mail_from', fn($email) => 'notify@website.com');

/* -------------------------------------------------------------------------- *
 * 5. Overlay la checkout: afișează formular OOS într-un popup o singură dată / 55 zile
 * -------------------------------------------------------------------------- */
add_action('wp_footer', 'backinstock_enqueue_oos_overlay');
function backinstock_enqueue_oos_overlay() {
    if (! is_product()) {
        return;
    }
    global $product;
    if ($product->get_stock_status() === 'instock') {
        return;
    }
    if (isset($_COOKIE['backinstock_overlay_shown'])) {
        return;
    }
    ?>
    <style>
    #backinstock-oos-overlay {position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:9999;display:flex;align-items:center;justify-content:center;}
    #backinstock-oos-box {background:#fff;padding:30px;border-radius:8px;max-width:90%;width:400px;text-align:center;}
    #backinstock-oos-box button {margin-top:20px;padding:10px 20px;background:#28a745;color:#fff;border:none;border-radius:4px;cursor:pointer;}
    </style>
    <div id="backinstock-oos-overlay">
      <div id="backinstock-oos-box">
        <h2>Produsul este momentan epuizat</h2>
        <p>Te rugăm lasă email-ul și te vom notifica când revine în stoc.</p>
        <div style="margin:20px 0;">
          <?php echo do_shortcode('[back_in_stock]'); ?>
        </div>
        <button id="backinstock-oos-close">Am înțeles</button>
      </div>
    </div>
    <script>
    (function(){
      var btn = document.getElementById('backinstock-oos-close');
      btn.addEventListener('click', function(){
        var d = new Date();
        d.setTime(d.getTime() + (55*24*60*60*1000));
        document.cookie = 'backinstock_overlay_shown=1;expires='+d.toUTCString()+';path=/';
        var ov = document.getElementById('backinstock-oos-overlay');
        ov.parentNode.removeChild(ov);
      });
    })();
    </script>
    <?php
}
