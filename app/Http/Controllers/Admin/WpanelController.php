<?php

namespace App\Http\Controllers\Admin;

/*
 * Antvel - Admin Panel Controller
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;

class WpanelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('admin.dashboard');
    }
}
