<?php

namespace App\Repositories;

use App\Models\Follow;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;

class FollowRepo extends BaseRepository
{
  public function __construct(Request $request)
  {
    parent::__construct($request);
  }

  public function getModel(): string
  {
    return Follow::class;
  }
  public function getSearchable(): array
  {
    return [
      AllowedFilter::scope('keyword'),
      AllowedFilter::callback('is_accept', fn($builder, $value) => $builder->where('is_accept', boolval($value))),
      AllowedFilter::callback('user_id', fn($builder, $value) => $builder->where('user_id', $value)),
      AllowedFilter::callback('follower_user_id', fn($builder, $value) => $builder->where('follower_user_id', $value)),
    ];
  }
  public function getSorts(): array
  {
    return [];
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
    return ['user', 'follower_user','following_user'];
  }
}
