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

function check_admin_notices(): void {
    if (!is_plugin_active('mycred/mycred.php')) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('The myCred plugin is not activated. Please install and activate the myCred plugin to use the ChubGame WP REST API plugin.', 'chubgame-wp-rest-api'); ?></p>
        </div>
        <?php
    } else {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('The myCred plugin is activated. You can now use the ChubGame WP REST API plugin.', 'chubgame-wp-rest-api'); ?></p>
        </div>
        <?php
    }
}

// finally display any admin notices.
add_action( 'admin_notices', 'check_admin_notices' );

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
    // Check if user is allowed access
    if (!current_user_can('manage_options')) {
        return;
    }

    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'options-settings';

    echo '<h2 class="nav-tab-wrapper">';
    echo '<a href="?page=awpr_settings&tab=options-settings" class="nav-tab ' . ($active_tab == 'options-settings' ? 'nav-tab-active' : '') . '">' . __('Options Settings', 'chubgame-wp-rest-api') . '</a>';
    echo '<a href="?page=awpr_settings&tab=user-guide" class="nav-tab ' . ($active_tab == 'user-guide' ? 'nav-tab-active' : '') . '">' . __('User Guide', 'chubgame-wp-rest-api') . '</a>';
    echo '</h2>';

    if ($active_tab == 'options-settings') {
        AWPR_options_settings();
    } else {
        AWPR_user_guide_page();
    }
}

function AWPR_options_settings(): void {
?>
    <div class="awpr_main">
        <h2><?php _e('ChubGame WP Routes Management', 'chubgame-wp-rest-api'); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'awpr-plugin-settings-group' ); ?>
            <?php do_settings_sections( 'awpr-plugin-settings-group' ); ?>

            <!-- API Enable/Disable Section -->
            <fieldset>
                <h3><?php _e('API Enable/Disable', 'chubgame-wp-rest-api'); ?></h3>
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
                <h3><?php _e('API Routes', 'chubgame-wp-rest-api'); ?></h3>
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
                <h3><?php _e('MyCred Settings', 'chubgame-wp-rest-api'); ?></h3>
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
                <h3><?php _e('Limitation Settings', 'chubgame-wp-rest-api'); ?></h3>
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

            <!-- Domain Settings Section -->
            <fieldset>
                <h3><?php _e('Domain Settings', 'chubgame-wp-rest-api'); ?></h3>
                <table>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="enable_allowed_domain"><?php _e('Enable Allowed Domain', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="checkbox" id="enable_allowed_domain" name="enable_allowed_domain" value="yes" <?php if (get_option('enable_allowed_domain') == 'yes') { echo "checked"; } ?>/>
                            <p><?php _e('Please check if you want to enable access for the specified domain.', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" class="awpr-api-table">
                        <th scope="row"><label for="allowed_domain"><?php _e('Allowed Domain', 'chubgame-wp-rest-api'); ?></label></th>
                        <td>
                            <input type="text" id="allowed_domain" name="allowed_domain" value="<?php echo esc_attr(get_option('allowed_domain', 'https://dice.chubgame.com')); ?>" />
                            <p><?php _e('Enter the allowed domain to access the API endpoints.', 'chubgame-wp-rest-api'); ?></p>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

function AWPR_user_guide_page(): void {
    ?>
    <div class="awpr_main">
        <h2><?php _e('How To Use ChubGame WP REST API', 'chubgame-wp-rest-api'); ?></h2>
        
        <h3><?php _e('Promotion Code Validation API', 'chubgame-wp-rest-api'); ?></h3>
        <p><?php _e('Validates a promotion code and associates the parent user with the promotion code.', 'chubgame-wp-rest-api'); ?></p>
        <h4><?php _e('Endpoint', 'chubgame-wp-rest-api'); ?></h4>
        <p><code>POST /wp-json/chubgame/v1/validate</code></p>
        <h4><?php _e('Parameters', 'chubgame-wp-rest-api'); ?></h4>
        <ul>
            <li><code>promotionCode</code> (string): <?php _e('The promotion code to validate.', 'chubgame-wp-rest-api'); ?></li>
            <li><code>username</code> (string): <?php _e('The username of the child user.', 'chubgame-wp-rest-api'); ?></li>
        </ul>
        <h4><?php _e('Response', 'chubgame-wp-rest-api'); ?></h4>
        <h5><?php _e('Success', 'chubgame-wp-rest-api'); ?></h5>
        <pre><code>{
    "code": 200,
    "message": "Promotion code is valid and successfully applied.",
    "data": {
        "status": "success",
        "valid": true,
        "parent_user_id": 123,
        "parent_dice_amount": 5
    }
}</code></pre>
        <h5><?php _e('Error', 'chubgame-wp-rest-api'); ?></h5>
        <pre><code>{
    "code": 400,
    "message": "Invalid promotion code",
    "data": {
        "status": "invalid_promotion_code"
    }
}</code></pre>

        <h3><?php _e('Balance Validation API', 'chubgame-wp-rest-api'); ?></h3>
        <p><?php _e('Checks if the user has sufficient balance for the specified chips.', 'chubgame-wp-rest-api'); ?></p>
        <h4><?php _e('Endpoint', 'chubgame-wp-rest-api'); ?></h4>
        <p><code>POST /wp-json/chubgame/v1/check-balance</code></p>
        <h4><?php _e('Parameters', 'chubgame-wp-rest-api'); ?></h4>
        <ul>
            <li><code>username</code> (string): <?php _e('The username of the user.', 'chubgame-wp-rest-api'); ?></li>
            <li><code>chips</code> (int): <?php _e('The number of chips to check.', 'chubgame-wp-rest-api'); ?></li>
        </ul>
        <h4><?php _e('Response', 'chubgame-wp-rest-api'); ?></h4>
        <h5><?php _e('Success', 'chubgame-wp-rest-api'); ?></h5>
        <pre><code>{
    "code": 200,
    "message": "Balance is sufficient for current user",
    "data": {
        "status": "success",
        "balance": 1000
    }
}</code></pre>
        <h5><?php _e('Error', 'chubgame-wp-rest-api'); ?></h5>
        <pre><code>{
    "code": 400,
    "message": "Insufficient balance for parent user",
    "data": {
        "status": "insufficient_balance",
        "balance": 500
    }
}</code></pre>

        <h3><?php _e('Dice Data and Manage Chips API', 'chubgame-wp-rest-api'); ?></h3>
        <p><?php _e('Handles the dice game data and manages the chips for parent and child users.', 'chubgame-wp-rest-api'); ?></p>
        <h4><?php _e('Endpoint', 'chubgame-wp-rest-api'); ?></h4>
        <p><code>POST /wp-json/chubgame/v1/send</code></p>
        <h4><?php _e('Parameters', 'chubgame-wp-rest-api'); ?></h4>
        <ul>
            <li><code>diceAmount</code> (int): <?php _e('The amount of dice rolled.', 'chubgame-wp-rest-api'); ?></li>
            <li><code>totalPoints</code> (int): <?php _e('The total points scored.', 'chubgame-wp-rest-api'); ?></li>
            <li><code>promotionCode</code> (string): <?php _e('Optional: The promotion code used, if empty, then the user is in the PvE mode.', 'chubgame-wp-rest-api'); ?></li>
            <li><code>isPromotionUser</code> (bool): <?php _e('Indicates if the user is a promotion user.', 'chubgame-wp-rest-api'); ?></li>
            <li><code>username</code> (string): <?php _e('The username of the user.', 'chubgame-wp-rest-api'); ?></li>
            <li><code>chips</code> (int): <?php _e('The number of chips of the current user.', 'chubgame-wp-rest-api'); ?></li>
        </ul>
        <h4><?php _e('Response', 'chubgame-wp-rest-api'); ?></h4>
        <h5><?php _e('Success', 'chubgame-wp-rest-api'); ?></h5>
        <pre><code>{
    "code": 200,
    "message": "Game processed successfully",
    "data": {
        "status": "success",
        "balance": 1000,
        "result": 100,
        "promotion_code": "DEBUGCODE1234567"
    }
}</code></pre>
        <h5><?php _e('Error', 'chubgame-wp-rest-api'); ?></h5>
        <pre><code>{
    "code": 400,
    "message": "This promotion code has already been used",
    "data": {
        "status": "promotion_used"
    }
}</code></pre>

        <h2><?php _e('About ChubGame WP REST API', 'chubgame-wp-rest-api'); ?></h2>
        <p><?php _e('This plugin registers multiple REST API endpoints for ChubGame.', 'chubgame-wp-rest-api'); ?></p>
        <p><?php _e('Version: 1.0.0', 'chubgame-wp-rest-api'); ?></p>
        <p><?php _e('Author: ChubGame', 'chubgame-wp-rest-api'); ?></p>
        <p><?php _e('For more information, visit the plugin page on ', 'chubgame-wp-rest-api'); ?><a href="https://wordpress.org/plugins/chubgame-wp-rest-api/" target="_blank"><?php _e('WordPress.org', 'chubgame-wp-rest-api'); ?></a>.</p>
    </div>
    <?php
}