<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 30/03/2018
 * Time: 11:37 AM
 */

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Community\Tables\CommunityCompany;
use Illuminate\Http\Request;
use App\Common\SaveImage;
use Illuminate\Support\Facades\DB;
// 数据库模型
use App\Http\Controllers\Community\Tables\CommunityDelivery;

class DeliveryController extends BaseController
{
    /**
     * 信息展示
     *
     * @param Request $request
     * @return string
     */
    public function index(Request $request)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $obj = CommunityDelivery::where('community_small_id', $this->smallid)->where('is_delete', 0)->select('id', 'deliver_name', 'create_at')->get();

        return jsonHelper(0, '获取成功', $obj);
    }

    /**
     * 更新或修改配送区域
     *
     * @param Request $request
     * @return string
     */
    public function createOrUpdate(Request $request)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $id = $request->input('id');
        if ( !$id) {
            $obj = new CommunityDelivery();
        } else {
            $obj = CommunityDelivery::find($id);
        }

        $obj->community_small_id = $this->smallid;

        $deliver_name = $request->input('deliver_name');
        if ( !$deliver_name) {
            return jsonHelper(102, '必要的参数不能为空: deliver_name');
        }
        $obj->deliver_name = $deliver_name;

        // 数据入库
        try {
            $obj->save();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {
            return jsonHelper(101, '操作失败');
        }
    }

    /**
     * 单个详情
     *
     * @param Request $request
     * @return string
     */
    public function show(Request $request)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $id = $request->input('id');
        if ( !$id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $obj = CommunityDelivery::where('id', $id)->select('id', 'deliver_name')->first();
        if (empty($obj)) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        return jsonHelper(0, '获取成功', $obj);
    }

    /**
     * 删除数据
     *
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function destroy(Request $request)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $id = $request->input('id');
        if ( !$id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $obj = CommunityDelivery::find($id);
        if ( !$obj) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        if ($obj->community_small_id != $this->smallid) {
            return jsonHelper(104, '权限不足，不能删除');
        }

        $obj->update([
            'is_delete' => 1
        ]);

        return jsonHelper(0, '删除成功');
    }

    /**
     * 添加配送城市
     *
     * @param Request $request
     * @return string
     */
    public function addOrEditCity(Request $request)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $delivery_city = $request->input('delivery_city');
        if ( !$delivery_city) {
            return jsonHelper(102, '必要的参数不能为空: delivery_city');
        }

        $city_number = (int)$request->input('city_number');
        if ($city_number === '') {
            return jsonHelper(103, '必要的参数不能为空: city_number');
        }

        $delivery_province = $request->input('delivery_province');
        if ( !$delivery_province) {
            return jsonHelper(104, '必要的参数不能为空: delivery_province');
        }

        $province_number = (int)$request->input('province_number');
        if ($province_number === '') {
            return jsonHelper(105, '必要的参数不能为空: province_number');
        }

        $company = CommunityCompany::where('community_small_id', $this->smallid)->first();
        if ( !$company) {
            return jsonHelper(106, '公司信息尚未填写, 请完善公司信息');
        }
        CommunityCompany::where('community_small_id', $this->smallid)->update([
            'delivery_city'     => $delivery_city,
            'city_number'       => $city_number,
            'delivery_province' => $delivery_province,
            'province_number'   => $province_number
        ]);

        return jsonHelper(0, '操作成功');
    }

    /**
     * 搜索
     *
     * @param Request $request
     * @return string
     */
    public function search(Request $request)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $delivery_name = $request->input('delivery_name');
        if ( !$delivery_name) {
            return jsonHelper(102, '必要的参数不能为空: delivery_name');
        }

        $res = CommunityDelivery::where('community_small_id', $this->smallid)->where('deliver_name', 'like', $delivery_name . '%')->get();

        return jsonHelper(0, '获取成功', $res);
    }

    /**
     * 获取默认配送区域地址
     *
     * @param Request $request
     * @return string
     */
    public function defaultArea(Request $request)
    {
        // 检查小程序用户权限
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $area = CommunityCompany::where('community_small_id', $this->smallid)->select('delivery_province', 'province_number', 'delivery_city', 'city_number')->first();

        return jsonHelper(0, '获取成功', $area);
    }
}
