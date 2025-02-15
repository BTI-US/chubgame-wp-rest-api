=== ChubGame WP REST API ===
Contributors: ChubGame
Tags: REST API, Endpoint API, WP REST API, WP Login API, WP post API
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

This plugin provides custom endpoints to the WordPress REST API.

== Description ==

ChubGame WP REST API is a WP REST API plugin that provides custom endpoints, to the WordPress REST API. You can enable API routes through a convenient settings panel and then manage the API requests and responses.

**Features:**

* Validate The Promotion Code

* Check The User Balance

* Send The Dice Data

**Note:** The API method must be a POST method.

For any Feedback and Queries please contact contact@chubgame.com

== Installation ==
This plugin can be installed directly from your site.
1. Log in and navigate to _Plugins → Add New.
2. Type "ChubGame WP REST API" into the Search and hit Enter.
3. Locate the ChubGame WP REST API plugin in the list of search results and click Install Now.
4. Once installed, click the Activate link.

It can also be installed manually.

1. Download the ChubGame WP REST API plugin from WordPress.org.
2. Unzip the package and move to your plugins directory.
3. Log into WordPress and navigate to the Plugins screen.
4. Locate ChubGame WP REST API in the list and click the Activate link.

== Frequently Asked Questions ==
= How we can enable/disable the REST API routes?
You can enable/disable it from the ChubGame WP REST API options page that exists under the settings, Just choose to enable/disable API.

= Promotion Code Validation API
Validates a promotion code and associates the parent user with the promotion code.

== Endpoint ==
POST /wp-json/chubgame/v1/validate

== Parameters ==
- promotionCode (string): The promotion code to validate.
- username (string): The username of the child user.

== Response ==

=== Success ===
{
    "code": 200,
    "message": "Promotion code is valid and successfully applied.",
    "data": {
        "status": "success",
        "valid": true,
        "parent_user_id": 123,
        "parent_dice_amount": 5
    }
}

=== Error ===
{
    "code": 400,
    "message": "Invalid promotion code",
    "data": {
        "status": "invalid_promotion_code"
    }
}

= Balance Validation API
Checks if the user has sufficient balance for the specified chips.

== Endpoint ==
POST /wp-json/chubgame/v1/check-balance

== Parameters ==
- username (string): The username of the user.
- chips (int): The number of chips to check.

== Response ==

=== Success ===
{
    "code": 200,
    "message": "Balance is sufficient for current user",
    "data": {
        "status": "success",
        "balance": 1000
    }
}

=== Error ===
{
    "code": 400,
    "message": "Insufficient balance for parent user",
    "data": {
        "status": "insufficient_balance",
        "balance": 500
    }
}

= Dice Data and Manage Chips API
Handles the dice game data and manages the chips for parent and child users.

== Endpoint ==
POST /wp-json/chubgame/v1/send

== Parameters ==
- diceAmount (int): The amount of dice rolled.
- totalPoints (int): The total points scored.
- promotionCode (string): Optional: The promotion code used, if empty, then the user is in the PvE mode.
- isPromotionUser (bool): Indicates if the user is a promotion user.
- username (string): The username of the user.
- chips (int): The number of chips of the current user.

== Response ==

=== Success ===
{
    "code": 200,
    "message": "Game processed successfully",
    "data": {
        "status": "success",
        "balance": 1000,
        "result": 100,
        "promotion_code": "DEBUGCODE1234567"
    }
}

=== Error ===
{
    "code": 400,
    "message": "This promotion code has already been used",
    "data": {
        "status": "promotion_used"
    }
}

== Screenshots ==
1. backend-settings.png

== Changelog ==

= 1.0.0 =
First Stable Release

== Upgrade Notice ==

= 1.0.0 =
First Stable Release
