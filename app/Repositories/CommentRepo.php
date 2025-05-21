<?php

namespace App\Repositories;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;

class CommentRepo extends BaseRepository
{
  public function __construct(Request $request)
  {
    parent::__construct($request);
  }

  public function getModel(): string
  {
    return Comment::class;
  }
  public function getSearchable(): array
  {
    return [
      AllowedFilter::callback('commentable_type', function ($builder, $value) {
        $morph_class = match ($value) {
          Comment::COMMENTABLE_TYPE_POST => Post::class,
          Comment::COMMENTABLE_TYPE_REPLY => Comment::class,
          default => Post::class,
        };

        $builder->where('commentable_type', $morph_class);
      }),
      AllowedFilter::exact('commentable_id'),
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
    return ['user'];
  }
}
