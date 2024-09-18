<?php

namespace App\Helpers;

use DateTime;

function formatDate($dateString, $format = 'Y-m-d')
{
    $date = new DateTime($dateString);
    return $date->format($format);
}
