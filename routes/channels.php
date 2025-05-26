<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('subscribe-chat.user_id.{userId}', function ($user, $userId) {
  return true;
});
