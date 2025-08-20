<?php

namespace App\Repositories;

use Illuminate\Http\UploadedFile;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Services\ImageServices;

class ConversationRepository
{

    protected $imageService;

    public function __construct()
    {
        $this->imageService = new ImageServices();
    }
    public function getUserConversations(User $user)
    {
        $provider = $user->serviceProvider;

        return Conversation::where('user_id', $user->id)
            ->when($provider, function ($query) use ($provider) {
                $query->orWhere('service_provider_id', $provider->id);
            })
            ->with(['user', 'serviceProvider'])
            ->latest()
            ->get();
    }

    public function getMessagesForConversation(Conversation $conversation)
    {
        return $conversation->messages()->orderBy('created_at', 'asc')->get();
    }

    public function createMessage(Conversation $conversation, User $sender, string $body, ?UploadedFile $file = null): Message
    { 
        $senderId = $sender->id;
        $senderType = User::class; 

        if ($sender->serviceProvider && $sender->serviceProvider->id === $conversation->service_provider_id) {
            $senderId = $sender->serviceProvider->id;
            $senderType = ServiceProvider::class;
        }
        return $conversation->messages()->create([
            'sender_id'   => $senderId,
            'sender_type' => $senderType,
            'body'        => $body,
            'type'        => 'text',
        ]);
        if ($file) {
            $path = $this->imageService->_storeImage($file, 'messages', $conversation->id);
            $messageData['file_path'] = $path;

            $mimeType = $file->getMimeType();
            if (str_starts_with($mimeType, 'image/')) {
                $messageData['type'] = 'image';
            } elseif (str_starts_with($mimeType, 'video/')) {
                $messageData['type'] = 'video';
            } elseif (str_starts_with($mimeType, 'audio/')) {
                $messageData['type'] = 'audio';
            }
        }

        return $conversation->messages()->create($messageData);
    }

       public function createConversation(array $data): Conversation
    {
        return Conversation::create($data);
    }
}
