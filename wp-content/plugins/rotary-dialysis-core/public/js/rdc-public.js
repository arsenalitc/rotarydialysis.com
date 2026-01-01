/**
 * Rotary Dialysis Core - Public JavaScript
 */

(function($) {
    'use strict';

    // Review form handler
    $(document).on('submit', '.rdc-review-form', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $wrapper = $form.closest('.rdc-review-form-wrapper');
        var $button = $form.find('button[type="submit"]');
        var $message = $form.find('.rdc-form-message');
        var storeId = $wrapper.data('store-id');

        // Validate
        var rating = $form.find('input[name="rating"]:checked').val();
        if (!rating) {
            showMessage($message, rdcPublic.i18n.required, 'error');
            return;
        }

        var email = $form.find('input[name="reviewer_email"]').val();
        if (!isValidEmail(email)) {
            showMessage($message, rdcPublic.i18n.invalidEmail, 'error');
            return;
        }

        // Submit
        $form.addClass('rdc-loading');
        $button.prop('disabled', true).text(rdcPublic.i18n.submitting);

        $.ajax({
            url: rdcPublic.restUrl + 'centers/' + storeId + '/reviews',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', rdcPublic.nonce);
            },
            data: {
                rating: rating,
                review_text: $form.find('textarea[name="review_text"]').val(),
                reviewer_name: $form.find('input[name="reviewer_name"]').val(),
                reviewer_email: email
            },
            success: function(response) {
                showMessage($message, response.message, 'success');
                $form[0].reset();
            },
            error: function(xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : rdcPublic.i18n.error;
                showMessage($message, message, 'error');
            },
            complete: function() {
                $form.removeClass('rdc-loading');
                $button.prop('disabled', false).text($button.data('original-text') || 'Submit Review');
            }
        });
    });

    // Booking form handler
    $(document).on('submit', '.rdc-booking-form', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $wrapper = $form.closest('.rdc-booking-form-wrapper');
        var $button = $form.find('button[type="submit"]');
        var $message = $form.find('.rdc-form-message');
        var storeId = $wrapper.data('store-id');

        var phone = $form.find('input[name="patient_phone"]').val();
        if (!phone || phone.length < 10) {
            showMessage($message, rdcPublic.i18n.invalidPhone, 'error');
            return;
        }

        var email = $form.find('input[name="patient_email"]').val();
        if (email && !isValidEmail(email)) {
            showMessage($message, rdcPublic.i18n.invalidEmail, 'error');
            return;
        }

        // Submit
        $form.addClass('rdc-loading');
        $button.prop('disabled', true).text(rdcPublic.i18n.submitting);

        $.ajax({
            url: rdcPublic.restUrl + 'centers/' + storeId + '/appointments',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', rdcPublic.nonce);
            },
            data: {
                patient_name: $form.find('input[name="patient_name"]').val(),
                patient_phone: phone,
                patient_email: email,
                preferred_date: $form.find('input[name="preferred_date"]').val(),
                shift_id: $form.find('select[name="shift_id"]').val(),
                message: $form.find('textarea[name="message"]').val()
            },
            success: function(response) {
                var successMsg = response.message + ' ' + 'Confirmation code: ' + response.confirmation_code;
                showMessage($message, successMsg, 'success');
                $form[0].reset();
            },
            error: function(xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : rdcPublic.i18n.error;
                showMessage($message, message, 'error');
            },
            complete: function() {
                $form.removeClass('rdc-loading');
                $button.prop('disabled', false).text($button.data('original-text') || 'Request Appointment');
            }
        });
    });

    // Store original button text
    $('button[type="submit"]').each(function() {
        $(this).data('original-text', $(this).text());
    });

    // Helper: Show message
    function showMessage($el, message, type) {
        $el.removeClass('success error')
           .addClass(type)
           .html(message)
           .show();

        // Auto-hide after 10 seconds for success
        if (type === 'success') {
            setTimeout(function() {
                $el.fadeOut();
            }, 10000);
        }
    }

    // Helper: Validate email
    function isValidEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Gallery lightbox (simple implementation)
    $(document).on('click', '.rdc-gallery-link', function(e) {
        e.preventDefault();
        var imageUrl = $(this).attr('href');

        // Create lightbox
        var $lightbox = $('<div class="rdc-lightbox">' +
            '<div class="rdc-lightbox-backdrop"></div>' +
            '<div class="rdc-lightbox-content">' +
                '<img src="' + imageUrl + '" alt="">' +
                '<button class="rdc-lightbox-close">&times;</button>' +
            '</div>' +
        '</div>');

        $('body').append($lightbox).addClass('rdc-lightbox-open');

        // Close handlers
        $lightbox.on('click', '.rdc-lightbox-backdrop, .rdc-lightbox-close', function() {
            $lightbox.remove();
            $('body').removeClass('rdc-lightbox-open');
        });

        $(document).on('keyup.rdcLightbox', function(e) {
            if (e.key === 'Escape') {
                $lightbox.remove();
                $('body').removeClass('rdc-lightbox-open');
                $(document).off('keyup.rdcLightbox');
            }
        });
    });

    // Add lightbox styles dynamically
    if (!$('#rdc-lightbox-styles').length) {
        $('head').append('<style id="rdc-lightbox-styles">' +
            '.rdc-lightbox { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 999999; display: flex; align-items: center; justify-content: center; }' +
            '.rdc-lightbox-backdrop { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); }' +
            '.rdc-lightbox-content { position: relative; max-width: 90%; max-height: 90%; }' +
            '.rdc-lightbox-content img { max-width: 100%; max-height: 90vh; display: block; }' +
            '.rdc-lightbox-close { position: absolute; top: -40px; right: 0; background: none; border: none; color: #fff; font-size: 32px; cursor: pointer; }' +
            '.rdc-lightbox-open { overflow: hidden; }' +
        '</style>');
    }

})(jQuery);
