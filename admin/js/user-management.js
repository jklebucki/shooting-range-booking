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

    // Sorting functionality
    document.querySelectorAll(".sortable-column").forEach(column => {
        column.addEventListener("click", function() {
            const sort_by = this.getAttribute("data-sort");
            const current_order = new URLSearchParams(window.location.search).get("order");
            const order = current_order === "asc" ? "desc" : "asc";
            const url = new URL(window.location.href);
            url.searchParams.set("sort_by", sort_by);
            url.searchParams.set("order", order);
            window.location.href = url.toString();
        });
    });
});
