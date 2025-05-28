<?php

namespace App\Traits;

use DateTimeInterface;

trait LocalizesTimestamps
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
    }
}
