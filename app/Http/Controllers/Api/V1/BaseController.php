<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/6/21
 * Time: 11:57 AM
 */

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class BaseController extends Controller
{
    public function __construct()
    {
        if ( !Cache::get('admin')) {
            echo '请登录, 未登录无法访问';die;
        }
    }
}