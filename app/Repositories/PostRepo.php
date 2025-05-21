<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;

class PostRepo extends BaseRepository
{
  public function __construct(Request $request)
  {
    parent::__construct($request);
  }

  public function getModel(): string
  {
    return Post::class;
  }
  public function getSearchable(): array
  {
    return [
      AllowedFilter::scope('keyword'),
      AllowedFilter::callback('status', fn($builder, $value) => $builder->where('status', boolval($value))),
    ];
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
      'user'
    ];
  }
}
