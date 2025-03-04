<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Resources\MediaResource;
use Inovector\Mixpost\Models\Media;

class MediaFetchUploadsController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        $records = Media::latest('created_at')->simplePaginate(30);

        return MediaResource::collection($records);
    }
}
