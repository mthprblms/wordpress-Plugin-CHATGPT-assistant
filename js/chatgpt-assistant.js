// js/chatgpt-assistant.js
jQuery(document).ready(function ($) {
    // Function to send user input to the backend and handle ChatGPT's response
    function sendUserInputToChatGPT(userInput) {
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'chatgpt_assistant',
                userInput: userInput,
            },
            success: function (response) {
                // Handle the response from the backend (ChatGPT's response)
                displayChatMessage(response);
            },
            error: function (error) {
                console.error('Error communicating with backend:', error);
            }
        });
    }

    // Function to display chat messages in the chat window
    function displayChatMessage(message) {
        $('#chat-output').append('<div class="chat-message">' + message + '</div>');
        // Scroll to the bottom of the chat window to show the latest message
        $('#chat-output').scrollTop($('#chat-output')[0].scrollHeight);
    }

    // Event listener for Enter key press in the chat input
    $('#chat-input').keypress(function (e) {
        if (e.which === 13) { // Enter key pressed
            var userInput = $(this).val();

            // Display user's message in the chat window
            displayChatMessage('<strong>You:</strong> ' + userInput);

            // Send user input to the backend and get ChatGPT's response
            sendUserInputToChatGPT(userInput);

            // Clear the input field
            $(this).val('');
        }
    });

    // Automatically send a greeting message when the page loads
    sendUserInputToChatGPT('Hello, ChatGPT!');

    // Example: Add a button that triggers a special command when clicked
    $('#special-command-button').click(function () {
        var specialCommand = 'Execute special command';
        displayChatMessage('<strong>You:</strong> ' + specialCommand);
        sendUserInputToChatGPT(specialCommand);
    });

    // For additional JavaScript logic for the chat assistant
    // For example, trigger certain actions based on ChatGPT's response
    // This could include updating other parts of the UI, making additional API calls, etc.
});