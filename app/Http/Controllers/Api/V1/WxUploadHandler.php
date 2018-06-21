<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 22/12/2017
 * Time: 3:04 PM
 */

namespace App\Http\Controllers\Api\V1;

class WxUploadHandler
{
    // 只允许以下后缀名的图片文件上传
    protected $allowed_ext = ['pem'];

    /**
     * 微信公钥与私钥文件上传
     *
     * @param $file
     * @param $file_prefix
     * @return bool|string
     */
    public function save($file, $type, $file_prefix)
    {
        // dd($file->getClientOriginalName());
        // 文件具体存储的物理路径，`public_path()` 获取的是 `public` 文件夹的物理路径。
        // 值如：/home/vagrant/Code/larabbs/public/uploads/images/avatars/201709/21/
        $upload_path = __DIR__.'../../../../../storage/cert/community';

        // 获取文件的后缀名，因图片从剪贴板里黏贴时后缀名为空，所以此处确保后缀一直存在
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'pem';
        if ($extension != 'pem') {
            return jsonHelper(112, '上传文件类型不符');
        }

        // 拼接文件名，加前缀是为了增加辨析度，前缀可以是相关数据模型的 ID
        // 值如：1_1493521050_7BVc9v9ujP.png
        if (!is_dir($upload_path . '/' . $file_prefix)) {
            mkdir($upload_path . '/' . $file_prefix);
        }
        //        if ($type == 'cert') {
        //            $filename = $file_prefix . '_' . time() . '_' . 'cert' . '.' . $extension;
        //        } else if ($type == 'key') {
        //            $filename = $file_prefix . '_' . time() . '_' . 'key' . '.' . $extension;
        //        }

        // 如果上传的不是图片将终止操作
        if ( !in_array($extension, $this->allowed_ext)) {
            return false;
        }

        // 将图片移动到我们的目标存储路径中
        $res = $file->move($upload_path. '/' . $file_prefix, $file->getClientOriginalName());

        if ($res) {
            return '/../../../storage/cert/community/' . $file_prefix . '/' . $file->getClientOriginalName();
        } else {
            return false;
        }
    }
}