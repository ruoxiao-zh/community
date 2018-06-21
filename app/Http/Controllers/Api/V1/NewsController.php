<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/30
 * Time: 2:43 PM
 */

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Community\Tables\CommunityGroupOrder;
use Illuminate\Http\Request;
use App\Common\SaveImage;
use Illuminate\Support\Facades\DB;
use App\Common\CurlHelper;
use App\Http\Controllers\Community\Tables\CommunityPayConfig;
use App\Http\Controllers\Community\Tables\CommunityGroup;

class NewsController extends BaseController
{
    /**
     * 团购预定模板消息
     *
     * @param Request $request
     * @return mixed|string
     */
    public function groupReserve(Request $request)
    {
        // 判断用户是否登录失败
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期;后台未登陆');
        }

        $pay_config = CommunityPayConfig::first();
        if ( !$pay_config) {
            return jsonHelper(102, '请填写支付信息, 支付信息不完全无法发送模板消息');
        }

        // 微信用户的 openID
        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(103, '必要的参数不能为空: openid');
        }

        // 团购 ID
        $group_id = (int)$request->input('group_id');
        if ( !$group_id) {
            return jsonHelper(104, '必要的参数不能为空: group_id');
        } else {
            $group = CommunityGroup::find($group_id);
        }

        // form_id
        $form_id = $request->input('form_id');
        if ( !$form_id) {
            return jsonHelper(105, '必要的参数不能为空: form_id');
        }

        // 消息模板 ID
        $template_id = 'NpVbtjDvgeEJHw95hCxqV7aPA5NL4FfI32inPU64Yi4';

        $message = [
            'touser'      => $openid,
            'template_id' => $template_id,
            'page'        => 'pages/goods/goods?id=' . $group->id . '&openid=' . $openid,
            'form_id'     => $form_id,
            'data'        => [
                'keyword1' => [
                    'value' => '石门团购惠: ' . $group->theme,
                    'color' => '#173177',
                ],
                'keyword2' => [
                    'value' => date('Y-m-d H:i:s', $group->begin_time),
                    'color' => '#173177',
                ],
            ],
        ];

        // 获取 access_token
        $access_token = $this->getAccessToken($pay_config);

        // 发送模板消息 API
        $api = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $access_token;
        $curl = new CurlHelper();
        $result = $curl->postCurl($api, json_encode($message, JSON_UNESCAPED_UNICODE));

        return $result;
    }

    /**
     * 订单支付成功模板消息
     *
     * @param $openid
     * @param $group_id
     * @param $form_id
     * @param $pay_config
     * @return mixed
     */
    public function paySuccess($openid, $group_id, $form_id, $pay_config)
    {
        // 团购 ID
        $group = CommunityGroup::find($group_id);
        $order = CommunityGroupOrder::where('prepay_id', $form_id)->first();

        // 消息模板 ID
        $template_id = 'FM9CRAgcyC3PaD4mxeAI1gJMI0hzcyPDYaoIZ91vR8I';

        $message = [
            'touser'      => $openid,
            'template_id' => $template_id,
            // 'page'        => 'pages/goods/goods?id=' . $group->id . '&openid=' . $openid,
            'form_id'     => $form_id,
            'data'        => [
                'keyword1' => [
                    'value' => $order->create_at,
                    'color' => '#173177',
                ],
                'keyword2' => [
                    'value' => $group->theme,
                    'color' => '#173177',
                ],
                'keyword3' => [
                    'value' => $order->order_number,
                    'color' => '#173177',
                ],
                'keyword4' => [
                    'value' => $order->update_at,
                    'color' => '#173177',
                ],
                'keyword5' => [
                    'value' => $order->total_money . '元',
                    'color' => '#173177',
                ],
                'keyword6' => [
                    'value' => $order->update_at,
                    'color' => '#173177',
                ],
            ],
        ];

        // 获取 access_token
        $access_token = $this->getAccessToken($pay_config);

        // 发送模板消息 API
        $api = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $access_token;
        $curl = new CurlHelper();
        $result = $curl->postCurl($api, json_encode($message, JSON_UNESCAPED_UNICODE));
        file_put_contents('/data/wwwroot/default/ailetutourism/app/Http/Controllers/Community/b.txt', $result);

        return $result;
    }

    /**
     * 获取 access_token
     *
     * @param $pay_config
     * @return mixed
     */
    private function getAccessToken($pay_config)
    {
        $api = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $pay_config->appid . '&secret=' . $pay_config->appsecret;
        $curl = new CurlHelper();
        $result = $curl->getCurl($api);

        return $result['access_token'];
    }
}