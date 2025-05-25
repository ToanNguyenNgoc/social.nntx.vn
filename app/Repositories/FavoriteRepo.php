<?php

namespace App\Repositories;

use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteRepo extends BaseRepository
{
  public function __construct(Request $request)
  {
    parent::__construct($request);
  }

  public function getModel(): string
  {
    return Favorite::class;
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
    return ['user'];
  }
}
