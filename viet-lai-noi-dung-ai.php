<?php
/**
 * Plugin Name: Viết lại nội dung AI
 * Plugin URI: https://congcuseoai.com/
 * Description: Viết lại nội dung bằng AI với hỗ trợ nhiều nhà cung cấp (OpenAI, Claude, Gemini, OpenAI-compatible) và lưu thành bản nháp tự động.
 * Version: 1.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Phạm Ngọc Tú
 * Author URI: https://congcuseoai.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: viet-lai-noi-dung-ai
 * Domain Path: /languages
 *
 * @package Viet_Lai_Noi_Dung_AI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'AIWD_VERSION', '1.0' );
define( 'AIWD_PLUGIN_FILE', __FILE__ );
define( 'AIWD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AIWD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AIWD_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Load plugin text domain for translations.
 */
function aiwd_load_textdomain() {
	load_plugin_textdomain( 'viet-lai-noi-dung-ai', false, dirname( AIWD_PLUGIN_BASENAME ) . '/languages' );
}
add_action( 'init', 'aiwd_load_textdomain' );

/**
 * Load plugin classes.
 */
function aiwd_load_classes() {
	require_once AIWD_PLUGIN_DIR . 'includes/class-encryption.php';
	require_once AIWD_PLUGIN_DIR . 'includes/class-ai-provider-base.php';
	require_once AIWD_PLUGIN_DIR . 'includes/class-openai-provider.php';
	require_once AIWD_PLUGIN_DIR . 'includes/class-claude-provider.php';
	require_once AIWD_PLUGIN_DIR . 'includes/class-openai-compatible-provider.php';
	require_once AIWD_PLUGIN_DIR . 'includes/class-gemini-provider.php';
	require_once AIWD_PLUGIN_DIR . 'includes/class-prompt-builder.php';
	require_once AIWD_PLUGIN_DIR . 'includes/class-article-generator.php';

	if ( is_admin() ) {
		require_once AIWD_PLUGIN_DIR . 'admin/class-settings-page.php';
		require_once AIWD_PLUGIN_DIR . 'admin/class-generator-page.php';
	}
}
add_action( 'plugins_loaded', 'aiwd_load_classes' );

/**
 * Register admin menus.
 */
function aiwd_register_admin_menus() {
	add_posts_page(
		__( 'Viết lại nội dung AI', 'viet-lai-noi-dung-ai' ),
		__( 'Viết lại nội dung AI', 'viet-lai-noi-dung-ai' ),
		'edit_posts',
		'viet-lai-noi-dung-ai',
		array( 'AIWD_Generator_Page', 'render' ),
		10
	);

	add_options_page(
		__( 'Cấu hình Viết lại nội dung AI', 'viet-lai-noi-dung-ai' ),
		__( 'Viết lại nội dung AI', 'viet-lai-noi-dung-ai' ),
		'manage_options',
		'viet-lai-noi-dung-ai-settings',
		array( 'AIWD_Settings_Page', 'render' )
	);
}
add_action( 'admin_menu', 'aiwd_register_admin_menus' );

/**
 * Register settings via WordPress Settings API.
 */
function aiwd_register_settings() {
	// General.
	register_setting( 'aiwd_general', 'aiwd_active_provider', array(
		'type'              => 'string',
		'sanitize_callback' => 'aiwd_sanitize_provider',
		'default'           => 'openai',
	) );

	// OpenAI.
	register_setting( 'aiwd_openai', 'aiwd_openai_api_key', array(
		'type'              => 'string',
		'sanitize_callback' => 'aiwd_sanitize_api_key',
	) );
	register_setting( 'aiwd_openai', 'aiwd_openai_model', array(
		'type'              => 'string',
		'sanitize_callback' => 'aiwd_sanitize_model',
		'default'           => 'gpt-4o-mini',
	) );
	register_setting( 'aiwd_openai', 'aiwd_openai_temperature', array(
		'type'              => 'number',
		'sanitize_callback' => 'aiwd_sanitize_temperature',
		'default'           => 0.7,
	) );

	// Claude.
	register_setting( 'aiwd_claude', 'aiwd_claude_api_key', array(
		'type'              => 'string',
		'sanitize_callback' => 'aiwd_sanitize_api_key',
	) );
	register_setting( 'aiwd_claude', 'aiwd_claude_model', array(
		'type'              => 'string',
		'sanitize_callback' => 'aiwd_sanitize_model',
		'default'           => 'claude-3-5-sonnet-20241022',
	) );
	register_setting( 'aiwd_claude', 'aiwd_claude_temperature', array(
		'type'              => 'number',
		'sanitize_callback' => 'aiwd_sanitize_claude_temperature',
		'default'           => 0.7,
	) );

	// Gemini.
	register_setting( 'aiwd_gemini', 'aiwd_gemini_api_key', array(
		'type'              => 'string',
		'sanitize_callback' => 'aiwd_sanitize_api_key',
	) );
	register_setting( 'aiwd_gemini', 'aiwd_gemini_model', array(
		'type'              => 'string',
		'sanitize_callback' => 'aiwd_sanitize_model',
		'default'           => 'gemini-2.5-flash',
	) );
	register_setting( 'aiwd_gemini', 'aiwd_gemini_temperature', array(
		'type'              => 'number',
		'sanitize_callback' => 'aiwd_sanitize_temperature',
		'default'           => 0.7,
	) );

	// Compatible.
	register_setting( 'aiwd_compatible', 'aiwd_compatible_api_key', array(
		'type'              => 'string',
		'sanitize_callback' => 'aiwd_sanitize_api_key',
	) );
	register_setting( 'aiwd_compatible', 'aiwd_compatible_endpoint', array(
		'type'              => 'string',
		'sanitize_callback' => 'aiwd_sanitize_endpoint_url',
	) );
	register_setting( 'aiwd_compatible', 'aiwd_compatible_model', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	register_setting( 'aiwd_compatible', 'aiwd_compatible_temperature', array(
		'type'              => 'number',
		'sanitize_callback' => 'aiwd_sanitize_temperature',
		'default'           => 0.7,
	) );

	// Brand.
	register_setting( 'aiwd_brand', 'aiwd_brand_name', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	register_setting( 'aiwd_brand', 'aiwd_brand_description', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	register_setting( 'aiwd_brand', 'aiwd_brand_website', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	register_setting( 'aiwd_brand', 'aiwd_brand_contact_name', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	register_setting( 'aiwd_brand', 'aiwd_brand_contact_phone', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	) );
}
add_action( 'admin_init', 'aiwd_register_settings' );

/**
 * Sanitize active provider.
 *
 * @param string $value Provider key.
 * @return string Valid provider key.
 */
function aiwd_sanitize_provider( $value ) {
	$value     = sanitize_key( $value );
	$providers = array( 'openai', 'claude', 'gemini', 'openai_compatible' );

	return in_array( $value, $providers, true ) ? $value : 'openai';
}

/**
 * Sanitize API key — encrypt before storing.
 *
 * @param string $value Raw API key.
 * @return string Encrypted API key.
 */
function aiwd_sanitize_api_key( $value ) {
	$value = sanitize_text_field( $value );
	if ( ! empty( $value ) && class_exists( 'AIWD_Encryption' ) ) {
		// If already encrypted, decrypt first then re-encrypt (value might have been displayed decrypted).
		if ( AIWD_Encryption::is_encrypted( $value ) ) {
			return $value;
		}
		return AIWD_Encryption::encrypt( $value );
	}
	return $value;
}

/**
 * Sanitize model name — handle custom model input.
 *
 * @param string $value Model name.
 * @return string Sanitized model name.
 */
function aiwd_sanitize_model( $value ) {
	$value = sanitize_text_field( $value );
	// If "custom" was selected, check for the custom model field in POST.
	if ( 'custom' === $value ) {
		// Find the matching custom model field from $_POST.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified by Settings API.
		$post_data = wp_unslash( $_POST );
		foreach ( $post_data as $key => $val ) {
			if ( false !== strpos( $key, '_custom_model' ) ) {
				return sanitize_text_field( $val );
			}
		}
	}
	return $value;
}

/**
 * Sanitize temperature value.
 *
 * @param mixed $value Temperature value.
 * @return float Clamped temperature.
 */
function aiwd_sanitize_temperature( $value ) {
	$value = floatval( $value );
	return max( 0, min( 2, $value ) );
}

/**
 * Sanitize Claude temperature value.
 *
 * @param mixed $value Temperature value.
 * @return float Clamped temperature.
 */
function aiwd_sanitize_claude_temperature( $value ) {
	$value = floatval( $value );
	return max( 0, min( 1, $value ) );
}

/**
 * Sanitize endpoint URL — enforce HTTPS and block private/localhost IPs (SSRF protection).
 *
 * @param string $value Endpoint URL.
 * @return string Sanitized URL or empty string on failure.
 */
function aiwd_sanitize_endpoint_url( $value ) {
	$value = esc_url_raw( $value, array( 'https' ) );
	if ( empty( $value ) ) {
		add_settings_error(
			'aiwd_compatible_endpoint',
			'invalid_scheme',
			__( 'Endpoint API phải sử dụng HTTPS.', 'viet-lai-noi-dung-ai' ),
			'error'
		);
		return '';
	}

	$host = wp_parse_url( $value, PHP_URL_HOST );
	if ( empty( $host ) ) {
		return '';
	}

	$normalized_host = strtolower( trim( $host, '[]' ) );

	// Block localhost and private/reserved IPs.
	$blocked_hosts = array( 'localhost', '127.0.0.1', '::1', '0.0.0.0' );
	if ( in_array( $normalized_host, $blocked_hosts, true ) ) {
		add_settings_error(
			'aiwd_compatible_endpoint',
			'blocked_host',
			__( 'Endpoint API không được trỏ đến localhost hoặc IP nội bộ.', 'viet-lai-noi-dung-ai' ),
			'error'
		);
		return '';
	}

	// Block literal private/link-local IPs before DNS resolution.
	if (
		filter_var( $normalized_host, FILTER_VALIDATE_IP )
		&& false === filter_var( $normalized_host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE )
	) {
		add_settings_error(
			'aiwd_compatible_endpoint',
			'private_ip',
			__( 'Endpoint API không được trỏ đến địa chỉ IP nội bộ hoặc reserved.', 'viet-lai-noi-dung-ai' ),
			'error'
		);
		return '';
	}

	// Block private/link-local IP ranges after DNS resolution.
	$ip = gethostbyname( $normalized_host );
	if ( $ip !== $normalized_host && filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false ) {
		add_settings_error(
			'aiwd_compatible_endpoint',
			'private_ip',
			__( 'Endpoint API không được trỏ đến địa chỉ IP nội bộ hoặc reserved.', 'viet-lai-noi-dung-ai' ),
			'error'
		);
		return '';
	}

	return $value;
}

/**
 * Enqueue admin assets.
 *
 * @param string $hook Current admin page hook.
 */
function aiwd_enqueue_admin_assets( $hook ) {
	$plugin_pages = array( 'posts_page_viet-lai-noi-dung-ai', 'settings_page_viet-lai-noi-dung-ai-settings' );
	if ( ! in_array( $hook, $plugin_pages, true ) ) {
		return;
	}

	wp_enqueue_style(
		'aiwd-admin',
		AIWD_PLUGIN_URL . 'admin/css/admin.css',
		array(),
		AIWD_VERSION
	);

	wp_enqueue_script(
		'aiwd-generator',
		AIWD_PLUGIN_URL . 'admin/js/generator.js',
		array( 'jquery' ),
		AIWD_VERSION,
		true
	);

	wp_localize_script( 'aiwd-generator', 'aiwdGenerator', array(
		'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
		'testNonce' => wp_create_nonce( 'aiwd_test_connection' ),
		'i18n'      => array(
			'wordCount'       => __( 'Số từ', 'viet-lai-noi-dung-ai' ),
			'genericError'    => __( 'Đã có lỗi xảy ra', 'viet-lai-noi-dung-ai' ),
			'connectionError' => __( 'Lỗi kết nối', 'viet-lai-noi-dung-ai' ),
			'timeoutError'    => __( '⚠️ Quá thời gian chờ. Vui lòng kiểm tra danh sách bản nháp WordPress, nội dung có thể đã được lưu.', 'viet-lai-noi-dung-ai' ),
			'copied'          => __( 'Đã sao chép!', 'viet-lai-noi-dung-ai' ),
			'testing'         => __( 'Đang kiểm tra...', 'viet-lai-noi-dung-ai' ),
		),
	) );
}
add_action( 'admin_enqueue_scripts', 'aiwd_enqueue_admin_assets' );

/**
 * Plugin activation hook.
 */
function aiwd_activate_plugin() {
	$defaults = array(
		'aiwd_active_provider'        => 'openai',
		'aiwd_openai_model'           => 'gpt-4o-mini',
		'aiwd_openai_temperature'     => '0.7',
		'aiwd_claude_model'           => 'claude-3-5-sonnet-20241022',
		'aiwd_claude_temperature'     => '0.7',
		'aiwd_compatible_temperature' => '0.7',
		'aiwd_gemini_model'           => 'gemini-2.5-flash',
		'aiwd_gemini_temperature'     => '0.7',
		'aiwd_brand_name'             => 'Công Cụ SEO AI',
		'aiwd_brand_description'      => 'Tối ưu hóa mọi khía cạnh SEO của bạn với sức mạnh của Trí tuệ nhân tạo.',
		'aiwd_brand_website'          => 'congcuseoai.com',
		'aiwd_brand_contact_name'     => 'Phạm Ngọc Tú',
		'aiwd_brand_contact_phone'    => '0896009111',
	);

	foreach ( $defaults as $key => $value ) {
		if ( false === get_option( $key ) ) {
			add_option( $key, $value );
		}
	}

	// Load encryption class directly — plugins_loaded has not fired yet during activation.
	if ( ! class_exists( 'AIWD_Encryption' ) ) {
		require_once AIWD_PLUGIN_DIR . 'includes/class-encryption.php';
	}

	$api_key_options = array(
		'aiwd_openai_api_key',
		'aiwd_claude_api_key',
		'aiwd_gemini_api_key',
		'aiwd_compatible_api_key',
	);
	foreach ( $api_key_options as $opt ) {
		AIWD_Encryption::maybe_encrypt_option( $opt );
	}
}
register_activation_hook( __FILE__, 'aiwd_activate_plugin' );

/**
 * Plugin deactivation hook.
 */
function aiwd_deactivate_plugin() {
	// Reserved for future cleanup tasks.
}
register_deactivation_hook( __FILE__, 'aiwd_deactivate_plugin' );

/**
 * Add settings link on plugins page.
 *
 * @param array $links Plugin action links.
 * @return array
 */
function aiwd_add_settings_link( $links ) {
	$settings_link  = '<a href="' . admin_url( 'options-general.php?page=viet-lai-noi-dung-ai-settings' ) . '">' . __( 'Cài đặt', 'viet-lai-noi-dung-ai' ) . '</a>';
	$generator_link = '<a href="' . admin_url( 'edit.php?page=viet-lai-noi-dung-ai' ) . '" style="font-weight:bold;color:#00a32a">' . __( 'Viết lại nội dung AI', 'viet-lai-noi-dung-ai' ) . '</a>';
	array_unshift( $links, $generator_link, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'aiwd_add_settings_link' );

/**
 * Add admin notice if no API key is configured.
 */
function aiwd_admin_notice_no_api_key() {
	$screen = get_current_screen();
	if ( $screen && 'settings_page_viet-lai-noi-dung-ai-settings' === $screen->id ) {
		return;
	}

	$active_provider = get_option( 'aiwd_active_provider', 'openai' );
	$key_map         = array(
		'openai'            => 'aiwd_openai_api_key',
		'claude'            => 'aiwd_claude_api_key',
		'openai_compatible' => 'aiwd_compatible_api_key',
		'gemini'            => 'aiwd_gemini_api_key',
	);

	$option_name = isset( $key_map[ $active_provider ] ) ? $key_map[ $active_provider ] : '';
	$api_key     = $option_name ? get_option( $option_name, '' ) : '';

	if ( empty( $api_key ) && current_user_can( 'manage_options' ) ) {
		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'Viết lại nội dung AI:', 'viet-lai-noi-dung-ai' ); ?></strong>
				<?php esc_html_e( 'Vui lòng cấu hình API key để sử dụng plugin.', 'viet-lai-noi-dung-ai' ); ?>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=viet-lai-noi-dung-ai-settings' ) ); ?>" class="button button-small" style="margin-left:10px">
					<?php esc_html_e( 'Cài đặt ngay', 'viet-lai-noi-dung-ai' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'aiwd_admin_notice_no_api_key' );

// AJAX handlers.
add_action( 'wp_ajax_aiwd_generate_article', array( 'AIWD_Generator_Page', 'handle_ajax_request' ) );
add_action( 'wp_ajax_aiwd_test_connection', array( 'AIWD_Settings_Page', 'ajax_test_connection' ) );
