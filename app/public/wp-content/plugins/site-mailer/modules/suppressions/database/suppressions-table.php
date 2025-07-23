<?php

namespace SiteMailer\Modules\Suppressions\Database;

use SiteMailer\Classes\Database\{
	Table,
	Database_Constants
};

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Suppressions_Table extends Table {
	// override base's const:
	const DB_VERSION = '1';
	const DB_VERSION_FLAG_NAME = 'site_mailer_suppressions_db_version';

	const ID = 'id';
	const EMAIL = 'email';
	const REASON = 'reason';
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	public static $table_name = 'site_mail_suppressions';

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
			self::EMAIL => [
				'type'  => Database_Constants::get_col_type( Database_Constants::VARCHAR, 255 ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::NOT_NULL,
				] ),

				'key' => Database_Constants::build_key_string( Database_Constants::UNIQUE, self::EMAIL ),
			],
			self::REASON => [
				'type'  => Database_Constants::get_col_type( Database_Constants::VARCHAR, 255 ),
				'flags' => Database_Constants::build_flags_string( [
					Database_Constants::COMMENT,
					'\'unsubscribed | manual | bounced | blocked\'',
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
