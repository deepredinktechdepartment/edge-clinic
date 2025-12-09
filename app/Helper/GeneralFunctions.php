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

}