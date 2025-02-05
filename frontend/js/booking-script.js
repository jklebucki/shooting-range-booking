jQuery(document).ready(function ($) {
    $(".srbs-book-slot").on("click", function () {
        var standNumber = $(this).data("stand");
        var timeSlot = $(this).data("time");
        var isDynamic = $(this).data("dynamic") || false;

        if (confirm("Czy na pewno chcesz zarezerwować to miejsce?")) {
            $.ajax({
                url: srbs_ajax.ajaxurl,
                type: "POST",
                data: {
                    action: "make_booking",
                    stand_number: standNumber,
                    time_slot: timeSlot,
                    dynamic: isDynamic,
                    security: srbs_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        alert("Rezerwacja została pomyślnie dodana.");
                        location.reload();
                    } else {
                        alert(response.data || "Wystąpił błąd.");
                    }
                }
            });
        }
    });
});
