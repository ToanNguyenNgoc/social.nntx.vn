<?php

namespace App\Utils;

class RegexUtils
{
  const REGEX_TAGS = 'regex:/^(?!.*<(\/?script|\/?div|iframe|object|embed|form|style|svg|on\w+|img|a|input|select|textarea)[^>]*?>)(?!.*(select|update|insert|delete|drop|alter)\s)/i';
}
