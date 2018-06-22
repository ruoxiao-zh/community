<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/6/21
 * Time: 10:46 AM
 */

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $username = $request->username;
        if ( !$username) {
            return jsonHelper(102, '必要的参数不能为空: username');
        }

        $password = $request->password;
        if ( !$password) {
            return jsonHelper(103, '必要的参数不能为空: password');
        }

        $admin = Admin::first();
        if ($admin->username != $username || Crypt::decryptString($admin->password) != $password) {
            return jsonHelper(104, '用户名或密码错误');
        }

        Cache::put('admin', $admin, 60);

        return jsonHelper(0, '登陆成功');
    }

    public function logout()
    {
        if ( !Cache::get('admin')) {
            return jsonHelper(102, '请登录, 未登录无法访问');
        }

        Cache::forget('admin');

        return jsonHelper(0, '登出成功');
    }

    public function updatePass(Request $request)
    {
        if ( !Cache::get('admin')) {
            return jsonHelper(102, '请登录, 未登录无法访问');
        }

        $new_password = $request->new_password;
        if ( !$new_password) {
            return jsonHelper(102, '必要的参数不能为空: new_password');
        }

        $admin = Admin::first();
        $old_password = $request->old_password;
        if ( !$old_password) {
            return jsonHelper(102, '必要的参数不能为空: old_password');
        } else if (Crypt::decryptString($admin->password) != $old_password) {
            return back()->with('danger', '原密码错误');
        }

        $admin->update(['password' => Crypt::encryptString($request->password)]);

        Cache::forget('admin');

        return jsonHelper(0, '密码修改成功, 请重新登录');
    }
}
