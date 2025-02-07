<?php
if (!defined('ABSPATH')) {
    exit;
}

//Wyłączenie cache dla strony rezerwacji
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Sprawdzenie, czy użytkownik jest zalogowany
if (!is_user_logged_in()) {
    // Przekierowanie do logowania i powrót na stronę rezerwacji po zalogowaniu
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// Sprawdzenie, czy użytkownik należy do właściwej grupy (np. 'shooter')
$current_user = wp_get_current_user();
$allowed_roles = ['shooter']; // Nazwa grupy użytkowników, która może rezerwować

$user_has_access = false;
foreach ($current_user->roles as $role) {
    if (in_array($role, $allowed_roles)) {
        $user_has_access = true;
        break;
    }
}

// Pobranie numeru klubowego użytkownika
global $wpdb;
$current_user_id = get_current_user_id();
$club_number = get_user_meta($current_user_id, 'club_number', true);

// Jeśli użytkownik nie ma wymaganej roli, wyświetl komunikat
if (!$user_has_access) {
    echo "<p style='color: red; font-size: 18px;'>Nie masz uprawnień do rezerwacji. Skontaktuj się z administratorem, aby dodał Cię do właściwej grupy.</p>";
    return;
}

// **NOWE ZABEZPIECZENIE** - Jeśli użytkownik nie ma numeru klubowego, nie może rezerwować
if (!$club_number) {
    echo "<p style='color: red; font-size: 18px;'>Nie masz przypisanego numeru klubowego. Uzupełnij go w swoim profilu lub skontaktuj się z administratorem.</p>";
    return;
}

$next_reservation_date = srbs_get_setting('next_reservation_date');
$dynamic_slots = srbs_get_setting('max_dynamic_slots');
$dynamic_slots = $dynamic_slots ? intval($dynamic_slots) : 5;
$custom_message = srbs_get_setting('custom_message');

if (!$next_reservation_date) {
    echo '<p>Rezerwacje są obecnie niedostępne. Skontaktuj się z administratorem.</p>';
    return;
}

// Pobierz istniejące rezerwacje
$bookings_table = $wpdb->prefix . 'srbs_bookings';
$bookings = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM $bookings_table WHERE date = %s
", $next_reservation_date));

function srbs_is_slot_booked($bookings, $stand_number, $time_slot)
{
    foreach ($bookings as $booking) {
        if ($booking->time_slot == $time_slot && ($booking->stand_number == $stand_number || ($booking->booking_type == 'dynamic' && $booking->stand_number == 0))) {
            return $booking->club_number;
        }
    }
    return false;
}
?>

<div class="srbs-booking-container">
    <h1>Rezerwacje na dzień <?php echo date_i18n('j F Y', strtotime($next_reservation_date)); ?> r.</h1>
    <p><?php echo esc_html($custom_message); ?></p>

    <table class="srbs-booking-table">
        <thead>
            <tr>
                <th>Godzina</th>
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <th>St. <?php echo $i; ?></th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $time_slots = ['17:00-18:00', '18:00-19:00'];
            foreach ($time_slots as $time_slot): ?>
                <tr>
                    <td><?php echo $time_slot; ?></td>
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <td>
                            <?php
                            $booked_club_number = srbs_is_slot_booked($bookings, $i, $time_slot);
                            if ($booked_club_number): ?>
                                <span>#<?php echo esc_html($booked_club_number); ?> </span>
                            <?php else: ?>
                                <button class="srbs-book-slot" data-stand="<?php echo $i; ?>" data-time="<?php echo $time_slot; ?>">Rezerwuj</button>
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td>19:00-20:00</td>
                <td colspan="8">
                    <?php
                    $dynamic_bookings = array_filter($bookings, function ($booking) {
                        return $booking->time_slot == '19:00-20:00' && $booking->booking_type == 'dynamic';
                    });

                    if (count($dynamic_bookings) >= $dynamic_slots): ?>
                        <span>Wszystkie miejsca zajęte</span>
                    <?php else: ?>
                        <button class="srbs-book-slot" data-time="19:00-20:00" data-dynamic="true">Rezerwuj miejsce</button>
                    <?php endif; ?>

                    <br>
                    <?php if (!empty($dynamic_bookings)): ?>
                        <strong>Uczestnicy:</strong>
                        <?php foreach ($dynamic_bookings as $booking): ?>
                            <span>Nr #<?php echo esc_html($booking->club_number); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span>Brak zapisanych uczestników.</span>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>