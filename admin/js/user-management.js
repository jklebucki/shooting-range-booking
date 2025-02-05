jQuery(document).ready(function ($) {
    $(".club-number-input").on("change", function () {
        var userId = $(this).data("user-id");
        var clubNumber = $(this).val();

        $.post(srbs_ajax.ajaxurl, {
            action: "srbs_update_club_number",
            security: srbs_ajax.nonce,
            user_id: userId,
            club_number: clubNumber
        }, function (response) {
            alert(response.data);
        });
    });

    $(".add-shooter-role, .remove-shooter-role").on("click", function () {
        var userId = $(this).data("user-id");

        $.post(srbs_ajax.ajaxurl, {
            action: "srbs_toggle_shooter_role",
            security: srbs_ajax.nonce,
            user_id: userId
        }, function (response) {
            alert(response.data);
            location.reload();
        });
    });
});
