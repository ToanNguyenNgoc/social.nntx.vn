<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('subscribe-notification.user_id.{userId}', function () {
  return auth('sanctum')->check();
});

Broadcast::channel('subscribe-topic_id.${topicId}', function ($topicId) {
  return auth('sanctum')->check();
});
