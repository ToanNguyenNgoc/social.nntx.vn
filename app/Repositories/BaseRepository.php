<?php

namespace App\Repositories;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\QueryBuilderRequest;

abstract class BaseRepository
{
  public Model|Builder $model;
  public QueryBuilder $filter;
  public int $page = 1;
  public int $limit = 30;
  public int $maxLimit = 100;

  /**
   * @throws BindingResolutionException
   */
  public function __construct(public Request $request)
  {
    QueryBuilderRequest::setArrayValueDelimiter('|');
    QueryBuilderRequest::setIncludesArrayValueDelimiter('|');
    QueryBuilderRequest::setAppendsArrayValueDelimiter('|');
    QueryBuilderRequest::setFieldsArrayValueDelimiter('|');
    QueryBuilderRequest::setSortsArrayValueDelimiter('|');
    QueryBuilderRequest::setFilterArrayValueDelimiter('|');

    $this->setModel();
    $this->filter = QueryBuilder::for($this->model->applyScopes());

    $searchable = $this->getSearchable();
    if (!empty($searchable)) {
      $this->filter->allowedFilters($searchable);
    }

    $sorts = $this->getSorts();
    if (!empty($sorts)) {
      $this->filter->allowedSorts($sorts);
    }

    $allowedFields = $this->getOnlyFields();
    if (!empty($allowedFields)) {
      $this->filter->allowedFields($allowedFields);
    }

    $appends = $this->getAppends();
    if (!empty($appends)) {
      $this->filter->allowedAppends($appends);
    }

    $includes = $this->getIncludes();
    if (!empty($includes)) {
      $this->filter->allowedIncludes($includes);
    }

    $page = $request->query('page');
    $this->page = (is_numeric($page) && $page > 0) ? $page : 1;

    $limit = $request->query('limit');
    $this->limit = (is_numeric($limit) && $limit > 0 && $limit <= $this->maxLimit) ? $limit : $this->maxLimit;
  }

  /**
   * users?filter[name]=John
   * User`s that contain the string "John" in their name
   */
  abstract public function getSearchable(): array;

  /**
   * /users?sort=id
   * all `User`s sorted by ascending id
   */
  abstract public function getSorts(): array;

  /**
   * /users?fields=id,email
   * the fetched `User`s will only have their id & email set
   */
  abstract public function getOnlyFields(): array;

  /**
   * /users?append=full_name
   * the resulting JSON will have the `getFullNameAttribute` attributes included
   */
  abstract public function getAppends(): array;

  /**
   * /users?include=posts
   * all `User`s with their `posts` loaded
   */
  abstract public function getIncludes(): array;

  public function getModel() {}

  /**
   * Set model
   * @throws BindingResolutionException
   */
  public function setModel()
  {
    $this->model = app()->make($this->getModel());
  }

  public function getAll(): array|Collection
  {
    return $this->filter->get()->all();
  }

  public function find($id): mixed
  {
    return $this->model->find($id)->makeHidden($this->getHidden());
  }

  public function findOrFail($id): Model|null
  {
    return $this->filter->findOrFail($id)->makeHidden($this->getHidden());
  }

  public function create(array $attributes = []): mixed
  {
    return $this->model->create($attributes);
  }

  public function update(int $id, $attributes = []): mixed
  {
    return $this->findOrFail($id)->update($attributes);
  }

  public function delete($id): bool
  {
    $result = $this->find($id);
    if ($result) {
      $result->delete();

      return true;
    }

    return false;
  }

  public function with(array $columns)
  {
    return $this->filter->with($columns);
  }

  public function paginate(array $columns = ['*']): LengthAwarePaginator|Collection|array
  {
    $paginate = $this->filter->paginate($this->limit, $columns, 'page', $this->page);
    $data = $paginate->makeHidden($this->getHidden());
    $paginate->data = $data;
    return $paginate;
  }

  public function getHidden(): array
  {
    return [];
  }
}
