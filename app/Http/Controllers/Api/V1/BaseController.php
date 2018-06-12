<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 30/03/2018
 * Time: 9:46 AM
 */

namespace App\Http\Controllers\Community;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Table2\Factor\SmallUser;

class BaseController extends Controller
{
    protected $appid;
    protected $smallid;
    protected $smallobj;

    // 登陆初始化
    protected function getSmallid($request)
    {
        if ($request->hasCookie('smallid')) {
            $smallid = $request->cookie('smallid');
            $small = SmallUser::find($smallid);
            if (count($small)) {
                $this->smallid = $smallid;
                $this->appid = $small->appid;
                $this->smallobj = $small;

                return true;
            }
        } else {
            if (isset($_SERVER['HTTP_REFERER'])) {
                $appid = substr($_SERVER['HTTP_REFERER'], 26, 18);
                if ($appid) {
                    // 根据appid获取用户id
                    $small = SmallUser::where('appid', $appid)->first();
                    if (count($small)) {
                        $this->smallid = $small->id;
                        $this->appid = $appid;
                        $this->smallobj = $small;
                        // 判断小程序是否过期
                        if ($small->end_time < time()) {
                            return false;
                        }

                        return true;
                    }
                }
            }
        }
        // 用于非登陆测试
//        $smallid = '89fe8cd0a11a3d15ef9651db3d4fbe33';
//        $small = SmallUser::find($smallid);
//        if (count($small)) {
//            $this->smallid = $smallid;
//            $this->appid = $small->appid;
//            $this->smallobj = $small;
//
//            return true;
//        }

        return false;
    }
}