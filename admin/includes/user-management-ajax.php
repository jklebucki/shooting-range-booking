<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_srbs_update_club_number', 'srbs_update_club_number');
function srbs_update_club_number()
{
    check_ajax_referer('srbs_nonce', 'security');

    $user_id = intval($_POST['user_id']);
    $club_number = sanitize_text_field($_POST['club_number']);

    update_user_meta($user_id, 'club_number', $club_number);

    wp_send_json_success("Numer klubowy zaktualizowany.");
}

add_action('wp_ajax_srbs_toggle_shooter_role', 'srbs_toggle_shooter_role');
function srbs_toggle_shooter_role()
{
    check_ajax_referer('srbs_nonce', 'security');

    $user_id = intval($_POST['user_id']);
    $user = new WP_User($user_id);

    if (in_array('shooter', $user->roles)) {
        $user->remove_role('shooter');
        wp_send_json_success("Rola strzelca usunięta.");
    } else {
        $user->add_role('shooter');
        wp_send_json_success("Rola strzelca dodana.");
    }
}
