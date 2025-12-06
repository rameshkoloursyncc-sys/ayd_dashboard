<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalExecutive extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharma_company_id',
        'user_id',
        'api_id', // Added for Pinktree API integration
    ];

    public function pharmaCompany()
    {
        return $this->belongsTo(PharmaCompany::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'api_id';
    }
}