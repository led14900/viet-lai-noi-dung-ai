<?php
/**
 * Encryption utility for sensitive data (API keys)
 *
 * @package Viet_Lai_Noi_Dung_AI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AIWD_Encryption
 *
 * Encrypts and decrypts sensitive values using AES-256-CBC.
 * Uses WordPress AUTH_KEY as the encryption key.
 */
class AIWD_Encryption {

	/**
	 * Cipher method
	 *
	 * @var string
	 */
	private static $cipher = 'aes-256-cbc';

	/**
	 * Prefix to identify encrypted values
	 *
	 * @var string
	 */
	private static $prefix = 'aiwd_enc::';

	/**
	 * Get the encryption key derived from WordPress salts.
	 *
	 * @return string
	 */
	private static function get_key() {
		$raw_key = defined( 'AUTH_KEY' ) && AUTH_KEY !== 'put your unique phrase here'
			? AUTH_KEY
			: 'aiwd-fallback-' . md5( site_url() );

		return hash( 'sha256', $raw_key, true );
	}

	/**
	 * Check if openssl extension is available.
	 *
	 * @return bool
	 */
	private static function can_encrypt() {
		return function_exists( 'openssl_encrypt' );
	}

	/**
	 * Encrypt a value.
	 *
	 * @param string $value Plain text value.
	 * @return string Encrypted value or original if encryption unavailable.
	 */
	public static function encrypt( $value ) {
		if ( empty( $value ) || ! self::can_encrypt() ) {
			return $value;
		}

		// Don't double-encrypt.
		if ( self::is_encrypted( $value ) ) {
			return $value;
		}

		$iv        = openssl_random_pseudo_bytes( openssl_cipher_iv_length( self::$cipher ) );
		$encrypted = openssl_encrypt( $value, self::$cipher, self::get_key(), 0, $iv );

		if ( false === $encrypted ) {
			return $value;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return self::$prefix . base64_encode( $iv . '::' . $encrypted );
	}

	/**
	 * Decrypt a value.
	 *
	 * @param string $value Encrypted value.
	 * @return string Decrypted value or original if not encrypted.
	 */
	public static function decrypt( $value ) {
		if ( empty( $value ) || ! self::can_encrypt() ) {
			return $value;
		}

		// Not encrypted — return as-is (backward compatibility).
		if ( ! self::is_encrypted( $value ) ) {
			return $value;
		}

		$raw = substr( $value, strlen( self::$prefix ) );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$data  = base64_decode( $raw );
		$parts = explode( '::', $data, 2 );

		if ( count( $parts ) !== 2 ) {
			return $value;
		}

		$decrypted = openssl_decrypt( $parts[1], self::$cipher, self::get_key(), 0, $parts[0] );

		return ( false !== $decrypted ) ? $decrypted : $value;
	}

	/**
	 * Check if a value is encrypted.
	 *
	 * @param string $value Value to check.
	 * @return bool
	 */
	public static function is_encrypted( $value ) {
		return is_string( $value ) && 0 === strpos( $value, self::$prefix );
	}

	/**
	 * Migrate a plaintext option to encrypted.
	 *
	 * @param string $option_name WordPress option name.
	 */
	public static function maybe_encrypt_option( $option_name ) {
		$value = get_option( $option_name, '' );
		if ( ! empty( $value ) && ! self::is_encrypted( $value ) ) {
			update_option( $option_name, self::encrypt( $value ) );
		}
	}
}
