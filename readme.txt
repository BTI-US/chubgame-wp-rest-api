=== Advanced WP REST API ===
Contributors: galaxyweblinks
Donate link: https://www.galaxyweblinks.com/
Tags: REST API, Endpoint API, WP REST API, WP Login API, WP post API
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

This plugin provides custom endpoints to the WordPress REST API.

== Description ==

Advanced WP REST API is a WP REST API plugin that provides custom endpoints, to the WordPress REST API. You can enable API routes through a convenient settings panel and then manage the API requests and responses.

**Features:**

* Validate the user

* Handle Post request

* Handle User request

* Handle Product request

**Note:** The API method must be a POST method.

For any Feedback and Queries please contact vivek.jha@galaxyweblinks.in

== Installation ==
This plugin can be installed directly from your site.
1. Log in and navigate to _Plugins → Add New.
2. Type “Advanced WP REST API” into the Search and hit Enter.
3. Locate the Advanced WP REST API plugin in the list of search results and click Install Now.
4. Once installed, click the Activate link.

It can also be installed manually.

1. Download the Advanced WP REST API plugin from WordPress.org.
2. Unzip the package and move to your plugins directory.
3. Log into WordPress and navigate to the Plugins screen.
4. Locate Advanced WP REST API in the list and click the Activate link.

== Frequently Asked Questions ==
= How we can enable/disable the REST API routes?
You can enable/disable it from the Advanced WP REST API options page that exists under the settings, Just choose to enable/disable API.

= How we can validate the user?
This endpoint takes 'username' and 'password' in the body of the request.
Returns the user object on success
Also handles error by returning the relevant error if the fields are empty or credentials don't match.
Example: http://example.com/wp-json/api/v2/user/login

= How we can handle post request?
This endpoint takes 'post_id', 'post_type', and 'meta_keys' in the body of the request. 
The 'post_id' and 'meta_keys' must be an array.
Returns the post object on success
Example: http://example.com/wp-json/api/v2/postsData

= How we can handle user request?
This endpoint takes 'user_id', 'role' and 'meta_keys' in the body of the request.
The 'meta_keys' must be an array.
Returns the user object on success
Example: http://example.com/wp-json/api/v2/usersData

= How we can handle product request?
This endpoint takes 'product_id' and 'meta_keys' in the body of the request.
The 'product_id' and 'meta_keys' must be an array.
Returns the product object on success
Example: http://example.com/wp-json/api/v2/productsData

== Screenshots ==
1. backend-settings.png

== Changelog ==

= 1.0.3 =
Stable Release

= 1.0.2 =
Stable Release

= 1.0.1 =
Stable Release

= 1.0.0 =
First Stable Release

== Upgrade Notice ==

= 1.0.3 =
Stable Release

= 1.0.2 =
Stable Release

= 1.0.1 =
Stable Release

= 1.0.0 =
First Stable Release
