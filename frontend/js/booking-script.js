function refreshBookingTable() {
    $.ajax({
        url: srbs_ajax.ajaxurl,
        type: "POST",
        data: {
            action: "srbs_get_bookings",
            _timestamp: new Date().getTime()
        },
        success: function(response) {
            if (response.success) {
                $(".srbs-booking-table").html(response.data); // Podmiana tabeli rezerwacji
            }
        }
    });
}

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
                    security: srbs_ajax.nonce,
                    _timestamp: new Date().getTime()
                },
                success: function (response) {
                    if (response.success) {
                        refreshBookingTable();
                        alert("Rezerwacja została pomyślnie dodana.");
                        //location.reload();
                    } else {
                        alert(response.data || "Wystąpił błąd.");
                    }
                }
            });
        }
    });
});
