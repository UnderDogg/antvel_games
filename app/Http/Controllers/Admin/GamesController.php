<?php

namespace app\Http\Controllers\Admin;

/*
 * Antvel - Games Controller
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */

use App\Category;
use App\Helpers\featuresHelper;
use App\Helpers\File;
use App\Helpers\gamesHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Order;
use App\OrderDetail;
use App\Game;
use App\GameDetail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use Datatable;


class GamesController extends Controller
{
  private $form_rules = [
    'category_id' => 'required',
    'key' => 'required',
    'name' => 'required|max:100',
  ];










  public function getDatatable()
  {
    return Datatable::collection(Game::all(array('id', 'name')))
      ->showColumns('id', 'name')
      ->searchColumns('name')
      ->orderColumns('id', 'name')
      ->make();
  }


  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index(Request $request)
  {
    /**
     * $refine
     * array that contains all the information retrieved through the URL
     * array_unique is applied to avoid redundant variables.
     *
     * @var array
     */
    $refine = \Utility::requestToArrayUnique($request->all());

    /**
     * $search
     * this var contains the information typed into search box.
     *
     * @var [type]
     */
    $search = $request->get('search');

    /**
     * $games
     * Filtered games list.
     *
     * @var [type]
     */
    $games = Game::select('id', 'category_id', 'name', 'parent_id', 'tags')
      ->search($search, false)
      ->refine($refine)
      ->free()
      ->actives()
      ->orderBy('rate_val', 'desc');

    /**
     * $all_games
     * it is the game list refined, which will be used in each filter process below.
     *
     * @var [type]
     */
    $all_games = $games->get();

    /**
     * $suggestions
     * Array which contains the user game suggestions.
     *
     * @var array
     */
    $suggestions = [];
    if (count($all_games) < 28) {
      $suggestions = gamesHelper::suggest('my_searches');
    }

    /*
     * $filters
     * it is the refine menu array, which is used to build the search options
     * @var [type]
     */
    $category_id = $request->get('category') ? $request->get('category') : 'mothers';
    $categories = \Cache::remember('categories_' . $category_id, 25, function () use ($category_id) {
      return Category::select('id', 'name')
        ->childsOf($category_id)
        ->actives()
        ->get()
        ->toArray();
    });

    $filters = gamesHelper::countingGamesByCategory($all_games, $categories);

    //condition
    $filters['conditions'] = array_count_values($all_games->lists('condition')->toArray());

    //brand filter
    $filters['brands'] = array_count_values($all_games->lists('brand')->toArray());

    //features
    $features = [];
    $irrelevant_features = ['images', 'dimensions', 'weight', 'brand']; //this has to be in company setting module
    foreach ($all_games->lists('features') as $feature) {
      $feature = array_except($feature, $irrelevant_features);
      foreach ($feature as $key => $value) {
        $features[$key][] = $value;
      }
    }

    //games by feature
    foreach ($features as $key => $value) {
      foreach ($features[$key] as $row) {
        if (!is_array($row)) {
          $filters[$key][$row] = !isset($filters[$key][$row]) ? 1 : $filters[$key][$row] + 1;
        }
      }
    }

    //prices filter
    $prices = $all_games->lists('price', 'price')->toArray();
    sort($prices);

    //saving tags from searching games in users preferences
    if ($search != '') {
      $my_searches = [];
      $cont = 0;
      foreach ($all_games as $game) {
        if (trim($game->tags) != '') {
          $my_searches = array_merge($my_searches, explode(',', $game->tags));
        }
        if ($cont++ == 10) {
          break;
        }
      }

      if (count($my_searches) > 0) {
        UserController::setPreferences('my_searches', $my_searches);
      }
    }

    $games = $games->paginate(28);
    $panel = $this->panel;
    $panel['left']['class'] = 'categories-panel';
    $games->each(function (&$item) {
      if ($item['rate_count'] > 0) {
        $item['num_of_reviews'] = $item['rate_count'] . ' ' . \Lang::choice('store.review', $item['rate_count']);
      }
    });

    return view('games.index', compact('filters', 'games', 'panel', 'listActual', 'search', 'refine', 'suggestions'));
  }

  public function myGames(Request $request)
  {
    $filter = $request->get('filter');
    if ($filter && $filter != '') {
      switch ($filter) {
        case 'active':
          $games = Game::auth()->actives()->where('type', '<>', 'freegame')->paginate(12);
          break;
        case 'inactive':
          $games = Game::auth()->inactives()->where('type', '<>', 'freegame')->paginate(12);
          break;
        case 'low':
          $games = Game::auth()->whereRaw('stock <= low_stock')->where('type', '<>', 'freegame')->paginate(12);
          break;
        default:
          $games = Game::auth()->where('type', '<>', 'freegame')->paginate(12);
          break;
      }
    } else {
      $games = Game::auth()->where('type', '<>', 'freegame')->paginate(12);
    }
    $panel = $this->panel;

    return view('games.myGames', compact('panel', 'games', 'filter'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return Response
   */
  public function create()
  {
    $game = Game::find(-50);
    $features = GameDetail::all()->toArray();
    $arrayCategories = Category::actives()
      ->lightSelection()
      ->get()
      ->toArray();

    $categories = [
      '' => trans('game.controller.select_category'),
    ];

    $condition = [
      'new' => trans('game.controller.new'),
      'refurbished' => trans('game.controller.refurbished'),
      'used' => trans('game.controller.used'),
    ];

    $typesGame = [
      'item' => trans('game.controller.item'),
      'key' => trans('game.globals.digital_item') . ' ' . trans('game.globals.key'),
    ];

    $typeItem = 'item';

    //categories drop down formatted
    gamesHelper::categoriesDropDownFormat($arrayCategories, $categories);

    $disabled = '';
    $edit = false;
    $panel = $this->panel;
    $oldFeatures = GameDetail::oldFeatures([]);
    $gamesDetails = new featuresHelper();

    return view('games.form',
      compact('game', 'panel', 'features', 'categories', 'condition', 'typeItem', 'typesGame', 'disabled', 'edit', 'oldFeatures', 'gamesDetails'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store(Request $request)
  {
    if (!$request->input('type')) {
      return redirect()->back()
        ->withErrors(['induced_error' => [trans('globals.error') . ' ' . trans('globals.induced_error')]]);
    }

    $rules = $this->rulesByTypes($request);
    $v = Validator::make($request->all(), $rules);

    if ($v->fails()) {
      return redirect()->back()
        ->withErrors($v->errors())->withInput();
    }

    $features = $this->validateFeatures($request->all());
    if (!is_string($features)) {
      return redirect()->back()
        ->withErrors($features)->withInput();
    }

    $game = new Game();
    $game->name = $request->input('name');
    $game->category_id = $request->input('category_id');
    $game->user_id = \Auth::id();
    $game->description = $request->input('description');
    $game->bar_code = $request->input('bar_code');
    $game->brand = $request->input('brand');
    $game->price = $request->input('price');
    $game->condition = $request->input('condition');
    $game->features = $features;
    $game->type = $request->input('type');
    if ($request->input('type') == 'item') {
      $game->stock = $request->input('stock');
      $game->low_stock = $request->input('low_stock');
      if ($request->input('stock') > 0) {
        $game->status = $request->input('status');
      } else {
        $game->status = 0;
      }
    } else {
      $game->status = $request->input('status');
    }
    $game->save();
    $message = '';
    if ($request->input('type') != 'item') {
      switch ($request->input('type')) {
        case 'key':
          $num = 0;
          if (!Storage::disk('local')->exists($request->input('key'))) {
            return redirect()->back()
              ->withErrors(['induced_error' => [trans('globals.file_does_not_exist')]])->withInput();
            // ->withErrors(array('induced_error'=>array(storage_path().'files/key_code'.$request->input('key'))))->withInput();
          }
          $contents = Storage::disk('local')->get($request->input('key'));
          $contents = explode("\n", rtrim($contents));
          $warning = false;
          $len = 0;
          $virtualGame = new virtualGame();
          $virtualGame->game_id = $game->id;
          $virtualGame->key = 'undefined';
          $virtualGame->status = 'cancelled';
          $virtualGame->save();
          foreach ($contents as $row) {
            $virtualGame = new virtualGame();
            $virtualGame->game_id = $game->id;
            $virtualGame->status = 'open';
            $virtualGame->key = $row;
            $virtualGame->save();
            $num++;
            if ($len == 0) {
              $len = strlen(rtrim($row));
            } elseif (strlen(rtrim($row)) != $len) {
              $warning = true;
            }
          }
          $game->stock = $num;
          if ($num == 0) {
            $game->status = 0;
          }
          $game->save();
          $message = ' ' . trans('game.controller.review_keys');
          if ($warning) {
            $message .= ' ' . trans('game.controller.may_invalid_keys');
          }
          Storage::disk('local')->deleteDirectory('key_code/' . \Auth::id());
          break;
        case 'software':
          break;
        case 'software_key':
          break;
        case 'gift_card':
          break;
      }
    }
    Session::flash('message', trans('game.controller.saved_successfully') . $message);

    return redirect('games/' . $game->id);
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   *
   * @return Response
   */
  public function show($id)
  {
    $user = \Auth::user();
    $allWishes = '';
    $panel = [
      'center' => [
        'width' => '12',
      ],
    ];

    if ($user) {
      $allWishes = Order::ofType('wishlist')
        ->where('user_id', $user->id)
        ->where('description', '<>', '')
        ->orderBy('id', 'desc')
        ->take(5)
        ->get();
    }

    $game = Game::select([
      'id', 'category_id', 'user_id', 'name', 'description',
      'price', 'stock', 'features', 'condition', 'rate_val',
      'rate_count', 'low_stock', 'status', 'type', 'tags', 'games_group', 'brand',
    ])->with([
      'group' => function ($query) {
        $query->select(['id', 'games_group', 'features']);
      },
    ])->with('categories')->find($id);

    if ($game) {

      //if there is a user in session, the admin menu will be shown
      if ($user && $user->id == $game->user_id) {
        $panel = [
          'left' => ['width' => '2'],
          'center' => ['width' => '10'],
        ];
      }

      //retrieving games features
      $features = GameDetail::all()->toArray();

      //increasing game counters, in order to have a suggestion orden
      $this->setCounters($game, ['view_counts' => trans('globals.game_value_counters.view')], 'viewed');

      //saving the game tags into users preferences
      if (trim($game->tags) != '') {
        UserController::setPreferences('game_viewed', explode(',', $game->tags));
      }

      //receiving games user reviews & comments
      $reviews = OrderDetail::where('game_id', $game->id)
        ->whereNotNull('rate_comment')
        ->select('rate', 'rate_comment', 'updated_at')
        ->orderBy('updated_at', 'desc')
        ->take(5)
        ->get();

      //If it is a free game, we got to retrieve its package information
      if ($game->type == 'freegame') {
        $order = OrderDetail::where('game_id', $game->id)->first();
        $freegame = FreeGameOrder::where('order_id', $order->order_id)->first();
      }

      $freegameId = isset($freegame) ? $freegame->freegame_id : 0;

      //games suggestions control
      //saving game id into suggest-listed, in order to exclude games from suggestions type "view"
      Session::push('suggest-listed', $game->id);
      $suggestions = $this->getSuggestions(['preferences_key' => $game->id, 'limit' => 4]);
      Session::forget('suggest-listed');

      //retrieving games groups of the game shown
      if (count($game->group)) {
        $featuresHelper = new featuresHelper();
        $game->group = $featuresHelper->group($game->group);
      }

      return view('games.detailProd', compact('game', 'panel', 'allWishes', 'reviews', 'freegameId', 'features', 'suggestions'));
    } else {
      return redirect(route('games'));
    }
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param int $id
   *
   * @return Response
   */
  public function edit($id)
  {
    $game = Game::find($id);
    if (\Auth::id() != $game->user_id) {
      return redirect('games/' . $game->user_id)->withErrors(['not_access' => [trans('globals.not_access')]]);
    }

    $typeItem = $game->type;
    $disabled = '';

    $order = OrderDetail::where('game_id', $id)
      ->join('orders', 'order_details.order_id', '=', 'orders.id')
      ->first();

    if ($order) {
      $disabled = 'disabled';
    }

    $features = GameDetail::all()->toArray();

    $allCategoriesStore = Category::actives()->lightSelection()->get()->toArray();

    $categories = ['' => trans('game.controller.select_category')];

    //categories drop down formatted
    gamesHelper::categoriesDropDownFormat($allCategoriesStore, $categories);

    $condition = ['new' => trans('game.controller.new'), 'refurbished' => trans('game.controller.refurbished'), 'used' => trans('game.controller.used')];

    $edit = true;
    $panel = $this->panel;

    $oldFeatures = GameDetail::oldFeatures($game->features);

    $gamesDetails = new featuresHelper();

    return view('games.form', compact('game', 'panel', 'features', 'categories', 'condition', 'typeItem', 'disabled', 'edit', 'oldFeatures', 'gamesDetails'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param int $id
   *
   * @return Response
   */
  public function update($id, Request $request)
  {
    if (!$request->input('type')) {
      return redirect()->back()
        ->withErrors(['induced_error' => [trans('globals.error') . ' ' . trans('globals.induced_error')]])->withInput();
    }
    $rules = $this->rulesByTypes($request, true);
    $order = OrderDetail::where('game_id', $id)->join('orders', 'order_details.order_id', '=', 'orders.id')->first();
    if ($order) {
      unset($rules['name']);
      unset($rules['category_id']);
      unset($rules['condition']);
    }
    $v = Validator::make($request->all(), $rules);
    if ($v->fails()) {
      return redirect()->back()
        ->withErrors($v->errors())->withInput();
    }
    $features = $this->validateFeatures($request->all());
    if (!is_string($features)) {
      return redirect()->back()
        ->withErrors($features)->withInput();
    }
    $game = Game::find($id);
    if (\Auth::id() != $game->user_id) {
      return redirect('games/' . $game->user_id)->withErrors(['feature_images' => [trans('globals.not_access')]]);
    }
    if (!$order) {
      $game->name = $request->input('name');
      $game->category_id = $request->input('category_id');
      $game->condition = $request->input('condition');
    }
    $game->status = $request->input('status');
    $game->description = $request->input('description');
    $game->bar_code = $request->input('bar_code');
    $game->brand = $request->input('brand');
    $game->price = $request->input('price');
    $game->features = $features;
    if ($request->input('type') == 'item') {
      $game->stock = $request->input('stock');
      $game->low_stock = $request->input('low_stock');
      if ($request->input('stock') > 0) {
        $game->status = $request->input('status');
      } else {
        $game->status = 0;
      }
    } else {
      $game->status = $request->input('status');
    }
    $game->save();
    $message = '';
    if ($request->input('type') != 'item') {
      switch ($request->input('type')) {
        case 'key':
          if ($request->input('key') != '' && Storage::disk('local')->exists('key_code' . $request->input('key'))) {
            $contents = Storage::disk('local')->get('key_code' . $request->input('key'));
            $contents = explode("\n", rtrim($contents));
            $warning = false;
            $len = 0;
            foreach ($contents as $row) {
              $virtualGame = new virtualGame();
              $virtualGame->game_id = $game->id;
              $virtualGame->key = $row;
              $virtualGame->status = 'open';
              $virtualGame->save();
              if ($len == 0) {
                $len = strlen(rtrim($row));
              } elseif (strlen(rtrim($row)) != $len) {
                $warning = true;
              }
            }
            $stock = count(VirtualGame::where('game_id', $game->id)->where('status', 'open')->get()->toArray());
            $game->stock = $stock;
            if ($stock == 0) {
              $game->status = 0;
            }
            $game->save();
            $message = ' ' . trans('game.controller.review_keys');
            if ($warning) {
              $message .= ' ' . trans('game.controller.may_invalid_keys');
            }
            Storage::disk('local')->deleteDirectory('key_code/' . \Auth::id());
          }
          break;
        case 'software':

          break;
        case 'software_key':

          break;
        case 'gift_card':

          break;
      }
    }
    Session::flash('message', trans('game.controller.saved_successfully') . $message);

    return redirect('games/' . $game->id);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   *
   * @return Response
   */
  public function destroy($id)
  {
    $game = Game::find($id);
    if (\Auth::id() != $game->user_id) {
      return redirect('games/' . $game->user_id)->withErrors(['feature_images' => [trans('globals.not_access')]]);
    }
    $game->status = 0;
    $game->save();
    Session::flash('message', trans('game.controller.saved_successfully'));

    return redirect('games/' . $game->id);
  }

  /**
   * Change status a Game.
   *
   * @param int $id
   *
   * @return Response
   */
  public function changeStatus($id)
  {
    $game = Game::select('id', 'user_id', 'features', 'status', 'type')->find($id);
    if (\Auth::id() != $game->user_id) {
      return redirect('games/' . $game->user_id)->withErrors(['feature_images' => [trans('globals.not_access')]]);
    }
    $game->status = ($game->status) ? 0 : 1;
    $game->save();
    Session::flash('message', trans('game.controller.saved_successfully'));

    return redirect('games/' . $game->id);
  }

  /**
   *   upload image file.
   *
   * @param Resquest     file to upload
   *
   * @return string
   */
  public function upload(Request $request)
  {
    $v = Validator::make($request->all(), ['file' => 'image']);
    if ($v->fails()) {
      return $v->errors()->toJson();
    }

    return File::section('game_img')->upload($request->file('file'));
  }

  /**
   *   delete image file.
   *
   * @param Resquest     file to upload
   *
   * @return string
   */
  public function deleteImg(Request $request)
  {
    return File::deleteFile($request->get('file'));
  }

  /**
   *   upload the keys file in txt format.
   *
   * @param Resquest     file to upload
   *
   * @return string
   */
  public function upload_key(Request $request)
  {
    $v = Validator::make($request->all(), ['file' => 'mimes:txt']);
    if ($v->fails()) {
      return $v->errors()->toJson();
    }

    return File::section('game_key')->upload($request->file('file'));
  }

  /**
   *   upload the software file in txt format.
   *
   * @param Resquest     file to upload
   *
   * @return string
   */
  public function upload_software(Request $request)
  {
    $v = Validator::make($request->all(), ['file' => 'mimes:zip,rar']);
    if ($v->fails()) {
      return $v->errors()->toJson();
    }

    return 'README.7z';

    return File::section('game_software')->upload($request->file('file'));
  }

  /**
   *   dowload file txt example.
   *
   * @param Resquest     file to upload
   *
   * @return string
   */
  public function downloadExample()
  {
    return response()->download(storage_path() . '/files/key_code/example_keys.txt', 'file.txt');
  }

  /**
   *   validate game feature, as specified in the table of game details.
   *
   * @param  [array] $data all inputs
   *
   * @return [string|array]
   */
  private function validateFeatures($data)
  {
    $features = GameDetail::all()->toArray();
    $features_rules = [];
    $message_rules = [];
    foreach ($features as $row) {
      if ($row['status'] == 'active') {
        if ($row['max_num_values'] * 1 == 1) {
          $features_rules['feature_' . $row['indexByName']] = $row['validationRulesArray'][$row['indexByName'] . '_1'] ? $row['validationRulesArray'][$row['indexByName'] . '_1'] : '';
          $message_rules = array_merge($message_rules, $this->validationMessagesFeatures($row['validationRulesArray'][$row['indexByName'] . '_1'], 'feature_' . $row['indexByName'], $row['upperName']));
        } else {
          for ($i = 1; $i <= ($row['max_num_values'] * 1); $i++) {
            $features_rules['feature_' . $row['indexByName'] . '_' . $i] = $row['validationRulesArray'][$row['indexByName'] . '_' . $i] ? $row['validationRulesArray'][$row['indexByName'] . '_' . $i] : '';
            $message_rules = array_merge($message_rules, $this->validationMessagesFeatures($row['validationRulesArray'][$row['indexByName'] . '_' . $i], 'feature_' . $row['indexByName'] . '_' . $i, $row['upperName']));
          }
        }
      }
    }
    // dd($data, $features_rules,$message_rules);
    $v = Validator::make($data, $features_rules, $message_rules);
    if ($v->fails()) {
      $array = [];
      $errors = $v->errors()->toArray();
      foreach ($errors as $error) {
        foreach ($error as $row) {
          $array[] = $row;
        }
      }

      return array_unique($array);
    }
    $array = [];
    foreach ($features as $row) {
      $values = [];
      if (($row['max_num_values'] * 1) !== 1) {
        for ($i = 1; $i <= ($row['max_num_values'] * 1); $i++) {
          if (!$data['feature_' . $row['indexByName'] . '_' . $i]) {
            continue;
          }
          if ($row['help_message'] != '' && (strpos('video image document', $row['input_type']) === false)) {
            $message = '';
            if (isset($row['helpMessageArray']['general'])) {
              $message = $row['helpMessageArray']['general'];
            } elseif (isset($row['helpMessageArray']['specific'])) {
              $message = $row['helpMessageArray']['specific'][$row['indexByName'] . '_' . $i];
            } elseif (isset($row['helpMessageArray']['general_selection'])) {
              $message = $data['help_msg_' . $row['indexByName']];
            } elseif (isset($row['helpMessageArray']['specific_selection'])) {
              $message = $data['help_msg_' . $row['indexByName'] . '_' . $i];
            }
            $values[] = [$data['feature_' . $row['indexByName'] . '_' . $i], $message];
          } else {
            $values[] = $data['feature_' . $row['indexByName'] . '_' . $i];
          }
        }
      } else {
        if (isset($data['feature_' . $row['indexByName']]) && !$data['feature_' . $row['indexByName']]) {
          continue;
        }
        if ($row['help_message'] != '' && (strpos('video image document', $row['input_type']) === false)) {
          $message = '';
          if (isset($row['helpMessageArray']['general'])) {
            $message = $row['helpMessageArray']['general'];
          } elseif (isset($row['helpMessageArray']['general_selection'])) {
            $message = $data['help_msg_' . $row['indexByName']];
          }
          $values = [$data['feature_' . $row['indexByName']], $message];
        } else {
          $values = isset($data['feature_' . $row['indexByName']]) ? $data['feature_' . $row['indexByName']] : '';
        }
      }
      if ($values) {
        $array[$row['indexByName']] = $values;
      }
    }

    return json_encode($array);
  }

  /**
   * create the error message by taking the name feature, and validation rules.
   *
   * @param  [string] $rules Validation rules
   * @param  [string] $index name feature without spaces
   * @param  [string] $name  name feature
   *
   * @return [array] $return
   */
  private function validationMessagesFeatures($rules, $index, $name)
  {
    $return = [];
    if (strpos($rules, '|in') !== false) {
      $return[$index . '.in'] = $name . ' ' . trans('features.is_invalid');
    }
    if (strpos($rules, '|numeric') !== false) {
      $return[$index . '.numeric'] = $name . ' ' . trans('features.only_allows_numbers');
      if (strpos($rules, '|min') !== false) {
        $num = explode('min:', $rules);
        $num = explode('|', $num[1]);
        $return[$index . '.min'] = $name . ' ' . str_replace('*N*', $num[0], trans('features.minimum_number'));
      } elseif (strpos($rules, '|max') !== false) {
        $num = explode('max:', $rules);
        $num = explode('|', $num[1]);
        $return[$index . '.max'] = $name . ' ' . str_replace('*N*', $num[0], trans('features.maximum_number_2'));
      } elseif (strpos($rules, '|between') !== false) {
        $num = explode('between:', $rules);
        $num = explode('|', $num[1]);
        $num = explode(',', $num[0]);
        $return[$index . '.between'] = $name . ' ' . str_replace(['*N1*', '*N2*'], $num, trans('features.between_n_and_n'));
      }
    } else {
      if (strpos($rules, '|alpha') !== false) {
        $return[$index . '.alpha'] = $name . ' ' . trans('features.only_allows_letters');
      }
      if (strpos($rules, '|min') !== false) {
        $num = explode('min:', $rules);
        $num = explode('|', $num[1]);
        $return[$index . '.min'] = $name . ' ' . str_replace('*N*', $num[0], trans('features.minimum_characters'));
      } elseif (strpos($rules, '|max') !== false) {
        $num = explode('max:', $rules);
        $num = explode('|', $num[1]);
        $return[$index . '.max'] = $name . ' ' . str_replace('*N*', $num[0], trans('features.maximum_characters'));
      } elseif (strpos($rules, '|between') !== false) {
        $num = explode('between:', $rules);
        $num = explode('|', $num[1]);
        $num = explode(',', $num[0]);
        $return[$index . '.between'] = $name . ' ' . str_replace(['*N1*', '*N2*'], $num, trans('features.between_n_and_n_characters'));
      }
    }
    if (strpos($rules, 'required_without_all') !== false) {
      $return[$index . '.required_without_all'] = $name . ' ' . trans('features.one_is_required');
    } elseif (strpos($rules, 'required_with') !== false) {
      $return[$index . '.required_with'] = $name . ' ' . trans('features.is_required');
    } elseif (strpos($rules, 'required') !== false) {
      $return[$index . '.required'] = $name . ' ' . trans('features.is_required');
    }

    return $return;
  }

  private function rulesByTypes($request, $edit = false)
  {
    $rules = $this->form_rules;
    switch ($request->input('type')) {
      case 'item':
        unset($rules['amount']);
        unset($rules['key']);
        unset($rules['software']);
        unset($rules['key_software']);
        unset($rules['software_key']);
        break;
      case 'key':
        unset($rules['amount']);
        unset($rules['stock']);
        unset($rules['low_stock']);
        unset($rules['software']);
        unset($rules['key_software']);
        unset($rules['software_key']);
        if ($edit) {
          unset($rules['key']);
        }
        break;
      case 'software':
        unset($rules['amount']);
        unset($rules['stock']);
        unset($rules['low_stock']);
        unset($rules['key']);
        unset($rules['key_software']);
        unset($rules['software_key']);
        if ($edit) {
          unset($rules['software']);
        }
        break;
      case 'software_key':
        unset($rules['amount']);
        unset($rules['stock']);
        unset($rules['low_stock']);
        unset($rules['key']);
        unset($rules['software']);
        if ($edit) {
          unset($rules['key_software']);
          unset($rules['software_key']);
        }
        break;
      case 'gift_card':
        unset($rules['stock']);
        unset($rules['low_stock']);
        unset($rules['key']);
        unset($rules['software']);
        unset($rules['key_software']);
        unset($rules['software_key']);
        break;
      default:
        return redirect()->back()
          ->withErrors(['induced_error' => [trans('globals.error') . ' ' . trans('globals.induced_error')]])->withInput();
        break;
    }

    return $rules;
  }

  /**
   * Get the category id from tags array.
   *
   * @param  [array] $tags, tags list to find out their categories
   *
   * @return [array] $categories, category id array
   */
  public static function getTagsCategories($tags = [])
  {
    $categories = Game::
    like('tags', $tags)
      ->groupBy('category_id')
      ->free()
      ->get(['category_id']);

    return $categories;
  }

  /**
   * Increase the game counters.
   *
   * @param [object] $game is the object which contain the game evaluated
   * @param [array]  $data    is the method config that has all the info requeried (table field, amount to be added)
   * @param [string] $wrapper is the games session array position key.
   */
  public static function setCounters($game = null, $data = [], $wrapper = '')
  {
    if (\Auth::user() && $game != '' && count($data) > 0) {
      $_array = Session::get('games.' . $wrapper); //games already evaluated
      if (count($_array) == 0 || !in_array($game->id, $_array)) {
        //looked up to make sure the game is not in $wrapper array already
        foreach ($data as $key => $value) {
          if ($key != '' && $data[$key] != '') {
            $game->$key = $game->$key + intval($data[$key]);
            $game->save();
          }
        }
        Session::push('games.' . $wrapper, $game->id); //build game list to not increase a game more than one time per day
        Session::save();
      }
    }
  }

  /**
   * To get the games suggestion, taking in account either the preference key, such as
   * (game_viewed, game_purchased, game_shared, game_categories, my_searches), or all of them.
   *
   * @param  [array] $data, which is the suggest configuration
   *
   * @return [array] $games, which will contain all the suggestion for the user either in session or suggested
   */
  public static function getSuggestions($data)
  {
    $options = [
      'user_id' => '',
      'preferences_key' => '',
      'limit' => '4',
      'category' => '',
      'select' => '*', //array with items to select
    ];

    $suggest_listed = Session::get('suggest-listed');

    if (count($suggest_listed)) {
      $suggest_listed = array_unique($suggest_listed);
    } else {
      $suggest_listed = [];
    }

    $data = $data + $options;
    $diff = 0;
    $gamesHelper = new GamesHelper();
    $needle['tags'] = [];

    // the suggestions based on one id (one game)
    if (is_int($data['preferences_key'])) {
      $data['preferences_key'] = [$data['preferences_key']];
    }

    // the suggestions based on a list of games
    if (is_array($data['preferences_key'])) {
      foreach ($data['preferences_key'] as $id) {
        $needleAux = Game::select('tags', 'name')
          ->where('id', $id)
          ->free()
          ->orderBy('rate_count', 'desc')
          ->first()
          ->toArray();

        //extraction of tags and name of games
        $needle['tags'] = array_merge($needle['tags'],
          explode(',', trim($needleAux['tags'])),
          explode(' ', trim($needleAux['name'])));
      }
    } else {
      $needle = UserController::getPreferences($data['preferences_key']); //getting the user preferences
    }

    if (count($needle['tags']) > 0) {
      //by preferences
      if ($data['preferences_key'] == 'game_categories') {
        //look up by categories. If we want to get a specific category, we have to add "category" to data array
        \DB::enableQueryLog();
        $games[0] = Game::select($data['select'])
          ->free()
          ->whereNotIn('id', $suggest_listed)
          ->inCategories('category_id', $needle['tags'])
          ->orderBy('rate_count', 'desc')
          ->take($data['limit'])
          ->get()
          ->toArray();
      } else {
        //look up by games tags and name
        $games[0] = Game::select($data['select'])
          ->free()
          ->whereNotIn('id', $suggest_listed)
          ->like(['tags', 'name'], $needle['tags'])
          ->orderBy('rate_count', 'desc')
          ->take($data['limit'])
          ->get()
          ->toArray();
      }
    }

    $diff = $data['limit'] - (isset($games[0]) ? count($games[0]) : 0); //limit control

    //if we get suggestion results, we save those id
    if (isset($games[0])) {
      $gamesHelper->setToHaystack($games[0]);
    }

    //by rate
    if ($diff > 0 && $diff <= $data['limit']) {
      $games[1] = Game::select($data['select'])
        ->where($gamesHelper->getFieldToSuggestions($data['preferences_key']), '>', '0')
        ->whereNotIn('id', $suggest_listed)
        ->free()
        ->orderBy($gamesHelper->getFieldToSuggestions($data['preferences_key']), 'DESC')
        ->take($diff)
        ->get()
        ->toArray();

      $diff = $diff - count($games[1]); //limit control
    }

    //if we get suggestion results, we save those id
    if (isset($games[1])) {
      $gamesHelper->setToHaystack($games[1]);
    }

    //by rand
    if ($diff > 0 && $diff <= $data['limit']) {
      $games[2] = Game::select($data['select'])
        ->free()
        ->whereNotIn('id', $suggest_listed)
        ->orderByRaw('RAND()')
        ->take($diff)
        ->get()
        ->toArray();
    }

    //if we get suggestion results, we save those id
    if (isset($games[2])) {
      $gamesHelper->setToHaystack($games[2]);
    }

    //making one array to return
    $array = [];
    $games = array_values($games);
    for ($i = 0; $i < count($games); $i++) {
      if (count($games[$i]) > 0) {
        $array = array_merge($array, $games[$i]);
      }
    }

    return $array;
  }

  /**
   * To get a existing category id from games.
   *
   * @return [integer] $category_id [game category id field]
   */
  public static function getRandCategoryId()
  {
    $game = Game::select(['category_id'])
      ->free()
      ->orderByRaw('RAND()')
      ->take(1)
      ->first();

    return ($game) ? $game->id : 1;
  }

  /**
   * [Search games in auto complete fields].
   *
   * @param Request $request [Request laravel]
   *
   * @return [type] [json array]
   */
  public function searchAll(Request $request)
  {
    $crit = $request->get('crit');
    $suggest = $request->get('suggest');
    $group = $request->get('group');
    $response['games'] = ['results' => null, 'suggestions' => null];

    $crit = str_replace(' ', '%', trim($crit));
    $crit = str_replace('%%', '%', $crit);

    if ($crit != '') {
      if ($suggest) {
        $response['games']['categories'] = Category::select('id', 'name')
          ->search($crit, null, true)
          ->actives()
          ->where('type', 'store')
          ->orderBy('name')
          ->take(3)
          ->get();
      }

      $response['games']['results'] = Game::where(function ($query) use ($crit) {
        $query->where('name', 'like', '%' . $crit . '%')
          ->orWhere('description', 'like', '%' . $crit . '%');
      })
        ->select('id', 'name', 'games_group')
        ->actives()
        ->free()
        ->orderBy('rate_val', 'desc');
      if ($group) {
        $response['games']['results']->where(function ($query) use ($group) {
          $query->where('games_group', '<>', $group)
            ->orWhereNull('games_group');
        })->where('id', '<>', $group);
      }

      $response['games']['results'] = $response['games']['results']->take(5)->get();

      $deep = '';
      if ($suggest) {
        $crit = str_replace('%', '', $crit);
        for ($i = 0; $i < strlen($crit); $i++) {
          $deep .= ' ' . $crit[$i];
        }
      }

      if (!$response['games']['results']->count() && strlen($crit) > 2) {
        $response['games']['results'] = Game::select('id', 'name', 'games_group')
          ->search($deep, null, true)
          ->actives()
          ->free()
          ->orderBy('rate_val', 'desc');
        if ($group) {
          $response['games']['results']->where(function ($query) use ($group) {
            $query->where('games_group', '<>', $group)
              ->orWhereNull('games_group');
          })->where('id', '<>', $group);
        }

        $response['games']['results'] = $response['games']['results']->take(5)->get();
      }

      if ($suggest) {
        $response['games']['suggestions'] = self::getSuggestions([
          'user_id' => \Auth::id(),
          'preferences_key' => 'my_searches',
          'limit' => 3,
          'select' => ['id', 'name', 'features'],
        ]);

        if (!$response['games']['categories']->count() && strlen($crit) > 2) {
          $response['games']['categories'] = Category::select('id', 'name')
            ->search($deep, null, true)
            ->actives()
            ->where('type', 'store')
            ->orderBy('name')
            ->take(3)
            ->get();
        }
      }
    }

    $response['games']['categories_title'] = trans('globals.suggested_categories');
    $response['games']['suggestions_title'] = trans('globals.suggested_games');
    $response['games']['results_title'] = trans('globals.searchResults');

    if ($request->wantsJson()) {
      return json_encode($response);
    } else {
      if (env('APP_DEBUG', false)) {
        dd($response);
      }
    }
  }

  /**
   * This method is able to return the higher rate game list, everything will depends of $point parameter.
   *
   * @param  [integer] $point [it is the rate evaluates point, which allows get the games list required]
   * @param  [integer] $limit [num of records to be returned]
   * @param  [boolean] $tags  [it sees if we want to return a game list or a game tags list]
   *
   * @return [array or laravel collection] $_tags, $games [returning either games tags array or games collection]
   */
  public static function getTopRated($point = '5', $limit = 5, $tags = false)
  {
    if ($tags == true) {
      $games = Game::select(['id', 'tags', 'rate_count', 'rate_val'])
        ->WhereNotNull('tags')
        ->free()
        ->orderBy('rate_count', 'desc')
        ->orderBy('rate_val', 'desc')
        ->take($limit)
        ->get();

      $_tags = [];
      $games->each(function ($prod) use (&$_tags) {
        $array = explode(',', $prod->tags);
        foreach ($array as $value) {
          if (trim($value) != '') {
            $_tags[] = trim($value);
          }
        }
      });

      return array_unique($_tags, SORT_STRING);
    } else {
      $games = Game::select(['id', 'name', 'description', 'features', 'price', 'type', 'stock'])
        ->free()
        ->orderBy('rate_count', 'desc')
        ->orderBy('rate_val', 'desc')
        ->take($limit)
        ->get();

      return $games;
    }
  }

  /**
   * getFeatures
   * Allows consulting games features. It can return either a required feature or a full array.
   *
   * @param array $data function setting
   *
   * @return [type] feature or a full array
   */
  public function getFeatures($data = [])
  {
    $options = [
      'game' => [],
      'game_id' => '',
      'feature' => '',
    ];

    $features = [];
    $data = $data + $options;

    if (count($data['game']) > 0) {
      $features = $data['game']->features;
    } elseif (trim($data['game_id']) != '') {
      $game = Game::find($data['game_id']);
      $features = $game->features;
    }

    return trim($data['feature']) != '' ? $features[$data['feature']] : $features;
  }
}
