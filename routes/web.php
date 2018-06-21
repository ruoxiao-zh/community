<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 社区团购
Route::namespace('Api\V1')->prefix('api/v1')->group(function () {
    // 登录
    Route::post('login', 'LoginController@login');

//     Route::middleware('admin.login')->group(function () {
        // 登出
        Route::get('logout', 'LoginController@logout');
        Route::post('change-password', 'LoginController@updatePass');

        // 公司管理
        Route::get('company', 'CompanyController@index');
        Route::post('company-upload-img', 'CompanyController@uploadImg');
        Route::post('company-create', 'CompanyController@store');
        Route::post('company-update', 'CompanyController@update');
        Route::get('company-delete', 'CompanyController@destroy');

        // 支付管理
        Route::get('pay-config', 'PayConfigController@index');
        Route::post('pay-config-create-or-update', 'PayConfigController@createOrUpdate');
        Route::post('pay-upload-cert', 'PayConfigController@payCertUpload');
        Route::post('pay-upload-key', 'PayConfigController@payKeyUpload');
        Route::get('pay-config-delete', 'PayConfigController@destroy');

        // 配送区域管理
        Route::get('delivery', 'DeliveryController.php@index');
        Route::get('delivery-detail', 'DeliveryController.php@show');
        Route::post('delivery-create-or-update', 'DeliveryController.php@createOrUpdate');
        Route::get('delivery-delete', 'DeliveryController.php@destroy');
        Route::post('delivery/city', 'DeliveryController.php@addOrEditCity');
        Route::post('delivery/search', 'DeliveryController.php@search');
        Route::get('delivery/default', 'DeliveryController.php@defaultArea');

        // 客服
        Route::get('customer-service-auto-send-message', 'CustomerServiceController@index');
        Route::post('customer-service-auto-send-message', 'CustomerServiceController@sendMessage');
        Route::get('get-qrcode', 'CustomerServiceController@getUserQRCode');

        Route::post('customer-service/create-or-update', 'CustomerServiceController@createOrUpdate');
        Route::get('customer-service', 'CustomerServiceController@show');
        Route::get('customer-service/show', 'CustomerServiceController@detail');
        Route::get('customer-service/delete', 'CustomerServiceController@destroy');

        // 团购管理
        Route::post('group/upload-introduce', 'GroupController@groupUploadImg');
        Route::post('group/create', 'GroupController@create');
        Route::post('group/update', 'GroupController@update');
        Route::get('group', 'GroupController@show');
        Route::get('group/detail', 'GroupController@detail');
        Route::post('group/delete', 'GroupController@delete');
        Route::post('group/putaway', 'GroupController@putaway');
        Route::get('group/goods', 'GroupController@goodsList');
        Route::post('group/search', 'GroupController@search');
        Route::post('group/istop', 'GroupController@putGroupTop');


        // 商品管理
        Route::post('goods/upload-goods', 'GoodsController@uploadGoodsImg');
        Route::post('goods/create', 'GoodsController@create');
        Route::post('goods/update', 'GoodsController@update');
        Route::get('goods', 'GoodsController@index');
        Route::get('goods/detail', 'GoodsController@show');
        Route::get('goods/delete', 'GoodsController@delete');

        // 核销管理
        Route::get('check-code-getuser', 'CheckCodeController@getUser');

        // 核销员管理
        Route::get('check-code-get-delivery', 'CheckCodeController@getDelivery');
        Route::post('check-code-manager-search', 'CheckCodeController@searchCheckCodeManager');
        Route::post('check-code-manager-create-or-update', 'CheckCodeController@checkCodeManager');
        Route::post('check-code-manager-delete', 'CheckCodeController@deleteCheckCodeManager');
        Route::get('check-code-manager', 'CheckCodeController@getCheckCodeManager');
        Route::get('check-code-manager-detail', 'CheckCodeController@checkCodeManagerShow');

        // 团长管理
        Route::post('user/search', 'CommanderController@searchUser');
        Route::post('commander/create', 'CommanderController@create');
        Route::post('commander/update', 'CommanderController@update');
        Route::get('commander', 'CommanderController@index');
        Route::get('commander/detail', 'CommanderController@show');
        Route::get('commander/delete', 'CommanderController@delete');
        Route::post('commander/search', 'CommanderController@search');
        Route::get('commander/apply-for', 'CommanderController@applyForList');
        Route::post('commander/apply-for', 'CommanderController@applyFor');
        Route::post('commander/apply-for-confirm', 'CommanderController@applyForConfirm');
        Route::get('commander/count', 'CommanderController@countMoney');
        Route::post('commander/withdraw-money', 'CommanderController@withdraw');
        Route::get('commander/export', 'CommanderController@commanderOrderExportToExcel');
        Route::get('commander/get-id', 'CommanderController@getCommanderId');

        // 前台首页
        Route::post('user', 'IndexController@user');
        Route::get('shopping/start-soon', 'IndexController@startSoon');
        Route::get('shopping/start-soon/all', 'IndexController@startSoonAll');
        Route::get('onsell', 'IndexController@onSell');

        // 前台团购详情
        Route::get('front/group/detail', 'GroupController@frontDetail');
        Route::get('front/group/person', 'GroupController@orderPerson');
        Route::get('front/group/count', 'GroupController@orderPersonCount');
        Route::get('front/group/qrcode', 'GroupController@getGroupQRCode');

        // 下单
        Route::post('order', 'OrderController@placeOrder');

        // 支付
        Route::post('pay-pre-order', 'PayController@getPreOrder');
        Route::post('pay-notify', 'PayController@receiveNotify');
        // Route::post('pay-refund', 'PayController@payRefund');
        Route::post('pay/refund', 'PayController@moneyRefund');

        // 订单
        Route::get('orderlist/back', 'OrderListController@getBackOrderList');
        Route::get('orderlist/back/search', 'OrderListController@searchBackOrderList');
        Route::post('orderlist/deliver-goods', 'OrderListController@deliverGoods');
        Route::post('orderlist/deliver-goods-together', 'OrderListController@deliverGoodsToghter');
        Route::get('order/getqrcode', 'OrderListController@getqrcode');
        Route::get('order/check', 'OrderListController@checkOrder');
        Route::post('orderlist/front', 'OrderListController@getFrontOrderList');
        Route::get('orderlist/export', 'OrderListController@export');
        Route::get('orderlist/single-order-export', 'OrderListController@singleOrderExport');
        Route::post('orderlist/pay/refund', 'PayController@orderMoneyRefund');
        Route::get('orderlist/delete', 'OrderListController@deleteOrder');
        Route::get('order/confirm', 'OrderListController@orderConfirm');

        // 地址
        Route::post('address-add', 'AddressController@addAddress');
        Route::get('address-list', 'AddressController@addressList');
        Route::get('address-delete', 'AddressController@addressDel');

        // 模板消息
        Route::post('news/group-reserve', 'NewsController@groupReserve');

        // 用户推荐商品
        Route::get('user-recommend', 'UserRecommendController@index');
        Route::post('user-recommend/add', 'UserRecommendController@add');
        Route::get('user-recommend/delete', 'UserRecommendController@delete');

        // 定时任务
        Route::get('auto-confirm-order', 'OrderController@confirmOrder');
        // 个人中心团长收�
        Route::get('user-commander-balance', 'CommanderController@commanderBalance');

        // 配送点管理
        Route::get('delivery-area', 'DeliveryAreaController@index');
        Route::get('delivery-area-detail', 'DeliveryAreaController@show');
        Route::post('delivery-area-create', 'DeliveryAreaController@store');
        Route::post('delivery-area-update', 'DeliveryAreaController@update');
        Route::get('delivery-area-delete', 'DeliveryAreaController@destroy');

        // 用户配送点
        Route::post('user-delivery-add', 'UserDeliveryController@addUserDelivery');
        Route::get('user-delivery-list', 'UserDeliveryController@userDeliveryList');
        Route::get('user-delivery-delete', 'UserDeliveryController@userDeliveryDel');

        Route::get('recent-order', 'OrderListController@recentOrder');
        Route::get('find-commander-area', 'CommanderController@area');
//     });
});