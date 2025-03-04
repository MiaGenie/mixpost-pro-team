<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inovector\Mixpost\Events\Media\UploadingMediaFile;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Integrations\Unsplash\Jobs\TriggerDownloadJob;
use Inovector\Mixpost\MediaConversions\MediaImageResizeConversion;
use Inovector\Mixpost\Support\File;
use Inovector\Mixpost\Support\MediaUploader;
use Inovector\Mixpost\Util;

class MediaDownloadExternal extends FormRequest
{
    public function rules(): array
    {
        return [
            'from' => ['required', 'string', 'in:stock,gifs'],
            'items' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($value as $item) {
                        $validKeys = ['id', 'url', 'source', 'author', 'download_data'];

                        $extraKeys = array_diff(array_keys($item), $validKeys);

                        if (!empty($extraKeys)) {
                            $fail('The ' . $attribute . ' item contains invalid keys: ' . implode(', ', $extraKeys));
                            break;
                        }

                        foreach (array_flip(Arr::except(array_flip($validKeys), ['source', 'author'])) as $key) {
                            if (empty($item[$key])) {
                                $fail('The ' . $attribute . ' item must have a non-empty "' . $key . '" key.');
                                break 2;
                            }
                        }

                        if (!Util::isPublicDomainUrl($item['url'])) {
                            $fail('The ' . $attribute . ' contains non-public domain URLs.');
                        }
                    }
                },
            ],
        ];
    }

    public function handle(): Collection
    {
        return collect($this->input('items'))->map(function ($item) {
            $result = Http::get($item['url']);

            $file = File::fromBase64(base64_encode($result->body()));

            UploadingMediaFile::dispatch($file);

            $prefix = WorkspaceManager::current()->uuid;
            $date = now()->format('m-Y');

            $media = MediaUploader::fromFile($file)->path("$prefix/uploads/$date")
                ->conversions([
                    MediaImageResizeConversion::name('thumb')->width(430),
                ])
                ->data($this->getData($item))
                ->uploadAndInsert();

            $method = 'downloadAction' . Str::studly($this->input('from'));

            $this->$method($item);

            return $media;
        });
    }

    protected function getData(array $item): array
    {
        $data = [];

        if (!empty($item['source'])) {
            $data['source'] = $item['source'];
        }

        if (!empty($item['author'])) {
            $data['author'] = $item['author'];
        }

        return $data;
    }

    protected function downloadActionStock(array $item): void
    {
        if (empty($item['download_data']['download_location'])) {
            return;
        }

        TriggerDownloadJob::dispatch($item['download_data']['download_location']);
    }

    protected function downloadActionGifs(array $item): void
    {

    }
}
