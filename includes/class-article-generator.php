<?php
/**
 * Content rewrite generator class.
 *
 * @package Viet_Lai_Noi_Dung_AI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIWD_Article_Generator {

	/**
	 * Generate rewritten content and create a draft post.
	 *
	 * @param array $params Rewrite parameters.
	 * @return array{success: bool, post_id?: int, content?: string, error?: string}
	 */
	public static function generate_article( $params ) {
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 600 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}

		self::ensure_db_connection();

		$provider = self::get_provider_instance( get_option( 'aiwd_active_provider', 'openai' ) );
		if ( is_wp_error( $provider ) ) {
			return array( 'success' => false, 'error' => $provider->get_error_message() );
		}

		$result = $provider->generate_content( AIWD_Prompt_Builder::build_article_prompt( $params ) );
		if ( ! $result['success'] ) {
			return array( 'success' => false, 'error' => $result['error'] );
		}

		$content = wp_kses_post( self::clean_content( $result['content'] ) );

		if ( strlen( $content ) > 5000000 ) {
			return array(
				'success' => false,
				'content' => $content,
				'error'   => sprintf(
					/* translators: %s: content length */
					__( 'Nội dung quá dài (%s ký tự). Vui lòng thử lại với yêu cầu ngắn hơn.', 'viet-lai-noi-dung-ai' ),
					number_format( strlen( $content ) )
				),
			);
		}

		self::ensure_db_connection();
		$post_id = self::create_draft_post( $params['topic'], $content, $params );

		if ( is_wp_error( $post_id ) ) {
			return array( 'success' => false, 'content' => $content, 'error' => $post_id->get_error_message() );
		}

		return array( 'success' => true, 'post_id' => $post_id, 'content' => $content );
	}

	/**
	 * Get provider instance with decrypted API key.
	 *
	 * @param string $provider_name Provider name.
	 * @return AIWD_AI_Provider_Base|WP_Error
	 */
	private static function get_provider_instance( $provider_name ) {
		$configs = array(
			'openai' => array(
				'class'    => 'AIWD_OpenAI_Provider',
				'api_key'  => 'aiwd_openai_api_key',
				'model'    => 'aiwd_openai_model',
				'temp'     => 'aiwd_openai_temperature',
				'defaults' => array( 'model' => 'gpt-4o-mini', 'temp' => 0.7 ),
			),
			'claude' => array(
				'class'    => 'AIWD_Claude_Provider',
				'api_key'  => 'aiwd_claude_api_key',
				'model'    => 'aiwd_claude_model',
				'temp'     => 'aiwd_claude_temperature',
				'defaults' => array( 'model' => 'claude-3-5-sonnet-20241022', 'temp' => 0.7 ),
			),
			'gemini' => array(
				'class'    => 'AIWD_Gemini_Provider',
				'api_key'  => 'aiwd_gemini_api_key',
				'model'    => 'aiwd_gemini_model',
				'temp'     => 'aiwd_gemini_temperature',
				'defaults' => array( 'model' => 'gemini-2.5-flash', 'temp' => 0.7 ),
			),
			'openai_compatible' => array(
				'class'    => 'AIWD_OpenAI_Compatible_Provider',
				'api_key'  => 'aiwd_compatible_api_key',
				'model'    => 'aiwd_compatible_model',
				'temp'     => 'aiwd_compatible_temperature',
				'defaults' => array( 'model' => 'gpt-3.5-turbo', 'temp' => 0.7 ),
				'endpoint' => 'aiwd_compatible_endpoint',
			),
		);

		if ( ! isset( $configs[ $provider_name ] ) ) {
			return new WP_Error( 'invalid_provider', __( 'Nhà cung cấp AI không hợp lệ', 'viet-lai-noi-dung-ai' ) );
		}

		$cfg     = $configs[ $provider_name ];
		$api_key = AIWD_Encryption::decrypt( get_option( $cfg['api_key'], '' ) );

		if ( empty( $api_key ) ) {
			/* translators: %s: provider name */
			return new WP_Error( 'no_api_key', sprintf( __( '%s API key chưa được cấu hình', 'viet-lai-noi-dung-ai' ), $provider_name ) );
		}

		$params = array(
			'api_key'     => $api_key,
			'model'       => get_option( $cfg['model'], $cfg['defaults']['model'] ),
			'temperature' => floatval( get_option( $cfg['temp'], $cfg['defaults']['temp'] ) ),
		);

		if ( isset( $cfg['endpoint'] ) ) {
			$endpoint = get_option( $cfg['endpoint'], '' );
			if ( empty( $endpoint ) ) {
				return new WP_Error( 'no_config', __( 'API tương thích OpenAI chưa được cấu hình đầy đủ', 'viet-lai-noi-dung-ai' ) );
			}
			$params['endpoint'] = $endpoint;
		}

		return new $cfg['class']( $params );
	}

	/**
	 * Ensure database connection is alive.
	 */
	private static function ensure_db_connection() {
		global $wpdb;
		if ( ! $wpdb->check_connection( false ) ) {
			$wpdb->db_connect();
		}
	}

	/**
	 * Insert post with retry logic for connection errors.
	 *
	 * @param array $post_data    Post data array.
	 * @param int   $max_retries Maximum retry attempts.
	 * @return int|WP_Error
	 */
	private static function insert_post_with_retry( $post_data, $max_retries = 3 ) {
		$post_id = 0;
		for ( $i = 0; $i < $max_retries; $i++ ) {
			self::ensure_db_connection();
			$post_id = wp_insert_post( $post_data, true );

			if ( ! is_wp_error( $post_id ) && $post_id > 0 ) {
				return $post_id;
			}

			// Only retry on connection errors.
			global $wpdb;
			$err = is_wp_error( $post_id ) ? strtolower( $post_id->get_error_message() ) : '';
			$err .= ! empty( $wpdb->last_error ) ? ' ' . strtolower( $wpdb->last_error ) : '';

			if ( false === stripos( $err, 'connection' ) && false === stripos( $err, 'gone away' ) ) {
				return $post_id;
			}

			if ( $i < $max_retries - 1 ) {
				sleep( pow( 2, $i ) );
			}
		}
		return $post_id;
	}

	/**
	 * Clean generated content — remove markdown artifacts.
	 *
	 * @param string $content Raw content from AI.
	 * @return string
	 */
	private static function clean_content( $content ) {
		$content = preg_replace( '/```html\s*/i', '', $content );
		$content = preg_replace( '/```\s*$/i', '', $content );
		$content = preg_replace( '/```/i', '', $content );
		return trim( $content );
	}

	/**
	 * Create WordPress draft post.
	 *
	 * @param string $title   Post title.
	 * @param string $content Post content.
	 * @param array  $meta    Additional meta data.
	 * @return int|WP_Error
	 */
	private static function create_draft_post( $title, $content, $meta = array() ) {
		if ( empty( $title ) ) {
			return new WP_Error( 'empty_title', __( 'Tiêu đề nội dung không được để trống', 'viet-lai-noi-dung-ai' ) );
		}
		if ( empty( $content ) ) {
			return new WP_Error( 'empty_content', __( 'Nội dung không được để trống', 'viet-lai-noi-dung-ai' ) );
		}
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error( 'insufficient_permissions', __( 'Bạn không có quyền tạo bản nháp', 'viet-lai-noi-dung-ai' ) );
		}

		self::ensure_db_connection();

		$post_id = self::insert_post_with_retry( array(
			'post_title'   => wp_strip_all_tags( $title ),
			'post_content' => wp_kses_post( $content ),
			'post_status'  => 'draft',
			'post_type'    => 'post',
			'post_author'  => get_current_user_id(),
		) );

		if ( is_wp_error( $post_id ) ) {
			global $wpdb;
			$full_error = $post_id->get_error_message();
			if ( ! empty( $wpdb->last_error ) ) {
				$full_error .= ' | DB: ' . $wpdb->last_error;
			}
			return new WP_Error( $post_id->get_error_code(), sprintf(
				/* translators: %s: error details */
				__( 'Không thể tạo bản nháp: %s', 'viet-lai-noi-dung-ai' ),
				$full_error
			) );
		}

		if ( ! $post_id ) {
			return new WP_Error( 'post_creation_failed', __( 'Không thể tạo bản nháp', 'viet-lai-noi-dung-ai' ) );
		}

		// Save meta.
		if ( ! empty( $meta['main_keyword'] ) ) {
			update_post_meta( $post_id, '_aiwd_main_keyword', sanitize_text_field( $meta['main_keyword'] ) );
		}
		if ( ! empty( $meta['secondary_keywords'] ) ) {
			update_post_meta( $post_id, '_aiwd_secondary_keywords', sanitize_text_field( $meta['secondary_keywords'] ) );
		}
		if ( ! empty( $meta['word_count'] ) ) {
			update_post_meta( $post_id, '_aiwd_target_word_count', intval( $meta['word_count'] ) );
		}
		update_post_meta( $post_id, '_aiwd_generated', true );
		update_post_meta( $post_id, '_aiwd_generated_date', current_time( 'mysql' ) );

		return $post_id;
	}

	/**
	 * Test provider connection.
	 *
	 * @param string $provider_name Provider name.
	 * @return array
	 */
	public static function test_provider( $provider_name ) {
		$provider = self::get_provider_instance( $provider_name );
		if ( is_wp_error( $provider ) ) {
			return array( 'success' => false, 'message' => $provider->get_error_message() );
		}
		return $provider->test_connection();
	}
}
