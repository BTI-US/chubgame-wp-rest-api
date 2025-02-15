<?php
/**
 * Plugin Name: ChubGame WP REST API
 * Plugin URI: https://wordpress.org/plugins/chubgame-wp-rest-api/
 * Description: This plugin register multiple REST API endpoints
 * Version: 1.0.0
 * Author: ChubGame
 * Author URI: https://chubgame.com/
 * License: GPL3
 * Text Domain: chubgame-wp-rest-api
 */

include_once 'apis/class-chubgame-register-route-api.php';

add_action('admin_enqueue_scripts', 'awpr_callback_for_setting_up_scripts');
function awpr_callback_for_setting_up_scripts(): void {
    wp_register_style( 'awpr-custom-css', plugins_url('assets/css/custom.css', __FILE__), false, '1.0.0', 'all' );
    wp_enqueue_style( 'awpr-custom-css' );
}

function AWPR_register_options_page(): void {

    //create new setting
    add_options_page('AWPR Settings', 'ChubGame WP REST API', 'manage_options', 'awpr_settings', 'AWPR_options_page');

    //call register settings function
	add_action( 'admin_init', 'register_awpr_plugin_settings' );
}
add_action('admin_menu', 'AWPR_register_options_page');

function register_awpr_plugin_settings(): void {
    // Register settings for API Enable/Disable
	register_setting( 'awpr-plugin-settings-group', 'promotion_validation_api' );
	register_setting( 'awpr-plugin-settings-group', 'check_balance_api' );
	register_setting( 'awpr-plugin-settings-group', 'dice_send_api' );

    // Register settings for API Routes
    register_setting( 'awpr-plugin-settings-group', 'api_route_prefix' );
    register_setting( 'awpr-plugin-settings-group', 'promotion_validation_route' );
    register_setting( 'awpr-plugin-settings-group', 'check_balance_route' );
    register_setting( 'awpr-plugin-settings-group', 'dice_send_route' );

    // Register settings for MyCred Settings
    register_setting('awpr-plugin-settings-group', 'mycred_points_add_reference');
    register_setting('awpr-plugin-settings-group', 'mycred_points_add_log_entry_pve');
    register_setting('awpr-plugin-settings-group', 'mycred_points_add_log_entry_pvp');
    register_setting('awpr-plugin-settings-group', 'mycred_points_add_log_entry_refund');
    register_setting('awpr-plugin-settings-group', 'mycred_points_subtract_reference');
    register_setting('awpr-plugin-settings-group', 'mycred_points_subtract_log_entry_pve');
    register_setting('awpr-plugin-settings-group', 'mycred_points_subtract_log_entry_pvp');
}

function AWPR_options_page(): void {
?>
    <div class="awpr_main">
        <h2><?php _e('Enable/Disable Routes', 'chubgame-wp-rest-api'); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'awpr-plugin-settings-group' ); ?>
            <?php do_settings_sections( 'awpr-plugin-settings-group' ); ?>

            <!-- API Enable/Disable Section -->
            <fieldset>
                <legend><?php _e('API Enable/Disable', 'chubgame-wp-rest-api'); ?></legend>
                <table>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="promotion_validation_api"><?php _e('Login API', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="checkbox" id="promotion_validation_api" name="promotion_validation_api" value="yes" <?php if (get_option('promotion_validation_api') == 'yes') { echo "checked"; } ?>/>
                            <p><?php _e('Please check if you want to enable the Login API', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="check_balance_api"><?php _e('Post API', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="checkbox" id="check_balance_api" name="check_balance_api" value="yes" <?php if (get_option('check_balance_api') == 'yes') { echo "checked"; } ?> />
                            <p><?php _e('Please check if you want to enable the Post API', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="dice_send_api"><?php _e('User API', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="checkbox" id="dice_send_api" name="dice_send_api" value="yes" <?php if (get_option('dice_send_api') == 'yes') { echo "checked"; } ?> />
                            <p><?php _e('Please check if you want to enable the User API', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <!-- API Routes Section -->
            <fieldset>
                <legend><?php _e('API Routes', 'chubgame-wp-rest-api'); ?></legend>
                <table>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="api_route_prefix"><?php _e('API Route Prefix', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="text" id="api_route_prefix" name="api_route_prefix" value="<?php echo esc_attr(get_option('api_route_prefix', 'chubgame/v1')); ?>" />
                            <p><?php _e('Enter the route prefix for the APIs', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="promotion_validation_route"><?php _e('Promotion Validation Route', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="text" id="promotion_validation_route" name="promotion_validation_route" value="<?php echo esc_attr(get_option('promotion_validation_route', '/validate')); ?>" />
                            <p><?php _e('Enter the route for the Promotion Validation API', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="check_balance_route"><?php _e('Check Balance Route', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="text" id="check_balance_route" name="check_balance_route" value="<?php echo esc_attr(get_option('check_balance_route', '/check-balance')); ?>" />
                            <p><?php _e('Enter the route for the Check Balance API', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="dice_send_route"><?php _e('Dice Send Route', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="text" id="dice_send_route" name="dice_send_route" value="<?php echo esc_attr(get_option('dice_send_route', '/send')); ?>" />
                            <p><?php _e('Enter the route for the Dice Send API', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <!-- MyCred Settings Section -->
            <fieldset>
                <legend><?php _e('MyCred Settings', 'chubgame-wp-rest-api'); ?></legend>
                <table>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="mycred_points_add_reference"><?php _e('MyCred Points Add Reference', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="text" id="mycred_points_add_reference" name="mycred_points_add_reference" value="<?php echo esc_attr(get_option('mycred_points_add_reference', 'dice_game_add')); ?>" />
                            <p><?php _e('Enter the reference for adding MyCred points', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="mycred_points_add_log_entry_pve"><?php _e('MyCred Points Add Log Entry for PvE', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="text" id="mycred_points_add_log_entry_pve" name="mycred_points_add_log_entry_pve" value="<?php echo esc_attr(get_option('mycred_points_add_log_entry_pve', 'PvE dice game win')); ?>" />
                            <p><?php _e('Enter the log entry for adding MyCred points in PvE', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="mycred_points_add_log_entry_pvp"><?php _e('MyCred Points Add Log Entry for PvP', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="text" id="mycred_points_add_log_entry_pvp" name="mycred_points_add_log_entry_pvp" value="<?php echo esc_attr(get_option('mycred_points_add_log_entry_pvp', 'PvP dice game win')); ?>" />
                            <p><?php _e('Enter the log entry for adding MyCred points in PvP', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="mycred_points_add_log_entry_refund"><?php _e('MyCred Points Add Log Entry for Refund', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="text" id="mycred_points_add_log_entry_refund" name="mycred_points_add_log_entry_refund" value="<?php echo esc_attr(get_option('mycred_points_add_log_entry_refund', 'Refund for insufficient child points')); ?>" />
                            <p><?php _e('Enter the log entry for adding MyCred points in PvP', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="mycred_points_subtract_reference"><?php _e('MyCred Points Add Reference', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="text" id="mycred_points_subtract_reference" name="mycred_points_subtract_reference" value="<?php echo esc_attr(get_option('mycred_points_subtract_reference', 'dice_game_subtract')); ?>" />
                            <p><?php _e('Enter the reference for subtracting MyCred points', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="mycred_points_subtract_log_entry_pve"><?php _e('MyCred Points Subtract Log Entry for PvE', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="text" id="mycred_points_subtract_log_entry_pve" name="mycred_points_subtract_log_entry_pve" value="<?php echo esc_attr(get_option('mycred_points_subtract_log_entry_pve', 'PvE dice game lose')); ?>" />
                            <p><?php _e('Enter the log entry for adding MyCred points in PvE', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="mycred_points_subtract_log_entry_pvp"><?php _e('MyCred Points Subtract Log Entry for PvP', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="text" id="mycred_points_subtract_log_entry_pvp" name="mycred_points_subtract_log_entry_pvp" value="<?php echo esc_attr(get_option('mycred_points_subtract_log_entry_pvp', 'PvP dice game lose')); ?>" />
                            <p><?php _e('Enter the log entry for adding MyCred points in PvP', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <!-- Limitation Settings Section -->
            <fieldset>
                <legend><?php _e('Limitation Settings', 'chubgame-wp-rest-api'); ?></legend>
                <table>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="win_points_max"><?php _e('Win Points Max', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="number" id="win_points_max" name="win_points_max" value="<?php echo esc_attr(get_option('win_points_max', '5000')); ?>" />
                            <p><?php _e('Enter the maximum points for winning', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="win_points_min"><?php _e('Win Points Min', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="number" id="win_points_min" name="win_points_min" value="<?php echo esc_attr(get_option('win_points_min', '5')); ?>" />
                            <p><?php _e('Enter the minimum points for winning', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="loss_points_max"><?php _e('Loss Points Max', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="number" id="loss_points_max" name="loss_points_max" value="<?php echo esc_attr(get_option('loss_points_max', '5000')); ?>" />
                            <p><?php _e('Enter the maximum points for losing', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="loss_points_min"><?php _e('Loss Points Min', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="number" id="loss_points_min" name="loss_points_min" value="<?php echo esc_attr(get_option('loss_points_min', '5')); ?>" />
                            <p><?php _e('Enter the minimum points for losing', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <?php submit_button(); ?>
        </form>
    </div>
<?php
}