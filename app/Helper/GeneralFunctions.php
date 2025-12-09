<?php
namespace App\Helper;
use Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;

class GeneralFunctions
{



public static function formatDate($date, $format = null)
{
    if (empty($date)) {
        return '';
    }

    if ($format === null) {
        $format = 'd M y';
    }

    $carbonDate = new \Carbon\Carbon($date);

    if ($format === 'd M') {
        return $carbonDate->format($format);
    }

    if ($format === 'm Y') {
        return $carbonDate->format('M Y');
    }

    return $carbonDate->format($format);
}
 /**
     * Get doctor profile image with fallback default image.
     */
    public static function doctorImage($photo = null, $gender = 'male')
    {
        // Base upload path (the one you provided)
        $uploadPath = public_path('uploads/doctors/' . ($photo ?? ''));

        // If real image exists → return that
        if ($photo && file_exists($uploadPath)) {
            return asset('public/uploads/doctors/' . $photo);
        }

        // Fallback defaults based on gender
        if ($gender === 'female') {
            return asset('assets/img/doctors/default-female-doctor.png');
        }

        return asset('assets/img/doctors/default-male-doctor.png');
    }

}