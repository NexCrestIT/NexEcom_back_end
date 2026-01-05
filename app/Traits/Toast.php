<?php
namespace App\Traits;
use Illuminate\Support\Facades\Session;

trait Toast
{
    protected function toast( string $type,string $title, string $message)
    {
        $toast = [
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
        ];
 
        Session::flash('toast', $toast);
    }
}