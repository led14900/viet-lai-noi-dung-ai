<?php
/**
 * Settings Page — WordPress Settings API
 *
 * @package Viet_Lai_Noi_Dung_AI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIWD_Settings_Page {

	/**
	 * Render settings page.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Bạn không có quyền truy cập trang này.', 'viet-lai-noi-dung-ai' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- tab param is display-only.
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
		$valid_tabs = array( 'general', 'openai', 'claude', 'gemini', 'compatible', 'brand' );
		if ( ! in_array( $active_tab, $valid_tabs, true ) ) {
			$active_tab = 'general';
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Cấu hình Viết lại nội dung AI', 'viet-lai-noi-dung-ai' ); ?></h1>

			<?php settings_errors(); ?>

			<h2 class="nav-tab-wrapper">
				<?php
				$tabs = array(
					'general'    => __( 'Cài Đặt Chung', 'viet-lai-noi-dung-ai' ),
					'openai'     => 'OpenAI',
					'claude'     => 'Claude',
					'gemini'     => 'Gemini',
					'compatible' => __( 'Tương Thích OpenAI', 'viet-lai-noi-dung-ai' ),
					'brand'      => __( 'Thương Hiệu', 'viet-lai-noi-dung-ai' ),
				);
				foreach ( $tabs as $tab_key => $tab_label ) {
					$class = ( $active_tab === $tab_key ) ? ' nav-tab-active' : '';
					printf(
						'<a href="%s" class="nav-tab%s">%s</a>',
						esc_url( add_query_arg( array( 'page' => 'viet-lai-noi-dung-ai-settings', 'tab' => $tab_key ), admin_url( 'options-general.php' ) ) ),
						esc_attr( $class ),
						esc_html( $tab_label )
					);
				}
				?>
			</h2>

			<form method="post" action="options.php">
				<?php settings_fields( 'aiwd_' . $active_tab ); ?>

				<?php
				$render_method = 'render_' . $active_tab . '_tab';
				if ( method_exists( __CLASS__, $render_method ) ) {
					self::$render_method();
				}
				?>

				<?php submit_button( __( 'Lưu Cài Đặt', 'viet-lai-noi-dung-ai' ) ); ?>
			</form>

			<?php if ( in_array( $active_tab, array( 'openai', 'claude', 'gemini', 'compatible' ), true ) ) : ?>
			<hr>
			<h3><?php esc_html_e( 'Kiểm Tra Kết Nối', 'viet-lai-noi-dung-ai' ); ?></h3>
			<p>
				<button type="button" class="button aiwd-test-btn" data-provider="<?php echo esc_attr( $active_tab === 'compatible' ? 'openai_compatible' : $active_tab ); ?>">
					<?php esc_html_e( 'Test Kết Nối', 'viet-lai-noi-dung-ai' ); ?>
				</button>
				<span class="aiwd-test-result"></span>
			</p>
			<p class="description"><?php esc_html_e( 'Lưu ý: Hãy lưu cài đặt trước khi test kết nối.', 'viet-lai-noi-dung-ai' ); ?></p>
			<?php endif; ?>

			<div class="aiwd-footer">
				<h3><?php esc_html_e( 'Về công cụ Viết lại nội dung AI', 'viet-lai-noi-dung-ai' ); ?></h3>
				<p>
					<strong><?php esc_html_e( 'Tác giả:', 'viet-lai-noi-dung-ai' ); ?></strong> Phạm Ngọc Tú<br>
					<strong><?php esc_html_e( 'Website:', 'viet-lai-noi-dung-ai' ); ?></strong>
					<a href="https://congcuseoai.com" target="_blank" rel="noopener noreferrer">congcuseoai.com</a>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * General tab.
	 */
	private static function render_general_tab() {
		$active = get_option( 'aiwd_active_provider', 'openai' );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="aiwd_active_provider"><?php esc_html_e( 'Nhà Cung Cấp AI', 'viet-lai-noi-dung-ai' ); ?></label></th>
				<td>
					<select name="aiwd_active_provider" id="aiwd_active_provider" class="regular-text">
						<option value="openai" <?php selected( $active, 'openai' ); ?>>OpenAI</option>
						<option value="claude" <?php selected( $active, 'claude' ); ?>>Claude</option>
						<option value="gemini" <?php selected( $active, 'gemini' ); ?>>Google Gemini</option>
						<option value="openai_compatible" <?php selected( $active, 'openai_compatible' ); ?>>OpenAI Compatible</option>
					</select>
					<p class="description"><?php esc_html_e( 'Chọn nhà cung cấp AI bạn muốn sử dụng để tạo nội dung', 'viet-lai-noi-dung-ai' ); ?></p>
				</td>
			</tr>
		</table>

		<div class="notice notice-info inline" style="margin-top:20px">
			<h3><?php esc_html_e( 'Hướng dẫn sử dụng nhanh:', 'viet-lai-noi-dung-ai' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Chọn tab nhà cung cấp (OpenAI, Claude, Gemini...) ở trên.', 'viet-lai-noi-dung-ai' ); ?></li>
				<li><?php esc_html_e( 'Nhập API Key và cấu hình Model, Temperature.', 'viet-lai-noi-dung-ai' ); ?></li>
				<li><?php esc_html_e( 'Quay lại "Cài Đặt Chung" chọn nhà cung cấp vừa cấu hình.', 'viet-lai-noi-dung-ai' ); ?></li>
				<li><?php esc_html_e( 'Lưu cài đặt.', 'viet-lai-noi-dung-ai' ); ?></li>
				<li><?php printf( esc_html__( 'Truy cập %s để bắt đầu viết lại nội dung.', 'viet-lai-noi-dung-ai' ), '<strong>' . esc_html__( 'Posts → Viết lại nội dung AI', 'viet-lai-noi-dung-ai' ) . '</strong>' ); ?></li>
			</ol>
		</div>
		<?php
	}

	/**
	 * OpenAI tab.
	 */
	private static function render_openai_tab() {
		$api_key = AIWD_Encryption::decrypt( get_option( 'aiwd_openai_api_key', '' ) );
		$model   = get_option( 'aiwd_openai_model', 'gpt-4o-mini' );
		$temp    = get_option( 'aiwd_openai_temperature', '0.7' );
		$known   = array( 'gpt-4o', 'gpt-4o-mini', 'o1-preview', 'o1-mini', 'gpt-4-turbo', 'gpt-3.5-turbo' );
		$is_custom = ! in_array( $model, $known, true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="aiwd_openai_api_key"><?php esc_html_e( 'API Key', 'viet-lai-noi-dung-ai' ); ?> <span style="color:red">*</span></label></th>
				<td>
					<input type="password" name="aiwd_openai_api_key" id="aiwd_openai_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" />
					<p class="description"><?php printf( esc_html__( 'Lấy API key tại %s', 'viet-lai-noi-dung-ai' ), '<a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="aiwd_openai_model"><?php esc_html_e( 'Model', 'viet-lai-noi-dung-ai' ); ?></label></th>
				<td>
					<?php self::render_model_select( 'aiwd_openai_model', $model, array(
						'gpt-4o'        => 'GPT-4o',
						'gpt-4o-mini'   => 'GPT-4o Mini (Khuyên dùng)',
						'o1-preview'    => 'o1-preview (Reasoning)',
						'o1-mini'       => 'o1-mini',
						'gpt-4-turbo'   => 'GPT-4 Turbo',
						'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
					), $is_custom ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="aiwd_openai_temperature"><?php esc_html_e( 'Temperature', 'viet-lai-noi-dung-ai' ); ?></label></th>
				<td>
					<input type="number" step="0.1" min="0" max="2" name="aiwd_openai_temperature" id="aiwd_openai_temperature" value="<?php echo esc_attr( $temp ); ?>" class="small-text" />
					<p class="description"><?php esc_html_e( 'Mức độ sáng tạo (0-2). Khuyến nghị: 0.7', 'viet-lai-noi-dung-ai' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Claude tab.
	 */
	private static function render_claude_tab() {
		$api_key = AIWD_Encryption::decrypt( get_option( 'aiwd_claude_api_key', '' ) );
		$model   = get_option( 'aiwd_claude_model', 'claude-3-5-sonnet-20241022' );
		$temp    = get_option( 'aiwd_claude_temperature', '0.7' );
		$known   = array( 'claude-sonnet-4-5', 'claude-3-5-sonnet-latest', 'claude-3-5-sonnet-20241022', 'claude-3-5-haiku-latest', 'claude-3-opus-latest' );
		$is_custom = ! in_array( $model, $known, true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="aiwd_claude_api_key"><?php esc_html_e( 'API Key', 'viet-lai-noi-dung-ai' ); ?> <span style="color:red">*</span></label></th>
				<td>
					<input type="password" name="aiwd_claude_api_key" id="aiwd_claude_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" />
					<p class="description"><?php printf( esc_html__( 'Lấy API key tại %s', 'viet-lai-noi-dung-ai' ), '<a href="https://console.anthropic.com/settings/keys" target="_blank">Anthropic Console</a>' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="aiwd_claude_model"><?php esc_html_e( 'Model', 'viet-lai-noi-dung-ai' ); ?></label></th>
				<td>
					<?php self::render_model_select( 'aiwd_claude_model', $model, array(
						'claude-sonnet-4-5'          => 'Claude 4.5 Sonnet (Mới nhất)',
						'claude-3-5-sonnet-latest'   => 'Claude 3.5 Sonnet (Latest)',
						'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet (v2)',
						'claude-3-5-haiku-latest'    => 'Claude 3.5 Haiku (Nhanh & Rẻ)',
						'claude-3-opus-latest'       => 'Claude 3 Opus (Mạnh nhất)',
					), $is_custom ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="aiwd_claude_temperature"><?php esc_html_e( 'Temperature', 'viet-lai-noi-dung-ai' ); ?></label></th>
				<td>
					<input type="number" step="0.1" min="0" max="1" name="aiwd_claude_temperature" id="aiwd_claude_temperature" value="<?php echo esc_attr( $temp ); ?>" class="small-text" />
					<p class="description"><?php esc_html_e( 'Mức độ sáng tạo (0-1). Khuyến nghị: 0.7', 'viet-lai-noi-dung-ai' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Gemini tab.
	 */
	private static function render_gemini_tab() {
		$api_key = AIWD_Encryption::decrypt( get_option( 'aiwd_gemini_api_key', '' ) );
		$model   = get_option( 'aiwd_gemini_model', 'gemini-2.5-flash' );
		$temp    = get_option( 'aiwd_gemini_temperature', '0.7' );
		$known   = array( 'gemini-3.0-flash', 'gemini-2.5-pro', 'gemini-2.5-flash', 'gemini-1.5-pro', 'gemini-1.5-flash' );
		$is_custom = ! in_array( $model, $known, true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="aiwd_gemini_api_key"><?php esc_html_e( 'API Key', 'viet-lai-noi-dung-ai' ); ?> <span style="color:red">*</span></label></th>
				<td>
					<input type="password" name="aiwd_gemini_api_key" id="aiwd_gemini_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" />
					<p class="description"><?php printf( esc_html__( 'Lấy API key tại %s', 'viet-lai-noi-dung-ai' ), '<a href="https://aistudio.google.com/apikey" target="_blank">Google AI Studio</a>' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="aiwd_gemini_model"><?php esc_html_e( 'Model', 'viet-lai-noi-dung-ai' ); ?></label></th>
				<td>
					<?php self::render_model_select( 'aiwd_gemini_model', $model, array(
						'gemini-3.0-flash' => 'Gemini 3.0 Flash (Mới nhất)',
						'gemini-2.5-pro'   => 'Gemini 2.5 Pro (Mạnh nhất)',
						'gemini-2.5-flash' => 'Gemini 2.5 Flash (Khuyên dùng)',
						'gemini-1.5-pro'   => 'Gemini 1.5 Pro',
						'gemini-1.5-flash' => 'Gemini 1.5 Flash',
					), $is_custom ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="aiwd_gemini_temperature"><?php esc_html_e( 'Temperature', 'viet-lai-noi-dung-ai' ); ?></label></th>
				<td>
					<input type="number" step="0.1" min="0" max="2" name="aiwd_gemini_temperature" id="aiwd_gemini_temperature" value="<?php echo esc_attr( $temp ); ?>" class="small-text" />
					<p class="description"><?php esc_html_e( 'Mức độ sáng tạo (0-2). Khuyến nghị: 0.7', 'viet-lai-noi-dung-ai' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Compatible tab.
	 */
	private static function render_compatible_tab() {
		$api_key  = AIWD_Encryption::decrypt( get_option( 'aiwd_compatible_api_key', '' ) );
		$endpoint = get_option( 'aiwd_compatible_endpoint', '' );
		$model    = get_option( 'aiwd_compatible_model', '' );
		$temp     = get_option( 'aiwd_compatible_temperature', '0.7' );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="aiwd_compatible_endpoint"><?php esc_html_e( 'API Endpoint', 'viet-lai-noi-dung-ai' ); ?> <span style="color:red">*</span></label></th>
				<td>
					<input type="url" name="aiwd_compatible_endpoint" id="aiwd_compatible_endpoint" value="<?php echo esc_attr( $endpoint ); ?>" class="regular-text" placeholder="https://ai.megallm.io/v1/chat/completions" />
					<p class="description"><?php esc_html_e( 'URL endpoint của API', 'viet-lai-noi-dung-ai' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="aiwd_compatible_api_key"><?php esc_html_e( 'API Key', 'viet-lai-noi-dung-ai' ); ?> <span style="color:red">*</span></label></th>
				<td><input type="password" name="aiwd_compatible_api_key" id="aiwd_compatible_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aiwd_compatible_model"><?php esc_html_e( 'Model Name', 'viet-lai-noi-dung-ai' ); ?> <span style="color:red">*</span></label></th>
				<td>
					<input type="text" name="aiwd_compatible_model" id="aiwd_compatible_model" value="<?php echo esc_attr( $model ); ?>" class="regular-text" placeholder="gpt-4o-mini" />
					<p class="description"><?php esc_html_e( 'Nhập ID model từ nhà cung cấp dịch vụ', 'viet-lai-noi-dung-ai' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="aiwd_compatible_temperature"><?php esc_html_e( 'Temperature', 'viet-lai-noi-dung-ai' ); ?></label></th>
				<td>
					<input type="number" step="0.1" min="0" max="2" name="aiwd_compatible_temperature" id="aiwd_compatible_temperature" value="<?php echo esc_attr( $temp ); ?>" class="small-text" />
					<p class="description"><?php esc_html_e( 'Mức độ sáng tạo (0-2). Khuyến nghị: 0.7', 'viet-lai-noi-dung-ai' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Brand tab.
	 */
	private static function render_brand_tab() {
		$name    = get_option( 'aiwd_brand_name', 'Công Cụ SEO AI' );
		$desc    = get_option( 'aiwd_brand_description', '' );
		$website = get_option( 'aiwd_brand_website', '' );
		$contact = get_option( 'aiwd_brand_contact_name', '' );
		$phone   = get_option( 'aiwd_brand_contact_phone', '' );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="aiwd_brand_name"><?php esc_html_e( 'Tên Thương Hiệu', 'viet-lai-noi-dung-ai' ); ?></label></th>
				<td><input type="text" name="aiwd_brand_name" id="aiwd_brand_name" value="<?php echo esc_attr( $name ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aiwd_brand_description"><?php esc_html_e( 'Mô Tả', 'viet-lai-noi-dung-ai' ); ?></label></th>
				<td><textarea name="aiwd_brand_description" id="aiwd_brand_description" rows="3" class="large-text"><?php echo esc_textarea( $desc ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="aiwd_brand_website"><?php esc_html_e( 'Website', 'viet-lai-noi-dung-ai' ); ?></label></th>
				<td><input type="text" name="aiwd_brand_website" id="aiwd_brand_website" value="<?php echo esc_attr( $website ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aiwd_brand_contact_name"><?php esc_html_e( 'Tên Liên Hệ', 'viet-lai-noi-dung-ai' ); ?></label></th>
				<td><input type="text" name="aiwd_brand_contact_name" id="aiwd_brand_contact_name" value="<?php echo esc_attr( $contact ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aiwd_brand_contact_phone"><?php esc_html_e( 'Số Điện Thoại', 'viet-lai-noi-dung-ai' ); ?></label></th>
				<td><input type="text" name="aiwd_brand_contact_phone" id="aiwd_brand_contact_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text" /></td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render model select with custom option.
	 *
	 * @param string $name      Input name.
	 * @param string $current   Current value.
	 * @param array  $options   Known models.
	 * @param bool   $is_custom Whether current value is custom.
	 */
	private static function render_model_select( $name, $current, $options, $is_custom ) {
		$custom_name = str_replace( '_model', '_custom_model', $name );
		$select_id   = $name;
		$custom_id   = $custom_name . '_container';
		?>
		<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $select_id ); ?>" class="regular-text"
				onchange="document.getElementById('<?php echo esc_js( $custom_id ); ?>').style.display = this.value === 'custom' ? 'block' : 'none';">
			<?php foreach ( $options as $val => $label ) : ?>
				<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current, $val ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
			<option value="custom" <?php selected( $is_custom ); ?>><?php esc_html_e( 'Model Khác (Nhập thủ công)', 'viet-lai-noi-dung-ai' ); ?></option>
		</select>
		<div id="<?php echo esc_attr( $custom_id ); ?>" style="margin-top:5px; display:<?php echo $is_custom ? 'block' : 'none'; ?>;">
			<input type="text" name="<?php echo esc_attr( $custom_name ); ?>" value="<?php echo esc_attr( $is_custom ? $current : '' ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Nhập ID model', 'viet-lai-noi-dung-ai' ); ?>" />
		</div>
		<?php
	}

	/**
	 * AJAX handler for test connection.
	 */
	public static function ajax_test_connection() {
		check_ajax_referer( 'aiwd_test_connection', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Bạn không có quyền thực hiện hành động này.', 'viet-lai-noi-dung-ai' ) );
		}

		$provider = isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '';
		$result   = AIWD_Article_Generator::test_provider( $provider );

		if ( $result['success'] ) {
			wp_send_json_success( $result['message'] );
		} else {
			wp_send_json_error( $result['message'] );
		}
	}
}
