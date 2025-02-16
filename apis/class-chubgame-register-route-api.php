<?php
/**
 * ChubGame Register REST API Routes
 *
 * @package REST API ENDPOINTS
 */
class ChubGame_Register_Route_API {

	/**
	 * ChubGame_Register_Routes constructor.
	 */
	public function __construct() {
		register_activation_hook(__FILE__, 'create_dice_data_table');

		add_action( 'rest_api_init', array( $this, 'rest_api_endpoints' ) );
	}

	/**
	 * Creates the dice_data table in the WordPress database.
	 *
	 * This function creates a new table in the WordPress database to store dice data.
	 * The table includes columns for user ID, dice amount, total points, promotion code,
	 * chips, and other related data. It also sets up foreign key constraints to link
	 * parent and child user IDs to the WordPress users table.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return void
	 */
	function create_dice_data_table(): void {
		global $wpdb;
		$table_name = $wpdb->prefix . 'dice_data';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			dice_amount int(11) NOT NULL,
			total_points int(11) NOT NULL,
			promotion_code varchar(16) NOT NULL,
			is_promotion_user tinyint(1) NOT NULL,
			chips int(11) NOT NULL,
			deduct_chips int(11) NOT NULL DEFAULT 0,
			increase_chips int(11) NOT NULL DEFAULT 0,
			total_chips int(11) NOT NULL,
			parent_user_id bigint(20) DEFAULT NULL,
			child_user_id bigint(20) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY user_id_idx (user_id),
			KEY promotion_code_idx (promotion_code),
			FOREIGN KEY (parent_user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE,
			FOREIGN KEY (child_user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
		) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	/**
	 * Register user endpoints.
	 *
	 * This function registers the REST API endpoints for user-related actions such as
	 * validating promotion codes, checking balance, and sending dice data. The endpoints
	 * are registered based on the plugin settings retrieved from the WordPress options.
	 *
	 * Endpoints:
	 * - Promotion Validation: Registers the endpoint for validating promotion codes if the
	 *   'promotion_validation_api' option is enabled.
	 *   Example: http://example.com/wp-json/{api_route_prefix}/{promotion_validation_route}
	 *
	 * - Check Balance: Registers the endpoint for checking user balance if the
	 *   'check_balance_api' option is enabled.
	 *   Example: http://example.com/wp-json/{api_route_prefix}/{check_balance_route}
	 *
	 * - Send Dice Data: Registers the endpoint for sending dice data if the
	 *   'dice_send_api' option is enabled.
	 *   Example: http://example.com/wp-json/{api_route_prefix}/{dice_send_route}
	 *
	 * Each endpoint requires the user to be logged in to access it.
	 *
	 * @return void
	 */
	function rest_api_endpoints(): void {
		
		//get plugin settings
		$promotion_validation_api = esc_attr( get_option( 'promotion_validation_api' ) );
		$check_balance_api = esc_attr( get_option( 'check_balance_api' ) );
		$dice_send_api = esc_attr( get_option( 'dice_send_api' ) );

		//get plugin route settings
		$api_route_prefix = esc_attr( get_option( 'api_route_prefix' ) );
		$promotion_validation_route = esc_attr( get_option( 'promotion_validation_route' ) );
		$check_balance_route = esc_attr( get_option( 'check_balance_route' ) );
		$dice_send_route = esc_attr( get_option( 'dice_send_route' ) );

		if( !empty( $promotion_validation_api ) && ( $promotion_validation_api == 'yes' )
			&& !empty( $api_route_prefix ) && !empty( $promotion_validation_route ) ) {
			/**
			 * Handle validate request.
			 *
			 * Example: http://example.com/wp-json/chubgame/v1/validate
			 */
			register_rest_route(
				$api_route_prefix,
				$promotion_validation_route,
				array(
					'methods'  => 'POST',
					'callback' => array( $this, 'handle_validate_promotion_code' ),
        			'permission_callback' => 'is_user_logged_in',
				)
			);
		}
		
		if( !empty( $check_balance_api ) && ( $check_balance_api == 'yes' )
			&& !empty( $api_route_prefix ) && !empty( $check_balance_route ) ) {
			/**
			 * Handle check balance request.
			 *
			 * Example: http://example.com/wp-json/chubgame/v1/check-balance
			 */
			register_rest_route(
				$api_route_prefix,
				$check_balance_route,
				array(
					'methods' => 'POST',
					'callback' => array( $this, 'handle_check_balance' ),
        			'permission_callback' => 'is_user_logged_in',
				)
			);
		}	

		if( !empty( $dice_send_api ) && ( $dice_send_api == 'yes' )
			&& !empty( $api_route_prefix ) && !empty( $dice_send_route ) ) {
			/**
			 * Handle send dice data.
			 *
			 * Example: http://example.com/wp-json/chubgame/v1/send
			 */
			register_rest_route(
				$api_route_prefix,
				$dice_send_route,
				array(
					'methods' => 'POST',
					'callback' => array( $this, 'handle_send_dice_data' ),
        			'permission_callback' => 'is_user_logged_in',
				)
			);
		}
	}

	/**
	 * Check if the request domain matches the allowed domain.
	 *
	 * @param WP_REST_Request $request The REST request.
	 * @return WP_REST_Response|bool Returns true if the domain is allowed, otherwise returns a WP_REST_Response with an error.
	 */
	function check_allowed_domain(WP_REST_Request $request): bool|WP_REST_Response {
		$enable_allowed_domain = esc_attr(get_option('enable_allowed_domain'));
		$allowed_domain = esc_attr(get_option('allowed_domain'));

		if (!empty($enable_allowed_domain) && ($enable_allowed_domain == 'yes') && !empty($allowed_domain)) {
			// Check the Origin or Referer header
			$origin = $request->get_header('origin');
			$referer = $request->get_header('referer');

			$is_allowed = false;
			if ($origin) {
				$origin_host = parse_url($origin, PHP_URL_HOST);
				if ($origin_host === $allowed_domain) {
					$is_allowed = true;
				}
			}

			if ($referer) {
				$referer_host = parse_url($referer, PHP_URL_HOST);
				if ($referer_host === $allowed_domain) {
					$is_allowed = true;
				}
			}

			if (!$is_allowed) {
				return new WP_REST_Response(array('valid' => false, 'error' => 'Invalid origin or referer'), 403);
			}
		}

		return true;
	}

	/**
	 * Handle the validation of a promotion code.
	 *
	 * This function validates a promotion code provided by a user and associates the user with the parent user who generated the promotion code.
	 * It performs the following steps:
	 * 1. Checks if the request is from an allowed domain.
	 * 2. Retrieves and logs the promotion code and username from the request.
	 * 3. Validates the presence of the promotion code and username.
	 * 4. Retrieves the child user ID based on the provided username.
	 * 5. Validates the promotion code and fetches the parent user who generated the promotion code.
	 * 6. Checks if the promotion code has already been used.
	 * 7. Associates the parent user with the child user if the promotion code is valid and has not been used.
	 * 8. Returns a response indicating the success or failure of the operation.
	 *
	 * @param WP_REST_Request $request The login request parameter.
	 * @return WP_REST_Response The response indicating the result of the promotion code validation.
	 */
	function handle_validate_promotion_code(WP_REST_Request $request): WP_REST_Response {
		global $wpdb;

		$domain_check = $this->check_allowed_domain($request);
		if ($domain_check !== true) {
			return $domain_check;
		}

		$promotion_code = $request->get_param('promotionCode');
		$username = $request->get_param('username');

		// Debug: Log received parameters
		error_log("handle_validate_promotion_code: Received promotion code: $promotion_code, username: $username");

		// Check if the parameters are provided
		if (empty($promotion_code) || empty($username)) {
			error_log("handle_validate_promotion_code: Missing parameters (promotion code or username).");
			$error = new WP_Error(400, 'Promotion code and username are required.', array('status' => 'missing_parameters'));
			return new WP_REST_Response($error, 400);
		}

		// Get child user ID
		$child_user = get_user_by('login', $username);
		if (!$child_user) {
			error_log("handle_validate_promotion_code: No user found with username $username");
			$error = new WP_Error(404, 'Invalid username', array('status' => 'no_user'));
			return new WP_REST_Response($error, 404);
		}
		$child_user_id = $child_user->ID;
		error_log("handle_validate_promotion_code: Child user ID for username $username is $child_user_id");

		// Validate promotion code and fetch parent user where the user is the promotion code generator
		$table_name = $wpdb->prefix . 'dice_data';
		$result = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $table_name WHERE promotion_code = %s AND is_promotion_user = 1",
			$promotion_code
		));

		if (!$result) {
			error_log("handle_validate_promotion_code: Invalid promotion code or no promotion code generator found for code $promotion_code");
			$error = new WP_Error(400, 'Invalid promotion code', array('status' => 'invalid_promotion_code'));
			return new WP_REST_Response($error, 400);
		}

		// Check if the promotion code has already been used
		$used_result = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $table_name WHERE promotion_code = %s AND parent_user_id IS NOT NULL AND child_user_id IS NOT NULL",
			$promotion_code
		));

		if ($used_result) {
			error_log("handle_validate_promotion_code: Promotion code $promotion_code has already been used.");
			$error = new WP_Error(400, 'This promotion code has already been used', array('status' => 'promotion_code_used'));
			return new WP_REST_Response($error, 400);
		}

		// If valid, associate the parent and child users
		$wpdb->update($table_name, array(
			'parent_user_id' => $result->user_id
		), array('id' => $result->id));

		error_log("handle_validate_promotion_code: Parent user ID {$result->user_id} associated with child user ID $child_user_id");

		return new WP_REST_Response(array(
			'code' => 200,
			'message' => 'Promotion code is valid and successfully applied.',
			'data' => array(
				'status' => 'success',
				'valid' => true,
				'parent_user_id' => $result->user_id,
				'parent_dice_amount' => $result->dice_amount // Include parent dice amount
			)
		), 200);
	}

	/**
	 * Handle the check balance request.
	 *
	 * This function handles the check balance request by validating the request parameters,
	 * checking if the myCred plugin is active, verifying the user's existence, and ensuring
	 * that the user's balance is sufficient for the requested chips.
	 *
	 * @param WP_REST_Request $request The REST API request object containing the parameters.
	 * 
	 * @return WP_REST_Response The REST API response object containing the result of the balance check.
	 */
	function handle_check_balance(WP_REST_Request $request): WP_REST_Response {
		global $wpdb;

		// Check if the myCred plugin is active
		if (!is_plugin_active('mycred/mycred.php')) {
			return new WP_REST_Response(array('valid' => false, 'error' => 'myCred plugin is not activated'), 400);
		}

		$domain_check = $this->check_allowed_domain($request);
		if ($domain_check !== true) {
			return $domain_check;
		}

		// Get the request parameters
		$username = $request->get_param('username');
		$chips = $request->get_param('chips'); // The chips (bet amount) to be checked

		// Log the request parameters for debugging
		error_log("Check Balance Request: username={$username}, chips={$chips}");

		// Check if the parameters are provided
		if (empty($username) || empty($chips)) {
			error_log("Error: Missing parameters (username or chips).");

			$error = new WP_Error(400, 'Username and chips are required.', array('status' => 'missing_parameters'));
			return new WP_REST_Response($error, 400);
		}

		// Get the user by their username
		$user = get_user_by('login', $username);
		if (!$user) {
			error_log("Error: No user found for username {$username}.");

			$error = new WP_Error(404, 'Invalid username', array('status' => 'no_user'));
			return new WP_REST_Response($error, 400);
		}
		$user_id = $user->ID;
		error_log("User found: {$username}, user_id={$user_id}");

		// Get the current balance for the user
		$current_balance = mycred_get_users_balance($user_id);
		error_log("Current balance for user_id {$user_id}: {$current_balance}");

		// Check if the user's balance is greater than or equal to the deducted chips
		if ($current_balance >= $chips) {
			// Balance is sufficient, return success
			error_log("Balance is sufficient for {$username} (user_id={$user_id}). Returning success.");
			return new WP_REST_Response(array(
				'code' => 200,
				'message' => 'Balance is sufficient for current user',
				'data' => array(
					'status' => 'success',
					'balance' => $current_balance
				)
			), 200);
		} else {
			$error = new WP_Error(400, 'Insufficient balance for parent user', array(
				'status' => 'insufficient_balance',
				'balance' => $current_balance,
				'requested_chips' => $chips
			));
			error_log("Error: Insufficient balance for parent user");
			return new WP_REST_Response($error, 400);
		}
	}

	/**
	 * Handle sending dice data.
	 *
	 * This function processes the dice data sent by the user and updates the user's balance accordingly.
	 * It supports both PvE (Player vs Environment) and PvP (Player vs Player) modes.
	 *
	 * @param WP_REST_Request $request The user request parameter.
	 * 
	 * @return WP_REST_Response The response containing the result of the dice data processing.
	 * 
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * 
	 * @throws WP_Error If required parameters are missing or invalid.
	 * 
	 * @note This function requires the myCred plugin to be active.
	 * 
	 * @note The function performs the following steps:
	 * - Checks if the myCred plugin is active.
	 * - Validates the request parameters.
	 * - Retrieves user information based on the provided username.
	 * - Processes the dice data in PvE mode if no promotion code is provided and the user is not a promotion user.
	 * - Generates a random promotion code if the user is a promotion user and no promotion code is provided.
	 * - Checks if the promotion code has already been used.
	 * - Processes the dice data for parent and child users in PvP mode.
	 * - Logs the dice data in the database.
	 * 
	 * @note The function uses the following helper functions:
	 * - clamp_points: Clamps the points to the specified minimum and maximum values.
	 * 
	 * @note The function logs various events and errors using error_log for debugging purposes.
	 */
	function handle_send_dice_data(WP_REST_Request $request): WP_REST_Response {
		global $wpdb;

		// Check if the myCred plugin is active
		if (!is_plugin_active('mycred/mycred.php')) {
			return new WP_REST_Response(array('valid' => false, 'error' => 'myCred plugin is not activated'), 400);
		}

		$domain_check = $this->check_allowed_domain($request);
		if ($domain_check !== true) {
			return $domain_check;
		}

		$win_points_min = esc_attr( get_option( 'win_points_min' ) );
		$win_points_max = esc_attr( get_option( 'win_points_max' ) );
		$loss_points_min = esc_attr( get_option( 'loss_points_min' ) );
		$loss_points_max = esc_attr( get_option( 'loss_points_max' ) );

		$mycred_points_add_reference = esc_attr( get_option( 'mycred_points_add_reference' ) );
		$mycred_points_add_log_entry_pve = esc_attr( get_option( 'mycred_points_add_log_entry_pve' ) );
		$mycred_points_add_log_entry_pvp = esc_attr( get_option( 'mycred_points_add_log_entry_pvp' ) );
		$mycred_points_add_log_entry_refund = esc_attr( get_option( 'mycred_points_add_log_entry_refund' ) );
		$mycred_points_subtract_reference = esc_attr( get_option( 'mycred_points_subtract_reference' ) );
		$mycred_points_subtract_log_entry_pve = esc_attr( get_option( 'mycred_points_subtract_log_entry_pve' ) );
		$mycred_points_subtract_log_entry_pvp = esc_attr( get_option( 'mycred_points_subtract_log_entry_pvp' ) );

		// Extract request parameters
		$dice_amount = $request->get_param('diceAmount');
		$total_points = $request->get_param('totalPoints');
		$promotion_code = $request->get_param('promotionCode');
		$is_promotion_user = $request->get_param('isPromotionUser');
		$username = $request->get_param('username');
		$chips = $request->get_param('chips'); // Chips of the current user

		error_log("Received parameters: diceAmount=$dice_amount, totalPoints=$total_points, promotionCode=$promotion_code, isPromotionUser=$is_promotion_user, username=$username, chips=$chips");

		// Check if the parameters are provided
		if (empty($dice_amount) || empty($total_points) || !isset($is_promotion_user) || empty($username) || empty($chips)) {
			// Parameters are missing, return an error
			$error = new WP_Error(400, 'All parameters are required.', array('status' => 'missing_parameters'));
			error_log("Error: Missing parameters");
			return new WP_REST_Response($error, 400);
		}

		// Get user ID
		$user = get_user_by('login', $username);
		if (!$user) {
			$error = new WP_Error(400, 'Invalid username', array('status' => 'no_user'));
			error_log("Error: Invalid username");
			return new WP_REST_Response($error, 404);
		}
		$user_id = $user->ID;
		error_log("User ID: $user_id");

		// Function to clamp points
		function clamp_points($points, $min, $max) {
			return max($min, min($points, $max));
		}

		// PvE mode: If promotion_code is empty and is_promotion_user is false
		if (empty($promotion_code) && !$is_promotion_user) {
			// Generate a random boolean to decide if the user wins
			$user_wins = (bool)rand(0, 1);
			error_log("PvE mode: User wins: " . ($user_wins ? 'true' : 'false'));

			if ($user_wins) {
				// User wins: Clamp the chips to win points limits
				$chips = clamp_points($chips, $win_points_min, $win_points_max);

				// Add the chips to their balance
				mycred_add($mycred_points_add_reference, $user_id, $chips, $mycred_points_add_log_entry_pve);
				$new_balance = mycred_get_users_balance($user_id);
				error_log("PvE mode: User wins. New balance: $new_balance");

				// Log dice data for PvE win
				$wpdb->insert($wpdb->prefix . 'dice_data', array(
					'user_id' => $user_id,
					'dice_amount' => $dice_amount,
					'total_points' => $total_points,
					'promotion_code' => $promotion_code,
					'is_promotion_user' => $is_promotion_user,
					'chips' => $chips,
					'deduct_chips' => 0,
					'increase_chips' => $chips,
					'total_chips' => $new_balance,
					'parent_user_id' => null,
					'child_user_id' => null,
					'created_at' => current_time('mysql'),
				));
				error_log("Logged dice data for PvE win");

				return new WP_REST_Response(array(
					'code' => 200,
					'message' => 'PvE game processed successfully. User wins.',
					'data' => array(
						'status' => 'success',
						'balance' => $new_balance,
						'result' => $chips * 2, // Positive value for win
					)
				), 200);
			} else {
				// User loses: Clamp the chips to loss points limits
				$chips = clamp_points($chips, $loss_points_min, $loss_points_max);

				// Deduct the chips from their balance
				mycred_subtract($mycred_points_subtract_reference, $user_id, $chips, $mycred_points_subtract_log_entry_pve);
				$new_balance = mycred_get_users_balance($user_id);
				error_log("PvE mode: User loses. New balance: $new_balance");

				// Log dice data for PvE loss
				$wpdb->insert($wpdb->prefix . 'dice_data', array(
					'user_id' => $user_id,
					'dice_amount' => $dice_amount,
					'total_points' => $total_points,
					'promotion_code' => $promotion_code,
					'is_promotion_user' => $is_promotion_user,
					'chips' => $chips,
					'deduct_chips' => $chips,
					'increase_chips' => 0,
					'total_chips' => $new_balance,
					'parent_user_id' => null,
					'child_user_id' => null,
					'created_at' => current_time('mysql'),
				));
				error_log("Logged dice data for PvE loss");

				return new WP_REST_Response(array(
					'code' => 200,
					'message' => 'PvE game processed successfully. User loses.',
					'data' => array(
						'status' => 'success',
						'balance' => $new_balance,
						'result' => -$chips, // Negative value for loss
					)
				), 200);
			}
		}

		// Generate a random promotion code if it is empty and the user is a promotion user
		if (empty($promotion_code) && $is_promotion_user) {
			$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			$promotion_code = '';
			for ($i = 0; $i < 16; $i++) {
				$promotion_code .= $characters[rand(0, strlen($characters) - 1)];
			}
			error_log("Generated promotion code: $promotion_code");
		}

		// Check if the promotion code has already been used
		$promotion_entry = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}dice_data WHERE promotion_code = %s AND (parent_user_id IS NOT NULL OR child_user_id IS NOT NULL)",
			$promotion_code
		));

		if ($promotion_entry) {
			$error = new WP_Error(400, 'This promotion code has already been used', array('status' => 'promotion_used'));
			error_log("Error: Promotion code already used");
			return new WP_REST_Response($error, 400);
		}

		// Common operations for both parent and child users
		$child_balance = mycred_get_users_balance($user_id);
		error_log("Child balance: $child_balance");

		// If the user is a parent, we don't need to query the parent from the promotion code
		if ($is_promotion_user) {
			$parent_balance = mycred_get_users_balance($user_id);
			error_log("Parent balance: $parent_balance");

			if ($parent_balance < $chips) {
				$error = new WP_Error(400, 'Insufficient balance for parent user', array('status' => 'insufficient_balance'));
				error_log("Error: Insufficient balance for parent user");
				return new WP_REST_Response($error, 400);
			}

			// Deduct chips from parent user
			$chips = clamp_points($chips, $loss_points_min, $loss_points_max);
			mycred_subtract($mycred_points_subtract_reference, $user_id, $chips, $mycred_points_subtract_log_entry_pvp);
			$parent_balance = mycred_get_users_balance($user_id); // Updated balance
			error_log("Updated parent balance after deduction: $parent_balance");

			// Log dice data for parent
			$wpdb->insert($wpdb->prefix . 'dice_data', array(
				'user_id' => $user_id,
				'dice_amount' => $dice_amount,
				'total_points' => $total_points,
				'promotion_code' => $promotion_code,
				'is_promotion_user' => $is_promotion_user,
				'chips' => $chips,
				'deduct_chips' => $chips,
				'increase_chips' => 0,
				'total_chips' => $parent_balance,
				'parent_user_id' => null,
				'child_user_id' => null,
				'created_at' => current_time('mysql'),
			));
			error_log("Logged dice data for parent user");

			return new WP_REST_Response(array(
				'code' => 200,
				'message' => 'Parent game processed successfully',
				'data' => array(
					'status' => 'success',
					'balance' => $parent_balance,
					'result' => -$chips, // Negative value for loss
					'promotion_code' => $promotion_code // Include the generated promotion code
				)
			), 200);
		} else {
			// If the user is a child, find the parent by promotion code
			$parent_entry = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}dice_data WHERE promotion_code = %s AND is_promotion_user = 1",
				$promotion_code
			));

			if (!$parent_entry) {
				$error = new WP_Error(404, 'Invalid promotion code or parent not found', array('status' => 'no_parent'));
				error_log("Error: Invalid promotion code or parent not found");
				return new WP_REST_Response($error, 404);
			}

			$parent_user_id = $parent_entry->user_id;
			$parent_balance = mycred_get_users_balance($parent_user_id);
			error_log("Parent user ID: $parent_user_id, Parent balance: $parent_balance");

			// Ensure child has enough balance and refund parent if not
			if ($child_balance < $chips) {
				// Refund parent if child has insufficient balance
				mycred_add($mycred_points_add_reference, $parent_user_id, $parent_balance, $mycred_points_add_log_entry_refund);
				$error = new WP_Error(400, 'Child user does not have enough points. Parent refunded.', array('status' => 'insufficient_balance'));
				error_log("Error: Child user does not have enough points. Parent refunded.");
				return new WP_REST_Response($error, 400);
			}

			// Deduct chips from child user
			$chips = clamp_points($chips, $loss_points_min, $loss_points_max);
			mycred_subtract($mycred_points_subtract_reference, $user_id, $chips, $mycred_points_subtract_log_entry_pvp);
			$child_balance = mycred_get_users_balance($user_id); // Updated child balance
			error_log("Updated child balance after deduction: $child_balance");

			// Calculate winner and loser
			$winner_user_id = ($total_points > $parent_entry->total_points) ? $user_id : $parent_user_id;
			error_log("Winner user ID: $winner_user_id");

			// Service charge and winner chips calculation
			$total_chips = $chips + $parent_entry->chips;
			$service_charge = $total_chips * 0.005; // 0.5% service charge
			$winner_chips = $total_chips - $service_charge;
			error_log("Total chips: $total_chips, Service charge: $service_charge, Winner chips: $winner_chips");

			// Add chips to the winner
			mycred_add($mycred_points_add_reference, $winner_user_id, $winner_chips, $mycred_points_add_log_entry_pvp);
			
			// Update both the parent and the child balance
			$parent_balance = mycred_get_users_balance($parent_user_id);
			$child_balance = mycred_get_users_balance($user_id); // Updated child balance
			error_log("Updated parent balance: $parent_balance, Updated child balance: $child_balance");

			// Log dice data for the child user
			$wpdb->insert($wpdb->prefix . 'dice_data', array(
				'user_id' => $user_id,
				'dice_amount' => $dice_amount,
				'total_points' => $total_points,
				'promotion_code' => $promotion_code,
				'is_promotion_user' => $is_promotion_user,
				'chips' => $chips,
				'deduct_chips' => $chips,
				'increase_chips' => $winner_chips,
				'total_chips' => $child_balance,
				'parent_user_id' => $parent_user_id,
				'child_user_id' => null,
				'created_at' => current_time('mysql'),
			));
			error_log("Logged dice data for child user");

			// Log dice data for the parent user
			$wpdb->insert($wpdb->prefix . 'dice_data', array(
				'user_id' => $parent_user_id,
				'dice_amount' => $dice_amount,
				'total_points' => $total_points,
				'promotion_code' => $promotion_code,
				'is_promotion_user' => $is_promotion_user,
				'chips' => $parent_entry->chips,
				'deduct_chips' => 0,
				'increase_chips' => $winner_chips,
				'total_chips' => $parent_balance,
				'parent_user_id' => null,
				'child_user_id' => $user_id,
				'created_at' => current_time('mysql'),
			));
			error_log("Logged dice data for parent user");

			return new WP_REST_Response(array(
				'code' => 200,
				'message' => 'Game processed successfully',
				'data' => array(
					'status' => 'success',
					'balance' => $child_balance,
					'result' => ($winner_user_id === $user_id) ? $winner_chips : -$chips, // Positive for win, negative for loss
					'promotion_code' => $promotion_code // Include the generated promotion code
				)
			), 200);
		}
	}

}

new ChubGame_Register_Route_API();
