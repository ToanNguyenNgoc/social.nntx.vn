<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Exception;
use Illuminate\Support\Facades\Log;

class RabbitMQPublisher
{

  const PARTNER_USER_REGISTER = 'user.register';

  public function publish(array $payload, string $pattern = 'user.register', string $exchange = 'nestjs_exchange'): bool
  {
    try {
      $connection = new AMQPStreamConnection(
        config('app.rabbit_mq.host'),
        config('app.rabbit_mq.port'),
        config('app.rabbit_mq.user'),
        config('app.rabbit_mq.password')
      );
      $channel = $connection->channel();

      $channel->exchange_declare($exchange, 'topic', false, true, false);
      $channel->queue_declare('nestjs_queue', false, true, false, false);
      $channel->queue_bind('nestjs_queue', $exchange, $pattern);
      
      $messageData = [
        'pattern' => $pattern,
        'data' => $payload,
      ];

      $message = new AMQPMessage(
        json_encode($messageData),
        [
          'content_type' => 'application/json',
          'delivery_mode' => 2,
        ]
      );

      $channel->basic_publish($message, $exchange, $pattern);

      $channel->close();
      $connection->close();

      return true;
    } catch (Exception $e) {
      Log::error('[RabbitMQ] Failed to publish message', [
        'error' => $e->getMessage(),
        'exchange' => $exchange,
        'routingKey' => $pattern,
        'payload' => $payload,
      ]);
      return false;
    }
  }
}
