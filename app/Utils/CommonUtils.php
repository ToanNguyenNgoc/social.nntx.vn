<?php

namespace App\Utils;

class CommonUtils
{
  static function generateOTP($length = 6)
  {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
      $otp .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $otp;
  }

  static function decodeJwtPayload(string $token)
  {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
      return null;
    }

    $payload = $parts[1];

    $remainder = strlen($payload) % 4;
    if ($remainder !== 0) {
      $payload .= str_repeat('=', 4 - $remainder);
    }

    // Decode base64 URL-safe → JSON
    $decoded = base64_decode(strtr($payload, '-_', '+/'));

    if (!$decoded) {
      return null;
    }

    return json_decode($decoded, true);
  }
}
