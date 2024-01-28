<?php

namespace App\Services;
use Illuminate\Support\Facades\File;
use App\Models\Media;

class MediaService
{
	  /* function createMedia($request, $model)
    {
        if ($request->hasFile('videos')) {
            $image = $request->file('videos');
            $data = [
                'filename' => time() . $image->getClientOriginalExtension(),
                'filetype' => $image->getClientMimeType(),
                'type' => 'image',
                'createBy_type' => get_class($model),
                'createBy_id' => $model->id,
                'updateBy_type' => null,
                'updateBy_id' => null,
            ];

            $image->move(public_path('images'), $data['filename']);
            $model->image()->updateOrCreate(['createBy_id' => $model->id], $data);
        }
    }*/
	

	
	
	
	
	
	
	
/*	
public function createMedia($request, $model)
{
    $mediaFiles = $request->file('videos');

    foreach ($mediaFiles as $file) {
        if ($this->isValidMedia($file)) {
            $data = $this->getMediaData($file, $model);
            $this->saveMediaFile($file, $data, $model);
        }
    }
}

public function updateMedia($request, $model)
{
    $mediaFiles = $request->file('videos');

    if ($mediaFiles) {
        $this->deleteOldMedia($model->media);
        $this->saveMedia($mediaFiles, $model);
    }
}

protected function updateMediaFile($file, $data, $model, $existingMedia)
{
    $this->deleteMediaFile($existingMedia->filename);
    $file->move(public_path('images'), $data['filename']);
    $existingMedia->update($data);
}

protected function saveMediaFile($file, $data, $model)
{
    $file->move(public_path('images'), $data['filename']);
    $model->media()->create($data);
}

protected function deleteOldMedia($media)
{
    if ($media) {
       $this->deleteMediaFile($media->filename);
            $media->delete();
        }

      //  $media->delete();
    }


protected function deleteMediaFile($filename)
{
    $filePath = public_path('images') . '/' . $filename;

    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

protected function isValidMedia($file)
{
    return $file->isValid() && $file->isFile() && $file->getClientMimeType() && $file->getSize();
}

protected function getMediaData($file, $model)
{
    return [
        'filename' => time() . '_' . $file->getClientOriginalName(),
        'filetype' => $file->getClientMimeType(),
        'type' => 'videos',
        'createBy_type' => get_class($model),
        'createBy_id' => $model->id,
        'updateBy_type' => null,
        'updateBy_id' => null,
    ];
}

protected function saveMedia($files, $model)
{
    foreach ($files as $file) {
        if ($this->isValidMedia($file)) {
            $data = $this->getMediaData($file, $model);
            $this->saveMediaFile($file, $data, $model);
        }
    }
}
*/
	public function updateOrCreateMedia($product,$request)
{
    //$video = $request->file('videos');

    if ($request) {
        $data = $this->getMediaData($request, $product);
        $this->saveMediaFile($request, $data, $product);
    }
}

protected function saveMediaFile($file, array $data, $model)
{
    // Check if there is an existing media record
    $existingMedia = $model->media()->first();

    // Delete the old video if it exists
    if ($existingMedia) {
        $this->deleteMediaFile($existingMedia->filename);
    }

    // Move the new video to the 'videos' directory
    $file->move(public_path('images'), $data['filename']);

    // Update or create the media record
    $model->media()->updateOrCreate(['createBy_id' => $model->id], $data);
}

protected function deleteMediaFile($filename)
{
    // Delete the old video file
    $filePath = public_path('images') . '/' . $filename;

    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

protected function getMediaData($file, $model)
{
    $timestamp = time();
    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    $extension = $file->getClientOriginalExtension();

    return [
        'filename' => $timestamp . '_' . $originalName . '.' . $extension,
        'filetype' => $file->getClientMimeType(),
        'type' => 'videos',  // Assuming you're dealing with videos
        'createBy_type' => get_class($model),
        'createBy_id' => $model->id,
        'updateBy_type' => null,
        'updateBy_id' => null,
    ];
}
}
