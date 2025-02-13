<?php
if (!current_user_can('manage_options')) {
    wp_die(__('Brak uprawnień.'));
}

global $wpdb;
$users = get_users();

$sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'ID';
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc';

usort($users, function ($a, $b) use ($sort_by, $order) {
    if ($a->$sort_by == $b->$sort_by) {
        return 0;
    }
    return ($order === 'asc' ? ($a->$sort_by < $b->$sort_by) : ($a->$sort_by > $b->$sort_by)) ? -1 : 1;
});
?>
<div class="wrap srbs-admin">
    <h1>Zarządzanie Użytkownikami</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><a href="?sort_by=ID&order=<?php echo $sort_by === 'ID' && $order === 'asc' ? 'desc' : 'asc'; ?>">ID</a></th>
                <th><a href="?sort_by=user_login&order=<?php echo $sort_by === 'user_login' && $order === 'asc' ? 'desc' : 'asc'; ?>">Użytkownik</a></th>
                <th><a href="?sort_by=user_email&order=<?php echo $sort_by === 'user_email' && $order === 'asc' ? 'desc' : 'asc'; ?>">Email</a></th>
                <th><a href="?sort_by=first_name&order=<?php echo $sort_by === 'first_name' && $order === 'asc' ? 'desc' : 'asc'; ?>">Imię</a></th>
                <th><a href="?sort_by=last_name&order=<?php echo $sort_by === 'last_name' && $order === 'asc' ? 'desc' : 'asc'; ?>">Nazwisko</a></th>
                <th>Numer Klubowy</th>
                <th>Role</th>
                <th>Akcje</th>
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