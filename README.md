# Viết lại nội dung AI

Plugin WordPress hỗ trợ viết lại nội dung bằng AI, lưu kết quả thành bản nháp và cho phép dùng nhiều nhà cung cấp AI khác nhau.

- **Version:** 1.0
- **Requires WordPress:** 6.0+
- **Requires PHP:** 7.4+
- **Author:** Phạm Ngọc Tú
- **Website:** [congcuseoai.com](https://congcuseoai.com/)
- **License:** GPLv2 or later

## Tổng Quan

Viết lại nội dung AI thêm một công cụ trong khu vực quản trị WordPress để tạo bản nháp từ chủ đề, từ khóa, lĩnh vực, đối tượng mục tiêu và nội dung cần viết lại. Plugin hỗ trợ:

- OpenAI
- Claude
- Google Gemini
- API tương thích OpenAI

## Tính Năng

- Viết lại/tạo nội dung từ chủ đề và từ khóa chính.
- Hỗ trợ từ khóa phụ, nội dung cần viết lại, lĩnh vực và đối tượng mục tiêu.
- Chọn số từ mục tiêu: 1000, 1500, 2000, 2500 hoặc 3000.
- Chọn cấu trúc nội dung: nội dung chuẩn SEO, Listicle, How-to Guide, Review, Comparison.
- Chọn phong cách viết: chuyên nghiệp, thân thiện, hài hước, học thuật, thuyết phục.
- Chọn Search Intent: Informational, Commercial, Transactional, Navigational.
- Lưu bản nháp tự động vào post type `post`.
- Lưu metadata: từ khóa chính, từ khóa phụ, số từ mục tiêu, ngày tạo.
- Test kết nối từng provider ngay trong trang cài đặt.
- Mã hóa API key trước khi lưu vào database.
- Chặn endpoint OpenAI-compatible không an toàn như localhost, IP nội bộ hoặc reserved IP.

## Cài Đặt

1. Upload thư mục plugin vào:

```text
wp-content/plugins/viet-lai-noi-dung-ai/
```

2. Vào WordPress Admin -> Plugins.
3. Kích hoạt plugin **Viết lại nội dung AI**.
4. Vào Settings -> Viết lại nội dung AI để cấu hình provider.

## Cấu Hình Provider

### Cài Đặt Chung

Vào Settings -> Viết lại nội dung AI -> Cài Đặt Chung, chọn provider đang sử dụng:

- OpenAI
- Claude
- Google Gemini
- OpenAI Compatible

Sau khi cấu hình provider, quay lại tab này để chọn provider active.

### OpenAI

Vào tab OpenAI và nhập:

- API Key
- Model
- Temperature từ 0 đến 2

Plugin có sẵn một số model mẫu và hỗ trợ nhập model tùy chỉnh.

### Claude

Vào tab Claude và nhập:

- API Key
- Model
- Temperature từ 0 đến 1

Request Claude sử dụng `max_tokens` để phù hợp Messages API.

### Gemini

Vào tab Gemini và nhập:

- API Key
- Model
- Temperature từ 0 đến 2

Plugin gửi request tới Google Generative Language API.

### OpenAI Compatible

Vào tab Tương Thích OpenAI và nhập:

- API Endpoint, ví dụ:

```text
https://your-provider.example/v1/chat/completions
```

- API Key
- Model Name
- Temperature từ 0 đến 2

Endpoint phải dùng HTTPS. Plugin sẽ từ chối localhost, IP nội bộ, reserved IP và các URL không an toàn.

## Cấu Hình Thương Hiệu

Tab Thương Hiệu cho phép đặt thông tin được đưa vào prompt:

- Tên thương hiệu
- Mô tả
- Website
- Tên liên hệ
- Số điện thoại

Thông tin này giúp AI viết nội dung có ngữ cảnh thương hiệu rõ hơn.

## Cách Sử Dụng

1. Vào Posts -> Viết lại nội dung AI.
2. Kiểm tra provider đang dùng ở đầu trang.
3. Điền các trường bắt buộc:

- Chủ đề nội dung
- Từ khóa chính

4. Điền thêm các trường tùy chọn nếu cần:

- Lĩnh vực
- Đối tượng mục tiêu
- Từ khóa phụ
- Nội dung cần viết lại
- Cấu trúc
- Phong cách viết
- Search Intent

5. Bấm **Viết lại nội dung ngay**.
6. Chờ AI xử lý và xem preview ở cột kết quả.
7. Bấm **Sửa bản nháp ngay** để mở bản nháp trong WordPress Editor.
8. Kiểm tra, chỉnh sửa, thêm ảnh/category/tag rồi xuất bản.

## Bảo Mật Và Kiểm Soát Dữ Liệu

Plugin hiện có các lớp bảo vệ chính:

- Nonce cho AJAX tạo nội dung và test provider.
- Kiểm tra capability:
  - `edit_posts` cho trang tạo nội dung.
  - `manage_options` cho trang cài đặt.
- Sanitize input bằng API WordPress.
- Escape output trong admin.
- API key được mã hóa bằng OpenSSL với key dựa trên WordPress `AUTH_KEY`.
- Nội dung AI được kiểm tra trước khi lưu.
- Endpoint OpenAI-compatible được kiểm tra để hạn chế URL không an toàn.
- Uninstall chỉ xóa đúng option/meta key của plugin.

## Cấu Trúc Thư Mục

```text
viet-lai-noi-dung-ai.php
uninstall.php
admin/
  class-generator-page.php
  class-settings-page.php
  css/admin.css
  js/generator.js
includes/
  class-ai-provider-base.php
  class-openai-provider.php
  class-claude-provider.php
  class-gemini-provider.php
  class-openai-compatible-provider.php
  class-article-generator.php
  class-prompt-builder.php
  class-encryption.php
languages/
```

## Troubleshooting

### Lỗi "API key không hợp lệ"

- Kiểm tra API key đã copy đủ ký tự.
- Kiểm tra đúng provider đang active.
- Tạo API key mới nếu key cũ bị thu hồi.

### Lỗi "Endpoint API chưa được cấu hình"

- Vào tab Tương Thích OpenAI.
- Nhập endpoint đầy đủ dạng HTTPS.
- Lưu cài đặt trước khi test kết nối.

### Lỗi "Lỗi API: Lỗi HTTP 530"

HTTP 530 thường đến từ endpoint hoặc CDN/proxy của nhà cung cấp API, không phải lỗi cú pháp plugin.

Cách xử lý:

- Kiểm tra lại endpoint có đúng `/v1/chat/completions` không.
- Test kết nối trong trang cài đặt.
- Kiểm tra domain API của nhà cung cấp có đang hoạt động không.
- Thử provider khác để loại trừ lỗi từ nhà cung cấp hiện tại.

### Lỗi timeout khi tạo nội dung

- Giảm số từ xuống 1000 hoặc 1500.
- Rút gọn nội dung cần viết lại.
- Thử model hoặc provider khác.
- Kiểm tra giới hạn timeout của hosting.

### Không thấy menu plugin

- Trang tạo nội dung nằm trong Posts -> Viết lại nội dung AI.
- Trang cài đặt nằm trong Settings -> Viết lại nội dung AI.
- Tài khoản cần quyền `edit_posts` để tạo nội dung và `manage_options` để cấu hình.

## Ghi Chú Phát Triển

- Slug plugin và text domain vẫn là `viet-lai-noi-dung-ai` để giữ tương thích với option cũ.
- AJAX action hiện tại:
  - `aiwd_generate_article`
  - `aiwd_test_connection`
- Plugin không xóa bản nháp hoặc nội dung đã tạo khi uninstall; chỉ xóa option và post meta của plugin.

## Changelog

### 1.0

- Đổi nhận diện giao diện sang **Viết lại nội dung AI**.
- Hỗ trợ OpenAI, Claude, Gemini và OpenAI-compatible API.
- Thêm mã hóa API key.
- Thêm test kết nối provider.
- Lưu nội dung AI thành bản nháp WordPress.
- Kiểm tra nội dung AI trước khi lưu.
- Bổ sung kiểm tra endpoint OpenAI-compatible để hạn chế URL không an toàn.
- Thêm `max_tokens` cho Claude Messages API.
- Giới hạn server-side cho số từ mục tiêu.
- Clamp temperature riêng cho Claude.
- Cleanup uninstall chỉ xóa đúng meta key của plugin.

## Hỗ Trợ

- **Tác giả:** Phạm Ngọc Tú
- **Website:** [congcuseoai.com](https://congcuseoai.com/)
- **Điện thoại:** 0896009111

## License

GPLv2 or later. See [GNU General Public License](https://www.gnu.org/licenses/gpl-2.0.html).
