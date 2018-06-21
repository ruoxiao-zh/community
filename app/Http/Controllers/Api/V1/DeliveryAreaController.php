<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/6/4
 * Time: 3:41 PM
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\DeliveryArea;

class DeliveryAreaController extends Controller
{
    public function index(Request $request)
    {
        $obj = DeliveryArea::where('is_delete', 0)->select('id', 'delivery_area', 'phone', 'address', 'create_at')->paginate(15)->setPath(env('APP_URL') . '/public/api/v1/community/goods');

        return $obj->toJson();
    }

    public function show(Request $request)
    {
        $id = (int)$request->input('id');
        if ( !$id) {
            return jsonHelper(101, '必要的参数不能为空: id');
        }

        $obj = DeliveryArea::find($id);
        if ( !$obj) {
            return jsonHelper(102, '传入的id不存在');
        }

        return jsonHelper(0, '获取成功', $obj);
    }

    public function store(Request $request)
    {
        $delivery_area = $request->input('delivery_area');
        if ( !$delivery_area) {
            return jsonHelper(102, '必要的参数不能为空: delivery_area');
        } else {
            $is_insert = DeliveryArea::where('is_delete', 0)->where('delivery_area', $delivery_area)->first();
            if ($is_insert) {
                return jsonHelper(103, '配送点已添加, 请勿重复添加');
            }
        }
        $obj = new DeliveryArea();
        $obj->delivery_area = $delivery_area;

        $phone = $request->input('phone');
        if ( !$phone) {
            return jsonHelper(104, '必要的参数不能为空: phone');
        } else if ( !preg_match("/^1[345678]\d{9}$/", $phone)) {
            return jsonHelper(105, '手机号格式不正确');
        }
        $obj->phone = $phone;

        $address = $request->input('address');
        if ( !$phone) {
            return jsonHelper(106, '必要的参数不能为空: address');
        }
        $obj->address = $address;

        // 数据入库
        try {
            $obj->save();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {
            return jsonHelper(101, '操作失败');
        }
    }

    public function update(Request $request)
    {
        $id = $request->input('id');
        if ( !$id) {
            return jsonHelper(101, '必要的参数不能为空: id');
        }

        $obj = DeliveryArea::find($id);
        if ( !$obj) {
            return jsonHelper(102, '传入的id不存在');
        }

        $delivery_area = $request->input('delivery_area');
        if ( !$delivery_area) {
            return jsonHelper(102, '必要的参数不能为空: delivery_area');
        }
        $obj->delivery_area = $delivery_area;

        $phone = $request->input('phone');
        if ( !$phone) {
            return jsonHelper(104, '必要的参数不能为空: phone');
        } else if ( !preg_match("/^1[345678]\d{9}$/", $phone)) {
            return jsonHelper(105, '手机号格式不正确');
        }
        $obj->phone = $phone;

        $address = $request->input('address');
        if ( !$phone) {
            return jsonHelper(106, '必要的参数不能为空: address');
        }
        $obj->address = $address;

        // 数据入库
        try {
            $obj->save();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
        if ( !$id) {
            return jsonHelper(101, '必要的参数不能为空: id');
        }

        $obj = DeliveryArea::find($id);
        if ( !$obj) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        $obj->update([
            'is_delete' => 1
        ]);

        return jsonHelper(0, '删除成功');
    }
}