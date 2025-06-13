<?php

namespace App\Http\Controllers;

use App\Models\MediaTemporary;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection;

class MediaController extends Controller
{
    //
    /**
     * @OA\Post(
     *     path="/api/media",
     *     summary="media.store",
     *     tags={"Media"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     description="File",
     *                     property="file",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */

    public function store()
    {
        try {
            $mt = new MediaTemporary();
            $mt->addMediaFromRequest('file')->toMediaCollection(MediaTemporary::COLLECTION_TEMP);
            $mt->user()->associate(auth('sanctum')->user());
            $mt->save();
        } catch (\Exception $e) {
            if ($e instanceof FileUnacceptableForCollection) {
                return $this->jsonResponse([], 400, 'File type not allow');
            } else if ($e instanceof FileIsTooBig) {
                return $this->jsonResponse([], 400, 'File is too big');
            } else if ($e instanceof FileDoesNotExist) {
                return $this->jsonResponse([], 400, 'File does not exist');
            }

            return $this->jsonResponse([], 500, $e->getMessage());
        }

        return $this->jsonResponse($mt->getFirstMedia(MediaTemporary::COLLECTION_TEMP));
    }
}
