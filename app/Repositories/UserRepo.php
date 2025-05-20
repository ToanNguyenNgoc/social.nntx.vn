<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Http\Request;

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
    return [];
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
    return [];
  }
}
