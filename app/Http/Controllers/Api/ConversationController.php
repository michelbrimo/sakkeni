<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\ServiceTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ConversationController extends Controller
{
    protected $service_transformer;

    public function __construct()
    {
        $this->service_transformer = new ServiceTransformer();
    }


    public function viewConversations(Request $request)
    {
        $additionalData = ['user' => $request->user()];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Conversations fetched successfully.');
    }

    public function viewMessages(Request $request, Conversation $conversation)
    {
        $additionalData = [
            'user' => $request->user(),
            'conversation' => $conversation
        ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Messages fetched successfully.');
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $validator = Validator::make($request->all(), [
        'body' => 'required_without:file|string|max:2000',
        'file' => [
            'nullable',
            'file',
            'mimes:jpg,jpeg,png,gif,mp4,mov,avi,mp3,wav,m4a', 
            'max:20480', 
        ],
    ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 422);
        }

        $additionalData = [
        'user' => $request->user(),
        'conversation' => $conversation,
        'body' => $request->input('body'), 
        'file' => $request->file('file'),   
    ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Message sent successfully.');
    }
}
