<?php

class Optin_Comment_Notifications_Test extends WP_UnitTestCase {

	function tearDown() {
		parent::tearDown();
		$this->unset_current_user();
		// Ensure the filter gets removed
		remove_filter( 'c2c_optin_comment_notifications_has_cap', array( $this, 'restrict_capability' ), 10, 2 );
	}


	/*
	 * HELPER FUNCTIONS
	 */


	private function create_user( $email, $optin = false, $role = 'subscriber' ) {
		$user_id = $this->factory->user->create( array( 'user_email' => $email, 'role' => $role ) );
		if ( $optin ) {
			update_user_option(
				$user_id,
				c2c_Optin_Comment_Notifications::$option_name,
				c2c_Optin_Comment_Notifications::$yes_option_value
			);
		}
		return $user_id;
	}

	// helper function, unsets current user globally. Taken from post.php test.
	private function unset_current_user() {
		global $current_user, $user_ID;

		$current_user = $user_ID = null;
	}


	/*
	 * FUNCTIONS FOR HOOKING ACTIONS/FILTERS
	 */


	function restrict_capability( $default, $caps ) {
		// Only administrators and editors can subscribe to all comments.
		return !! array_intersect(
			(array) wp_get_current_user()->roles, // Get current user's roles.
			array( 'administrator', 'editor' )
		);
	}


	/*
	 * TESTS
	 */


	function test_plugin_version() {
		$this->assertEquals( '1.0', c2c_Optin_Comment_Notifications::version() );
	}

	function test_opted_in_user_is_notified_about_new_comment() {
		$post_id    = $this->factory->post->create();
		$email      = 'admin@example.com';
		$user_id    = $this->create_user( $email, true, 'administrator' );
		$comment_id = $this->factory->comment->create( array( 'comment_approved' => '1' ) );

		$this->assertEquals( array( $email ), apply_filters( 'comment_notification_recipients', array(), $comment_id ) );
	}

	function test_non_opted_in_user_is_not_notified_about_new_comment() {
		$post_id    = $this->factory->post->create();
		$email      = 'admin@example.com';
		$user_id    = $this->create_user( $email, false, 'administrator' );
		$comment_id = $this->factory->comment->create( array( 'comment_approved' => '1' ) );

		$this->assertEmpty( apply_filters( 'comment_notification_recipients', array(), $comment_id ) );
	}

	function test_opted_in_user_is_not_notified_about_their_own_comment() {
		$post_id    = $this->factory->post->create();
		$email      = 'admin@example.com';
		$user_id    = $this->create_user( $email, true, 'administrator' );
		$comment_id = $this->factory->comment->create( array( 'user_id' => $user_id, 'comment_approved' => '1' ) );

		$this->assertEmpty( apply_filters( 'comment_notification_recipients', array(), $comment_id ) );
	}

	function test_commenter_with_moderate_comments_capability_is_notified_about_moderated_comment() {
		$post_id    = $this->factory->post->create();
		$email      = 'admin@example.com';
		$user_id    = $this->create_user( $email, true, 'administrator' );
		$comment_id = $this->factory->comment->create( array( 'comment_approved' => '0' ) );

		$this->assertEquals( array( $email ), apply_filters( 'comment_moderation_recipients', array(), $comment_id ) );
	}

	function test_commenter_without_moderate_comments_capability_is_not_notified_about_moderated_comment() {
		$post_id    = $this->factory->post->create();
		$email      = 'subscriber@example.com';
		$user_id    = $this->create_user( $email, true, 'subscriber' );
		$comment_id = $this->factory->comment->create( array( 'comment_approved' => '0' ) );

		$this->assertEmpty( apply_filters( 'comment_moderation_recipients', array(), $comment_id ) );
	}

	function test_opted_in_user_is_not_notified_about_new_comment_if_capability_is_changed() {
		$post_id    = $this->factory->post->create();
		$email      = 'subscriber@example.com';
		$user_id    = $this->create_user( $email, true, 'subscriber' );
		$comment_id = $this->factory->comment->create( array( 'comment_approved' => '1' ) );

		add_filter( 'c2c_optin_comment_notifications_has_cap', array( $this, 'restrict_capability' ), 10, 2 );

		$this->assertEmpty( apply_filters( 'comment_moderation_recipients', array(), $comment_id ) );
	}

	function test_user_is_not_double_notified() {
		$post_id    = $this->factory->post->create();
		$email      = 'admin@example.com';
		$user_id    = $this->create_user( $email, true, 'administrator' );
		$comment_id = $this->factory->comment->create( array( 'comment_approved' => '1' ) );

		$this->assertEquals( array( $email ), apply_filters( 'comment_notification_recipients', array( $email ), $comment_id ) );
	}

	function test_verify_existing_notification_email_addresses_are_not_dropped_when_adding() {
		$post_id    = $this->factory->post->create();
		$email      = 'admin@example.com';
		$emails     = array( 'a@example.com', 'b@example.com' );
		$user_id    = $this->create_user( $email, true, 'administrator' );
		$comment_id = $this->factory->comment->create( array( 'comment_approved' => '1' ) );

		$this->assertEquals( array_merge( $emails, array( $email ) ), apply_filters( 'comment_notification_recipients', $emails, $comment_id ) );
	}

	function test_verify_existing_notification_email_addresses_are_not_dropped_when_not_adding() {
		$post_id    = $this->factory->post->create();
		$email      = 'subscriber@example.com';
		$emails     = array( 'a@example.com', 'b@example.com' );
		$user_id    = $this->create_user( $email, false, 'subscriber' );
		$comment_id = $this->factory->comment->create( array( 'comment_approved' => '1' ) );

		$this->assertEquals( $emails, apply_filters( 'comment_notification_recipients', $emails, $comment_id ) );
	}

	function test_checkbox_is_output_for_low_privilege_user( $value = false ) {
		$user_id = $this->create_user( 'test@example.com', $value, 'subscriber' );
		wp_set_current_user( $user_id );

		ob_start();
		c2c_Optin_Comment_Notifications::add_comment_notification_checkbox();
		$output = ob_get_contents();
		ob_end_clean();

		$this->assertNotEmpty( $output );

		// For use in another test
		return $output;
	}

	function test_checkbox_is_output_as_checked_when_option_is_set() {
		$output = $this->test_checkbox_is_output_for_low_privilege_user( true );

		$this->assertRegExp( '/checked=\'checked\'/', $output );
	}

	function test_checkbox_is_not_output_as_checked_when_option_is_not_set() {
		$output = $this->test_checkbox_is_output_for_low_privilege_user( false );

		$this->assertNotRegExp( '/checked=\'checked\'/', $output );
	}

	function test_checkbox_not_output_if_user_not_capable() {
		$user_id = $this->create_user( 'test@example.com', false, 'subscriber' );
		wp_set_current_user( $user_id );
		add_filter( 'c2c_optin_comment_notifications_has_cap', array( $this, 'restrict_capability' ), 10, 2 );

		ob_start();
		c2c_Optin_Comment_Notifications::add_comment_notification_checkbox();
		$output = ob_get_contents();
		ob_end_clean();

		$this->assertEmpty( $output );
	}

	function test_default_capability_is_true_for_low_privilege_user() {
		$user_id = $this->create_user( 'test@example.com', false, 'subscriber' );

		$this->assertTrue( user_can( $user_id, c2c_Optin_Comment_Notifications::$cap_name ) );
	}

	function test_filter_allows_customizing_capability() {
		$user_id = $this->create_user( 'test@example.com', false, 'subscriber' );

		add_filter( 'c2c_optin_comment_notifications_has_cap', array( $this, 'restrict_capability' ), 10, 2 );

		$this->assertFalse( user_can( $user_id, c2c_Optin_Comment_Notifications::$cap_name ) );
	}

}
