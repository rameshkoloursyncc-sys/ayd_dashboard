<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_id', // Doctor ID from external API
        'pharma_company_id',
        'medical_executive_id',
        'mobile_no',
    ];

    // Removed user relationship - doctors are managed externally
    // Only storing mapping to pharma company and medical executive

    public function pharmaCompany()
    {
        return $this->belongsTo(PharmaCompany::class);
    }

    public function medicalExecutive()
    {
        return $this->belongsTo(MedicalExecutive::class);
    }

    public function getRouteKeyName()
    {
        return 'api_id';
    }
}