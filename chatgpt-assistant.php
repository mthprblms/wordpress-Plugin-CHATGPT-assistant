<?php
/**
 * Plugin Name: ChatGPT Assistant
 * Description: A real-time chat assistant powered by ChatGPT.
 * Version: 1.0
 * Author: Your Name
 */

// Enqueue JavaScript and Styles
function enqueue_scripts_styles() {
    wp_enqueue_script('chatgpt-assistant', plugin_dir_url(__FILE__) . 'js/chatgpt-assistant.js', array('jquery'), '1.0', true);
    wp_enqueue_style('chatgpt-assistant', plugin_dir_url(__FILE__) . 'css/chatgpt-assistant.css');
}

add_action('wp_enqueue_scripts', 'enqueue_scripts_styles');

// Backend endpoint for ChatGPT interaction
function chatgpt_assistant_endpoint() {
    $user_input = sanitize_text_field($_POST['userInput']);

    // Get chatbot settings from options
    $chatbot_tone = get_option('chatbot_tone', 'friendly');
    $chatbot_role = get_option('chatbot_role', 'assistant');

    // Implement your logic to interact with the ChatGPT API
    $chatgpt_response = call_chatgpt_api($user_input, $chatbot_tone, $chatbot_role);

    // Send the ChatGPT response back to the frontend
    echo json_encode(array('botResponse' => $chatgpt_response));

    wp_die();
}

add_action('wp_ajax_nopriv_chatgpt_assistant', 'chatgpt_assistant_endpoint');
add_action('wp_ajax_chatgpt_assistant', 'chatgpt_assistant_endpoint');

// Function to interact with the ChatGPT API
function call_chatgpt_api($user_input, $tone, $role) {
    $api_key = 'YOUR_CHATGPT_API_KEY'; // Replace with your actual ChatGPT API key
    $api_url = 'https://api.openai.com/v1/chat/completions';

    // Construct the request data
    $data = array(
        'model' => 'gpt-3.5-turbo',
        'messages' => array(
            array('role' => 'system', 'content' => "You are a $tone $role."),
            array('role' => 'user', 'content' => $user_input),
        ),
    );

    // Encode data to JSON
    $json_data = json_encode($data);

    // Set up headers
    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key,
    );

    // Make the API request
    $response = wp_safe_remote_post($api_url, array(
        'body'    => $json_data,
        'headers' => $headers,
    ));

    if (is_wp_error($response)) {
        // Handle error appropriately
        return 'Error communicating with ChatGPT API';
    }

    // Decode the API response
    $decoded_response = json_decode($response['body'], true);

    // Extract the bot's response
    $bot_response = $decoded_response['choices'][0]['message']['content'];

    return $bot_response;
}

// Add settings page to WordPress admin panel
function chatgpt_assistant_settings_page() {
    add_menu_page(
        'ChatGPT Assistant Settings',
        'ChatGPT Settings',
        'manage_options',
        'chatgpt-settings',
        'chatgpt_assistant_settings_html'
    );
}

add_action('admin_menu', 'chatgpt_assistant_settings_page');

// Display the settings page in the WordPress admin panel
function chatgpt_assistant_settings_html() {
    ?>
    <div class="wrap">
        <h1>ChatGPT Assistant Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('chatgpt_settings_group'); ?>
            <?php do_settings_sections('chatgpt-settings'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register settings and fields for the settings page
function chatgpt_assistant_register_settings() {
    register_setting('chatgpt_settings_group', 'chatbot_tone', array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('chatgpt_settings_group', 'chatbot_role', array('sanitize_callback' => 'sanitize_text_field'));

    add_settings_section('chatgpt_section', 'ChatGPT Settings', 'chatgpt_section_html', 'chatgpt-settings');

    add_settings_field('chatbot_tone', 'Chatbot Tone', 'chatbot_tone_callback', 'chatgpt-settings', 'chatgpt_section');
    add_settings_field('chatbot_role', 'Chatbot Role', 'chatbot_role_callback', 'chatgpt-settings', 'chatgpt_section');
}

add_action('admin_init', 'chatgpt_assistant_register_settings');

// Display fields on the settings page
function chatbot_tone_callback() {
    $tone = get_option('chatbot_tone', 'friendly');
    ?>
    <select name="chatbot_tone">
        <option value="friendly" <?php selected($tone, 'friendly'); ?>>Friendly</option>
        <option value="professional" <?php selected($tone, 'professional'); ?>>Professional</option>
        <!-- Add more tone options as needed -->
    </select>
    <?php
}

function chatbot_role_callback() {
    $role = get_option('chatbot_role', 'assistant');
    ?>
    <select name="chatbot_role">
        <option value="assistant" <?php selected($role, 'assistant'); ?>>Assistant</option>
        <option value="advisor" <?php selected($role, 'advisor'); ?>>Advisor</option>
        <!-- Add more role options as needed -->
    </select>
    <?php
}

function chatgpt_section_html() {
    echo '<p>Configure the tone and role of the Chatbot.</p>';
}

// Shortcode for displaying the chat interface
function chatgpt_assistant_shortcode() {
    ob_start(); ?>
    <div id="chat-output"></div>
    <input type="text" id="chat-input" placeholder="Type your message..." />
    <?php
    return ob_get_clean();
}

add_shortcode('chatgpt_assistant', 'chatgpt_assistant_shortcode');