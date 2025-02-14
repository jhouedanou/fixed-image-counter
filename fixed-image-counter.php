<?php
/*
Plugin Name: Fixed Image Counter
Description: Affiche une image fixe en bas à droite et compte les vues
Version: 3.0
Author: Big Five Abidjan
Author URI: https://bigfiveabidjan.com
Plugin URI: https://bigfiveabidjan.com/plugins/fixed-image-counter
*/

// Création de la table pour le compteur lors de l'activation
function fic_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fixed_image_counter';
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        count int DEFAULT 0,
        PRIMARY KEY  (id)
    )";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    $wpdb->insert($table_name, array('count' => 0));
}
register_activation_hook(__FILE__, 'fic_activate');

// Ajout du lien "Réglages" dans la liste des plugins
function fic_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=fixed-image-counter">' . __('Réglages') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'fic_add_settings_link');

// Ajout de l'icône pour le menu admin
function fic_admin_menu() {
    add_menu_page(
        'Fixed Image Counter', 
        'Fixed Image', 
        'manage_options', 
        'fixed-image-counter', 
        'fic_admin_page',
        'dashicons-qr',
        30
    );
}
add_action('admin_menu', 'fic_admin_menu');

// Page d'administration
function fic_admin_page() {
    wp_enqueue_media();
    ?>
    <div class="wrap">
        <h1>Fixed Image Counter</h1>
        
        <div class="welcome-panel">
            <div class="welcome-panel-content">
                <h2>Bienvenue sur Fixed Image Counter!</h2>
                <p class="about-description">Développé par Big Five Abidjan pour optimiser votre visibilité.</p>
                <div class="welcome-panel-column-container">
                    <div class="welcome-panel-column">
                        <h3>Pour commencer :</h3>
                        <ul>
                            <li>1. Cliquez sur "Choisir une image" pour uploader votre visuel</li>
                            <li>2. L'image apparaîtra automatiquement en bas à droite de votre site</li>
                            <li>3. Le compteur s'incrémente à chaque affichage</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Configuration de l'image</h2>
            <input type="hidden" id="fixed_image_id" name="fixed_image_id" value="<?php echo get_option('fic_image_id'); ?>">
            <input type="button" id="upload_image_button" class="button button-primary" value="Choisir une image">
            <div id="image_preview">
                <?php 
                $image_id = get_option('fic_image_id');
                if($image_id) {
                    echo wp_get_attachment_image($image_id, 'medium');
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}

// Script pour l'upload d'image
function fic_admin_scripts() {
    wp_enqueue_script('fic-admin', plugins_url('js/admin.js', __FILE__), array('jquery'), '1.0');
}
add_action('admin_enqueue_scripts', 'fic_admin_scripts');

// Sauvegarde de l'image via AJAX
function fic_save_image() {
    if(isset($_POST['image_id'])) {
        update_option('fic_image_id', intval($_POST['image_id']));
    }
    wp_die();
}
add_action('wp_ajax_save_fixed_image', 'fic_save_image');

// Affichage de l'image et incrémentation du compteur
function fic_display_image() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fixed_image_counter';
    
    // Incrémente le compteur
    $wpdb->query("UPDATE $table_name SET count = count + 1 WHERE id = 1");
    
    // Affiche l'image
    $image_id = get_option('fic_image_id');
    if($image_id) {
        $image_url = wp_get_attachment_url($image_id);
        echo '<div id="fixed-image-container">';
        echo '<img src="' . esc_url($image_url) . '" alt="Fixed Image">';
        echo '</div>';
    }
}
add_action('wp_footer', 'fic_display_image');

// Ajout des styles admin
function fic_admin_styles() {
    ?>
    <style>
        .welcome-panel {
            background: #f9f9f9;
            border-color: #2271b1;
            padding: 20px;
            margin: 20px 0;
        }
        .card {
            padding: 20px;
            margin-top: 20px;
        }
        #image_preview {
            margin-top: 20px;
        }
        #image_preview img {
            max-width: 200px;
            border: 2px solid #ddd;
            padding: 5px;
        }
    </style>
    <?php
}
add_action('admin_head', 'fic_admin_styles');

// Ajout du CSS frontend
function fic_add_styles() {
    ?>
    <style>
        #fixed-image-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }
        #fixed-image-container img {
            max-width: 150px;
            height: auto;
        }
    </style>
    <?php
}
add_action('wp_head', 'fic_add_styles');
