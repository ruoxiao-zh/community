<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 12/12/2017
 * Time: 11:16 AM
 */

namespace App\Http\Controllers\Community;

use Illuminate\Http\Request;
use App\Common\SaveImage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
// 数据库模型
use App\Models\Company;

class CompanyController extends Controller
{
    /**
     * 信息展示
     *
     * @param Request $request
     * @return string
     */
    public function index(Request $request)
    {
        $obj = Company::select('id', 'company_name', 'company_img', 'company_phone', 'company_address', 'company_info', 'company_copyright', 'delivery_type', 'create_at')->get();

        return jsonHelper(0, '获取成功', $obj);
    }

    /**
     * 上传图片
     *
     * @param Request $request
     * @return string
     */
    public function uploadImg(Request $request)
    {
        $logo = SaveImage::getSaveImageUrl('images/community/company', 'company_img', '', false);
        if ( !$logo) {
            return jsonHelper(101, '必要的参数不能为空: company_img');
        } else {
            return jsonHelper(0, '上传成功', $logo);
        }
    }

    /**
     * 数据创建
     *
     * @param Request $request
     * @return string
     */
    public function store(Request $request)
    {
        $obj = new Company();

        $company_name = $request->input('company_name');
        if (empty($company_name)) {
            return jsonHelper(102, '必要的参数不能为空: company_name');
        }
        $obj->company_name = $company_name;

        $company_img = $request->input('company_img');
        if (!$company_img) {
            return jsonHelper(103, '必要的参数不能为空: company_img');
        }
        $obj->company_img = $company_img;

        $company_phone = $request->input('company_phone');
        if (empty($company_phone)) {
            return jsonHelper(104, '必要的参数不能为空: company_phone');
        }
        $obj->company_phone = $company_phone;

        $company_address = $request->input('company_address');
        if (empty($company_address)) {
            return jsonHelper(105, '必要的参数不能为空: company_address');
        }
        $obj->company_address = $company_address;

        $company_info = $request->input('company_info');
        if ( !$company_info) {
            return jsonHelper(106, '必要的参数不能为空: company_info');
        }
        $obj->company_info = $company_info;

        $company_copyright = $request->input('company_copyright');
        if ( !$company_copyright) {
            return jsonHelper(107, '必要的参数不能为空: company_copyright');
        }
        $obj->company_copyright = $company_copyright;

        $delivery_type = (int)$request->input('delivery_type');
        if (!$delivery_type) {
            return jsonHelper(108, '必要的参数不能为空: delivery_type');
        }
        $obj->delivery_type = $delivery_type;

        // 数据入库
        try {
            $obj->save();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {
            return jsonHelper(101, '操作失败');
        }
    }

    /**
     * 数据更新
     *
     * @param Request $request
     * @return string
     */
    public function update(Request $request)
    {
        $id = $request->input('id');
        if ( !$id) {
            return jsonHelper(101, '必要的参数不能为空: id');
        }

        $obj = Company::find($id);
        if ( !$obj) {
            return jsonHelper(102, '传入的id不存在');
        }

        $company_name = $request->input('company_name');
        if (empty($company_name)) {
            return jsonHelper(103, '必要的参数不能为空: company_name');
        }
        $obj->company_name = $company_name;

        $company_img = $request->input('company_img');
        if ( !empty($company_name)) {
            $obj->company_img = $company_img;
        }

        $company_phone = $request->input('company_phone');
        if (empty($company_phone)) {
            return jsonHelper(104, '必要的参数不能为空: company_phone');
        }
        $obj->company_phone = $company_phone;

        $company_address = $request->input('company_address');
        if (empty($company_address)) {
            return jsonHelper(105, '必要的参数不能为空: company_address');
        }
        $obj->company_address = $company_address;

        $company_info = $request->input('company_info');
        if ( !$company_info) {
            return jsonHelper(106, '必要的参数不能为空: company_info');
        }
        $obj->company_info = $company_info;

        $company_copyright = $request->input('company_copyright');
        if ( !$company_copyright) {
            return jsonHelper(107, '必要的参数不能为空: company_copyright');
        }
        $obj->company_copyright = $company_copyright;

        $delivery_type = (int)$request->input('delivery_type');
        if (!$delivery_type) {
            return jsonHelper(108, '必要的参数不能为空: delivery_type');
        }
        $obj->delivery_type = $delivery_type;

        // 数据入库
        try {
            $obj->save();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {
            return jsonHelper(101, '操作失败');
        }
    }

    /**
     * 数据删除
     *
     * @param Request $request
     * @return string
     */
    public function destroy(Request $request)
    {
        $obj = Company::first();
        if ( !$obj) {
            return jsonHelper(103, '暂无任何公司信息');
        }

        $obj->delete();

        return jsonHelper(0, '删除成功');
    }
}
