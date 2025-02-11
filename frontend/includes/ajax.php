<?php
if (!defined('ABSPATH')) {
    exit;
}

// ✅ Obsługa AJAX dla rezerwacji użytkownika
add_action('wp_ajax_make_booking', 'srbs_make_booking');

function srbs_make_booking()
{
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

    // Sprawdzenie, czy slot jest już zajęty
    $stand_number = $dynamic ? 0 : intval($_POST['stand_number']);

    if ($stand_number > 0) {
        $slot_booked = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $bookings_table
        WHERE date = %s AND time_slot = %s AND stand_number = %d
    ", $date, $time_slot, $stand_number));

        if ($slot_booked > 0) {
            wp_send_json_error("Wybrany slot jest już zajęty.");
        }
    }

    // Sprawdzenie, czy liczba miejsc dla dynamicznego strzelania nie została przekroczona
    if ($stand_number == 0) {
        $dynamic_slots = srbs_get_setting('max_dynamic_slots');
        $dynamic_slots = $dynamic_slots ? intval($dynamic_slots) : 5;
        $dynamic_bookings_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $bookings_table
            WHERE date = %s AND time_slot = %s AND stand_number = 0
        ", $date, $time_slot));

        if ($dynamic_bookings_count >= $dynamic_slots) {
            wp_send_json_error("Wszystkie miejsca dla dynamicznego strzelania są już zajęte.");
        }
    }

    // Wstawienie rezerwacji z wartością 0 dla dynamicznego strzelania
    $wpdb->insert($bookings_table, [
        'user_id' => $user_id,
        'club_number' => $club_number,
        'date' => $date,
        'time_slot' => $time_slot,
        'stand_number' => $stand_number,
        'booking_type' => $booking_type
    ]);

    wp_send_json_success("Rezerwacja dodana.");
}

// ✅ Obsługa AJAX dla anulowania rezerwacji
add_action('wp_ajax_cancel_booking', 'srbs_cancel_booking');

function srbs_cancel_booking()
{
    check_ajax_referer('srbs_nonce', 'security');

    if (!is_user_logged_in()) {
        wp_send_json_error("Musisz być zalogowany.");
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $booking_id = intval($_POST['booking_id']);

    // Sprawdzenie, czy rezerwacja należy do zalogowanego użytkownika
    $bookings_table = $wpdb->prefix . 'srbs_bookings';
    $booking = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM $bookings_table WHERE id = %d AND user_id = %d
    ", $booking_id, $user_id));

    if (!$booking) {
        wp_send_json_error("Nie znaleziono rezerwacji lub nie masz uprawnień do jej anulowania.");
    }

    // Usunięcie rezerwacji
    $wpdb->delete($bookings_table, ['id' => $booking_id]);

    wp_send_json_success("Rezerwacja anulowana.");
}

function srbs_is_slot_booked($bookings, $stand_number, $time_slot)
{
    foreach ($bookings as $booking) {
        if ($booking->time_slot == $time_slot && ($booking->stand_number == $stand_number || ($booking->booking_type == 'dynamic' && $booking->stand_number == 0))) {
            return $booking;
        }
    }
    return false;
}

// ✅ Obsługa AJAX dla ładowania tabeli rezerwacji
add_action('wp_ajax_load_booking_table', 'srbs_load_booking_table');

function srbs_load_booking_table()
{
    check_ajax_referer('srbs_nonce', 'security');

    if (!is_user_logged_in()) {
        wp_send_json_error("Musisz być zalogowany.");
    }

    global $wpdb;
    $next_reservation_date = srbs_get_setting('next_reservation_date');
    $bookings_table = $wpdb->prefix . 'srbs_bookings';
    $bookings = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM $bookings_table WHERE date = %s
    ", $next_reservation_date));

    $dynamic_slots = srbs_get_setting('max_dynamic_slots');
    $dynamic_slots = $dynamic_slots ? intval($dynamic_slots) : 5;

    ob_start();
    include plugin_dir_path(dirname(__FILE__)) . 'templates/booking-table.php';
    $table_html = ob_get_clean();

    wp_send_json_success($table_html);
}
