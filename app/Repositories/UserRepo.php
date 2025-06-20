<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;

class UserRepo extends BaseRepository
{
  public function __construct(Request $request)
  {
    parent::__construct($request);
  }

  public function getModel(): string
  {
    return User::class;
  }
  public function getSearchable(): array
  {
    return [
      AllowedFilter::scope('keyword'),
      AllowedFilter::callback('id', fn($builder, $value) => $builder->where('id', $value)),
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
    return ['roles', 'followers'];
  }
}
