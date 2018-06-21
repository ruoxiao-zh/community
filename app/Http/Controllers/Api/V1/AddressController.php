<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/4/21
 * Time: 11:28 AM
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\UserAddress;
use App\Http\Controllers\Controller;

class AddressController extends Controller
{
    /**
     * 添加或修改收货地址
     *
     * @param Request $request
     * @return string
     */
    public function addAddress(Request $request)
    {
        // 微信用户的 openid
        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(102, '必要的参数不能为空: openid');
        }

        // 姓名
        $username = $request->input('username');
        if ( !$username) {
            return jsonHelper(103, '必要的参数不能为空: username');
        }

        // 手机号
        $phone = $request->input('phone');
        if ( !$phone) {
            return jsonHelper(104, '必要的参数不能为空: phone');
        } else if ( !preg_match("/^1[345678]\d{9}$/", $phone)) {
            return jsonHelper(105, '手机号格式错误');
        }

        $province = $request->input('province');
        if ( !$province) {
            return jsonHelper(106, '必要的参数不能为空: province');
        }

        $city = $request->input('city');
        if ( !$city) {
            return jsonHelper(107, '必要的参数不能为空: city');
        }

        $area = $request->input('area');
        if ( !$area) {
            return jsonHelper(108, '必要的参数不能为空: area');
        }

        $housing_estate = $request->input('housing_estate');
        if ( !$housing_estate) {
            return jsonHelper(109, '必要的参数不能为空: housing_estate');
        }

        $detail = $request->input('detail');
        if ( !$detail) {
            return jsonHelper(110, '必要的参数不能为空: detail');
        }

        $result = UserAddress::where([
            'openid'             => $openid,
            'username'           => $username,
            'phone'              => $phone,
            'province'           => $province,
            'city'               => $city,
            'area'               => $area,
            'housing_estate'     => $housing_estate,
            'detail'             => $detail
        ])->first();

        if ($result) {
            return jsonHelper(0, '操作成功');
        } else {
            UserAddress::create([
                'openid'             => $openid,
                'username'           => $username,
                'phone'              => $phone,
                'province'           => $province,
                'city'               => $city,
                'area'               => $area,
                'housing_estate'     => $housing_estate,
                'detail'             => $detail
            ]);

            return jsonHelper(0, '操作成功');
        }
    }

    /**
     * 地址列表
     *
     * @param Request $request
     * @return string
     */
    public function addressList(Request $request)
    {
        // 微信用户的 openid
        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(102, '必要的参数不能为空: openid');
        }

        $result = UserAddress::where('openid', $openid)->select('id', 'openid', 'username', 'phone', 'province', 'city', 'area', 'housing_estate', 'detail', 'create_at')->orderBy('create_at', 'desc')->get();

        return jsonHelper(0, '获取成功', $result);
    }

    /**
     * 地址删除
     *
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function addressDel(Request $request)
    {
        $id = (int)$request->input('id');
        if ( !$id) {
            return jsonHelper(101, '必要的参数不能为空: id');
        }

        $obj = UserAddress::find($id);
        if ( !$obj) {
            return jsonHelper(102, '参数异常: id');
        }

        $obj->delete();

        return jsonHelper(0, '删除成功');
    }
}