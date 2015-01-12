=== Optin Comment Notifications ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: comment, comments, notifications, email, commenting, coffee2code
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.8
Tested up to: 4.1
Stable tag: 1.0

Allows users to opt into receiving a notification email whenever a comment is made to the site.

== Description ==

This plugin adds a checkbox to the profile page for users which allows them to opt into receiving a notification email whenever a comment is made to the site.

If a comment goes into moderation, only users who have the ability to manage comments on the site will receive the moderation notification email.

By default, all users of the site have the ability to subscribe to notifications about comments. A filter is provided to facilitate use of code to customize the feature's availability to users.

Note: a "user" is a person with an actual login account for the site. The plugin does not facilitate permitting visitors who do not have an account on the site to be able to subscribe to all comments.

Links: [Plugin Homepage](http://coffee2code.com/wp-plugins/optin-comment-notifications/) | [Plugin Directory Page](https://wordpress.org/plugins/optin-comment-notifications/) | [Author Homepage](http://coffee2code.com)


== Installation ==

1. Unzip `optin-comment-notifications.zip` inside the plugins directory for your site (typically `/wp-content/plugins/`). Or install via the built-in WordPress plugin installer)
2. Activate the plugin through the 'Plugins' admin menu in WordPress


== Screenshots ==

1. A screenshot of the checkbox added to user profiles.


== Frequently Asked Questions ==

= Who can sign up to receive notifications about comments to the site? =

Any user account on the site can sign up for comment notifications. Comments that go into moderation will only trigger notifications to users who can moderate comments. Visitors who do not have an account on the site cannot make use of the plugin to subscribe to comments.

= How does I sign up to receive notifications? =

On your profile page, there is a checkbox next to "New Comment Emails" that is labeled "Email me whenever a comment is submitted to the site.". Check the checkbox and click the button to update your profile. If you wish to discontinue receiving such notifications, simply uncheck the checkbox and save the change.

= Does this plugin include unit tests? =

Yes.

= Is this plugin localizable? =

Yes, though currently only English is available.

= How can I restrict the plugin to only offer the ability to subscribe to all comments to administrators and editors? =

Use the 'c2c_optin_comment_notifications_has_cap' filter to customize the capability as needed. The following code can be used or adapted for that purpose. Such code should ideally be put into a mu-plugin or site-specific plugin (which is beyond the scope of this readme to explain).

`
/**
 * Only permits administrators and editors to subscribe to comment notifications.
 */
function restrict_optin_comment_notifications( $default, $caps ) {
	// Only administrators and editors can subscribe to all comments.
	return !! array_intersect(
		wp_get_current_user()->roles, // Get current user's roles.
		array( 'administrator', 'editor' )
	);
}
add_filter( 'c2c_optin_comment_notifications_has_cap', 'restrict_optin_comment_notifications', 10, 2 );
`

== Changelog ==

= 1.0 =
* Initial public release
* Convert into true plugin
* Permit any user to subscribe to all comments
* Add 'c2c_optin_comment_notifications_has_cap' filter to enable customizing capability
* Change meta key name to 'c2c_comment_notification_optin'
* Change yes_meta_value from 'Y' to '1'
* Change from *_usermeta() to *_user_option()
* Ensure user being notified has the capability to receive notifications
* For moderated comments, only notify those who have the capability to moderate comments
* Change class name to c2c_Optin_Comment_Notifications
* Add unit tests
* Add icon
* Add banner
* Add readme

= 0.9 =
* Initial release as theme-packaged plugin on developer.wordpress.org

== Upgrade Notice ==

= 1.0 =
Initial public release.
