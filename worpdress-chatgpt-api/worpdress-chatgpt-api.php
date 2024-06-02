<?php
/*
Plugin Name: ChatGPT API Plugin
Description: A WordPress plugin to interact with the ChatGPT API.Created a custom plugin which integrate chatgpt api and give response like chatgpt
Version: 1.0
Author: Rajat
*/

// Enqueue scripts and styles
function chatgpt_enqueue_scripts() {
    wp_enqueue_style('chatgpt-css', plugin_dir_url(__FILE__) . 'chatgpt.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('chatgpt-ajax-script', plugin_dir_url(__FILE__) . 'chatgpt.js', array('jquery'), null, true);
    wp_localize_script('chatgpt-ajax-script', 'chatgpt_ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'chatgpt_enqueue_scripts');

// Add menu item for the plugin settings
function chatgpt_api_menu() {
    add_menu_page('ChatGPT API', 'ChatGPT API', 'manage_options', 'chatgpt-api', 'chatgpt_api_page');
}
add_action('admin_menu', 'chatgpt_api_menu');

// Render the settings page
function chatgpt_api_page() {
    ?>
    <div class="wrap">
        <h2>ChatGPT API Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('chatgpt_api_settings');
            do_settings_sections('chatgpt_api');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function chatgpt_api_settings_init() {
    register_setting('chatgpt_api_settings', 'chatgpt_api_key');
    
    add_settings_section(
        'chatgpt_api_section',
        'ChatGPT API Key',
        null,
        'chatgpt_api'
    );
    
    add_settings_field(
        'chatgpt_api_key',
        'API Key',
        'chatgpt_api_key_render',
        'chatgpt_api',
        'chatgpt_api_section'
    );
}
add_action('admin_init', 'chatgpt_api_settings_init');

// Render the API key input field
function chatgpt_api_key_render() {
    $api_key = get_option('chatgpt_api_key');
    ?>
    <input type="text" name="chatgpt_api_key" value="<?php echo esc_attr($api_key); ?>" style="width: 400px;">
    <?php
}

// Create shortcode for the chat form
function chatgpt_chat_form() {
    ob_start();
    ?>
    <div class="chat-container">
        <div class="chat-box" id="chat-box"></div>
        <form id="chatgpt-chat-form">
            <label for="user_message" class="sr-only">Your Message:</label>
            <input type="text" id="user_message" name="user_message" placeholder="Type your message here..." required>
            <button type="submit">Send</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('chatgpt_chat', 'chatgpt_chat_form');

// Handle AJAX request
function chatgpt_handle_ajax() {
    $api_key = get_option('chatgpt_api_key');
    $user_message = sanitize_text_field($_POST['user_message']);
    
    $response = chatgpt_send_message($api_key, $user_message);
    
    wp_send_json_success($response);
}
add_action('wp_ajax_chatgpt_message', 'chatgpt_handle_ajax');
add_action('wp_ajax_nopriv_chatgpt_message', 'chatgpt_handle_ajax');

// Function to send a message to the ChatGPT API
// Function to send a message to the ChatGPT API
function chatgpt_send_message($api_key, $message) {
    $url = 'https://api.openai.com/v1/chat/completions';
    
    $data = json_encode(array(
        'max_tokens' => 100,
        'model' => 'gpt-3.5-turbo',
        'messages' => array(
            array(
                'role' => 'user',
                'content' => $message
            )
        ),
        'temperature' => 0.7
    ));

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $api_key",
        ),
        'body' => $data,
        'timeout' => 50,
    );

    $response = wp_safe_remote_post($url, $args);

    if (is_wp_error($response)) {
        return 'Error: ' . $response->get_error_message();
    } else {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!empty($data['choices'][0]['message']['content'])) {
            $content = $data['choices'][0]['message']['content'];
            return wp_kses_post($content);
        } else {
            return 'No response from API';
        }
    }
}

?>
