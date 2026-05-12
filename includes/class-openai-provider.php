<?php
/**
 * OpenAI Provider Implementation
 *
 * @package Viet_Lai_Noi_Dung_AI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIWD_OpenAI_Provider extends AIWD_AI_Provider_Base {

	/**
	 * @var string
	 */
	protected $provider_name = 'OpenAI';

	/**
	 * @var string
	 */
	protected $api_endpoint = 'https://api.openai.com/v1/chat/completions';

	/**
	 * @param array $config Configuration array.
	 */
	public function __construct( $config = array() ) {
		parent::__construct( $config );
		if ( empty( $this->model ) ) {
			$this->model = 'gpt-4o-mini';
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate_content( $prompt ) {
		return $this->openai_chat_generate( $prompt );
	}

	/**
	 * {@inheritDoc}
	 */
	public function test_connection() {
		return $this->openai_chat_test();
	}
}
