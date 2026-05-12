<?php
/**
 * Base abstract class for AI providers
 *
 * @package Viet_Lai_Noi_Dung_AI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AIWD_AI_Provider_Base {

	/**
	 * Provider name
	 *
	 * @var string
	 */
	protected $provider_name = '';

	/**
	 * API key
	 *
	 * @var string
	 */
	protected $api_key = '';

	/**
	 * API endpoint
	 *
	 * @var string
	 */
	protected $api_endpoint = '';

	/**
	 * Model name
	 *
	 * @var string
	 */
	protected $model = '';

	/**
	 * Temperature setting
	 *
	 * @var float
	 */
	protected $temperature = 0.7;

	/**
	 * Constructor
	 *
	 * @param array $config Configuration array.
	 */
	public function __construct( $config = array() ) {
		if ( isset( $config['api_key'] ) ) {
			$this->api_key = $config['api_key'];
		}
		if ( isset( $config['model'] ) ) {
			$this->model = $config['model'];
		}
		if ( isset( $config['temperature'] ) ) {
			$this->temperature = floatval( $config['temperature'] );
		}
	}

	/**
	 * Generate rewritten content.
	 *
	 * @param string $prompt The prompt to generate content from.
	 * @return array{success: bool, content?: string, error?: string}
	 */
	abstract public function generate_content( $prompt );

	/**
	 * Test API connection.
	 *
	 * @return array{success: bool, message: string}
	 */
	abstract public function test_connection();

	/**
	 * Get provider name.
	 *
	 * @return string
	 */
	public function get_provider_name() {
		return $this->provider_name;
	}

	/**
	 * Parse combined SYSTEM/USER prompt into parts.
	 *
	 * @param string $prompt Combined prompt string.
	 * @return array{system: string, user: string}
	 */
	protected function parse_prompt( $prompt ) {
		$system = '';
		$user   = $prompt;

		if ( false !== strpos( $prompt, 'SYSTEM:' ) && false !== strpos( $prompt, 'USER:' ) ) {
			$parts = explode( 'USER:', $prompt, 2 );
			if ( 2 === count( $parts ) ) {
				$system = trim( str_replace( 'SYSTEM:', '', $parts[0] ) );
				$user   = trim( $parts[1] );
			}
		}

		return array(
			'system' => $system,
			'user'   => $user,
		);
	}

	/**
	 * Make HTTP request to API.
	 *
	 * @param string $url     API endpoint URL.
	 * @param array  $body    Request body.
	 * @param array  $headers Request headers.
	 * @param int    $timeout Request timeout in seconds.
	 * @return array|WP_Error
	 */
	protected function make_request( $url, $body, $headers = array(), $timeout = 120 ) {
		if ( ! isset( $headers['User-Agent'] ) ) {
			$headers['User-Agent'] = 'Viet-Lai-Noi-Dung-AI/' . AIWD_VERSION . '; ' . home_url();
		}
		if ( ! isset( $headers['Accept'] ) ) {
			$headers['Accept'] = 'application/json';
		}

		return wp_safe_remote_post( $url, array(
			'method'             => 'POST',
			'headers'            => $headers,
			'body'               => wp_json_encode( $body ),
			'timeout'            => $timeout,
			'reject_unsafe_urls' => true,
		) );
	}

	/**
	 * Parse error from response.
	 *
	 * @param WP_Error|array $response The response.
	 * @return string Error message.
	 */
	protected function parse_error( $response ) {
		if ( is_wp_error( $response ) ) {
			return sprintf(
				/* translators: %1$s: error code, %2$s: error message */
				__( '[Lỗi %1$s] %2$s', 'viet-lai-noi-dung-ai' ),
				$response->get_error_code(),
				$response->get_error_message()
			);
		}

		$body    = wp_remote_retrieve_body( $response );
		$code    = wp_remote_retrieve_response_code( $response );
		$message = wp_remote_retrieve_response_message( $response );
		$data    = json_decode( $body, true );

		$error_message = '';
		$error_type    = '';
		$error_code    = '';

		if ( is_array( $data ) && isset( $data['error'] ) ) {
			if ( is_array( $data['error'] ) ) {
				$error_message = isset( $data['error']['message'] ) ? $data['error']['message'] : '';
				$error_type    = isset( $data['error']['type'] ) ? $data['error']['type'] : '';
				$error_code    = isset( $data['error']['code'] ) ? $data['error']['code'] : '';
				if ( empty( $error_type ) && isset( $data['error']['status'] ) ) {
					$error_type = $data['error']['status'];
				}
			} else {
				$error_message = $data['error'];
			}
		}

		if ( ! empty( $error_message ) ) {
			$details = array();
			if ( ! empty( $error_type ) ) {
				$details[] = $error_type;
			}
			if ( ! empty( $error_code ) ) {
				$details[] = $error_code;
			}
			$detailed = $error_message;
			if ( ! empty( $details ) ) {
				$detailed .= ' (' . implode( ', ', $details ) . ')';
			}
		} else {
			$detailed = sprintf(
				/* translators: %d: HTTP status code */
				__( 'Lỗi HTTP %d', 'viet-lai-noi-dung-ai' ),
				$code
			);
			if ( ! empty( $message ) ) {
				$detailed .= ' - ' . $message;
			}
			if ( 530 === (int) $code ) {
				$detailed .= '. ' . __( 'Endpoint API đang bị lỗi ở tầng CDN/proxy hoặc không kết nối được tới máy chủ gốc. Hãy kiểm tra lại API Endpoint, thử Test kết nối, hoặc liên hệ nhà cung cấp API.', 'viet-lai-noi-dung-ai' );
			}
			if ( ! empty( $body ) ) {
				$body_excerpt = wp_strip_all_tags( $body );
				if ( ! empty( $body_excerpt ) ) {
					$detailed .= ': ' . substr( $body_excerpt, 0, 300 );
				}
			}
		}

		return sprintf(
			/* translators: %s: detailed error message */
			__( 'Lỗi API: %s', 'viet-lai-noi-dung-ai' ),
			$detailed
		);
	}

	/**
	 * Validate API key format.
	 *
	 * @return bool
	 */
	protected function validate_api_key() {
		return ! empty( $this->api_key ) && strlen( $this->api_key ) > 10;
	}

	/**
	 * Shared OpenAI-format chat completion request (used by OpenAI and Compatible providers).
	 *
	 * @param string $prompt   The prompt to generate content from.
	 * @param int    $timeout  Request timeout in seconds.
	 * @return array{success: bool, content?: string, error?: string}
	 */
	protected function openai_chat_generate( $prompt, $timeout = 180 ) {
		if ( ! $this->validate_api_key() ) {
			return array( 'success' => false, 'error' => __( 'API key không hợp lệ', 'viet-lai-noi-dung-ai' ) );
		}

		$parsed   = $this->parse_prompt( $prompt );
		$messages = array();

		if ( ! empty( $parsed['system'] ) ) {
			$messages[] = array( 'role' => 'system', 'content' => $parsed['system'] );
		}
		$messages[] = array( 'role' => 'user', 'content' => $parsed['user'] );

		$response = $this->make_request(
			$this->api_endpoint,
			array( 'model' => $this->model, 'messages' => $messages, 'temperature' => $this->temperature ),
			array( 'Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $this->api_key ),
			$timeout
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array( 'success' => false, 'error' => $this->parse_error( $response ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! isset( $data['choices'][0]['message']['content'] ) ) {
			return array( 'success' => false, 'error' => __( 'Không nhận được nội dung từ API', 'viet-lai-noi-dung-ai' ) );
		}

		return array( 'success' => true, 'content' => $data['choices'][0]['message']['content'] );
	}

	/**
	 * Shared OpenAI-format test connection (used by OpenAI and Compatible providers).
	 *
	 * @return array{success: bool, message: string}
	 */
	protected function openai_chat_test() {
		if ( ! $this->validate_api_key() ) {
			return array( 'success' => false, 'message' => __( 'API key không hợp lệ', 'viet-lai-noi-dung-ai' ) );
		}

		$response = $this->make_request(
			$this->api_endpoint,
			array( 'model' => $this->model, 'messages' => array( array( 'role' => 'user', 'content' => 'Hello' ) ) ),
			array( 'Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $this->api_key ),
			30
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array( 'success' => false, 'message' => $this->parse_error( $response ) );
		}

		return array(
			'success' => true,
			/* translators: %s: provider name */
			'message' => sprintf( __( 'Kết nối thành công với %s!', 'viet-lai-noi-dung-ai' ), $this->provider_name ),
		);
	}
}
