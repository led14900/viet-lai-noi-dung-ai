<?php
/**
 * Claude (Anthropic) Provider Implementation
 *
 * @package Viet_Lai_Noi_Dung_AI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIWD_Claude_Provider extends AIWD_AI_Provider_Base {

	/**
	 * @var string
	 */
	protected $provider_name = 'Claude';

	/**
	 * @var string
	 */
	protected $api_endpoint = 'https://api.anthropic.com/v1/messages';

	/**
	 * @var string
	 */
	protected $api_version = '2023-06-01';

	/**
	 * @param array $config Configuration array.
	 */
	public function __construct( $config = array() ) {
		parent::__construct( $config );
		if ( empty( $this->model ) ) {
			$this->model = 'claude-3-5-sonnet-20241022';
		}
		$this->temperature = max( 0, min( 1, $this->temperature ) );
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate_content( $prompt ) {
		if ( ! $this->validate_api_key() ) {
			return array( 'success' => false, 'error' => __( 'API key không hợp lệ', 'viet-lai-noi-dung-ai' ) );
		}

		$parsed  = $this->parse_prompt( $prompt );
		$headers = array(
			'Content-Type'      => 'application/json',
			'x-api-key'         => $this->api_key,
			'anthropic-version' => $this->api_version,
		);

		$body = array(
			'model'       => $this->model,
			'max_tokens'  => 4096,
			'temperature' => $this->temperature,
			'messages'    => array( array( 'role' => 'user', 'content' => $parsed['user'] ) ),
		);

		if ( ! empty( $parsed['system'] ) ) {
			$body['system'] = $parsed['system'];
		}

		$response = $this->make_request( $this->api_endpoint, $body, $headers, 180 );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array( 'success' => false, 'error' => $this->parse_error( $response ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! isset( $data['content'][0]['text'] ) ) {
			return array( 'success' => false, 'error' => __( 'Không nhận được nội dung từ API', 'viet-lai-noi-dung-ai' ) );
		}

		return array( 'success' => true, 'content' => $data['content'][0]['text'] );
	}

	/**
	 * {@inheritDoc}
	 */
	public function test_connection() {
		if ( ! $this->validate_api_key() ) {
			return array( 'success' => false, 'message' => __( 'API key không hợp lệ', 'viet-lai-noi-dung-ai' ) );
		}

		$response = $this->make_request(
			$this->api_endpoint,
			array( 'model' => $this->model, 'max_tokens' => 256, 'messages' => array( array( 'role' => 'user', 'content' => 'Hello' ) ) ),
			array( 'Content-Type' => 'application/json', 'x-api-key' => $this->api_key, 'anthropic-version' => $this->api_version ),
			30
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array( 'success' => false, 'message' => $this->parse_error( $response ) );
		}

		return array( 'success' => true, 'message' => __( 'Kết nối thành công với Claude!', 'viet-lai-noi-dung-ai' ) );
	}
}
