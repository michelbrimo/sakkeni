<?php

namespace App\Services;

use App\Events\NewMessageSent;
use App\Repositories\ConversationRepository;
use Exception;

class MessagingService
{
    protected $conversationRepository;

    public function __construct()
    {
        $this->conversationRepository = new ConversationRepository();
    }

    public function viewConversations($data)
    {
        $user = $data['user'];
        return $this->conversationRepository->getUserConversations($user);
    }

    public function viewMessages($data)
    {
        $user = $data['user'];
        $conversation = $data['conversation'];

        if ($user->id !== $conversation->user_id && (!$user->serviceProvider || $user->serviceProvider->id !== $conversation->service_provider_id)) {
            throw new Exception('Unauthorized', 403);
        }

        return $this->conversationRepository->getMessagesForConversation($conversation);
    }

    public function sendMessage($data)
    {
        $user = $data['user'];
        $conversation = $data['conversation'];

        if ($user->id !== $conversation->user_id && (!$user->serviceProvider || $user->serviceProvider->id !== $conversation->service_provider_id)) {
            throw new Exception('Unauthorized', 403);
        }

        $message = $this->conversationRepository->createMessage($conversation, $user, $data['body'], $data['file']);

        broadcast(new NewMessageSent($message))->toOthers();

        return $message;
    }
}
