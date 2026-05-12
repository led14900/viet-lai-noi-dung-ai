/**
 * Viết lại nội dung AI - Generator Page Scripts
 *
 * @package Viet_Lai_Noi_Dung_AI
 */

/* global jQuery, aiwdGenerator */
(function ($) {
	'use strict';

	$(document).ready(function () {
		var $form = $('#aiwd-form');
		var $submitBtn = $('#aiwd-submit-btn');
		var $loading = $('#aiwd-loading');
		var $messageContainer = $('#aiwd-message-container');
		var $resultContainer = $('#aiwd-result-container');
		var $resultPlaceholder = $('#aiwd-result-placeholder');
		var $content = $('#aiwd-content-preview');
		var $wordCount = $('#aiwd-word-count');
		var $copyBtn = $('#aiwd-copy');

		function showTextNotice(type, message) {
			$messageContainer.empty().append(
				$('<div>')
					.addClass('notice notice-' + type + ' is-dismissible')
					.append($('<p>').text(message))
			);
		}

		/**
		 * Handle form submission via AJAX.
		 */
		$form.on('submit', function (e) {
			e.preventDefault();

			// Reset UI.
			$messageContainer.empty();
			$resultContainer.hide();
			$resultPlaceholder.show();
			$submitBtn.prop('disabled', true);
			$loading.show();

			var formData = $form.serialize();

			$.ajax({
				url: aiwdGenerator.ajaxUrl,
				type: 'POST',
				data: formData,
				timeout: 300000,
				success: function (response) {
					$loading.hide();
					$submitBtn.prop('disabled', false);

					if (response.success) {
						$messageContainer.html(
							'<div class="notice notice-success is-dismissible"><p>' +
								response.data.message +
								'</p></div>'
						);

						$content.html(response.data.content);
						$resultPlaceholder.hide();
						$resultContainer.show();

						// Count words.
						var text = $content.text() || '';
						var words = text
							.trim()
							.split(/\s+/)
							.filter(function (w) {
								return w.length > 0;
							});
						$wordCount.html(
							'📊 ' +
								aiwdGenerator.i18n.wordCount +
								': <strong>' +
								words.length +
								'</strong>'
						);

						// Scroll to result on mobile.
						if ($(window).width() < 782) {
							$('html, body').animate(
								{ scrollTop: $resultContainer.offset().top - 50 },
								500
							);
						}
					} else {
						showTextNotice(
							'error',
							response.data || aiwdGenerator.i18n.genericError
						);
					}
				},
				error: function (xhr, status, error) {
					$loading.hide();
					$submitBtn.prop('disabled', false);

					var errorMsg = aiwdGenerator.i18n.connectionError + ': ' + error;
					if (status === 'timeout') {
						errorMsg = aiwdGenerator.i18n.timeoutError;
					} else if (xhr.responseText) {
						try {
							var jsonResp = JSON.parse(xhr.responseText);
							if (jsonResp && jsonResp.data) {
								errorMsg = jsonResp.data;
							}
						} catch (parseError) {
							// Use default error message.
						}
					}

					showTextNotice('error', errorMsg);
				},
			});
		});

		/**
		 * Copy content to clipboard using modern API.
		 */
		$copyBtn.on('click', function (e) {
			e.preventDefault();

			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard
					.writeText($content.text())
					.then(function () {
						showCopySuccess();
					})
					.catch(function () {
						fallbackCopy();
					});
			} else {
				fallbackCopy();
			}
		});

		/**
		 * Fallback copy method for older browsers.
		 */
		function fallbackCopy() {
			var $temp = $('<textarea>');
			$('body').append($temp);
			$temp.val($content.text()).select();
			try {
				document.execCommand('copy');
				showCopySuccess();
			} catch (err) {
				// Silent fail.
			}
			$temp.remove();
		}

		/**
		 * Show copy success feedback.
		 */
		function showCopySuccess() {
			var originalText = $copyBtn.html();
			$copyBtn
				.html(
					'<span class="dashicons dashicons-yes" style="margin-top:3px"></span> ' +
						aiwdGenerator.i18n.copied
				)
				.css('color', '#008000');

			setTimeout(function () {
				$copyBtn.html(originalText).css('color', '');
			}, 2000);
		}
	});

	/**
	 * Test connection AJAX handler (used on Settings page).
	 */
	$(document).on('click', '.aiwd-test-btn', function (e) {
		e.preventDefault();

		var $btn = $(this);
		var $result = $btn.siblings('.aiwd-test-result');
		var provider = $btn.data('provider');

		$btn.prop('disabled', true);
		$result
			.removeClass('success error')
			.text(aiwdGenerator.i18n.testing || 'Đang kiểm tra...');

		$.ajax({
			url: aiwdGenerator.ajaxUrl,
			type: 'POST',
			data: {
				action: 'aiwd_test_connection',
				nonce: aiwdGenerator.testNonce,
				provider: provider,
			},
			timeout: 30000,
			success: function (response) {
				$btn.prop('disabled', false);
				if (response.success) {
					$result.addClass('success').text(response.data);
				} else {
					$result.addClass('error').text(response.data);
				}
			},
			error: function () {
				$btn.prop('disabled', false);
				$result
					.addClass('error')
					.text(aiwdGenerator.i18n.connectionError || 'Lỗi kết nối');
			},
		});
	});
})(jQuery);
