<?php

/**
 * Secondary featured image.
 *
 * Dynamic Post Meta has an "image-sec (portfolio)" data type that reads the
 * `_secondary_featured_image` post meta. This registers that meta and adds the
 * meta box to set it; without a value the widget falls back to Elementor's
 * placeholder.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('pw_secondary_image_meta_key')) {

    function pw_secondary_image_meta_key()
    {
        return '_secondary_featured_image';
    }
}

if (! function_exists('pw_register_secondary_image_meta')) {

    function pw_register_secondary_image_meta($post_type = 'portfolios')
    {
        register_post_meta($post_type, pw_secondary_image_meta_key(), [
            'type'              => 'integer',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'absint',
            'auth_callback'     => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }
}

if (! function_exists('pw_add_secondary_image_meta_box')) {

    function pw_add_secondary_image_meta_box($post_type = 'portfolios')
    {
        add_meta_box(
            'pw_secondary_featured_image',
            __('Secondary Featured Image', 'kirollos-magdy-portfolio-builder'),
            'pw_render_secondary_image_meta_box',
            $post_type,
            'side',
            'low'
        );
    }
}

if (! function_exists('pw_render_secondary_image_meta_box')) {

    function pw_render_secondary_image_meta_box($post)
    {
        wp_nonce_field('pw_secondary_image_save', 'pw_secondary_image_nonce');

        $id  = (int) get_post_meta($post->ID, pw_secondary_image_meta_key(), true);
        $src = $id ? wp_get_attachment_image_url($id, 'medium') : '';
        ?>
        <div class="pw-secondary-image">
            <div class="pw-secondary-image__preview" style="margin-bottom:8px;">
                <?php if ($src) : ?>
                    <img src="<?php echo esc_url($src); ?>" style="max-width:100%;height:auto;display:block;" alt="" />
                <?php endif; ?>
            </div>

            <input type="hidden"
                   class="pw-secondary-image__input"
                   name="pw_secondary_featured_image"
                   value="<?php echo esc_attr($id ?: ''); ?>" />

            <button type="button" class="button pw-secondary-image__select">
                <?php echo $id
                    ? esc_html__('Replace image', 'kirollos-magdy-portfolio-builder')
                    : esc_html__('Set secondary image', 'kirollos-magdy-portfolio-builder'); ?>
            </button>

            <button type="button" class="button-link pw-secondary-image__remove" style="<?php echo $id ? '' : 'display:none;'; ?>margin-left:8px;color:#b32d2e;">
                <?php esc_html_e('Remove', 'kirollos-magdy-portfolio-builder'); ?>
            </button>
        </div>

        <script>
        (function ($) {
            var frame;
            var $box = $('#pw_secondary_featured_image');

            $box.on('click', '.pw-secondary-image__select', function (e) {
                e.preventDefault();

                if (frame) { frame.open(); return; }

                frame = wp.media({
                    title: <?php echo wp_json_encode(__('Select secondary image', 'kirollos-magdy-portfolio-builder')); ?>,
                    button: { text: <?php echo wp_json_encode(__('Use this image', 'kirollos-magdy-portfolio-builder')); ?> },
                    library: { type: 'image' },
                    multiple: false
                });

                frame.on('select', function () {
                    var att = frame.state().get('selection').first().toJSON();
                    var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;

                    $box.find('.pw-secondary-image__input').val(att.id);
                    $box.find('.pw-secondary-image__preview').html(
                        $('<img>', { src: url, alt: '' }).css({ maxWidth: '100%', height: 'auto', display: 'block' })
                    );
                    $box.find('.pw-secondary-image__remove').show();
                });

                frame.open();
            });

            $box.on('click', '.pw-secondary-image__remove', function (e) {
                e.preventDefault();
                $box.find('.pw-secondary-image__input').val('');
                $box.find('.pw-secondary-image__preview').empty();
                $(this).hide();
            });
        })(jQuery);
        </script>
        <?php
    }
}

if (! function_exists('pw_save_secondary_image_meta')) {

    function pw_save_secondary_image_meta($post_id)
    {
        if (! isset($_POST['pw_secondary_image_nonce'])
            || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pw_secondary_image_nonce'])), 'pw_secondary_image_save')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        $id = isset($_POST['pw_secondary_featured_image'])
            ? absint($_POST['pw_secondary_featured_image'])
            : 0;

        if ($id) {
            update_post_meta($post_id, pw_secondary_image_meta_key(), $id);
        } else {
            delete_post_meta($post_id, pw_secondary_image_meta_key());
        }
    }
}
