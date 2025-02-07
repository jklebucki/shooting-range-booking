jQuery(document).ready(function ($) {
    $(".srbs-book-slot").on("click", function () {
        var standNumber = $(this).data("stand");
        var timeSlot = $(this).data("time");
        var isDynamic = $(this).data("dynamic") || false;

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
                    location.reload();
                } else {
                    alert(response.data || "Wystąpił błąd podczas dodawania rezerwacji.");
                }
            },
            error: function () {
                alert("Wystąpił błąd podczas komunikacji z serwerem.");
            }
        });
    });

    $(".srbs-cancel-booking").on("click", function () {
        var bookingId = $(this).data("booking-id");

        $.ajax({
            url: srbs_ajax.ajaxurl,
            type: "POST",
            data: {
                action: "cancel_booking",
                booking_id: bookingId,
                security: srbs_ajax.nonce,
                _timestamp: new Date().getTime()
            },
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || "Wystąpił błąd podczas anulowania rezerwacji.");
                }
            },
            error: function () {
                alert("Wystąpił błąd podczas komunikacji z serwerem.");
            }
        });
    });
});
