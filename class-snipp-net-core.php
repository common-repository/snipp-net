<?php
/**
 * Wordpress Snipp.net Plugin Core Class.
 *
 * @package SnippNetCore
 */

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SnippNetCore {

    /**
     * Set up filters and actions
     */
    public static function add_hooks() {
        add_action( 'init', array( __CLASS__, 'register_get_var' ) );
        add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
        add_action( 'admin_menu', array( __CLASS__, 'register_config_page' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_boxes' ) );
        add_action( 'save_post', array( __CLASS__, 'save_postdata' ) );
        add_filter( 'the_content', array( __CLASS__, 'content_filter' ) );
    }

    /**
     * Loads the plugin's text domain.
     *
     * Sites on WordPress 4.6+ benefit from just-in-time loading of translations.
     */
    public static function load_textdomain() {
        load_plugin_textdomain( 'snipp-net' );
    }

    public static function register_get_var() {
        global $wp;
        $wp->add_query_var( 'snipp' );
    }

    public static function register_settings() {
        register_setting( 'snipp_net_options', 'snipp_net_username', 'intval' );
        register_setting( 'snipp_net_options', 'snipp_net_api_key', 'sanitize_text_field' );
    }

    public static function register_config_page() {
        add_options_page( 
            __( 'Snipp.net credentials', 'snipp-net'), 
            __( 'Snipp.net credentials', 'snipp-net'), 
            'manage_options', 
            'snipp_net_setup', 
            array( __CLASS__, 'config_page_content' )
        );
    }

    public static function config_page_content() {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'snipp-net' ) );
        }
    
        ?>
            <div class="wrap">
                <h1><?php echo __( 'Snipp.net credentials setup', 'snipp-net' ); ?></h1>
    
                <form method="post" action="options.php" novalidate="novalidate">
                    <?php
                        settings_fields( 'snipp_net_options' );
                        do_settings_sections( 'snipp_net_setup' );
                    ?>
                  
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="blogname">
                                        <?php echo __( 'Enter your domain id', 'snipp-net' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input 
                                        name="snipp_net_username" 
                                        type="text" 
                                        spellcheck="false"
                                        id="snipp_net_username" 
                                        value="<?php echo esc_attr( get_option( 'snipp_net_username' ) ); ?>" 
                                        class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="blogdescription">
                                        <?php echo __( 'Enter your API Key', 'snipp-net' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input 
                                        name="snipp_net_api_key" 
                                        type="text" 
                                        spellcheck="false"
                                        id="snipp_net_api_key" 
                                        value="<?php echo esc_attr( get_option( 'snipp_net_api_key' ) ); ?>" 
                                        class="regular-text">
                                    <!-- <p class="description" id="tagline-description">
                                        In a few words, explain what this is about.
                                    </p> -->
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
        <?php
    }

    public static function register_meta_boxes() {
        add_meta_box( 
            'snipp_net_metabox_require_membership',
            __( 'Require membership', 'snipp-net' ), 
            array( __CLASS__, 'do_metabox_require_membership' ), 
            'post', 
            'side'
        );
    }

    public static function do_metabox_require_membership( $post ) {
        $require_membership_value = get_post_meta( $post->ID, 'snipp_net_require_membership', true );

        wp_nonce_field( plugin_basename(SNIPP_NET_DIR), 'snipp_net_require_membership_nonce' );

        ?>
            <input
                name="snipp_net_require_membership"
                id="snipp_net_require_membership"
                type="checkbox"
                value="1"
                <?php checked( $require_membership_value ); ?>
            >
            <label for="snipp_net_require_membership">
                <?php echo __( 'Require snipp.net Membership', 'snipp-net' ); ?>
            </label>
        <?php
    }

    public static function save_postdata( $post_id ) {
        if ( ! wp_verify_nonce( $_POST['snipp_net_require_membership_nonce'], plugin_basename(SNIPP_NET_DIR) ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  {
            return;
        }

        if( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        update_post_meta( $post_id, 'snipp_net_require_membership', isset( $_POST['snipp_net_require_membership'] ) );
    }

    public static function content_filter( $content ) {
        $post_id = get_the_ID();

        $require_membership_value = get_post_meta( $post_id, 'snipp_net_require_membership', true );

        if( ! $require_membership_value || ! ( get_post_type() == "post" && is_singular() ) ) {
            return $content;
        }

        try {
            $access_token = sanitize_text_field( get_query_var( 'snipp' ) );

            if( ! $access_token ) {
                throw new Exception( 'Token is required' );
            }

            $domain_id = get_option( 'snipp_net_username' );
            $base64_api_key = get_option( 'snipp_net_api_key' );

            $api_key = base64_decode( strtr( $base64_api_key, '-_', '+/' ) );

            $decoded = JWT::decode( $access_token, new Key( $api_key, 'HS512' ) );

            if( $decoded->domain_id != $domain_id ) {
                throw new Exception( 'Wrong domain' );
            }

            $notify_payload = array(
                'user_id' => $decoded->user_id,
                'article_id' => $decoded->article_id,
                'domain_id' => $decoded->domain_id,
                'time' => time()
            );

            $notify_jwt = JWT::encode( $notify_payload, $api_key, 'HS512' );

            $notify_url = 'https://snipp.net/fapi/premium/' . $decoded->domain_id . '/view/' . $notify_jwt;

            wp_remote_get( $notify_url );

        } catch ( Exception $e ) {
            return SnippNetCore::get_required_membership_message();
        }

        return $content;
   }

    public static function get_required_membership_message() {
        return "<p>" . __( 'You require a membership on <a href="https://snipp.net" target="_blank">snipp.net</a> to view this article', 'snipp-net' ) . "</p>";
    }

    public static function delete_options() {
    }
}
