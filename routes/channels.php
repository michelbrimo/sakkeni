<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    // If the conversation exists, check if the authenticated user's ID
    // matches either the user_id or the service provider's user_id.
    // This ensures only the two participants can listen.
    if ($conversation) {
        return $user->id === $conversation->user_id || $user->id === $conversation->serviceProvider->user_id;
    }

    return false;
});