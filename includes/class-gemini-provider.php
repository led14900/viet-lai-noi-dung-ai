<?php
/**
 * Google Gemini Provider Implementation
 *
 * @package Viet_Lai_Noi_Dung_AI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIWD_Gemini_Provider extends AIWD_AI_Provider_Base {

	/**
	 * @var string
	 */
	protected $provider_name = 'Gemini';

	/**
	 * @var string
	 */
	protected $api_endpoint_base = 'https://generativelanguage.googleapis.com/v1beta/models/';

	/**
	 * @param array $config Configuration array.
	 */
	public function __construct( $config = array() ) {
		parent::__construct( $config );
		if ( empty( $this->model ) ) {
			$this->model = 'gemini-2.5-flash';
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate_content( $prompt ) {
		if ( ! $this->validate_api_key() ) {
			return array( 'success' => false, 'error' => __( 'API key không hợp lệ', 'viet-lai-noi-dung-ai' ) );
		}

		$url    = $this->api_endpoint_base . $this->model . ':generateContent?key=' . $this->api_key;
		$parsed = $this->parse_prompt( $prompt );

		$body = array(
			'contents'         => array( array( 'parts' => array( array( 'text' => $parsed['user'] ) ) ) ),
			'generationConfig' => array( 'temperature' => $this->temperature ),
		);

		if ( ! empty( $parsed['system'] ) ) {
			$body['systemInstruction'] = array( 'parts' => array( array( 'text' => $parsed['system'] ) ) );
		}

		$response = $this->make_request( $url, $body, array( 'Content-Type' => 'application/json' ), 180 );

		if ( is_wp_error( $response ) ) {
			return array( 'success' => false, 'error' => $this->parse_error( $response ) );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			return array(
				'success' => false,
				'error'   => sprintf(
					/* translators: %1$d: HTTP code, %2$s: error details */
					__( 'Gemini API Error (HTTP %1$d): %2$s', 'viet-lai-noi-dung-ai' ),
					$code,
					$this->get_gemini_error_hint( $code, $response )
				),
			);
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return array( 'success' => false, 'error' => __( 'Không thể parse JSON response từ Gemini API', 'viet-lai-noi-dung-ai' ) );
		}

		if ( isset( $data['error'] ) ) {
			$msg = isset( $data['error']['message'] ) ? $data['error']['message'] : __( 'Lỗi không xác định', 'viet-lai-noi-dung-ai' );
			return array( 'success' => false, 'error' => __( 'Gemini API Error: ', 'viet-lai-noi-dung-ai' ) . $msg );
		}

		if ( ! isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
			$finish = isset( $data['candidates'][0]['finishReason'] ) ? $data['candidates'][0]['finishReason'] : '';
			if ( 'SAFETY' === $finish ) {
				return array( 'success' => false, 'error' => __( 'Nội dung bị chặn bởi bộ lọc an toàn của Gemini. Hãy thử với prompt khác.', 'viet-lai-noi-dung-ai' ) );
			}
			if ( 'RECITATION' === $finish ) {
				return array( 'success' => false, 'error' => __( 'Nội dung bị chặn do vi phạm chính sách trích dẫn.', 'viet-lai-noi-dung-ai' ) );
			}
			return array( 'success' => false, 'error' => __( 'Không nhận được nội dung từ Gemini API', 'viet-lai-noi-dung-ai' ) );
		}

		return array( 'success' => true, 'content' => $data['candidates'][0]['content']['parts'][0]['text'] );
	}

	/**
	 * {@inheritDoc}
	 */
	public function test_connection() {
		if ( ! $this->validate_api_key() ) {
			return array( 'success' => false, 'message' => __( 'API key không hợp lệ', 'viet-lai-noi-dung-ai' ) );
		}

		$url      = $this->api_endpoint_base . $this->model . ':generateContent?key=' . $this->api_key;
		$response = $this->make_request(
			$url,
			array( 'contents' => array( array( 'parts' => array( array( 'text' => 'Hello' ) ) ) ) ),
			array( 'Content-Type' => 'application/json' ),
			30
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array( 'success' => false, 'message' => $this->parse_error( $response ) );
		}

		return array( 'success' => true, 'message' => __( 'Kết nối thành công với Google Gemini!', 'viet-lai-noi-dung-ai' ) );
	}

	/**
	 * Get human-readable error hint based on HTTP code.
	 *
	 * @param int            $code     HTTP status code.
	 * @param array|WP_Error $response Response object.
	 * @return string
	 */
	private function get_gemini_error_hint( $code, $response ) {
		switch ( $code ) {
			case 400:
				return __( 'Yêu cầu không hợp lệ. Kiểm tra model name hoặc request format.', 'viet-lai-noi-dung-ai' );
			case 401:
			case 403:
				return __( 'API key không đúng hoặc không có quyền truy cập.', 'viet-lai-noi-dung-ai' );
			case 404:
				/* translators: %s: model name */
				return sprintf( __( 'Model "%s" không tồn tại. Vui lòng kiểm tra tên model.', 'viet-lai-noi-dung-ai' ), $this->model );
			case 429:
				return __( 'Vượt quá giới hạn rate limit. Vui lòng thử lại sau.', 'viet-lai-noi-dung-ai' );
			case 500:
			case 503:
				return __( 'Gemini server đang gặp sự cố. Vui lòng thử lại sau.', 'viet-lai-noi-dung-ai' );
			default:
				return $this->parse_error( $response );
		}
	}
}
