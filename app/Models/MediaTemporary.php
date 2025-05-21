<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MediaTemporary extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $connection = 'mysql';
    protected $table = 'media_temporary';

    const COLLECTION_TEMP = 'temp';
    const COLLECTION_PROVINCE_COVER = 'cover';
    const COLLECTION_COMMENT = 'comment';
    const COLLECTION_AVATAR = 'avatar';
    const COLLECTION_TOPIC_AVATAR = 'topic_avatar';
    const COLLECTION_BANNER = 'banner';
    const COLLECTION_TAG = 'tag';
    const COLLECTION_MULTIPLE_TAG = 'multiple_tag';
    const COLLECTION_PRODUCTABLE = 'productable';
    const COLLECTION_PRODUCTABLE_GALLERIES = 'product_galleries';
    const COLLECTION_GALLERIES = 'galleries';
    const COLLECTION_CONTRACT = 'contract';
    const COLLECTION_POST = 'post';
    const COLLECTION_PROMOTION = 'promotion';
    const COLLECTION_PROMOTION_THUMBNAIL = 'promotion_thumbnail';
    const COLLECTION_MESSAGE = 'message';
    const COLLECTION_BRAND_APP_ICON = 'brand_app_icon';
    const COLLECTION_BRAND_APP_BUNDLE = 'brand_app_version_bundle';
    const COLLECTION_ORGANIZATION_TREND_THUMBNAIL = 'organization_trend_thumbnail';
    const COLLECTION_ORGANIZATION_TREND = 'organization_trend';

    // const RESOLUTION = [
    //     self::CI_AVATAR => [250, 250],
    //     self::CI_SERVICE => [450, 450],
    //     self::CI_PRODUCT => [450, 450],
    //     self::CI_TAGS => [100, 100],
    // ];

    const IMAGE_TYPE = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/bmp',
        'image/gif',
    ];
    const VIDEO_TYPE = [
        'video/mp4',
        'video/webm',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::COLLECTION_TEMP)
            ->useDisk('media')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/bmp',
                'image/gif',
                'video/webm',
                'video/mp4',
                'application/pdf',
                'text/csv',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/zip'
            ]);
    }
}
