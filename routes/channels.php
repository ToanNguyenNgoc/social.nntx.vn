<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('subscribe-chat.user_id.{userId}', function ($user, $userId) {
  return auth('sanctum')->check();
});
Broadcast::channel('subscribe-notification.user_id.{userId}', function ($user, $userId) {
  return auth('sanctum')->check();
});
