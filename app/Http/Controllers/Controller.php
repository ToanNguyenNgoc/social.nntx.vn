<?php

namespace App\Http\Controllers;

use App\Models\MediaTemporary;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function jsonResponse(
        MessageBag|LengthAwarePaginator|Collection|array|null|Model|\Illuminate\Support\Collection|bool|Validator $context = [],
        bool|int $status = 200,
        ?string $message = null
    ): JsonResponse {
        if ($context instanceof Model) {
            $context = $context->toArray();
        } else if ($context instanceof Validator) {
            $context = $context->errors()->toArray();
        }

        if ($status >= 500) {
            throw new \Exception($message);
        }

        return response()->json([
            'status' => ($status === true) ? 200 : (($status === false) ? 400 : $status),
            'message' => $message,
            'context' => $context
        ], ($status === true) ? 200 : (($status === false) ? 400 : $status));
    }

    public function onUserAuth(): User
    {
        return auth('sanctum')->user();
    }
    public function addMediaToModel(Model $model, $media_id = null,  string $media_collection)
    {
        if (!$media_id) return;
        $mediaTemporary = MediaTemporary::findOrFail($media_id);
        try {
            $pathToFile = $mediaTemporary->getFirstMedia(MediaTemporary::COLLECTION_TEMP);
            $model->addMedia($pathToFile->getPath())->toMediaCollection($media_collection);
            $mediaTemporary->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 400,
                'message' => $th->getMessage(),
                'context' => null
            ], 400);
            return false;
        }
        return true;
    }
}
