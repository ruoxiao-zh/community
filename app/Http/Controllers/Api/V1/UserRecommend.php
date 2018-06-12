<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/5/7
 * Time: 11:34 AM
 */

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Community\Tables\CommunityUserRecommend;
use Illuminate\Http\Request;

class UserRecommend extends BaseController
{
    /**
     * 添加
     *
     * @param Request                $request
     * @param CommunityUserRecommend $communityUserRecommend
     * @return string
     */
    public function add(Request $request, CommunityUserRecommend $communityUserRecommend)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $username = $request->input('username');
        if ( !$username) {
            return jsonHelper(102, '必要的参数不能为空: username');
        }
        $communityUserRecommend->username = $username;

        $phone = $request->input('phone');
        if ( !$phone) {
            return jsonHelper(103, '必要的参数不能为空: phone');
        } else if ( !preg_match("/^1[345678]\d{9}$/", $phone)) {
            return jsonHelper(104, '手机号格式不正确');
        }
        $communityUserRecommend->phone = $phone;

        $info = $request->input('info');
        if ( !$info) {
            return jsonHelper(105, '必要的参数不能为空: info');
        }
        $communityUserRecommend->info = $info;
        $communityUserRecommend->community_small_id = $this->smallid;

        try {
            $communityUserRecommend->save();

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
    public function index(Request $request, CommunityUserRecommend $communityUserRecommend)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $obj = $communityUserRecommend::where('community_small_id', $this->smallid)->select('id', 'username', 'phone', 'info')->orderBy('create_at', 'desc')->paginate(15)->setPath('https://www.ailetugo.com/ailetutourism/public/community/user-recommend');

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
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $id = (int)$request->input('id');
        if (!$id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $obj = CommunityUserRecommend::find($id);
        if (!$obj) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        if ($obj->community_small_id != $this->smallid) {
            return jsonHelper(104, '权限不足, 不能删除');
        }

        $obj->delete();

        return jsonHelper(0, '删除成功');
    }
}