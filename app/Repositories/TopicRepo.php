<?php

namespace App\Repositories;

use App\Models\Topic;
use Illuminate\Http\Request;

class TopicRepo extends BaseRepository
{
  public function __construct(Request $request)
  {
    parent::__construct($request);
  }

  public function getModel(): string
  {
    return Topic::class;
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
    return [
      'users'
    ];
  }
}
