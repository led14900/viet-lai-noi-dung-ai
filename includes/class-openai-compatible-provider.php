<?php
/**
 * OpenAI-Compatible API Provider Implementation
 *
 * @package Viet_Lai_Noi_Dung_AI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIWD_OpenAI_Compatible_Provider extends AIWD_AI_Provider_Base {

	/**
	 * @var string
	 */
	protected $provider_name = 'OpenAI Compatible';

	/**
	 * @param array $config Configuration array.
	 */
	public function __construct( $config = array() ) {
		parent::__construct( $config );
		if ( isset( $config['endpoint'] ) ) {
			$this->api_endpoint = $config['endpoint'];
		}
		if ( empty( $this->model ) ) {
			$this->model = 'gpt-3.5-turbo';
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate_content( $prompt ) {
		if ( empty( $this->api_endpoint ) ) {
			return array( 'success' => false, 'error' => __( 'Endpoint API chưa được cấu hình', 'viet-lai-noi-dung-ai' ) );
		}
		return $this->openai_chat_generate( $prompt );
	}

	/**
	 * {@inheritDoc}
	 */
	public function test_connection() {
		if ( empty( $this->api_endpoint ) ) {
			return array( 'success' => false, 'message' => __( 'Endpoint API chưa được cấu hình', 'viet-lai-noi-dung-ai' ) );
		}
		return $this->openai_chat_test();
	}
}
