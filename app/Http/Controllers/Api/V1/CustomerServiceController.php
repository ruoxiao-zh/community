<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/30
 * Time: 2:06 PM
 */

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Community\Tables\CommunityPayConfig;
use App\Http\Controllers\Community\Tables\CommunityUser;
use Illuminate\Http\Request;
use App\Common\SaveImage;
use Illuminate\Support\Facades\DB;
use App\Common\CurlHelper;
// 数据库模型
use App\Http\Controllers\Community\Tables\CommunityCustomerService;

class CustomerServiceController extends BaseController
{
    /**
     * 验证消息的确来自微信服务器
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        if ($this->checkSignature($request->signature, $request->timestamp, $request->nonce)) {
            echo $request->echostr;
        } else {
            echo 'error';
        }
    }

    /**
     * 检验 signature
     *
     * @param $signature
     * @param $timestamp
     * @param $nonce
     * @return bool
     */
    private function checkSignature($signature, $timestamp, $nonce)
    {
        $token = 'ailetugokefu';
        $tmpArr = [
            $token,
            $timestamp,
            $nonce
        ];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发送客服消息
     *
     * @return string
     */
    public function sendMessage()
    {
        // 发送消息用户
        $from_user_name_arr = json_decode($GLOBALS["HTTP_RAW_POST_DATA"], true);
        $from_user_name = $from_user_name_arr['FromUserName'];
        $community_small_id = CommunityUser::where('openid', $from_user_name)->first();
        if ($community_small_id) {
            $community_small_id = $community_small_id->community_small_id;
        }

        // 用户信息
        $customer_service = CommunityCustomerService::where('community_small_id', $community_small_id)->orderBy(\DB::raw('RAND()'))->take(1)->get();
        $small_user_info = CommunityPayConfig::where('community_small_id', $community_small_id)->first();
        if ($small_user_info) {
            $access_token = $this->getAccessToken($small_user_info);
        } else {
            return jsonHelper(110, '请填写支付配置信息!支付信息不完整无法添加客服');
        }

        if ($customer_service) {
            $userinfo = $customer_service->toArray();
            $qrcode = $userinfo[0]['qrcode'];

            // 发送消息数组, 请求接口时格式化为 json
            $message = [
                'touser'  => $from_user_name,
                'msgtype' => 'link',
                'link'    => [
                    'title'       => '欢迎您的到来，竭诚为您服务！',
                    'description' => '点击本条消息加我微信, 随时找我聊天!',
                    'url'         => 'https://www.ailetugo.com/ailetutourism/public/community/get-qrcode?customer_service=' . $userinfo[0]['id'],
                    'thumb_url'   => 'https://www.ailetugo.com' . $qrcode,
                ]
            ];

            $api = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $access_token;
            $curl = new CurlHelper();
            $result = $curl->postCurl($api, json_encode($message, JSON_UNESCAPED_UNICODE));
            file_put_contents('/data/wwwroot/default/ailetutourism/app/Http/Controllers/Community/c.txt', $from_user_name);

            return $result;
        }
    }

    /**
     * 获取 access_token
     *
     * @param $small_user_info
     * @return mixed
     */
    private function getAccessToken($small_user_info)
    {
        $appid = $small_user_info->appid;
        $appsecret = $small_user_info->appsecret;
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
        $curl = new CurlHelper();
        $result = $curl->getCurl($url);

        return $result['access_token'];
    }

    /**
     * 获取用户二维码
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getUserQRCode(Request $request)
    {
        $customer_service_id = $request->input('customer_service');
        if ($customer_service_id) {
            $customer_service_info = CommunityCustomerService::find($customer_service_id);
            if ($customer_service_info) {
                return view('community.qrcode', compact('customer_service_info'));
            }
            return view('community.noqrcode');
        }
    }

    /**
     * 客服列表
     *
     * @param Request $request
     * @return string
     */
    public function show(Request $request)
    {
        // 判断用户是否登录失败
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $obj = CommunityCustomerService::where('community_small_id', $this->smallid)->select('id', 'nickname', 'qrcode', 'create_at')->get();

        return jsonHelper(0, '获取成功', $obj);
    }

    /**
     * 客服详情
     *
     * @param Request $request
     * @return string
     */
    public function detail(Request $request)
    {
        // 判断用户是否登录失败
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $id = (int)$request->input('id');
        if ( !$id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $obj = CommunityCustomerService::where('community_small_id', $this->smallid)->where('id', $id)->select('id', 'nickname', 'qrcode', 'create_at')->first();
        if ( !$obj) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        return jsonHelper(0, '获取成功', $obj);
    }

    /**
     * 创建或修改二维码
     *
     * @param Request $request
     * @return string
     */
    public function createOrUpdate(Request $request)
    {
        // 判断用户是否登录失败
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $customer_service_id = $request->input('customer_service_id');

        $logo = SaveImage::getSaveImageUrl('images/community/qrcode', 'qrcode', '', false);
        if ($customer_service_id) {
            $obj = CommunityCustomerService::find($customer_service_id);
            if ( !$obj) {
                return jsonHelper(106, '传入的 customer_service_id 不存在');
            }

            if ($obj->community_small_id != $this->smallid) {
                return jsonHelper(105, '权限不足，不能修改');
            }

            if ($logo) {
                $oldlogo = $obj->qrcode;
                $oldlogo = str_replace('/ailetutourism', '../', $oldlogo);
                SaveImage::deletefile($oldlogo);
                $obj->qrcode = $logo;
            }
        } else {
            $obj = new CommunityCustomerService();
            $obj->community_small_id = $this->smallid;
            if ( !$logo) {
                return jsonHelper(102, '必要的参数不能为空: qrcode');
            }
            $obj->qrcode = $logo;
        }

        $nickname = $request->input('nickname');
        if ( !$nickname) {
            return jsonHelper(104, '必要的参数不能为空: nickname');
        }
        $obj->nickname = $nickname;

        try {
            $obj->save();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {
            return jsonHelper(101, '操作失败');
        }
    }

    /**
     * 删除客服二维码
     *
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function destroy(Request $request)
    {
        // 判断用户是否登录失败
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期');
        }

        $id = (int)$request->input('id');
        if (!$id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $obj = CommunityCustomerService::find($id);
        if ( !$obj) {
            return jsonHelper(103, '客服信息不存在');
        }

        if ($obj->community_small_id != $this->smallid) {
            return jsonHelper(104, '权限不足，不能删除');
        }

        $obj->delete();

        return jsonHelper(0, '删除成功');
    }
}