<?php

namespace SiteMailer\Modules\Logs\Database;

use SiteMailer\Classes\Database\{
	Table,
	Database_Constants
};

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Logs_Table extends Table {
	// override base's const:
	const DB_VERSION = '5';
	const DB_VERSION_FLAG_NAME = 'site_mail_logs_db_version';

	const ID = 'id';
	const API_ID = 'api_id';
	const TO = 'to';
	const SUBJECT = 'subject';
	const HEADERS = 'headers';
	const MESSAGE = 'message';
	const ACTIVITY = 'activity';
	const SOURCE = 'source';
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	/** @deprecated Will be deleted in a future */
	const STATUS = 'status';
	const OPENED = 'opened';

	public static $table_name = 'site_mail_logs';

	/**
	 * install
	 *
	 * This function compares the version of the installed table and the current version as reported by
	 * the class.
	 * If the versions are different, the table will be installed or updated, and the option
	 * will be set to the current version.
	 */
	public static function install(): void {
		$installed_ver = get_option( static::DB_VERSION_FLAG_NAME, - 1 );

		if ( static::DB_VERSION !== $installed_ver ) {

			self::run_migration( $installed_ver );

			$sql = static::get_create_table_sql();

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			update_option( static::DB_VERSION_FLAG_NAME, static::DB_VERSION, false );
		}

		static::set_table_prefix();
	}

	public static function run_migration( $version ) {
		// Drop index from self::TO
		if ( $version > 0 && $version < 4 ) {
			self::query( 'ALTER TABLE `' . self::table_name() . '` DROP INDEX `' . self::TO . '`' );
		}
	}

	/**
	 * build_sql_string
	 * add GROUP BY to Table::build_sql_string
	 *
	 */
	public static function build_sql_string( $fields = '*', $where = '1', int $limit = null, int $offset = null, string $join = '', array $order_by = [], $group_by = '' ): string {
		if ( is_array( $fields ) ) {
			$fields = implode( ', ', $fields );
		}

		$db = static::db();
		$query_string = 'SELECT %s FROM %s %s WHERE %s';
		$query_string = sprintf( $query_string,
			$fields,
			static::table_name(),
			$join,
			static::where( $where )
		);

		if ( is_array( $group_by ) ) {
			$group_by = implode( ', ', $group_by );
		}

		if ( $group_by ) {
			$query_string .= esc_sql( ' GROUP BY ' . $group_by );
		}

		if ( $order_by ) {
			$query_string .= static::build_order_by_sql_string( $order_by );
		}

		if ( $limit ) {
			$query_string .= $db->prepare( ' LIMIT %d', $limit );
		}

		if ( $offset ) {
			$query_string .= $db->prepare( ' OFFSET %d', $offset );
		}

		return $query_string;
	}

	public static function get_columns(): array {
		return [
			self::ID => [
				'type' => Database_Constants::get_col_type( Database_Constants::INT, 11 ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::UNSIGNED,
					Database_Constants::NOT_NULL,
					Database_Constants::AUTO_INCREMENT,
				] ),
				'key' => Database_Constants::get_primary_key_string( self::ID ),
			],
			self::API_ID => [
				'type' => Database_Constants::get_col_type( Database_Constants::VARCHAR, 255 ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::DEFAULT,
					'\'\'',
				] ),

				'key' => Database_Constants::build_key_string( Database_Constants::KEY, self::API_ID ),
			],
			self::TO => [
				'type' => Database_Constants::get_col_type( Database_Constants::TEXT ),
			],
			self::SUBJECT => [
				'type' => Database_Constants::get_col_type( Database_Constants::VARCHAR, 768 ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::DEFAULT,
					'\'\'',
				] ),
			],
			self::HEADERS => [
				'type' => Database_Constants::get_col_type( Database_Constants::TEXT ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::DEFAULT,
					Database_Constants::NULL,
				] ),
			],
			self::MESSAGE => [
				'type' => Database_Constants::get_col_type( Database_Constants::TEXT ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::DEFAULT,
					Database_Constants::NULL,
				] ),
			],
			self::ACTIVITY => [
				'type' => Database_Constants::get_col_type( Database_Constants::TEXT ),
			],
			self::SOURCE => [
				'type' => Database_Constants::get_col_type( Database_Constants::TEXT ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::NOT_NULL,
				] ),
			],
			self::STATUS => [
				'type' => Database_Constants::get_col_type( Database_Constants::VARCHAR, 255 ),
			],
			self::OPENED => [
				'type' => Database_Constants::get_col_type( Database_Constants::BOOLEAN ),
			],
			self::CREATED_AT => [
				'type' => Database_Constants::get_col_type( Database_Constants::DATETIME ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::NOT_NULL,
					Database_Constants::DEFAULT,
					Database_Constants::CURRENT_TIMESTAMP,
				] ),
			],
			self::UPDATED_AT => [
				'type' => Database_Constants::get_col_type( Database_Constants::DATETIME ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::NOT_NULL,
					Database_Constants::DEFAULT,
					Database_Constants::CURRENT_TIMESTAMP,
					Database_Constants::ON_UPDATE,
					Database_Constants::CURRENT_TIMESTAMP,
				] ),
			],
		];
	}
}
