<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/5/7
 * Time: 11:34 AM
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserRecommend;

class UserRecommendController extends Controller
{
    /**
     * 添加
     *
     * @param Request                $request
     * @param CommunityUserRecommend $communityUserRecommend
     * @return string
     */
    public function add(Request $request, UserRecommend $userRecommend)
    {
        $username = $request->input('username');
        if ( !$username) {
            return jsonHelper(102, '必要的参数不能为空: username');
        }
        $userRecommend->username = $username;

        $phone = $request->input('phone');
        if ( !$phone) {
            return jsonHelper(103, '必要的参数不能为空: phone');
        } else if ( !preg_match("/^1[345678]\d{9}$/", $phone)) {
            return jsonHelper(104, '手机号格式不正确');
        }
        $userRecommend->phone = $phone;

        $info = $request->input('info');
        if ( !$info) {
            return jsonHelper(105, '必要的参数不能为空: info');
        }
        $userRecommend->info = $info;

        try {
            $userRecommend->save();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {
            return jsonHelper(101, '操作失败');
        }
    }

    /**
     * 列表
     *
     * @param Request                $request
     * @param CommunityUserRecommend $communityUserRecommend
     * @return string
     */
    public function index(Request $request, UserRecommend $userRecommend)
    {
        $obj = $userRecommend::select('id', 'username', 'phone', 'info')->orderBy('create_at', 'desc')->paginate(15)->setPath('https://www.ailetugo.com/ailetutourism/public/community/user-recommend');

        return $obj->toJson();
    }

    /**
     * 删除
     *
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function delete(Request $request)
    {
        $id = (int)$request->input('id');
        if (!$id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $obj = UserRecommend::find($id);
        if (!$obj) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        $obj->delete();

        return jsonHelper(0, '删除成功');
    }
}