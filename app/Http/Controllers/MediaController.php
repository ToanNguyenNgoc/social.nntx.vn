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
