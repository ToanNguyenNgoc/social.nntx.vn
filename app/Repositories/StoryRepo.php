<?php

namespace App\Repositories;

use App\Models\Story;
use Illuminate\Http\Request;

class StoryRepo extends BaseRepository
{
  public function __construct(Request $request)
  {
    parent::__construct($request);
    $this->filter->defaultSort('-created_at');
  }

  public function getModel(): string
  {
    return Story::class;
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
      'favorites',
      'views'
    ];
  }
}
