<?php
/**
 * Gallery REST Controller
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Gallery_Controller extends RDC_REST_Controller {

    /**
     * Resource name
     */
    protected $rest_base = 'centers/(?P<store_id>[\d]+)/gallery';

    /**
     * Register routes
     */
    public function register_routes() {
        // GET/POST gallery images for a center
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_items'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'store_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                ),
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_item'),
                'permission_callback' => array($this, 'create_item_permissions_check'),
                'args' => array(
                    'store_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'attachment_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'title' => array(
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'caption' => array(
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ),
                    'is_featured' => array(
                        'type' => 'boolean',
                        'default' => false,
                    ),
                ),
            ),
        ));

        // PUT/DELETE single gallery image
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<image_id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_item'),
                'permission_callback' => array($this, 'update_item_permissions_check'),
                'args' => array(
                    'store_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'image_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'title' => array(
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'caption' => array(
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ),
                    'sort_order' => array(
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'is_featured' => array(
                        'type' => 'boolean',
                    ),
                ),
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_item'),
                'permission_callback' => array($this, 'delete_item_permissions_check'),
                'args' => array(
                    'store_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'image_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                ),
            ),
        ));
    }

    /**
     * Get gallery images
     */
    public function get_items($request) {
        $store_id = $request->get_param('store_id');

        $valid = $this->validate_store_id($store_id);
        if (is_wp_error($valid)) {
            return $valid;
        }

        $images = RDC_Gallery_Service::get_images($store_id);

        return $this->success_response(array(
            'images' => $images,
            'total' => count($images),
        ));
    }

    /**
     * Add gallery image
     */
    public function create_item($request) {
        $store_id = $request->get_param('store_id');
        $attachment_id = $request->get_param('attachment_id');

        $result = RDC_Gallery_Service::add_image($store_id, $attachment_id, array(
            'title' => $request->get_param('title'),
            'caption' => $request->get_param('caption'),
            'is_featured' => $request->get_param('is_featured'),
        ));

        if (is_wp_error($result)) {
            return $result;
        }

        return $this->success_response(array(
            'success' => true,
            'image_id' => $result,
        ), 201);
    }

    /**
     * Update gallery image
     */
    public function update_item($request) {
        $image_id = $request->get_param('image_id');

        $data = array();
        foreach (array('title', 'caption', 'sort_order', 'is_featured') as $field) {
            if ($request->has_param($field)) {
                $data[$field] = $request->get_param($field);
            }
        }

        $result = RDC_Gallery_Service::update_image($image_id, $data);

        if (is_wp_error($result)) {
            return $result;
        }

        return $this->success_response(array('success' => true));
    }

    /**
     * Delete gallery image
     */
    public function delete_item($request) {
        $image_id = $request->get_param('image_id');

        $result = RDC_Gallery_Service::delete_image($image_id);

        if (!$result) {
            return $this->error_response(__('Failed to delete image.', 'rotary-dialysis-core'));
        }

        return $this->success_response(array('success' => true));
    }

    /**
     * Permission check for create
     */
    public function create_item_permissions_check($request) {
        $store_id = $request->get_param('store_id');
        return $this->can_manage_center($store_id);
    }

    /**
     * Permission check for update
     */
    public function update_item_permissions_check($request) {
        $store_id = $request->get_param('store_id');
        return $this->can_manage_center($store_id);
    }

    /**
     * Permission check for delete
     */
    public function delete_item_permissions_check($request) {
        $store_id = $request->get_param('store_id');
        return $this->can_manage_center($store_id);
    }
}
