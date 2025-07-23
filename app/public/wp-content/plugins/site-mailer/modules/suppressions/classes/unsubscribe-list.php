<?php

namespace SiteMailer\Modules\Suppressions\Classes;

use SiteMailer\Modules\Suppressions\Database\Suppressions_Entry;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Unsubscribe_List {

	public static ?Unsubscribe_List $instance = null;

	/**
	 * get_instance
	 * @return Unsubscribe_List|null
	 */
	public static function get_instance(): ?Unsubscribe_List {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param array $to Array of recipients.
	 * @param array $cc Array of copy recipients.
	 * @param array $bcc Array of hidden copy recipients.
	 *
	 * @return array Array of unsubscribed recipients.
	 */
	public function get_unsubscribed( array $to, array $cc, array $bcc ): array {
		// Prepare the list of emails for the IN clause
		$emails = array_merge( $to, $cc, $bcc );

		// Surround each email with single quotes
		$quoted_emails = array_map( function ( $email ) {
			return "'" . esc_sql( $email ) . "'";
		}, $emails );

		// Create the where condition
		$where = '`email` IN (' . implode( ',', $quoted_emails ) . ')';
		$results = Suppressions_Entry::get_suppressions( 'email', $where );

		return array_map( function ( $result ) {
			return $result->email;
		}, $results );
	}
}
