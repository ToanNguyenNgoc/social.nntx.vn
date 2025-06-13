<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\AllowedFilter;

class RoleRepo extends BaseRepository
{
  public function __construct(Request $request)
  {
    parent::__construct($request);
  }

  public function getModel(): string
  {
    return Role::class;
  }
  public function getSearchable(): array
  {
    return [
      AllowedFilter::callback('keyword', function ($builder, $value) {
        $builder->where('name', 'like', $value . '%');
      }),
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
    return [];
  }
}
