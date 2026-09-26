<?php
/**
 * Plugin settings, stored in a single option.
 *
 * @package JeyTech\GpsrGuard
 */

namespace JeyTech\GpsrGuard;

defined( 'ABSPATH' ) || exit;

/**
 * Reads, validates and describes the settings.
 */
final class Settings {

	const OPTION = 'jeytech_gpsr_settings';

	/**
	 * Default values: the safety section is displayed.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		return array(
			'enabled' => true,
		);
	}

	/**
	 * Current settings merged with defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function get(): array {
		$saved = get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
	}

	/**
	 * Whether the safety section is displayed on product pages.
	 */
	public static function is_enabled(): bool {
		return ! empty( self::get()['enabled'] );
	}

	/**
	 * Sanitize callback of the settings form.
	 *
	 * @param mixed $input Raw form input.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ): array {
		if ( ! is_array( $input ) || ! $input ) {
			return self::defaults();
		}

		// WordPress runs the sanitize callback twice when the option is created: the second pass
		// receives our own output. The form only posts strings, so a boolean marks our output.
		if ( isset( $input['enabled'] ) && is_bool( $input['enabled'] ) ) {
			return wp_parse_args( $input, self::defaults() );
		}

		$output            = self::defaults();
		$output['enabled'] = ! empty( $input['enabled'] );

		return $output;
	}
}
