<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_user_id = get_current_user_id();
?>

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
                <td data-label="Godzina"><?php echo $time_slot; ?></td>
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <td data-label="St. <?php echo $i; ?>">
                        <?php
                        $booking = srbs_is_slot_booked($bookings, $i, $time_slot);
                        if ($booking): ?>
                            <span class="badge">#<?php echo esc_html($booking->club_number); ?> 
                                <?php if ($booking->user_id == $current_user_id): ?>
                                    <button class="srbs-cancel-booking" data-booking-id="<?php echo $booking->id; ?>">x</button>
                                <?php endif; ?>
                            </span>
                        <?php else: ?>
                            <button class="srbs-book-slot" data-stand="<?php echo $i; ?>" data-time="<?php echo $time_slot; ?>">Rezerwuj</button>
                        <?php endif; ?>
                    </td>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td data-label="Godzina">19:00-20:00</td>
            <td colspan="8">
                <?php
                $dynamic_bookings = array_filter($bookings, function ($booking) {
                    return $booking->time_slot == '19:00-20:00' && $booking->booking_type == 'dynamic';
                });

                if (count($dynamic_bookings) >= $dynamic_slots): ?>
                    <span>Wszystkie miejsca zajęte</span>
                <?php else: ?>
                    <button class="srbs-book-slot" style="margin-bottom: 3px !important;" data-time="19:00-20:00" data-dynamic="true">Rezerwuj</button>
                <?php endif; ?>

                <?php if (!empty($dynamic_bookings)): ?>
                    <strong>Uczestnicy:</strong>
                    <?php foreach ($dynamic_bookings as $booking): ?>
                        <span class="badge">#<?php echo esc_html($booking->club_number); ?> 
                            <?php if ($booking->user_id == $current_user_id): ?>
                                <button class="srbs-cancel-booking" data-booking-id="<?php echo $booking->id; ?>">x</button>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span>Brak zapisanych uczestników.</span>
                <?php endif; ?>
            </td>
        </tr>
    </tbody>
</table>
