<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/6/4
 * Time: 4:20 PM
 */

namespace App\Http\Controllers\Community;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// 数据库模型
use App\Models\DeliveryArea;
use App\Models\UserDelivery;

class UserDeliveryController extends Controller
{
    public function addUserDelivery(Request $request)
    {
        // 微信用户的 openid
        $openid = $request->input('openid');
        if (!$openid) {
            return jsonHelper(102, '必要的参数不能为空: openid');
        }

        $delivery_area_id = (int)$request->input('delivery_area_id');
        if (!$delivery_area_id) {
            return jsonHelper(103, '必要的参数不能为空: delivery_area_id');
        }

        $username = $request->input('username');
        if (!$username) {
            return jsonHelper(104, '必要的参数不能为空: username');
        }

        $phone = $request->input('phone');
        if (!$phone) {
            return jsonHelper(105, '必要的参数不能为空: phone');
        } else if (!preg_match("/^1[345678]\d{9}$/", $phone)) {
            return jsonHelper(106, '手机号格式不正确');
        }

        $result = UserDelivery::where([
            'openid' => $openid,
            'delivery_id' => $delivery_area_id,
            'username' => $username,
            'phone' => $phone,
        ])->first();

        if ($result) {
            return jsonHelper(0, '添加成功');
        } else {
            UserDelivery::create([
                'openid' => $openid,
                'delivery_id' => $delivery_area_id,
                'username' => $username,
                'phone' => $phone,
            ]);
        }

        return jsonHelper(0, '操作成功');
    }

    public function userDeliveryList(Request $request)
    {
        // 微信用户的 openid
        $openid = $request->input('openid');
        if (!$openid) {
            return jsonHelper(102, '必要的参数不能为空: openid');
        }

        $result = UserDelivery::where('openid', $openid)->select('id', 'openid', 'delivery_id', 'username', 'phone', 'create_at')->orderBy('create_at', 'desc')->get();
        if ($result) {
            $result = $result->toArray();
            foreach ($result as $key => $value) {
                $delivery_area = DeliveryArea::find($value['delivery_id']);

                $result[$key]['delivery_area'] = $delivery_area->delivery_area;
                $result[$key]['delivery_phone'] = $delivery_area->phone;
                $result[$key]['delivery_address'] = $delivery_area->address;
            }
        }

        return jsonHelper(0, '获取成功', $result);
    }

    public function userDeliveryDel(Request $request)
    {
        $id = (int)$request->input('id');
        if (!$id) {
            return jsonHelper(101, '必要的参数不能为空: id');
        }

        $obj = UserDelivery::find($id);
        if (!$obj) {
            return jsonHelper(102, '参数异常: id');
        }

        $obj->delete();

        return jsonHelper(0, '删除成功');
    }
}