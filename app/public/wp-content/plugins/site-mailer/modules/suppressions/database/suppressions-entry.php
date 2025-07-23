<?php

namespace SiteMailer\Modules\Suppressions\Database;

use SiteMailer\Classes\Database\Entry;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Log_Entry
 */
class Suppressions_Entry extends Entry {
	/**
	 * @var string|null
	 */
	public ?string $email;

	public static function get_helper_class(): string {
		return Suppressions_Table::get_class_name();
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
	public static function get_suppressions(
		string $fields = '*',
		$where = '1',
		$limit = null,
		$offset = null,
		string $join = '',
		array $order_by = []
	): array {
		return Suppressions_Table::select( $fields, $where, $limit, $offset, $join, $order_by );
	}

	/**
	 * @return array
	 */
	public static function get_suppressions_count(): array {
		return Suppressions_Table::select( 'COUNT(*) as count' );
	}

	/**
	 * @param array $ids
	 *
	 * @return void
	 */
	public static function delete_suppressions( array $ids ): void {
		$ids_int = array_map( 'absint', $ids );
		$escaped = implode( ',', array_map(function( $item ) {
			return Suppressions_Table::db()->prepare( '%d', $item );
		}, $ids_int));
		$query = 'DELETE FROM `' . Suppressions_Table::table_name() . '` WHERE `' . Suppressions_Table::ID . '` IN(' . $escaped . ')';
		Suppressions_Table::query( $query );
	}
}
