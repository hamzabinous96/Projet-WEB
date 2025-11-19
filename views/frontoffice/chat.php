<?php
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=role_selection");
    exit;
}

$coach_id = $_GET['coach_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;

// Sample conversations
$conversations = [
    ['other_user_id' => 1, 'other_user_name' => 'Math Coach John', 'course_name' => 'Mathematics', 'last_message_time' => '2024-01-20 14:30:00'],
    ['other_user_id' => 2, 'other_user_name' => 'HTML Coach Sarah', 'course_name' => 'HTML & CSS', 'last_message_time' => '2024-01-19 10:15:00'],
    ['other_user_id' => 3, 'other_user_name' => 'Social Coach Mike', 'course_name' => 'Société Paix et Inclusion', 'last_message_time' => '2024-01-18 16:45:00']
];
?>

<section class="chat">
    <div class="container">
        <h2>Chat Conversations</h2>
        <div class="chat-container">
            <div class="chat-sidebar">
                <h4>Conversations</h4>
                <div class="conversation-list">
                    <?php foreach($conversations as $conv): ?>
                        <div class="conversation" data-user="<?php echo $conv['other_user_id']; ?>" data-course="1">
                            <div class="conversation-avatar">
                                <?php echo strtoupper(substr($conv['other_user_name'], 0, 2)); ?>
                            </div>
                            <div class="conversation-info">
                                <h5><?php echo htmlspecialchars($conv['other_user_name']); ?></h5>
                                <p><?php echo htmlspecialchars($conv['course_name']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="chat-main">
                <div class="chat-header">
                    <h4>Select a conversation to start chatting</h4>
                </div>
                <div class="chat-messages">
                    <div class="no-conversation">
                        <p>Please select a conversation from the sidebar to start chatting.</p>
                    </div>
                </div>
                <div class="chat-input" style="display: none;">
                    <input type="text" placeholder="Type your message..." id="message-input">
                    <button id="send-button">Send</button>
                </div>
            </div>
        </div>
    </div>
</section>