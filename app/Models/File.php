<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $table="files";
    protected $fillable=[
        "fileable_type",
        "fileable_id",
        "filename",
        "filetype",
        "type",
        "createBy_id",
        "createBy_type",
        "updateBy_id",
        "updateBy_type",
    ];

    public function fileable()
    {
        return $this->morphTo();
    }
}
