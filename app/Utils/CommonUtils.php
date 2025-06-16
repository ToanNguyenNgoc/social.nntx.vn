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

  function renderSwaggerAnnotation(array $config, ?string $saveToFile = null): string
  {
    $indent = ' * ';
    $annotation = "/**\n";
    $annotation .= "{$indent}@OA\\{$config['method']}(\n";
    $annotation .= "{$indent}    path=\"{$config['path']}\",\n";

    if (!empty($config['tags'])) {
      $tags = array_map(fn($t) => "\"$t\"", $config['tags']);
      $annotation .= "{$indent}    tags={" . implode(', ', $tags) . "},\n";
    }

    if (!empty($config['summary'])) {
      $annotation .= "{$indent}    summary=\"{$config['summary']}\",\n";
    }

    if (!empty($config['security'])) {
        $security = json_encode([$config['security']]);
        $security = str_replace(['{', '}', ':'], ['[', ']', ':'], $security);
        $annotation .= "{$indent}    security={$security},\n";
    }

    // Parameters
    if (!empty($config['parameters'])) {
      foreach ($config['parameters'] as $param) {
        $annotation .= "{$indent}    @OA\\Parameter(\n";
        foreach ($param as $key => $value) {
          $annotation .= "{$indent}        {$key}=\"" . addslashes($value) . "\",\n";
        }
        $annotation .= "{$indent}    ),\n";
      }
    }

    // RequestBody
    if (!empty($config['requestBody'])) {
      $annotation .= "{$indent}    @OA\\RequestBody(\n";
      $annotation .= "{$indent}        required=true,\n";
      $annotation .= "{$indent}        @OA\\JsonContent(\n";

      if (!empty($config['requestBody']['required'])) {
        $reqFields = implode('","', $config['requestBody']['required']);
        $annotation .= "{$indent}            required={\"$reqFields\"},\n";
      }

      foreach ($config['requestBody']['properties'] as $propName => $prop) {
        if (($prop['type'] ?? '') === 'array') {
          $annotation .= "{$indent}            @OA\\Property(\n";
          $annotation .= "{$indent}                property=\"{$propName}\", type=\"array\",\n";
          $annotation .= "{$indent}                @OA\\Items(type=\"{$prop['items']['type']}\"";
          if (isset($prop['items']['enum'])) {
            $enums = implode('","', $prop['items']['enum']);
            $annotation .= ", enum={\"$enums\"}";
          }
          $annotation .= "),\n";
          $annotation .= "{$indent}            ),\n";
        } else {
          $enumPart = isset($prop['enum']) ? ', enum={"' . implode('","', $prop['enum']) . '"}' : '';
          $annotation .= "{$indent}            @OA\\Property(property=\"{$propName}\", type=\"{$prop['type']}\", example=\"{$prop['example']}\"{$enumPart}),\n";
        }
      }

      $annotation .= "{$indent}        )\n";
      $annotation .= "{$indent}    ),\n";
    }

    // Responses
    if (!empty($config['responses'])) {
      foreach ($config['responses'] as $code => $desc) {
        $annotation .= "{$indent}    @OA\\Response(response=$code, description=\"{$desc}\"),\n";
      }
    }

    $annotation .= "{$indent})\n";
    $annotation .= " */";

    if ($saveToFile) {
      file_put_contents($saveToFile, $annotation);
    }

    return $annotation;
  }
}
