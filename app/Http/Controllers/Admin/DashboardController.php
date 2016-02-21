<?php


namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Modules\Core\Http\Controllers\Admin\AdminBaseController;

class DashboardController extends AdminBaseController
{
  /**
   * @var Authentication
   */
  private $auth;

  /**
   * @param Repository       $modules
   * @param WidgetRepository $widget
   * @param Authentication   $auth
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Display the dashboard with its widgets
   *
   * @return \Illuminate\View\View
   */
  public function index()
  {
    return view('dashboard::admin.dashboard');
  }
}
