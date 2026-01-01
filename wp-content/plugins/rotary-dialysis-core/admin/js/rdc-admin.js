/**
 * Rotary Dialysis Core - Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize on page load
        initAvailabilityModal();
    });

    /**
     * Availability Modal Handler
     */
    function initAvailabilityModal() {
        // Edit button click
        $(document).on('click', '.rdc-edit-availability', function() {
            var $btn = $(this);
            var $modal = $('#rdc-availability-modal');

            $('#rdc-modal-title').text('Update: ' + $btn.data('store-name'));
            $('#rdc-modal-store-id').val($btn.data('store-id'));
            $('#rdc-modal-total-beds').val($btn.data('total-beds'));
            $('#rdc-modal-available-beds').val($btn.data('available-beds'));

            $modal.show();
        });

        // Close modal
        $(document).on('click', '.rdc-modal-close', function() {
            $(this).closest('.rdc-modal').hide();
        });

        // Close on backdrop click
        $(document).on('click', '.rdc-modal', function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });

        // Close on escape
        $(document).on('keyup', function(e) {
            if (e.key === 'Escape') {
                $('.rdc-modal').hide();
            }
        });

        // Validate available <= total
        $('#rdc-modal-available-beds').on('change', function() {
            var available = parseInt($(this).val()) || 0;
            var total = parseInt($('#rdc-modal-total-beds').val()) || 0;

            if (available > total) {
                $(this).val(total);
            }
        });

        $('#rdc-modal-total-beds').on('change', function() {
            var total = parseInt($(this).val()) || 0;
            var available = parseInt($('#rdc-modal-available-beds').val()) || 0;

            if (available > total) {
                $('#rdc-modal-available-beds').val(total);
            }
        });
    }

    /**
     * AJAX helper for REST API calls
     */
    window.rdcApi = {
        call: function(endpoint, method, data) {
            return $.ajax({
                url: rdcAdmin.restUrl + endpoint,
                method: method || 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', rdcAdmin.nonce);
                },
                data: data
            });
        },

        get: function(endpoint, data) {
            return this.call(endpoint, 'GET', data);
        },

        post: function(endpoint, data) {
            return this.call(endpoint, 'POST', data);
        },

        put: function(endpoint, data) {
            return this.call(endpoint, 'PUT', data);
        },

        delete: function(endpoint) {
            return this.call(endpoint, 'DELETE');
        }
    };

})(jQuery);
