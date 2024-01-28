<?php

namespace App\Services;
use Illuminate\Support\Facades\File;


class FileService
{
    public function createOrUpdateFile($request, $model)
    {
        $videos = $request->file('file');

        // Check if $videos is not null before iterating


                if ($this->isValidVideo($videos)) {
                    $data = $this->getVideoData($videos, $model);

                    // Check if media entry already exists
                    $existingMedia = $model->media()->first();

                    if ($existingMedia) {
                        $this->updateVideo($videos, $data, $model, $existingMedia);
                    } else {
                        $this->createVideo($videos, $data, $model);
                    }
                }



    }

    protected function createVideo($video, $data, $model)
    {
        $this->saveVideo($video, $data, $model);
    }

    protected function updateVideo($video, $data, $model, $existingMedia)
    {
        $this->deleteVideoFile($existingMedia->filename);
        $this->saveVideo($video, $data, $model);
        $existingMedia->delete();
    }

    protected function saveVideo($video, $data, $model)
    {
        $video->move(public_path('videos'), $data['filename']);
        $model->file()->create($data);
    }

    protected function isValidVideo($video)
    {
        return $video->isValid() && $video->isFile() && $this->isVideoMimeTypeValid($video) && $video->getSize();
    }

    protected function isVideoMimeTypeValid($video)
    {
        $validMimeTypes = ['video/mp4', 'video/mpeg', 'video/quicktime']; // Add more if needed

        return in_array($video->getClientMimeType(), $validMimeTypes);
    }

    protected function getVideoData($video, $model)
    {
        return [
            'filename' => 'videos/' . $video->getClientOriginalName(),
            'filetype' => $video->getClientMimeType(),
            'type' => 'video',
            'createBy_type' => get_class($model),
            'createBy_id' => $model->id,
            'updateBy_type' => null,
            'updateBy_id' => null,
        ];
    }

    protected function deleteVideoFile($filename)
    {
        $filePath = public_path('videos') . '/' . $filename;

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }









}
