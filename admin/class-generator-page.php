<?php
/**
 * Content rewrite admin page.
 *
 * @package Viet_Lai_Noi_Dung_AI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIWD_Generator_Page {

	/**
	 * Render generator page.
	 */
	public static function render() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'Bạn không có quyền truy cập trang này.', 'viet-lai-noi-dung-ai' ) );
		}

		$active_provider = get_option( 'aiwd_active_provider', 'openai' );
		$provider_names  = array(
			'openai'            => 'OpenAI',
			'claude'            => 'Claude',
			'openai_compatible' => __( 'Tương Thích OpenAI', 'viet-lai-noi-dung-ai' ),
			'gemini'            => 'Google Gemini',
		);
		$provider_display = isset( $provider_names[ $active_provider ] ) ? $provider_names[ $active_provider ] : $active_provider;

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Viết lại nội dung AI', 'viet-lai-noi-dung-ai' ); ?></h1>

			<div class="notice notice-info">
				<p>
					<strong><?php esc_html_e( 'Nhà cung cấp AI đang dùng:', 'viet-lai-noi-dung-ai' ); ?></strong>
					<?php echo esc_html( $provider_display ); ?>
					&nbsp;|&nbsp;
					<a href="<?php echo esc_url( admin_url( 'options-general.php?page=viet-lai-noi-dung-ai-settings' ) ); ?>">
						<?php esc_html_e( 'Thay đổi cài đặt', 'viet-lai-noi-dung-ai' ); ?>
					</a>
				</p>
			</div>

			<div id="aiwd-message-container"></div>

			<div class="aiwd-layout-container">
				<!-- Left Column: Form -->
				<div class="aiwd-left-column">
					<div class="postbox" style="padding:20px">
						<h2 class="hndle" style="margin-top:0"><?php esc_html_e( 'Thông tin nội dung', 'viet-lai-noi-dung-ai' ); ?></h2>
						<form id="aiwd-form" method="post" action="">
							<?php wp_nonce_field( 'aiwd_generate_article', 'aiwd_nonce' ); ?>
							<input type="hidden" name="action" value="aiwd_generate_article" />

							<table class="form-table" role="presentation" style="margin-top:0">
								<tbody>
									<tr>
										<th scope="row"><label for="domain"><?php esc_html_e( 'Lĩnh vực', 'viet-lai-noi-dung-ai' ); ?></label></th>
										<td>
											<input name="domain" id="domain" type="text" class="regular-text" placeholder="VD: Marketing, Sức khỏe, Công nghệ" />
											<p class="description"><?php esc_html_e( 'Lĩnh vực chuyên môn của nội dung', 'viet-lai-noi-dung-ai' ); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="audience"><?php esc_html_e( 'Đối tượng mục tiêu', 'viet-lai-noi-dung-ai' ); ?></label></th>
										<td><input name="audience" id="audience" type="text" class="regular-text" placeholder="VD: Chủ doanh nghiệp nhỏ" /></td>
									</tr>
									<tr>
										<th scope="row"><label for="wordCount"><?php esc_html_e( 'Số từ', 'viet-lai-noi-dung-ai' ); ?></label></th>
										<td>
											<select name="wordCount" id="wordCount">
												<option value="1000">1000</option>
												<option value="1500">1500</option>
												<option value="2000" selected>2000</option>
												<option value="2500">2500</option>
												<option value="3000">3000</option>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="topic"><?php esc_html_e( 'Chủ đề nội dung', 'viet-lai-noi-dung-ai' ); ?> <span style="color:#d63638">*</span></label></th>
										<td><input name="topic" id="topic" type="text" class="large-text" required placeholder="VD: 10 cách tối ưu SEO cho website WordPress" /></td>
									</tr>
									<tr>
										<th scope="row"><label for="mainKeyword"><?php esc_html_e( 'Từ khóa chính', 'viet-lai-noi-dung-ai' ); ?> <span style="color:#d63638">*</span></label></th>
										<td><input name="mainKeyword" id="mainKeyword" type="text" class="regular-text" required placeholder="VD: tối ưu SEO" /></td>
									</tr>
									<tr>
										<th scope="row"><label for="secondaryKeywords"><?php esc_html_e( 'Từ khóa phụ', 'viet-lai-noi-dung-ai' ); ?></label></th>
										<td><input name="secondaryKeywords" id="secondaryKeywords" type="text" class="large-text" placeholder="Cách nhau bởi dấu phẩy" /></td>
									</tr>
									<tr>
										<th scope="row"><label for="contentGap"><?php esc_html_e( 'Nội dung cần viết lại', 'viet-lai-noi-dung-ai' ); ?></label></th>
										<td><textarea name="contentGap" id="contentGap" rows="4" class="large-text" placeholder="Dán nội dung gốc hoặc yêu cầu cụ thể cần viết lại..."></textarea></td>
									</tr>
									<tr>
										<th scope="row"><label for="structure"><?php esc_html_e( 'Cấu trúc', 'viet-lai-noi-dung-ai' ); ?></label></th>
										<td>
											<select name="structure" id="structure" class="regular-text">
												<option value="Nội dung chuẩn SEO"><?php esc_html_e( 'Nội dung chuẩn SEO', 'viet-lai-noi-dung-ai' ); ?></option>
												<option value="Nội dung dạng danh sách (Listicle)">Listicle</option>
												<option value="Nội dung hướng dẫn (How-to Guide)">How-to Guide</option>
												<option value="Nội dung đánh giá (Review Article)">Review</option>
												<option value="Nội dung so sánh (Comparison Article)">Comparison</option>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="style"><?php esc_html_e( 'Phong cách viết', 'viet-lai-noi-dung-ai' ); ?></label></th>
										<td>
											<select name="style" id="style" class="regular-text">
												<option value="Chuyên nghiệp"><?php esc_html_e( 'Chuyên nghiệp', 'viet-lai-noi-dung-ai' ); ?></option>
												<option value="Thân thiện"><?php esc_html_e( 'Thân thiện', 'viet-lai-noi-dung-ai' ); ?></option>
												<option value="Hài hước"><?php esc_html_e( 'Hài hước', 'viet-lai-noi-dung-ai' ); ?></option>
												<option value="Học thuật"><?php esc_html_e( 'Học thuật', 'viet-lai-noi-dung-ai' ); ?></option>
												<option value="Thuyết phục"><?php esc_html_e( 'Thuyết phục', 'viet-lai-noi-dung-ai' ); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="intent"><?php esc_html_e( 'Search Intent', 'viet-lai-noi-dung-ai' ); ?></label></th>
										<td>
											<select name="intent" id="intent" class="regular-text">
												<option value="Thông tin (Informational)">Informational</option>
												<option value="Điều tra thương mại (Commercial Investigation)">Commercial</option>
												<option value="Giao dịch (Transactional)">Transactional</option>
												<option value="Điều hướng (Navigational)">Navigational</option>
											</select>
										</td>
									</tr>
								</tbody>
							</table>

							<p class="submit" style="padding-bottom:0;margin-bottom:0">
								<button type="submit" id="aiwd-submit-btn" class="button button-primary button-hero" style="display:inline-flex;align-items:center;width:100%;justify-content:center">
									<span class="dashicons dashicons-edit" style="margin-right:5px"></span>
									<?php esc_html_e( 'Viết lại nội dung ngay', 'viet-lai-noi-dung-ai' ); ?>
								</button>
								<span id="aiwd-loading" style="display:none">
									<span class="spinner is-active" style="float:none;margin:0 5px 0 0"></span>
									<?php esc_html_e( 'AI đang viết lại nội dung, vui lòng đợi...', 'viet-lai-noi-dung-ai' ); ?>
								</span>
							</p>
						</form>
					</div>
				</div>

				<!-- Right Column: Result -->
				<div class="aiwd-right-column">
					<div id="aiwd-result-placeholder">
						<span class="dashicons dashicons-format-aside"></span>
						<p><?php esc_html_e( 'Kết quả nội dung sẽ hiển thị ở đây sau khi AI xử lý xong.', 'viet-lai-noi-dung-ai' ); ?></p>
					</div>

					<div id="aiwd-result-container" style="display:none">
						<div class="postbox" style="padding:20px">
							<h2 class="hndle" style="margin-top:0;display:flex;justify-content:space-between;align-items:center">
								<span><?php esc_html_e( 'Kết quả nội dung', 'viet-lai-noi-dung-ai' ); ?></span>
								<span id="aiwd-word-count" style="color:#666;font-size:14px;font-weight:normal"></span>
							</h2>
							<div id="aiwd-content-preview"></div>
							<p style="margin-bottom:0;text-align:right">
								<button id="aiwd-copy" class="button button-large">
									<span class="dashicons dashicons-clipboard" style="margin-top:3px"></span>
									<?php esc_html_e( 'Sao chép nội dung', 'viet-lai-noi-dung-ai' ); ?>
								</button>
							</p>
						</div>
					</div>
				</div>
			</div>

			<div class="aiwd-footer">
				<p>
					<strong><?php esc_html_e( 'Tác giả:', 'viet-lai-noi-dung-ai' ); ?></strong> Phạm Ngọc Tú |
					<a href="https://congcuseoai.com" target="_blank" rel="noopener noreferrer">congcuseoai.com</a>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle AJAX request for content rewrite.
	 */
	public static function handle_ajax_request() {
		check_ajax_referer( 'aiwd_generate_article', 'aiwd_nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( __( 'Bạn không có quyền thực hiện hành động này.', 'viet-lai-noi-dung-ai' ) );
		}

		$topic        = isset( $_POST['topic'] ) ? sanitize_text_field( wp_unslash( $_POST['topic'] ) ) : '';
		$main_keyword = isset( $_POST['mainKeyword'] ) ? sanitize_text_field( wp_unslash( $_POST['mainKeyword'] ) ) : '';

		if ( empty( $topic ) || empty( $main_keyword ) ) {
			wp_send_json_error( __( 'Vui lòng điền đầy đủ Chủ đề nội dung và Từ khóa chính.', 'viet-lai-noi-dung-ai' ) );
		}

		$allowed_word_counts = array( 1000, 1500, 2000, 2500, 3000 );
		$word_count          = isset( $_POST['wordCount'] ) ? absint( wp_unslash( $_POST['wordCount'] ) ) : 2000;
		if ( ! in_array( $word_count, $allowed_word_counts, true ) ) {
			$word_count = 2000;
		}

		$params = array(
			'domain'             => isset( $_POST['domain'] ) ? sanitize_text_field( wp_unslash( $_POST['domain'] ) ) : '',
			'audience'           => isset( $_POST['audience'] ) ? sanitize_text_field( wp_unslash( $_POST['audience'] ) ) : '',
			'word_count'         => $word_count,
			'topic'              => $topic,
			'main_keyword'       => $main_keyword,
			'secondary_keywords' => isset( $_POST['secondaryKeywords'] ) ? sanitize_text_field( wp_unslash( $_POST['secondaryKeywords'] ) ) : '',
			'content_gap'        => isset( $_POST['contentGap'] ) ? sanitize_textarea_field( wp_unslash( $_POST['contentGap'] ) ) : '',
			'structure'          => isset( $_POST['structure'] ) ? sanitize_text_field( wp_unslash( $_POST['structure'] ) ) : 'Nội dung chuẩn SEO',
			'style'              => isset( $_POST['style'] ) ? sanitize_text_field( wp_unslash( $_POST['style'] ) ) : 'Chuyên nghiệp',
			'intent'             => isset( $_POST['intent'] ) ? sanitize_text_field( wp_unslash( $_POST['intent'] ) ) : 'Thông tin (Informational)',
		);

		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 300 );
		}

		try {
			$result = AIWD_Article_Generator::generate_article( $params );

			if ( $result['success'] ) {
				$edit_link = get_edit_post_link( $result['post_id'], '' );
				wp_send_json_success( array(
					'content' => $result['content'],
					'message' => sprintf(
						/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag */
						__( 'Đã tạo bản nháp thành công! %1$sSửa bản nháp ngay%2$s', 'viet-lai-noi-dung-ai' ),
						'<a href="' . esc_url( $edit_link ) . '" class="button button-primary" style="margin-left:10px" target="_blank">',
						'</a>'
					),
				) );
			} else {
				wp_send_json_error( $result['error'] );
			}
		} catch ( \Exception $e ) {
			wp_send_json_error( 'Error: ' . $e->getMessage() );
		}
	}
}
