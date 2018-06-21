<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/4/3
 * Time: 2:20 PM
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Common\SaveImage;
use Illuminate\Support\Facades\DB;
// 数据库模型
use App\Models\Commander;
use App\Models\CommanderOrder;
use App\Models\PayConfig;
use App\Models\GroupOrder;
use App\Models\GroupOrderDetail;
use App\Models\Delivery;
use App\Models\GroupDetail;

// 微信支付 SDK
require __DIR__ . '/../../../../SDK/WxPay/WxPay.Api.php';

class PayController extends BaseController
{
    /**
     * 支付检测
     *
     * @param Request $request
     *
     * @return string
     */
    public function getPreOrder(Request $request)
    {
        $order_id = $request->input('order_id');
        if ( !$order_id) {
            return jsonHelper(102, '必要的参数不能为空: order_id');
        }

        // 检测有没有支付过
        $order_status = GroupOrder::where('id', $order_id)->first();
        if ($order_status) {
            if ($order_status->order_status != 0) {
                return jsonHelper(103, '订单已经支付');
            }
        } else {
            return jsonHelper(104, '订单不存在');
        }

        // 支付配置
        $pay_config = PayConfig::first();
        if ($pay_config) {
            $pay_config = $pay_config->toArray();
        } else {
            return jsonHelper(105, '尚未填写支付配置信息, 支付配置信息不完整无法付款');
        }

        return $this->makeWxPreOrder($order_status, $pay_config);
    }

    /**
     * 生成微信预订单
     *
     * @param $obj
     *
     * @return mixed
     */
    private function makeWxPreOrder($obj, $pay_config)
    {
        $wxOrderData = new \WxPayUnifiedOrder();
        // 订单号
        $wxOrderData->SetOut_trade_no($obj->order_number);
        // 交易类型
        $wxOrderData->SetTrade_type('JSAPI');
        // 设置支付总金额, 默认已分为单位
        $wxOrderData->SetTotal_fee($obj->total_money * 100);
        // 本次交易描述
        $wxOrderData->SetBody($pay_config['pay_name']);
        // 设置微信用户的 openid, 唯一身份标识
        $wxOrderData->SetOpenid($obj->openid);
        // 微信支付的 key
//        $wxOrderData->SetAttach($pay_config['community_small_id']);
        // 接受微信的回调结果
        $wxOrderData->SetNotify_url(env('APP_URL') . '/public/api/v1/pay-notify');

        return $this->getPaySignature($wxOrderData, $obj, $pay_config);
    }

    /**
     * 向微信请求订单号并生成签名
     *
     * @param $wxOrderData
     * @param $obj
     * @param $pay_config
     *
     * @return array
     * @throws \WxPayException
     */
    private function getPaySignature($wxOrderData, $obj, $pay_config)
    {
        // 统一下单
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData, $pay_config);
        // 失败时不会返回result_code
        if ($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS') {
            \Log::info('获取预支付订单失败');
        }
        $this->recordPreOrder($wxOrder, $obj);
        $signature = $this->sign($wxOrder, $obj, $pay_config);

        return $signature;
    }

    /**
     * 更新 prepay_id
     *
     * @param $wxOrder
     * @param $obj
     */
    private function recordPreOrder($wxOrder, $obj)
    {
        // 必须是 update，每次用户取消支付后再次对同一订单支付，prepay_id是不同的
        GroupOrder::where([
            'openid' => $obj->openid,
            'id'     => $obj->id
        ])->update([
            'prepay_id' => $wxOrder['prepay_id']
        ]);
    }

    /**
     * 签名
     *
     * @param $wxOrder
     *
     * @return array
     */
    private function sign($wxOrder, $obj, $pay_config)
    {
        $jsApiPayData = new \WxPayJsApiPay();
        // 设置小程序 appid
        $jsApiPayData->SetAppid($pay_config['appid']);
        // 设置时间戳
        $jsApiPayData->SetTimeStamp((string)time());
        // 设置随机字符串
        $rand = md5(time() . mt_rand(0, 1000));
        $jsApiPayData->SetNonceStr($rand);
        // 设置统一下单生成的 prepay_id
        $jsApiPayData->SetPackage('prepay_id=' . $wxOrder['prepay_id']);
        // 设置加密方式
        $jsApiPayData->SetSignType('md5');
        // 生成签名
        $sign = $jsApiPayData->MakeSign($pay_config);
        // 获取设置的值
        $rawValues = $jsApiPayData->GetValues();
        // 赋值
        $rawValues['paySign'] = $sign;
        // appId 没有用, 不用返回
        unset($rawValues['appId']);
        // 微信用户的 openid
        $rawValues['openid'] = $obj['openid'];
        // 订单 id
        $rawValues['orderid'] = $obj['id'];

        return $rawValues;
    }

    //*************************************************************

    /**
     * 接受微信支付回调
     */
    public function receiveNotify()
    {
        $xml = file_get_contents('php://input');
        $data = $this->xmlToArray($xml);

        // 支付配置
        $pay_config = PayConfig::first();
        if ($pay_config) {
            $pay_config = $pay_config->toArray();
        } else {
            return jsonHelper(105, '尚未填写支付配置信息, 支付配置信息不完整无法付款');
        }

        // 1. 检测库存量 (商品库存量超卖的概率比较小, 但是有这种可能)
        // 2. 更新订单状态(订单表的 'order_status' 字段)
        // 3. 减少库存
        // 成功, 返回微信成功处理的消息, 否则, 我们需要返回没有成功处理
        $notify = new UserWxCallBackNotify();
        $notify->Handle($pay_config);
    }

    /**
     * xml 转换为数组
     *
     * @param $xml
     *
     * @return mixed
     */
    public function xmlToArray($xml)
    {
        // 禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);

        return $val;
    }

    //*************************************************************

    /**
     * 前台申请退款
     *
     * @param Request $request
     *
     * @return string
     */
    public function orderMoneyRefund(Request $request)
    {
        $order_id = $request->input('order_id');
        if ( !$order_id) {
            return jsonHelper(102, '必要的参数不能为空: order_id');
        }

        $token = $request->input('token');
        if ( !$token) {
            return jsonHelper(103, '必要的参数不能为空: token');
        }

        // 检测有没有支付过
        $order_status = GroupOrder::where('id', $order_id)->first();
        if ($order_status) {
            if ($order_status->token != $token) {
                return jsonHelper(0, '申请退款已提交,请不要重复点击');
            }

            if ($order_status->order_status != 1) {
                return jsonHelper(103, '订单尚未支付');
            }

            // 订单已支付
            if ($order_status->order_status == 1) {
                GroupOrder::where('id', $order_id)->update([
                    'order_status' => 6,
                    'token'        => md5(time() . $order_id)
                ]);

                return jsonHelper(0, '申请退款成功');
            }
        } else {
            return jsonHelper(104, '订单不存在');
        }
    }

    /**
     * 微信退款
     *
     * @param Request $request
     *
     * @return string
     */
    public function moneyRefund(Request $request)
    {
        $order_id = $request->input('order_id');
        if ( !$order_id) {
            return jsonHelper(102, '必要的参数不能为空: order_id');
        }

        // 检测有没有支付过
        $order_status = GroupOrder::where('id', $order_id)->first();
        if ($order_status) {
            if ($order_status->order_status != 6) {
                return jsonHelper(103, '订单尚未申请退款');
            }
        } else {
            return jsonHelper(104, '订单不存在');
        }

        // 支付配置
        $pay_config = PayConfig::first()->toArray();

        return $this->makeWxPreRefund($order_status, $pay_config);
    }

    /**
     * 执行微信退款
     *
     * @param $obj
     * @param $pay_config
     *
     * @return string
     * @throws \WxPayException
     */
    private function makeWxPreRefund($obj, $pay_config)
    {
        $WxPayRefund = new \WxPayRefund();

        // 微信订单号
        $WxPayRefund->SetTransaction_id($obj->transaction_id);
        // 设置订单总金额，单位为分，只能为整数，详见支付金额
        $WxPayRefund->SetTotal_fee($obj->total_money * 100);
        // 设置退款总金额，订单总金额，单位为分，只能为整数，详见支付金额
        $WxPayRefund->SetRefund_fee($obj->total_money * 100);
        // 设置商户系统内部的退款单号，商户系统内部唯一，同一退款单号多次请求只退一笔
        $WxPayRefund->SetOut_refund_no($pay_config['mch_id'] . date("YmdHis"));
        // 设置操作员帐号, 默认为商户号
        $WxPayRefund->SetOp_user_id($pay_config['mch_id']);

        /** 发起退款 */
        $order = \WxPayApi::refund($WxPayRefund, $pay_config);
        // 写入日志信息
        \Log::INFO('community_order_id: ' . $obj->id . '; wx_pay_refund_message => return_code: ' . $order['return_code'] . '; return_msg: ' . $order['return_msg']);
        /** 在返回的数组中,我们能够获取键名return_code */
        if ($order["return_code"] == "SUCCESS") {
            // 更新订单状态为 退款
            $update_order_status = GroupOrder::where('id', $obj->id)->update([
                'order_status' => 5
            ]);

            // 退款申请成功
            if ($update_order_status) {
                // 订单通过团长分享购买, 将团长的提成取消
                if ($obj->is_buy_for_commander == 1) {
                    $commander_id = $obj->belongs_commander;
                    $commander_info = Commander::find($commander_id);
                    // 计算团长提成
                    $order_goods_id = GroupOrderDetail::where('order_id', $obj->id)->get();
                    $commander_money = 0;
                    if ($order_goods_id) {
                        foreach ($order_goods_id as $key => $value) {
                            $goods_id = $value->goods_id;
                            $single_goods_royalty_rate = GroupDetail::where('id', $goods_id)->first();
                            $single_order_goods_withdraw = ($value->goods_sum) * ($single_goods_royalty_rate->royalty_rate) / 100;
                            $commander_money += $single_order_goods_withdraw;
                        }
                    }

                    // 订单提成金额
                    if ($commander_money) {
                        Commander::where('id', $commander_id)->update([
                            'total_money'    => ($commander_info->total_money) - ($obj->total_money),
                            'withdraw_money' => ($commander_info->withdraw_money) - $commander_money,
                            'residue_money'  => ($commander_info->residue_money) - $commander_money
                        ]);
                    }
                    CommanderOrder::where('order_id', $obj->id)->delete();
                }

                return jsonHelper(0, '退款成功');
            }
        } else if ($order["return_code"] == "FAIL") {
            // 退款申请失败
            return jsonHelper(105, '退款失败');
        } else {
            return jsonHelper(101, '未知错误');
        }
    }
}
