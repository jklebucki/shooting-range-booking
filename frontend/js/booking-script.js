jQuery(document).ready(function ($) {
    function showModal(message) {
        var modalHtml = `
            <div class="srbs-modal-overlay">
                <div class="srbs-modal">
                    <p>${message}</p>
                    <button class="srbs-modal-close">OK</button>
                </div>
            </div>
        `;
        $("body").append(modalHtml);
        $(".srbs-modal-close").on("click", function () {
            $(".srbs-modal-overlay").remove();
        });
    }

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
                    showModal(response.data || "Wystąpił błąd podczas dodawania rezerwacji.");
                    location.reload(); // Refresh the page to show the updated booking status
                }
            },
            error: function () {
                showModal("Wystąpił błąd podczas komunikacji z serwerem.");
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
                    showModal(response.data || "Wystąpił błąd podczas anulowania rezerwacji.");
                }
            },
            error: function () {
                showModal("Wystąpił błąd podczas komunikacji z serwerem.");
            }
        });
    });
});
