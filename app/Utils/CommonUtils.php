<?php

namespace App\Utils;

class CommonUtils
{
  static function generateOTP($length = 6)
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
      $otp .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $otp;
  }
}
