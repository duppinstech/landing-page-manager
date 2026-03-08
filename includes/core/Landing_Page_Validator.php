<?php
namespace LPManager\core;

Use LPManager\core\Required_Fields_Registry;

class Landing_Page_Validator {

    public static function init() {
        //error_log('Landing_Page_Validator::init fired');

        add_action('save_post', [__CLASS__, 'validateLandingPageFields'], 20, 3);
        add_action('admin_notices', [__CLASS__, 'showLandingPageValidationNotices'], 20);
    }

    public static function validateLandingPageFields($post_id, $post, $update) {
        //error_log("validateLandingPageFields fired for post_id {$post_id}");

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            error_log("Skipping post_id {$post_id} because DOING_AUTOSAVE is true.");
            return;
        }

        // Redirect revision post IDs to parent post ID
        if (wp_is_post_revision($post_id)) {
            $parent_post_id = wp_is_post_revision($post_id);
            $post = get_post($parent_post_id);
            if (!$post) {
                return;
            }
            $post_id = $parent_post_id;
        }

        if ($post->post_type !== 'landing_page') {
            error_log("Skipping post_id {$post_id} because post type is {$post->post_type}");
            return;
        }

        if (wp_is_post_autosave($post_id)) {
            error_log("Skipping post_id {$post_id} because it's an autosave.");
            return;
        }

        //$template = get_post_meta($post_id, '_landing_page_template', true);
        $template = carbon_get_theme_option('lpmanager_active_template');
        error_log("Template for post_id {$post_id}: " . ($template ?: 'none'));

        if (empty($template)) {
            error_log("No template set for post_id {$post_id}, skipping.");
            return;
        }

        $required_fields = Required_Fields_Registry::get($template);
        error_log("Required fields for template {$template}: " . print_r($required_fields, true));

        if (empty($required_fields)) {
            error_log("No required fields registered for template {$template}, skipping.");
            return;
        }

        $missing_fields = [];

        foreach ($required_fields as $field) {
            $value = carbon_get_post_meta($post_id, $field);
            if (empty($value)) {
                error_log("Missing value for field '{$field}' on post_id {$post_id}");
                $missing_fields[] = $field;
            }
        }

        if (!empty($missing_fields)) {
            error_log("Setting transient for post_id {$post_id} with missing fields: " . implode(', ', $missing_fields));
            set_transient("lp_missing_fields_{$post_id}", $missing_fields, 60);
        } else {
            error_log("All required fields present for post_id {$post_id}. Deleting any existing transient.");
            delete_transient("lp_missing_fields_{$post_id}");
        }
    }

    public static function showLandingPageValidationNotices() {
        //error_log("showLandingPageValidationNotices fired");
        error_log("admin_notices action fired on screen: " . get_current_screen()->base);
        $screen = get_current_screen();
        if ($screen->post_type !== 'landing_page') {
            error_log("Not on a landing_page screen — current screen: {$screen->post_type}");
            return;
        }

        // Try to get post ID from screen or request
        $post_id = 0;

        if (isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
        } elseif (isset($_POST['post_ID'])) {
            $post_id = intval($_POST['post_ID']);
        }

        if (!$post_id) {
            global $post;
            if ($post) {
                $post_id = $post->ID;
            }
        }

        if (!$post_id) {
            error_log("No post ID available in showLandingPageValidationNotices.");
            return;
        }

        $missing_fields = get_transient("lp_missing_fields_{$post_id}");
        error_log("Transient lookup for post_id {$post_id}: " . print_r($missing_fields, true));

        if ($missing_fields) {
            echo '<div class="notice notice-error is-dismissible"><p><strong>Missing Required Fields:</strong></p><ul>';
            foreach ($missing_fields as $field) {
                echo "<li>{$field}</li>";
            }
            echo '</ul></div>';
        }
    }

}
