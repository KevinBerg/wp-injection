<?php
/**
 * @package wp-injection
 */
/*
Plugin Name: WP-injection
Plugin URI: Todo
Description: Gives the opportunity to inject html code inside the body or header of a specific page.
Version: 1.0
Author: KevinBerg
Author URI: https://www.kevinberg.de
License: GPLv2 or later
Text Domain: WP-injection
*/

if ( ! function_exists( 'add_action' )) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

class WPInjection {

    public function __construct() {

        add_action( 'add_meta_boxes', array( $this, 'addMetaBox' ));
        add_action( 'save_post', array( $this, 'saveMeta' ));
        add_action('wp_head', array( $this, 'injectHead' ));
        add_action('wp_footer', array( $this, 'injectFooter' ));

    }

    public function uninstall(){
        delete_post_meta_by_key( 'wpinjection_fields' );
    }

    public function injectHead() {

        $post = get_post();
        $meta = get_post_meta( $post->ID, 'wpinjection_fields', true );

        if( ! empty( $meta['meta-content'] )) {
            echo $meta['meta-content'];
        }

    }

    public function injectFooter() {

        $post = get_post();
        $meta = get_post_meta( $post->ID, 'wpinjection_fields', true );

        if( ! empty( $meta['footer-content'] )) {
            echo $meta['footer-content'];
        }

    }

    function addMetaBox() {
        add_meta_box(
            'wpinjection-meta-box',
            'WP-Injection',
            array( $this, 'printInputFields' ),
            'page',
            'normal',
            'high'
        );
    }

    function saveMeta( $post_id ) {

        // verify nonce
        if ( !wp_verify_nonce( $_POST['meta_box_nonce'], basename(__FILE__) ) ) {
            return $post_id;
        }
        // check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
        // check permissions
        if ( 'page' === $_POST['post_type'] ) {
            if ( !current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            } elseif ( !current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }

        $old = get_post_meta( $post_id, 'wpinjection_fields', true );
        $new = $_POST['wpinjection_fields'];

        if ( $new && $new !== $old ) {
            update_post_meta( $post_id, 'wpinjection_fields', $new );
        } elseif ( '' === $new && $old ) {
            delete_post_meta( $post_id, 'wpinjection_fields', $old );
        }

    }


    function printInputFields() {

        $post = get_post();
        $meta = get_post_meta( $post->ID, 'wpinjection_fields', true );

        if( isset( $meta['meta-content'] )) {
            $metaContent = $meta['meta-content'];
        } else {
            $metaContent = '';
        }

        if( isset( $meta['footer-content'] )) {
            $footerContent = $meta['footer-content'];
        } else {
            $footerContent = '';
        }

    ?>

        <input type="hidden" name="meta_box_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">

        <p>
            <label for="wpinjection_fields[meta-content]">Meta-Injection</label>
            <br>
            <textarea name="wpinjection_fields[meta-content]" id="wpinjection_fields[meta-content]" rows="5" cols="30" style="width:500px;"><?php echo $metaContent; ?></textarea>
        </p>

        <p>
            <label for="wpinjection_fields[footer-content]">Footer-Injection</label>
            <br>
            <textarea name="wpinjection_fields[footer-content]" id="wpinjection_fields[footer-content]" rows="5" cols="30" style="width:500px;"><?php echo $footerContent; ?></textarea>
        </p>

    <?php

    }
}

if( class_exists( 'WPInjection' )) {
    $wpInjection = new WPInjection();
    register_uninstall_hook( __FILE__, array( $wpInjection, 'uninstall' ));
}


