<?php
if (!defined('ABSPATH')) {
    exit;
}

// ✅ Obsługa AJAX dla rezerwacji użytkownika
add_action('wp_ajax_make_booking', 'srbs_make_booking');

function srbs_make_booking() {
    check_ajax_referer('srbs_nonce', 'security');

    if (!is_user_logged_in()) {
        wp_send_json_error("Musisz być zalogowany.");
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $club_number = get_user_meta($user_id, 'club_number', true);
    $time_slot = sanitize_text_field($_POST['time_slot']);
    $dynamic = filter_var($_POST['dynamic'], FILTER_VALIDATE_BOOLEAN);
    $date = srbs_get_setting('next_reservation_date');

    $booking_type = $dynamic ? 'dynamic' : 'static';

    // Sprawdzenie, czy użytkownik już dokonał rezerwacji na ten typ strzelania
    $bookings_table = $wpdb->prefix . 'srbs_bookings';
    $existing_booking = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $bookings_table
        WHERE user_id = %d AND date = %s AND booking_type = %s
    ", $user_id, $date, $booking_type));

    if ($existing_booking > 0) {
        wp_send_json_error("Możesz dokonać tylko jednej rezerwacji na strzelanie $booking_type.");
    }

    // Wstawienie rezerwacji z wartością 0 dla dynamicznego strzelania
    $wpdb->insert($bookings_table, [
        'user_id' => $user_id,
        'club_number' => $club_number,
        'date' => $date,
        'time_slot' => $time_slot,
        'stand_number' => $dynamic ? 0 : intval($_POST['stand_number']),
        'booking_type' => $booking_type
    ]);

    wp_send_json_success("Rezerwacja dodana.");
}
