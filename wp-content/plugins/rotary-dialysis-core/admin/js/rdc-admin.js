/**
 * Rotary Dialysis Core - Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize on page load
        initAvailabilityModal();
        initGalleryManager();
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

    /**
     * Gallery Manager
     */
    function initGalleryManager() {
        var $grid = $('#rdc-gallery-grid');
        if (!$grid.length) return;

        var storeId = $grid.data('store-id');
        var mediaFrame;

        // Upload images button
        $('.rdc-upload-images').on('click', function(e) {
            e.preventDefault();

            if (mediaFrame) {
                mediaFrame.open();
                return;
            }

            mediaFrame = wp.media({
                title: rdcAdmin.i18n.selectImages,
                button: { text: rdcAdmin.i18n.addToGallery },
                multiple: true,
                library: { type: 'image' }
            });

            mediaFrame.on('select', function() {
                var attachments = mediaFrame.state().get('selection').toJSON();
                addImagesToGallery(attachments);
            });

            mediaFrame.open();
        });

        // Add images to gallery
        function addImagesToGallery(attachments) {
            var promises = [];

            attachments.forEach(function(attachment) {
                promises.push(
                    rdcApi.post('centers/' + storeId + '/gallery', {
                        attachment_id: attachment.id,
                        title: attachment.title || attachment.filename
                    })
                );
            });

            $.when.apply($, promises).done(function() {
                location.reload();
            }).fail(function() {
                alert(rdcAdmin.i18n.error);
            });
        }

        // Delete image
        $grid.on('click', '.rdc-delete-image', function() {
            if (!confirm(rdcAdmin.i18n.confirmDelete)) return;

            var $item = $(this).closest('.rdc-gallery-item');
            var imageId = $item.data('id');

            rdcApi.delete('gallery/' + imageId).done(function() {
                $item.fadeOut(300, function() {
                    $(this).remove();
                    updateGalleryCount();
                });
            }).fail(function() {
                alert(rdcAdmin.i18n.error);
            });
        });

        // Set featured
        $grid.on('click', '.rdc-set-featured', function() {
            var $item = $(this).closest('.rdc-gallery-item');
            var imageId = $item.data('id');

            rdcApi.put('gallery/' + imageId, { is_featured: true }).done(function() {
                // Remove featured from all
                $grid.find('.rdc-gallery-item').removeClass('rdc-gallery-item--featured');
                $grid.find('.rdc-gallery-featured-badge').remove();
                $grid.find('.rdc-set-featured .dashicons').removeClass('dashicons-star-filled').addClass('dashicons-star-empty');

                // Add featured to current
                $item.addClass('rdc-gallery-item--featured');
                $item.find('.rdc-gallery-item-image').append('<span class="rdc-gallery-featured-badge">Featured</span>');
                $item.find('.rdc-set-featured .dashicons').removeClass('dashicons-star-empty').addClass('dashicons-star-filled');
            }).fail(function() {
                alert(rdcAdmin.i18n.error);
            });
        });

        // Update title on blur
        $grid.on('blur', '.rdc-gallery-title', function() {
            var $input = $(this);
            var $item = $input.closest('.rdc-gallery-item');
            var imageId = $item.data('id');
            var title = $input.val();

            rdcApi.put('gallery/' + imageId, { title: title });
        });

        // Move up
        $grid.on('click', '.rdc-move-up', function() {
            var $item = $(this).closest('.rdc-gallery-item');
            var $prev = $item.prev('.rdc-gallery-item');
            if ($prev.length) {
                $item.insertBefore($prev);
                saveSortOrder();
            }
        });

        // Move down
        $grid.on('click', '.rdc-move-down', function() {
            var $item = $(this).closest('.rdc-gallery-item');
            var $next = $item.next('.rdc-gallery-item');
            if ($next.length) {
                $item.insertAfter($next);
                saveSortOrder();
            }
        });

        // Save sort order
        function saveSortOrder() {
            var order = [];
            $grid.find('.rdc-gallery-item').each(function(index) {
                order.push({
                    id: $(this).data('id'),
                    sort_order: index + 1
                });
            });

            // Update each item's sort order
            order.forEach(function(item) {
                rdcApi.put('gallery/' + item.id, { sort_order: item.sort_order });
            });
        }

        // Update gallery count
        function updateGalleryCount() {
            var count = $grid.find('.rdc-gallery-item').length;
            var text = count === 1 ? '1 image' : count + ' images';
            $('.rdc-gallery-count').text(text);

            if (count === 0) {
                $grid.html(
                    '<div class="rdc-gallery-empty">' +
                    '<span class="dashicons dashicons-format-gallery"></span>' +
                    '<p>No images in this gallery yet.</p>' +
                    '<p>Click "Add Images" to upload photos of this center.</p>' +
                    '</div>'
                );
            }
        }
    }

})(jQuery);
