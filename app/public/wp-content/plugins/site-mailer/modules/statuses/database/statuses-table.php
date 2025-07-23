<?php

namespace SiteMailer\Modules\Statuses\Database;

use SiteMailer\Classes\Database\{
	Table,
	Database_Constants
};

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Statuses_Table extends Table {
	// override base's const:
	const DB_VERSION = '2';
	const DB_VERSION_FLAG_NAME = 'site_mailer_statuses_db_version';

	const TYPES = [ 'to', 'cc', 'bcc' ];
	const STATUSES = [
		'pending',
		'accepted',
		'processed',
		'delivered',
		'bounce',
		'dropped',
		'deferred',
		'not sent',
		'rate limit',
		'not valid',
		'unsubscribed',
	];

	const ID = 'id';
	const LOG_ID = 'log_id';
	const EMAIL = 'email';
	const TYPE = 'type';
	const STATUS = 'status';
	const OPENED = 'opened';
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	public static $table_name = 'site_mail_statuses';

	public static function get_columns(): array {
		return [
			self::ID => [
				'type'  => Database_Constants::get_col_type( Database_Constants::INT, 11 ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::UNSIGNED,
					Database_Constants::NOT_NULL,
					Database_Constants::AUTO_INCREMENT,
				] ),
				'key'   => Database_Constants::get_primary_key_string( self::ID ),
			],
			self::LOG_ID     => [
				'type'  => Database_Constants::get_col_type( Database_Constants::VARCHAR, 255 ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::NOT_NULL,
				] ),

				'key' => Database_Constants::build_key_string( Database_Constants::KEY, self::LOG_ID ),
			],
			self::EMAIL => [
				'type'  => Database_Constants::get_col_type( Database_Constants::VARCHAR, 255 ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::NOT_NULL,
				] ),
			],
			self::TYPE     => [
				'type'  => Database_Constants::get_col_type( Database_Constants::VARCHAR, 3 ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::DEFAULT,
					'\'to\'',
					Database_Constants::COMMENT,
					'"' . implode( '|', self::TYPES ) . '"',
				] ),
			],
			self::STATUS     => [
				'type'  => Database_Constants::get_col_type( Database_Constants::VARCHAR, 255 ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::DEFAULT,
					'\'pending\'',
					Database_Constants::COMMENT,
					'"' . implode( '|', self::STATUSES ) . '"',
				] ),
			],
			self::OPENED => [
				'type'  => Database_Constants::get_col_type( Database_Constants::BOOLEAN ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::NOT_NULL,
					Database_Constants::DEFAULT,
					0,
				] ),
			],
			self::CREATED_AT => [
				'type'  => Database_Constants::get_col_type( Database_Constants::DATETIME ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::NOT_NULL,
					Database_Constants::DEFAULT,
					Database_Constants::CURRENT_TIMESTAMP,
				] ),
			],
			self::UPDATED_AT => [
				'type'  => Database_Constants::get_col_type( Database_Constants::DATETIME ),
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
