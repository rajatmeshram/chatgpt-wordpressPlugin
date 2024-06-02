jQuery(document).ready(function($) {
    $('#chatgpt-chat-form').on('submit', function(e) {
        e.preventDefault();
        
        var userMessage = $('#user_message').val();
        
        $.ajax({
            type: 'POST',
            url: chatgpt_ajax_object.ajax_url,
            data: {
                action: 'chatgpt_message',
                user_message: userMessage
            },
            success: function(response) {
                if (response.success) {
                    $('#chat-box').append('<div class="message user-message">' + escapeHtml(userMessage) + '</div>');
                    $('#chat-box').append('<div class="message bot-message">' + formatResponse(response.data) + '</div>');
                    $('#user_message').val('');
                    $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight); // Auto-scroll to the bottom
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
    });

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatResponse(response) {
        return response.replace(/```([\s\S]*?)```/g, function(match, code) {
            return '<pre><code>' + escapeHtml(code) + '</code></pre>';
        });
    }
});
