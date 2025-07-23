<?php

namespace SiteMailer\Modules\Statuses\Database;

use SiteMailer\Classes\Database\Entry;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Log_Entry
 */
class Status_Entry extends Entry {

	public static function get_helper_class(): string {
		return Statuses_Table::get_class_name();
	}

	/**
	 * @param string $fields
	 * @param string | array $where
	 * @param number $limit
	 * @param number $offset
	 * @param string $join
	 * @param array $order_by
	 *
	 * @return array
	 */
	public static function get_statuses(
		string $fields = '*',
		$where = '1',
		$limit = null,
		$offset = null,
		string $join = '',
		array $order_by = []
	): array {
		return Statuses_Table::select( $fields, $where, $limit, $offset, $join, $order_by );
	}

	/**
	 * @param string | array $where
	 *
	 * @return array
	 */
	public static function get_statuses_count( $where ): array {
		return Statuses_Table::select( 'COUNT(*) as count', $where );
	}

	/**
	 * @param string $status
	 *
	 * @return bool
	 */
	public static function validate_status( string $status ): bool {
		return in_array( $status, Statuses_Table::STATUSES, true );
	}

	/**
	 * @param string $id
	 * @param string $email
	 * @param string $status
	 *
	 * @return void
	 */
	public static function patch_status( string $id, string $email, string $status ): void {
		Statuses_Table::update(
			[
				Statuses_Table::STATUS => $status,
				Statuses_Table::UPDATED_AT => current_time( 'mysql' ),
			],
			[
				Statuses_Table::LOG_ID => $id,
				Statuses_Table::EMAIL => $email,
			]
		);
	}

	/**
	 * @param string $id
	 * @param string $email
	 *
	 * @return void
	 */
	public static function set_opened( string $id, string $email ): void {
		Statuses_Table::update(
			[
				Statuses_Table::OPENED => true,
				Statuses_Table::STATUS => 'delivered',
				Statuses_Table::UPDATED_AT => current_time( 'mysql' ),
			],
			[
				Statuses_Table::LOG_ID => $id,
				Statuses_Table::EMAIL => $email,
			]
		);
	}

	/**
	 * @param string|array $where
	 *
	 * @return array
	 */
	public static function get_stats( $where ): array {
		return Statuses_Table::select(
			"COUNT(
					CASE WHEN `status` NOT IN ('not sent', 'rate limit', 'not valid', 'unsubscribed') THEN 1 END
				) as total,
				COUNT(CASE WHEN `status` = 'delivered' THEN 1 END) as delivered,
				COUNT(CASE WHEN `status` IN ('failed', 'bounce', 'dropped') THEN 1 END) as failed,
				COUNT(CASE WHEN `opened` = 1 THEN 1 END) as opened",
			$where
		);
	}

	/**
	 * @param array $ids
	 *
	 * @return void
	 */
	public static function delete_statuses( array $ids ): void {
		$escaped = implode( ',', array_map( function ( $item ) {
			return Statuses_Table::db()->prepare( '%s', $item );
		}, $ids ) );
		$query = 'DELETE FROM `' . Statuses_Table::table_name() . '` WHERE `' . Statuses_Table::LOG_ID . '` IN(' . $escaped . ')';
		Statuses_Table::query( $query );
	}

	/**
	 * Delete logs oldest then 30 days
	 * @return void
	 */
	public static function delete_expired_statuses() {
		$query = 'DELETE FROM `' . Statuses_Table::table_name() . '` WHERE `' . Statuses_Table::CREATED_AT . '` < NOW() - INTERVAL 30 DAY';
		Statuses_Table::query( $query );
	}
}
