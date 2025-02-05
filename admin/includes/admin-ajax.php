<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_delete_booking', 'srbs_delete_booking');

function srbs_delete_booking()
{
    // Sprawdzenie nonce dla bezpieczeństwa
    check_ajax_referer('srbs_nonce', 'security');

    // Sprawdzenie uprawnień użytkownika
    if (!current_user_can('manage_options')) {
        wp_send_json_error("Brak uprawnień.");
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'srbs_bookings';
    $booking_id = intval($_POST['booking_id']);

    // Usunięcie rezerwacji na podstawie ID
    $result = $wpdb->delete($table_name, ['id' => $booking_id]);

    if ($result) {
        wp_send_json_success("Rezerwacja została usunięta.");
    } else {
        wp_send_json_error("Nie udało się usunąć rezerwacji.");
    }
}
