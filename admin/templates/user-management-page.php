<?php
if (!current_user_can('manage_options')) {
    wp_die(__('Brak uprawnień.'));
}

global $wpdb;
$users = get_users();

?>
<div class="wrap">
    <h1>Zarządzanie Użytkownikami</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Imię</th>
                <th>Nazwisko</th>
                <th>Numer Klubowy</th>
                <th>Role</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo esc_html($user->ID); ?></td>
                    <td><?php echo esc_html($user->user_email); ?></td>
                    <td><?php echo esc_html($user->first_name); ?></td>
                    <td><?php echo esc_html($user->last_name); ?></td>
                    <td>
                        <input type="text" class="club-number-input" data-user-id="<?php echo esc_attr($user->ID); ?>" value="<?php echo esc_attr(get_user_meta($user->ID, 'club_number', true)); ?>">
                    </td>
                    <td><?php echo implode(', ', $user->roles); ?></td>
                    <td>
                        <?php if (in_array('shooter', $user->roles)): ?>
                            <button class="remove-shooter-role" data-user-id="<?php echo esc_attr($user->ID); ?>">Usuń rolę strzelca</button>
                        <?php else: ?>
                            <button class="add-shooter-role" data-user-id="<?php echo esc_attr($user->ID); ?>">Dodaj rolę strzelca</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>