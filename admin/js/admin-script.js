jQuery(document).ready(function ($) {
    $(".delete-booking").on("click", function () {
        var bookingId = $(this).data("id");
        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                action: "delete_booking",
                booking_id: bookingId,
                security: srbs_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    //alert("Rezerwacja została usunięta.");
                    $(`.details-row[data-id='${bookingId}']`).remove();
                } else {
                    alert("Wystąpił błąd: " + response.data);
                }
            }
        });
    });
});
