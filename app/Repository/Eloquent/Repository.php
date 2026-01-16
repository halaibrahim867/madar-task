<?php

namespace App\Repository\Eloquent;

use App\Repository\RepositoryInterface;
use App\Traits\FileManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Repository implements RepositoryInterface
{
    use FileManager;
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    final public function getAll(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    final public function getById(
        $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model {
        return $this->model->select($columns)->with($relations)->findOrFail($modelId)->append($appends);
    }

    final public function get(
        $byColumn,
        $value,
        array $columns = ['*'],
        array $relations = [],
    ): array|Collection {
        return $this->model::query()->select($columns)->with($relations)->where($byColumn, $value)->get();
    }

    final public function first(
        $byColumn,
        $value,
        array $columns = ['*'],
        array $relations = [],
    ): Builder|Model|null {
        return $this->model::query()->select($columns)->with($relations)->where($byColumn, $value)->first();
    }

    final public function getFirst(): ?Model
    {
        return $this->model->first();
    }

    final public function create(array $payload): ?Model
    {
        $model = $this->model->create($payload);

        return $model->fresh();
    }

    final public function insert(array $payload): bool
    {
        $model = $this->model::query()->insert($payload);

        return $model;
    }


    final public function update($modelId, array $payload): bool
    {
        $model = $this->getById($modelId);

        return $model->update($payload);
    }

    final public function updateAndReturn($modelId, array $payload): Model|bool
    {
        $model = $this->getById($modelId);

        return $model->update($payload) ? $this->getById($modelId) : false;

    }

    final public function delete($modelId, array $filesFields = []): bool
    {
        $model = $this->getById($modelId);
        foreach ($filesFields as $field) {
            if ($model->$field !== null) {
                $this->deleteFile($model->$field);
            }
        }

        return $model->delete();
    }

    final public function forceDelete($modelId, array $filesFields = []): bool
    {
        $model = $this->getById($modelId);
        foreach ($filesFields as $field) {
            if ($model->$field !== null) {
                $this->deleteFile($model->$field);
            }
        }

        return $model->forceDelete();
    }

    final public function paginate(int $perPage = 10, array $relations = [], $orderBy = 'ASC', $columns = ['*'])
    {
        return $this->model::query()->select($columns)->with($relations)->orderBy('id', $orderBy)->paginate($perPage);
    }

    final public function paginateWithQuery(
        $query,
        int $perPage = 10,
        array $relations = [],
        $orderBy = 'ASC',
        $columns = ['*'],
    ) {
        return $this->model::query()->select($columns)->where($query)->with($relations)->orderBy('id', $orderBy)->paginate($perPage);
    }

    final public function count()
    {
        return $this->model::query()->count();
    }


    final public function findOrFail($modelId, array $columns = ['*'], array $relations = []): Model
    {
        return $this->model::query()->select($columns)->with($relations)->findOrFail($modelId);
    }
}
