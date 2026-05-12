<?php
/**
 * Prompt builder for AI content rewriting.
 *
 * @package Viet_Lai_Noi_Dung_AI
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AIWD_Prompt_Builder {
    
    /**
     * Build prompt from rewrite parameters.
     *
     * @param array $params Rewrite parameters.
     * @return string
     */
    public static function build_article_prompt( $params ) {
        $domain            = isset( $params['domain'] ) ? $params['domain'] : '';
        $audience          = isset( $params['audience'] ) ? $params['audience'] : '';
        $word_count        = isset( $params['word_count'] ) ? intval( $params['word_count'] ) : 2000;
        $topic             = isset( $params['topic'] ) ? $params['topic'] : '';
        $main_keyword      = isset( $params['main_keyword'] ) ? $params['main_keyword'] : '';
        $secondary_keywords = isset( $params['secondary_keywords'] ) ? $params['secondary_keywords'] : '';
        $content_gap       = isset( $params['content_gap'] ) ? $params['content_gap'] : '';
        $structure         = isset( $params['structure'] ) ? $params['structure'] : 'Nội dung chuẩn SEO';
        $style             = isset( $params['style'] ) ? $params['style'] : 'Chuyên nghiệp';
        $intent            = isset( $params['intent'] ) ? $params['intent'] : 'Thông tin (Informational)';
        
        // Build system prompt
        $system_prompt = "Bạn là chuyên gia trong lĩnh vực: {$domain}, có kinh nghiệm và chuyên môn cao trong việc viết lại nội dung chuẩn SEO.\n";
        $system_prompt .= "Bạn có khả năng tạo ra các nội dung hấp dẫn và tuân thủ các tiêu chuẩn SEO hiện hành";
        
        if ( ! empty( $audience ) ) {
            $system_prompt .= " nhằm hướng đến đối tượng độc giả mục tiêu: {$audience}";
        }
        
        $system_prompt .= ".\nMục tiêu của bạn là tạo ra nội dung tối ưu cho công cụ tìm kiếm, đảm bảo bố cục, từ khóa và cấu trúc hợp lý, đồng thời cung cấp thông tin giá trị cho người đọc.";
        
        // Build user prompt
        $user_prompt = "# Nội dung cần viết lại: #\n";
        $user_prompt .= "- Chủ đề: {$topic}.\n";
        $user_prompt .= "- Từ khóa chính: {$main_keyword}.\n";
        
        if ( ! empty( $secondary_keywords ) ) {
            $user_prompt .= "- Từ khóa phụ: {$secondary_keywords}.\n";
        }
        
        $user_prompt .= "- Tối ưu hành vi tìm kiếm của người dùng (Search Intent): {$intent}.\n";
        
        // Source content to rewrite.
        if ( ! empty( $content_gap ) ) {
            $user_prompt .= "- Nội dung gốc hoặc yêu cầu cần viết lại:\n";
            $user_prompt .= "*{$content_gap}*\n\n";
        }
        
        // Brand information (from settings)
        $brand_name = get_option( 'aiwd_brand_name', 'Công Cụ SEO AI' );
        $brand_desc = get_option( 'aiwd_brand_description', 'Tối ưu hóa mọi khía cạnh SEO của bạn với sức mạnh của Trí tuệ nhân tạo. Nhanh chóng, chính xác và hiệu quả.' );
        $brand_website = get_option( 'aiwd_brand_website', 'congcuseoai.com' );
        $brand_contact_name = get_option( 'aiwd_brand_contact_name', 'Phạm Ngọc Tú' );
        $brand_contact_phone = get_option( 'aiwd_brand_contact_phone', '0896009111' );
        
        $user_prompt .= "# Thông tin Thương hiệu: #\n";
        $user_prompt .= "- Tên: {$brand_name}\n";
        $user_prompt .= "- Giới thiệu: {$brand_desc}\n";
        $user_prompt .= "- Website: {$brand_website}\n";
        $user_prompt .= "- Liên hệ: {$brand_contact_name} - {$brand_contact_phone}\n\n";
        
        // SEO requirements
        $user_prompt .= "# Yêu cầu SEO: #\n";
        $user_prompt .= "- Cấu trúc nội dung: {$structure}.\n";
        $user_prompt .= "- Sử dụng từ khóa đồng nghĩa để tránh lặp lại từ khóa quá mức để không bị đánh giá là \"nhồi nhét từ khóa.\" Đảm bảo mật độ từ khóa tự nhiên (1–3% cho từ khóa chính).\n";
        $user_prompt .= "- Nội dung có sử dụng các thẻ tiêu đề (Thẻ: H2, H3...) một cách rõ ràng. KHÔNG sử dụng thẻ H1. Cấu trúc outline tuân theo nguyên tắc phân cấp rõ ràng của SEO, với H2 > H3, không bỏ sót cấp độ nào, tạo ra một cấu trúc trang web chuẩn mà Google ưa thích.\n";
        $user_prompt .= "- Hạn chế các đoạn liệt kê. Mỗi thẻ H2 nên có ít nhất 300-500 từ để phân tích sâu và cung cấp giá trị thực sự cho người đọc.\n";
        $user_prompt .= "- TRÁNH sử dụng các từ ngữ sáo rỗng, mơ hồ như \"đột phá\", \"nhất\", \"số một\", \"thế hệ mới\", \"công nghệ tiên tiến\", \"sâu xắc\", \"hiện đại\" \"trong thời đại 4.0\", \"xu hướng tất yếu\", \"không thể phủ nhận\" nếu không có minh chứng, dữ liệu hoặc ví dụ cụ thể. Chỉ dùng khi thực sự cần thiết. \n";
        $user_prompt .= "- Sử dụng thẻ HTML ngữ nghĩa để trình bày rõ ràng: <ol> cho danh sách có thứ tự, <ul> cho danh sách không thứ tự, <table> cho dữ liệu so sánh. Phân biệt rõ phần nội dung chính và phần phụ.\n\n";
        
        // Writing style requirements for natural content
        $user_prompt .= "# Phong cách viết: {$style} #\n";
        $user_prompt .= "- Viết như một người thật đang chia sẻ kinh nghiệm, KHÔNG viết theo khuôn mẫu rập khuôn hay công thức có sẵn.\n";
        $user_prompt .= "- Thỉnh thoảng đặt câu hỏi tu từ để kích thích suy nghĩ của người đọc và tạo sự tương tác.\n";
        $user_prompt .= "- Kể câu chuyện, ví dụ thực tế, tình huống cụ thể thay vì chỉ liệt kê lý thuyết khô khan.\n";
        $user_prompt .= "- Thay đổi độ dài câu: kết hợp câu dài và câu ngắn để tạo nhịp điệu tự nhiên, tránh đơn điệu.\n";
        $user_prompt .= "- Sử dụng từ nối, từ chuyển tiếp một cách tự nhiên (tuy nhiên, ngoài ra, bên cạnh đó, điều này có nghĩa là...) để nội dung mạch lạc.\n";
        $user_prompt .= "- Dùng số liệu, dữ liệu cụ thể khi có thể để tăng độ tin cậy, nhưng trình bày một cách tự nhiên trong ngữ cảnh.\n";
        
        // Introduction requirements
        $user_prompt .= "\n# Phần mở đầu: #\n";
        $user_prompt .= "- BẮT BUỘC: Phần mở đầu PHẢI bắt đầu bằng một hoặc nhiều đoạn văn (thẻ <p>), TUYỆT ĐỐI KHÔNG được bắt đầu bằng bất kỳ heading nào (H1, H2, H3).\n";
        $user_prompt .= "- Mở đầu nên có 2-3 đoạn văn (100-200 từ) để giới thiệu chủ đề một cách tự nhiên, thu hút người đọc.\n";
        $user_prompt .= "- Có thể bắt đầu bằng một câu hỏi, một tình huống thực tế, hoặc một thống kê thú vị liên quan đến chủ đề.\n";
        $user_prompt .= "- SAU phần mở đầu (các đoạn <p>), mới bắt đầu sử dụng heading H2 cho các phần nội dung chính.\n\n";
        
        // Structure-specific instructions
        $user_prompt .= self::get_structure_instructions( $structure );
        
        // Output requirements
        $user_prompt .= "\n# Kết quả Đầu ra: #\n";
        $user_prompt .= "- **KHÔNG** thêm bất kỳ chú thích hay giải thích nào.\n";
        $user_prompt .= "- **TUYỆT ĐỐI KHÔNG** sử dụng cú pháp Markdown (*, #, **, __, -, >, ```, v.v.). Output phải là HTML thuần túy.\n";
        $user_prompt .= "- **CẤM** sử dụng: dấu sao (*), dấu thăng (#), dấu gạch ngang (-), dấu underscore (__), backticks (```), hoặc bất kỳ ký hiệu Markdown nào khác.\n";
        $user_prompt .= "- Nội dung có độ dài tối đa: {$word_count} từ.\n";
        $user_prompt .= "- Trả về toàn bộ nội dung ở định dạng HTML THUẦN với các thẻ <h2>, <h3>, <p>, <strong>, <em>, <ul>, <ol>, <li>. KHÔNG sử dụng thẻ <h1>.\n";
        $user_prompt .= "- BẮT ĐẦU trực tiếp bằng 2-3 đoạn văn mở đầu (thẻ <p>), sau đó mới đến các heading <h2> cho nội dung chính.\n";
        $user_prompt .= "- Viết hoàn toàn bằng tiếng Việt có dấu.\n";
        $user_prompt .= "- Ví dụ format đúng: <h2>Tiêu đề</h2><p>Nội dung...</p><ul><li>Mục 1</li><li>Mục 2</li></ul>\n";
        $user_prompt .= "- Ví dụ format SAI (TUYỆT ĐỐI KHÔNG làm): ## Tiêu đề, **bold text**, * danh sách, - bullet point.\n";
        
        // Combine system and user prompts
        // For providers that support system messages, this will be split in the provider class
        // For now, return as combined prompt with clear separation
        return "SYSTEM:\n{$system_prompt}\n\nUSER:\n{$user_prompt}";
    }
    
    /**
     * Get structure-specific instructions.
     *
     * @param string $structure Article structure.
     * @return string
     */
    private static function get_structure_instructions( $structure ) {
        $instructions = "\n";
        
        switch ( $structure ) {
            case 'Nội dung dạng danh sách (Listicle)':
                $instructions .= "- Mỗi mục trong danh sách phải có heading riêng (H2 hoặc H3).\n";
                $instructions .= "- Giải thích chi tiết cho mỗi mục, không chỉ liệt kê.\n";
                $instructions .= "- Sử dụng số thứ tự rõ ràng trong các heading.\n";
                break;
                
            case 'Nội dung hướng dẫn (How-to Guide)':
                $instructions .= "- Chia thành các bước rõ ràng, dễ theo dõi.\n";
                $instructions .= "- Mỗi bước có heading riêng với số thứ tự.\n";
                $instructions .= "- Giải thích chi tiết cách thực hiện từng bước.\n";
                $instructions .= "- Có thể thêm tips và lưu ý quan trọng.\n";
                break;
                
            case 'Nội dung đánh giá (Review Article)':
                $instructions .= "- Phân tích ưu điểm và nhược điểm một cách cân bằng.\n";
                $instructions .= "- Đánh giá dựa trên tiêu chí cụ thể.\n";
                $instructions .= "- Có đánh giá và khuyến nghị rõ ràng.\n";
                break;
                
            case 'Nội dung so sánh (Comparison Article)':
                $instructions .= "- So sánh các đặc điểm, tính năng cụ thể.\n";
                $instructions .= "- Phân tích ưu nhược điểm của từng đối tượng.\n";
                $instructions .= "- Có khuyến nghị cho từng trường hợp sử dụng.\n";
                break;
                
            default:
                // Standard SEO article
                $brand_name = get_option( 'aiwd_brand_name', 'Công Cụ SEO AI' );
                $instructions .= "- Phần mở đầu: Giới thiệu chủ đề, nêu vấn đề (100-150 từ).\n";
                $instructions .= "- Phần thân nội dung: Phát triển nội dung với các heading H2, H3 phân cấp rõ ràng.\n";
                $instructions .= "- Thông điệp tóm tắt: Tóm tắt lại toàn bộ nội dung và call-to-action nhắc đến {$brand_name}.\n";
                break;
        }
        
        return $instructions;
    }
}
