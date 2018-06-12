<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/31
 * Time: 9:11 AM
 */

namespace App\Http\Controllers\Community;

use Illuminate\Http\Request;
use App\Common\SaveImage;
use Illuminate\Support\Facades\DB;
// 数据库模型
use App\Http\Controllers\Community\Tables\CommunityDelivery;
use App\Http\Controllers\Community\Tables\CommunityCheckCodeManager;
use App\Http\Controllers\Community\Tables\CommunityUser;

class CheckCodeController extends BaseController
{
    /**
     * 获取配送区域
     *
     * @param Request $request
     * @return string
     */
    public function getDelivery(Request $request)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $delivery = CommunityDelivery::where('community_small_id', $this->smallid)->where('is_delete', 0)->select('id', 'deliver_name', 'create_at')->get();

        return jsonHelper(0, '获取成功', $delivery);
    }

    /**
     * 搜索用户
     *
     * @param Request $request
     * @return string
     */
    public function searchCheckCodeManager(Request $request)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        // 核销员名称
        $check_code_manager_name = $request->input('nickname');
        if ( !$check_code_manager_name) {
            return jsonHelper(102, '必要的参数不能为空: nickname');
        }

        $users = CommunityUser::where('community_small_id', $this->smallid)->where('nickname', 'like', $check_code_manager_name . '%')->select('id', 'openid', 'nickname', 'avatar')->get();

        return jsonHelper(0, '获取成功', $users);
    }

    /**
     * 添加或修改指定区域核销员
     *
     * @param Request $request
     * @return string
     */
    public function checkCodeManager(Request $request)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $token = $request->input('token');
        if ( !$token) {
            return jsonHelper(102, '必要的参数不能为空: token');
        } else {
            $is_insert_delivery = CommunityCheckCodeManager::where('token', $token)->first();
            if ($is_insert_delivery) {
                return jsonHelper(103, '该用户已经绑定为此区域核销员, 请勿重复添加');
            }
        }

        $id = $request->input('id');
        if ($id) {
            $obj = CommunityCheckCodeManager::find($id);
        } else {
            $obj = new CommunityCheckCodeManager();
            $obj->community_small_id = $this->smallid;
        }
        $obj->token = $token;

        // 核销员的 openID
        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(104, '必要的参数不能为空: openid');
        }
        $obj->openid = $openid;

        // 配送级别
        $level = (int)$request->input('level');
        if ($level == 1) {
            $delivery_id = null;
            $obj->level = 1;
//            $is_insert_level_euql_1 = CommunityCheckCodeManager::where('community_small_id', $this->smallid)->where('level', 1)->first();
//            if ($is_insert_level_euql_1) {
//                $is_insert_level_euql_1->save();

//            return jsonHelper(0, '操作成功');
//            }
        } else {
            // 配送区域 ID
            $delivery_id = (int)$request->input('delivery_id');
            if ( !$delivery_id) {
                return jsonHelper(105, '必要的参数不能为空: delivery_id');
            }
            $obj->level = 0;
            $obj->delivery_id = $delivery_id;
        }

        if ( !$id) {
            if (CommunityCheckCodeManager::where('community_small_id', $this->smallid)->where('delivery_id', $delivery_id)->where('openid', $openid)->where('is_delete', 0)->first()) {
                return jsonHelper(103, '该用户已经绑定为此区域核销员, 请勿重复添加');
            } else if (CommunityCheckCodeManager::where('community_small_id', $this->smallid)->where('delivery_id', $delivery_id)->where('openid', $openid)->where('is_delete', 1)->first()) {
                CommunityCheckCodeManager::where('community_small_id', $this->smallid)->where('delivery_id', $delivery_id)->where('openid', $openid)->update([
                    'is_delete' => 0
                ]);

                return jsonHelper(0, '操作成功');
            }
        }

        try {
            $obj->save();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {

            return jsonHelper(101, '操作失败');
        }
    }

    /**
     * 核销员列表
     *
     * @param Request $request
     * @return string
     */
    public function getCheckCodeManager(Request $request)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $res = CommunityCheckCodeManager::where('community_small_id', $this->smallid)->where('is_delete', 0)->select('id', 'level', 'delivery_id', 'openid', 'create_at')->orderBy('delivery_id')->paginate(15)->setPath('https://www.ailetugo.com/ailetutourism/public/community/check-code-manager');
        if ($res) {
            foreach ($res as $key => $value) {
                $userinfo = CommunityUser::where('community_small_id', $this->smallid)->where('openid', $value->openid)->select('id', 'nickname')->first();
                if ($userinfo) {
                    $res[$key]['userinfo'] = $userinfo->nickname;
                }
                $delivery_info = CommunityDelivery::where('id', $value->delivery_id)->select('id', 'deliver_name')->first();
                if ($delivery_info) {
                    $res[$key]['delivery_info'] = $delivery_info->deliver_name;
                }
            }
        }

        return $res->toJson();
    }

    /**
     * 单个核销员信息详情
     *
     * @param Request $request
     * @return string
     */
    public function checkCodeManagerShow(Request $request)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $id = (int)$request->input('id');
        if ( !$id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $result = CommunityCheckCodeManager::where('id', $id)->select('id', 'level', 'delivery_id', 'openid', 'create_at')->first();
        if ($result) {
            $userinfo = CommunityUser::where('community_small_id', $this->smallid)->where('openid', $result->openid)->select('openid', 'nickname')->first();
            if ($userinfo) {
                $result->userinfo = $userinfo;
            }

            $delivery_info = CommunityDelivery::where('id', $result->delivery_id)->select('id', 'deliver_name')->first();
            if ($delivery_info) {
                $result->delivery_info = $delivery_info;
            }

            return jsonHelper(0, '获取成功', $result);
        }

        return jsonHelper(103, '传入的参数异常: id');
    }

    /**
     * 删除指定区域核销员
     *
     * @param Request $request
     * @return string
     */
    public function deleteCheckCodeManager(Request $request)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        // 核销员与配送区域关系 id
        $id = (int)$request->input('id');
        if ( !$id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $obj = CommunityCheckCodeManager::find($id);
        if ( !$obj) {
            return jsonHelper(103, '暂无任何核销员信息');
        }

        if ($obj->community_small_id != $this->smallid) {
            return jsonHelper(104, '权限不足，不能删除');
        }

        $obj->update([
            'is_delete' => 1
        ]);

        return jsonHelper(0, '删除成功');
    }
}
