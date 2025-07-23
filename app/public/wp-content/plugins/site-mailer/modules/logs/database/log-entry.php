<?php

namespace SiteMailer\Modules\Logs\Database;

use SiteMailer\Classes\Database\Entry;
use SiteMailer\Modules\Statuses\Database\Statuses_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Log_Entry
 */
class Log_Entry extends Entry {
	/**
	 * @var mixed|null
	 */
	public mixed $subject;
	/**
	 * @var mixed|null
	 */
	public mixed $message;
	/**
	 * @var mixed|null
	 */
	public mixed $to;

	public static function get_helper_class(): string {
		return Logs_Table::get_class_name();
	}

	/**
	 * @param string | array $where
	 * @param null $limit
	 * @param null $offset
	 * @param array $order_by
	 *
	 * @return array
	 */
	public static function get_logs(
		$where = '1',
		$limit = null,
		$offset = null,
		array $order_by = []
	): array {

		$logs_table = Logs_Table::table_name();
		$statuses_table = Statuses_Table::table_name();

		$fields = [
			"$logs_table.*",
			"$statuses_table.status AS single_status",
			"$statuses_table.opened AS single_opened",
			"COUNT($statuses_table.id) AS status_count",
		];

		$group_by = [
			"$logs_table.id",
		];

		$join = "LEFT JOIN $statuses_table ON $logs_table.api_id = $statuses_table.log_id";

		$query = Logs_Table::build_sql_string( $fields, $where, $limit, $offset, $join, $order_by, $group_by );

		return Logs_Table::db()->get_results( $query );
	}

	/**
	 * @param string|array $where
	 *
	 * @return array
	 */
	public static function get_logs_count( $where ): array {
		return Logs_Table::select( 'COUNT(*) as count', $where );
	}

	/**
	 * @param string|array $where
	 *
	 * @return array
	 */
	public static function get_logs_stats( $where ): array {
		return Logs_Table::select(
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
	public static function delete_logs( array $ids ): void {
		$escaped = implode( ',', array_map( function ( $item ) {
			return Logs_Table::db()->prepare( '%s', $item );
		}, $ids ) );
		$query = 'DELETE FROM `' . Logs_Table::table_name() . '` WHERE `' . Logs_Table::API_ID . '` IN(' . $escaped . ')';
		Logs_Table::query( $query );
	}

	/**
	 * Delete logs oldest then 30 days
	 * @return void
	 */
	public static function delete_expired_logs() {
		$query = 'DELETE FROM `' . Logs_Table::table_name() . '` WHERE `' . Logs_Table::CREATED_AT . '` < NOW() - INTERVAL 30 DAY';
		Logs_Table::query( $query );
	}
}
