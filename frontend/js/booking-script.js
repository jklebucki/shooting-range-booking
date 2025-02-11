jQuery(document).ready(function ($) {
    function loadBookingTable() {
        $.ajax({
            url: srbs_ajax.ajaxurl,
            type: "POST",
            data: {
                action: "load_booking_table",
                security: srbs_ajax.nonce,
                _timestamp: new Date().getTime()
            },
            success: function (response) {
                if (response.success) {
                    $("#srbs-booking-table-container").html(response.data);
                    addEventHandlers(); // Add event handlers after loading the table
                } else {
                    $("#srbs-booking-table-container").html("<p>Wystąpił błąd podczas ładowania tabeli rezerwacji.</p>");
                }
            },
            error: function () {
                $("#srbs-booking-table-container").html("<p>Wystąpił błąd podczas komunikacji z serwerem.</p>");
            }
        });
    }

    function addEventHandlers() {
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
                        loadBookingTable(); // Reload the table to show the updated booking status
                    } else {
                        showModal(response.data || "Wystąpił błąd podczas dodawania rezerwacji.");
                        loadBookingTable(); // Reload the table to show the updated booking status
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
                        loadBookingTable(); // Reload the table to show the updated booking status
                    } else {
                        showModal(response.data || "Wystąpił błąd podczas anulowania rezerwacji.");
                    }
                },
                error: function () {
                    showModal("Wystąpił błąd podczas komunikacji z serwerem.");
                }
            });
        });
    }

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

    loadBookingTable(); // Initial load of the booking table
});
