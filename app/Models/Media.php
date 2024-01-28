<?php

namespace App\Models;

use App\Traits\ActivityLogTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $table="medias";

    protected $fillable=[
        "mediaable_type",
        "mediaable_id",
        "filename",
        "filetype",
        "type",
        "createBy_id",
        "createBy_type",
        "updateBy_id",
        "updateBy_type",
    ];

    public function mediaable()
    {
        return $this->morphTo();
    }
}
