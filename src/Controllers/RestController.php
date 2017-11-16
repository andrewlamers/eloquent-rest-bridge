<?php
/**
 * Created by PhpStorm.
 * User: andrewlamers
 * Date: 11/15/17
 * Time: 10:19 AM
 */

namespace Andrewlamers\EloquentRestBridge\Controllers;

use Illuminate\Routing\Controller;

class RestController extends Controller
{
    public function handler()
    {
        $handler = app('restHandler');

        return $handler->response();
    }
}