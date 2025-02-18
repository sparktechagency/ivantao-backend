<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplyForm extends Model
{
    protected $guarded = ['id'];

    public function career()
    {
        return $this->belongsTo(Career::class, 'career_id');
    }

    public function getDocumentAttribute($document)
    {
        return asset('uploads/documents/' . ($document ?? null));
    }
}
