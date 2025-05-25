<?php

namespace App\Repositories;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageRepo extends BaseRepository
{
  public function __construct(Request $request)
  {
    parent::__construct($request);
  }

  public function getModel(): string
  {
    return Message::class;
  }
  public function getSearchable(): array
  {
    return [];
  }
  public function getSorts(): array
  {
    return ['-id', '-created_at'];
  }
  public function getOnlyFields(): array
  {
    return [];
  }
  public function getAppends(): array
  {
    return [];
  }
  public function getIncludes(): array
  {
    return ['favorites'];
  }
}
