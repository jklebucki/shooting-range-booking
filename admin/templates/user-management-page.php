<?php
if (!current_user_can('manage_options')) {
    wp_die(__('Brak uprawnień.'));
}

global $wpdb;
$users = get_users();

$sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'ID';
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'asc' : 'desc';

usort($users, function ($a, $b) use ($sort_by, $order) {
    if ($sort_by === 'club_number') {
        $a_meta = get_user_meta($a->ID, 'club_number', true);
        $b_meta = get_user_meta($b->ID, 'club_number', true);
        if ($a_meta == $b_meta) {
            return 0;
        }
        return ($order === 'asc' ? ($a_meta < $b_meta) : ($a_meta > $b_meta)) ? -1 : 1;
    } else {
        if ($a->$sort_by == $b->$sort_by) {
            return 0;
        }
        return ($order === 'asc' ? ($a->$sort_by < $b->$sort_by) : ($a->$sort_by > $b->$sort_by)) ? -1 : 1;
    }
});
?>
<div class="wrap srbs-admin">
    <h1>Zarządzanie Użytkownikami</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th class="sortable-column" data-sort="ID" style="color: white;">ID</th>
                <th class="sortable-column" data-sort="user_login" style="color: white;">Użytkownik</th>
                <th class="sortable-column" data-sort="user_email" style="color: white;">Email</th>
                <th class="sortable-column" data-sort="first_name" style="color: white;">Imię</th>
                <th class="sortable-column" data-sort="last_name" style="color: white;">Nazwisko</th>
                <th class="sortable-column" data-sort="club_number" style="color: white;">Numer Klubowy</th>
                <th style="color: white;">Role</th>
                <th style="color: white;">Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo esc_html($user->ID); ?></td>
                    <td><?php echo esc_html($user->user_login); ?></td>
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