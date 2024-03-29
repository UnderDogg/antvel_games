<?php

namespace app;

/*
 * Antvel - Games Model
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */

use App\Category;
use App\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Game extends Model
{
    use SearchableTrait;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'games';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'parent_id',
        'name',
        'status',
    ];

    /**
     * Columns for search rules.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'name'        => 100,
            'tags'        => 255,
        ],
    ];

    // protected $casts = ['features'];

//	protected $appends = ['last_comments'];

    protected $hidden = ['details', 'created_at'];

    public function categories()
    {
        return $this->belongsTo('App\Category', 'category_id');
    }

    public function group()
    {
        return $this->hasMany('App\Game', 'games_group', 'games_group');
    }

    public static function create(array $attr = [])
    {
        if (isset($attr['features']) && is_array($attr['features'])) {
            $attr['features'] = json_encode($attr['features']);
        }

        return parent::create($attr);
    }


    public function scopeActives($query)
    {
        return $query->where('status', 1)
                     ->where('stock', '>', 0);
    }

    public function scopeInactives($query)
    {
        return $query->where('status', 0);
    }

    public function scopeRefine($query, $filters)
    {
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'category':
                    $children = \Cache::remember('progeny_of_'.$value, 15, function () use ($value) {
                        Category::progeny($value, $children, ['id']);

                        return $children;
                    });
                    $children[] = ['id' => $value * 1];
                    $query->whereIn('category_id', $children);
                break;

                case 'conditions':
                    $query->where('condition', 'LIKE', $value);
                break;

                case 'brands':
                   $query->where('brand', 'LIKE', $value);
                break;

                case 'min':
                case 'max':
                    $min = array_key_exists('min', $filters) ? (trim($filters['min']) != '' ? $filters['min'] : '') : '';
                    $max = array_key_exists('max', $filters) ? (trim($filters['max']) != '' ? $filters['max'] : '') : '';

                    if ($min != '' && $max != '') {
                        $query->whereBetween('price', [$min, $max]);
                    } elseif ($min == '' && $max != '') {
                        $query->where('price', '<=', $max);
                    } elseif ($min != '' && $max == '') {
                        $query->where('price', '>=', $min);
                    }
                break;

                default:
                    if ($key != 'category_name' && $key != 'search' && $key != 'page') {

                        //changing url encoded character by the real ones
                        $value = urldecode($value);

                        //applying filter to json field
                        $query->whereRaw("features LIKE '%\"".$key.'":%"%'.str_replace('/', '%', $value)."%\"%'");
                    }
                break;
            }
        }
    }

    public function scopeName($query, $input)
    {
        if (trim($input) != '') {
            $query->where('name', 'LIKE', "%$input%");
        }
    }

    public function scopeType($query, $input)
    {
        if (count($input) > 0) {
            $query->whereIn('category_id', $input);
        }
    }

    public function getStatusLettersAttribute()
    {
        if ($this->status == 0) {
            return trans('globals.inactive');
        }

        return trans('globals.active');
    }

    /**
     * Games tags filter.
     *
     * @param [object] $query, which is the laravel builder
     * @param [string] $attr,  which is used to evaluate the where In (categories requiered)
     * @param [array]  $data,  which is the info to be evaluated
     */
    public function scopeLike($query, $attr = [], $search = [])
    {
        //if the search contains a string of words, we split them in an array
        if (!is_array($search)) {
            $search = explode(' ', preg_replace('/\s+/', ' ', trim($search)));
        }

        $needle = '(';

        if (!is_array($attr)) {
            $attr = [$attr];
        }
        foreach ($attr as $key) {
            foreach ($search as $word) {
                if (trim($word) != '') {
                    $needle .= $key." like '%".$word."%' or ";
                }
            }
        }

        $needle = rtrim($needle, ' or ').')';

        $query->whereRaw($needle);

        return $query;
    }

    /**
     * categories filter.
     *
     * @param [object] $query, which is the laravel builder
     * @param [string] $attr,  which is used to evaluate the where In (categories requiered)
     * @param [array]  $data,  which is the info to be evaluated
     */
    public function scopeInCategories($query, $attr, $data = [])
    {
        if (count($data) > 0 && empty($data['category'])) {
            $query->whereIn($attr, $data);
        } elseif (isset($data['category'])) {
            $query->where($attr, '=', $data['category']);
        }

        return $query;
    }

}
