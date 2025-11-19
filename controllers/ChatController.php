<?php
class ChatController {
    private $chatModel;

    public function __construct($db) {
        $this->chatModel = new Chat($db);
    }

    public function sendMessage($data) {
        $this->chatModel->sender_id = $data['sender_id'];
        $this->chatModel->receiver_id = $data['receiver_id'];
        $this->chatModel->course_id = $data['course_id'];
        $this->chatModel->message = $data['message'];
        
        return $this->chatModel->create();
    }

    public function getConversation($user1_id, $user2_id, $course_id) {
        return $this->chatModel->getConversation($user1_id, $user2_id, $course_id);
    }

    public function getUserConversations($user_id) {
        return $this->chatModel->getUserConversations($user_id);
    }
}
?>