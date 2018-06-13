<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 30/03/2018
 * Time: 11:08 AM
 */

namespace App\Http\Controllers\Community;

use Illuminate\Http\Request;
use App\Common\SaveImage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
// 数据库模型
use App\Models\PayConfig;
use App\Http\Controllers\Api\V1\WxUploadHandler;

class PayConfigController extends Controller
{
    /**
     * 获取信息
     *
     * @param Request $request
     * @return string
     */
    public function index(Request $request)
    {
        $obj = PayConfig::select('id', 'pay_name', 'appid', 'appsecret', 'mch_id', 'key', 'apiclient_cert', 'apiclient_key', 'payment_agreement')->get();

        return jsonHelper(0, '获取成功', $obj);
    }

    /**
     * 添加或修改
     *
     * @param Request $request
     * @return string
     */
    public function createOrUpdate(Request $request)
    {
        $obj = PayConfig::first();
        if ( !$obj) {
            $obj = new PayConfig();
        }

        // 收款方姓名
        $pay_name = $request->input('pay_name');
        if ( !$pay_name) {
            return jsonHelper(102, '必要的参数不能为空: pay_name');
        }
        $obj->pay_name = $pay_name;

        // appid
        $appid = $request->input('appid');
        if ( !$appid) {
            return jsonHelper(103, '必要的参数不能为空: appid');
        }
        $obj->appid = $appid;

        // 商户号
        $mch_id = $request->input('mch_id');
        if ( !$mch_id) {
            return jsonHelper(104, '必要的参数不能为空: mch_id');
        }
        $obj->mch_id = $mch_id;

        // appsecret
        $appsecret = $request->input('appsecret');
        if ( !$appsecret) {
            return jsonHelper(105, '必要的参数不能为空: appsecret');
        }
        $obj->appsecret = $appsecret;

        $key = $request->input('key');
        if ( !$key) {
            return jsonHelper(106, '必要的参数不能为空: key');
        }
        $obj->key = $key;

        // 私钥文件
        $apiclient_cert = $request->input('apiclient_cert');
        if (!$apiclient_cert) {
            return jsonHelper(107, '必要的参数不能为空: apiclient_cert');
        }
        $obj->apiclient_cert = $apiclient_cert;

        // 公钥文件
        $apiclient_key = $request->input('apiclient_key');
        if (!$apiclient_key) {
            return jsonHelper(108, '必要的参数不能为空: apiclient_key');
        }
        $obj->apiclient_key = $apiclient_key;

        $payment_agreement = $request->input('payment_agreement');
        if (!$payment_agreement) {
            return jsonHelper(109, '必要的参数不能为空: payment_agreement');
        }
        $obj->payment_agreement = $payment_agreement;

        try {
            $obj->save();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {
            return jsonHelper(101, '操作失败');
        }
    }

    /**
     * 上传 Cert
     *
     * @param Request                                         $request
     * @param \App\Http\Controllers\Community\WxUploadHandler $upload
     * @return string
     */
    public function payCertUpload(Request $request, WxUploadHandler $upload)
    {
        // 私钥文件
        $apiclient_cert = $request->file('apiclient_cert');
        if ($apiclient_cert) {
            $path = $upload->save($request->file('apiclient_cert'), 'cert', time());
            if ($path) {
                return jsonHelper(0, '获取成功', $path);
            }
        } else {
            return jsonHelper(107, '必要的参数不能为空: apiclient_cert');
        }
    }

    /**
     * 上传 key
     *
     * @param Request                                         $request
     * @param \App\Http\Controllers\Community\WxUploadHandler $upload
     * @return string
     */
    public function payKeyUpload(Request $request, WxUploadHandler $upload)
    {
        // 公钥文件
        $apiclient_key = $request->file('apiclient_key');
        if ($apiclient_key) {
            $path = $upload->save($request->file('apiclient_key'), 'key', time());
            if ($path) {
                return jsonHelper(0, '获取成功', $path);
            }
        } else {
            return jsonHelper(108, '必要的参数不能为空: apiclient_key');
        }
    }

    /**
     * 删除
     *
     * @param Request $request
     * @return string
     */
    public function destroy(Request $request)
    {
        $obj = PayConfig::first();
        if ( !$obj) {
            return jsonHelper(103, '暂无任何支付配置信息');
        }

        $obj->delete();

        return jsonHelper(0, '删除成功');
    }
}