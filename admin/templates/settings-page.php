<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$settings_table = $wpdb->prefix . 'srbs_settings';

// Pobierz bieżące ustawienia
$next_reservation_date = $wpdb->get_var("SELECT setting_value FROM $settings_table WHERE setting_key = 'next_reservation_date'");
$dynamic_slots = $wpdb->get_var("SELECT setting_value FROM $settings_table WHERE setting_key = 'max_dynamic_slots'");
$custom_message = $wpdb->get_var("SELECT setting_value FROM $settings_table WHERE setting_key = 'custom_message'");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_referer('srbs_save_settings');

    $next_reservation_date = sanitize_text_field($_POST['next_reservation_date']);
    $dynamic_slots = intval($_POST['dynamic_slots']);
    $custom_message = sanitize_textarea_field($_POST['custom_message']);

    $wpdb->replace($settings_table, ['setting_key' => 'next_reservation_date', 'setting_value' => $next_reservation_date]);
    $wpdb->replace($settings_table, ['setting_key' => 'max_dynamic_slots', 'setting_value' => $dynamic_slots]);
    $wpdb->replace($settings_table, ['setting_key' => 'custom_message', 'setting_value' => $custom_message]);

    echo '<div class="notice notice-success is-dismissible"><p>Ustawienia zostały zapisane.</p></div>';
}
?>

<div class="wrap">
    <h1>Ustawienia Systemu</h1>
    <form method="POST">
        <?php wp_nonce_field('srbs_save_settings'); ?>

        <table class="form-table">
            <tr>
                <th><label for="next_reservation_date">Data następnej rezerwacji:</label></th>
                <td><input type="date" id="next_reservation_date" name="next_reservation_date" value="<?php echo esc_attr($next_reservation_date); ?>" required></td>
            </tr>
            <tr>
                <th><label for="dynamic_slots">Maksymalna liczba miejsc na strzelanie dynamiczne:</label></th>
                <td><input type="number" id="dynamic_slots" name="dynamic_slots" value="<?php echo esc_attr($dynamic_slots); ?>" min="1" max="10" required></td>
            </tr>
            <tr>
                <th><label for="custom_message">Komunikat dla użytkowników:</label></th>
                <td>
                    <textarea id="custom_message" name="custom_message" rows="5" cols="50"><?php echo esc_textarea($custom_message); ?></textarea>
                </td>
            </tr>
        </table>

        <p class="submit">
            <input type="submit" class="button-primary" value="Zapisz ustawienia">
        </p>
    </form>
</div>
