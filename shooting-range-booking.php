<?php

/**
 * Plugin Name: Shooting Range Booking System
 * Description: System rezerwacji stanowisk strzeleckich dla WordPress.
 * Version: 1.2
 * Author: Jarosław Kłębucki
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// ✅ Rejestracja menu administracyjnego
function srbs_admin_menu()
{
    add_menu_page(
        'Rezerwacje Strzelnicy',
        'Rezerwacje Strzelnicy',
        'manage_options',
        'srbs_admin',
        'srbs_admin_page',
        'dashicons-calendar-alt',
        25
    );

    add_submenu_page(
        'srbs_admin',
        'Ustawienia Systemu',
        'Ustawienia Systemu',
        'manage_options',
        'srbs_settings',
        'srbs_settings_page'
    );

    add_submenu_page(
        'srbs_admin',
        'Zarządzanie Użytkownikami',
        'Użytkownicy',
        'manage_options',
        'srbs_user_management',
        'srbs_user_management_page'
    );
}
add_action('admin_menu', 'srbs_admin_menu');

// ✅ Wyłączenie cache dla strony rezerwacji
function srbs_disable_cache() {
    if (is_page('rezerwacje-klub')) { // Podmień na właściwy slug strony
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }
}
add_action('send_headers', 'srbs_disable_cache');

// ✅ Funkcja dodająca rolę shooter przy aktywacji wtyczki
function srbs_add_shooter_role() {
    if (!get_role('shooter')) {
        add_role('shooter', 'Strzelec', [
            'read' => true, // Użytkownik może czytać posty
        ]);
    }
}

// Hook do aktywacji wtyczki
register_activation_hook(__FILE__, 'srbs_add_shooter_role');

// ✅ Funkcja renderująca stronę administracyjną
function srbs_admin_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('Nie masz uprawnień do tej strony.'));
    }

    include plugin_dir_path(__FILE__) . 'admin/templates/admin-page.php';
}

// ✅ Funkcja renderująca stronę ustawień systemu
function srbs_settings_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('Nie masz uprawnień do tej strony.'));
    }

    include plugin_dir_path(__FILE__) . 'admin/templates/settings-page.php';
}

function srbs_user_management_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Nie masz uprawnień do tej strony.'));
    }
    include plugin_dir_path(__FILE__) . 'admin/templates/user-management-page.php';
}

// ✅ Ładowanie plików CSS i JS dla panelu użytkowników
function srbs_enqueue_user_management_assets($hook)
{
    if (strpos($hook, 'srbs_user_management') === false) {
        return;
    }

    wp_enqueue_style('srbs-user-management-css', plugin_dir_url(__FILE__) . 'admin/css/user-management.css');
    wp_enqueue_script('srbs-user-management-js', plugin_dir_url(__FILE__) . 'admin/js/user-management.js', array('jquery'), null, true);

    wp_localize_script('srbs-user-management-js', 'srbs_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('srbs_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'srbs_enqueue_user_management_assets');

// ✅ Załadowanie obsługi AJAX
include plugin_dir_path(__FILE__) . 'admin/includes/user-management-ajax.php';

// ✅ Rejestracja shortcode dla interfejsu użytkownika
function srbs_booking_shortcode()
{
    ob_start();
    include plugin_dir_path(__FILE__) . 'frontend/templates/booking-page.php';
    return ob_get_clean();
}
add_shortcode('srbs_booking', 'srbs_booking_shortcode');

// ✅ Ładowanie stylów i skryptów dla użytkownika
function srbs_enqueue_assets()
{
    if (!is_admin()) {
        wp_enqueue_style('srbs-frontend-css', plugin_dir_url(__FILE__) . 'frontend/css/booking-style.css');
        wp_enqueue_script('srbs-frontend-js', plugin_dir_url(__FILE__) . 'frontend/js/booking-script.js', array('jquery'), null, true);
        wp_localize_script('srbs-frontend-js', 'srbs_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('srbs_nonce'),
        ));
    }
}
add_action('wp_enqueue_scripts', 'srbs_enqueue_assets');

// ✅ Ładowanie stylów i skryptów dla panelu administracyjnego
function srbs_enqueue_admin_assets($hook)
{
    // Sprawdź, czy jesteśmy na stronie wtyczki
    if (strpos($hook, 'srbs_admin') === false) {
        return;
    }

    wp_enqueue_style('srbs-admin-css', plugin_dir_url(__FILE__) . 'admin/css/admin-style.css'); // Import admin-style.css
    wp_enqueue_script('srbs-admin-js', plugin_dir_url(__FILE__) . 'admin/js/admin-script.js', array('jquery'), null, true);
    wp_localize_script('srbs-admin-js', 'srbs_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('srbs_nonce'),
    ));
}
add_action('admin_enqueue_scripts', 'srbs_enqueue_admin_assets');

// ✅ Obsługa AJAX dla użytkownika (przeniesiona do osobnego pliku)
include plugin_dir_path(__FILE__) . 'frontend/includes/ajax.php';

// ✅ Obsługa AJAX dla panelu administracyjnego (przeniesiona do osobnego pliku)
include plugin_dir_path(__FILE__) . 'admin/includes/admin-ajax.php';

// ✅ Funkcja pobierająca ustawienia systemu
function srbs_get_setting($key)
{
    global $wpdb;
    $settings_table = $wpdb->prefix . 'srbs_settings';
    $value = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $settings_table WHERE setting_key = %s", $key));
    return $value ? $value : null;
}

// ✅ Instalacja tabel w bazie danych
function srbs_install()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . 'srbs_bookings';
    $sql1 = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        club_number VARCHAR(255) NOT NULL,
        date DATE NOT NULL,
        time_slot VARCHAR(20) NOT NULL,
        stand_number INT NOT NULL,
        booking_type ENUM('static', 'dynamic') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    $table_settings = $wpdb->prefix . 'srbs_settings';
    $sql2 = "CREATE TABLE $table_settings (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(255) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
}
register_activation_hook(__FILE__, 'srbs_install');

// ✅ Dodanie pola "Numer klubowy" do profilu użytkownika
function srbs_add_club_number_field($user)
{
?>
    <h3>Dodatkowe informacje</h3>
    <table class="form-table">
        <tr>
            <th><label for="club_number">Numer klubowy</label></th>
            <td>
                <input type="text" name="club_number" id="club_number" value="<?php echo esc_attr(get_the_author_meta('club_number', $user->ID)); ?>" class="regular-text" />
                <p class="description">Numer klubowy użytkownika na potrzeby rezerwacji.</p>
            </td>
        </tr>
    </table>
<?php
}
add_action('show_user_profile', 'srbs_add_club_number_field');
add_action('edit_user_profile', 'srbs_add_club_number_field');

// ✅ Zapisanie pola "Numer klubowy"
function srbs_save_club_number_field($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    update_user_meta($user_id, 'club_number', sanitize_text_field($_POST['club_number']));
}
add_action('personal_options_update', 'srbs_save_club_number_field');
add_action('edit_user_profile_update', 'srbs_save_club_number_field');
