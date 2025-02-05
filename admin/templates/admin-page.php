<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'srbs_bookings';
$bookings = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date, time_slot");

?>
<div class="wrap">
    <h1>Rezerwacje Strzelnicy</h1>
    <p>Panel zarządzania rezerwacjami stanowisk strzeleckich.</p>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Użytkownik</th>
                <th>Numer Klubowy</th>
                <th>Data</th>
                <th>Godzina</th>
                <th>Stanowisko</th>
                <th>Typ</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?php echo $booking->id; ?></td>
                    <td><?php echo get_userdata($booking->user_id)->display_name; ?></td>
                    <td><?php echo esc_html($booking->club_number); ?></td>
                    <td><?php echo esc_html($booking->date); ?></td>
                    <td><?php echo esc_html($booking->time_slot); ?></td>
                    <td><?php echo esc_html($booking->stand_number); ?></td>
                    <td><?php echo esc_html(ucfirst($booking->booking_type)); ?></td>
                    <td>
                        <button class="button button-primary delete-booking" data-id="<?php echo $booking->id; ?>">Usuń</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
