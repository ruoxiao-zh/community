<?php

namespace App\Common;

use Log;

/*
 * 保存图片
 * 根据上传的图片和剪切范围
 * 剪切图片并保存
 * 传入参数 图片想保存的文件夹 以public为根目录
 * */

class SaveImage
{

    //单张图片
    public static function getSaveImageUrl($path, $name = 'img', $pre = '', $zoom = true, $width = 720, $height = 400)
    {

        $base64 = '';
        if (isset($_GET[$name])) {

            $base64 = $_GET[$name];
        }

        if (isset($_POST[$name])) {

            $base64 = $_POST[$name];
        }

        $path = trim($path, '/');
        //可能是base64的数据  直接转成图片
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
            $type = $result[2];
            //产生一个时间数
            $timestamp = time() . 'r' . rand(0, 3000);
            $realpath = storage_path($path) . "/$pre{$timestamp}.$type";
            if (file_put_contents($realpath, base64_decode(str_replace($result[1], '', $base64)))) {

                return  "/storage/$path/$pre{$timestamp}.$type";
            }

        }
        /*	上传错误信息
        UPLOAD_ERR_OK
        其值为 0，没有错误发生，文件上传成功。

        UPLOAD_ERR_INI_SIZE
        其值为 1，上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。

        UPLOAD_ERR_FORM_SIZE
        其值为 2，上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。

        UPLOAD_ERR_PARTIAL
        其值为 3，文件只有部分被上传。

        UPLOAD_ERR_NO_FILE
        其值为 4，没有文件被上传。

        UPLOAD_ERR_NO_TMP_DIR
        其值为 6，找不到临时文件夹。PHP 4.3.10 和 PHP 5.0.3 引进。

        UPLOAD_ERR_CANT_WRITE
        其值为 7，文件写入失败。PHP 5.1.0 引进。 */
//		if(!isset($_FILES[$name])){
//			//有错误   返回错误信息
//			Log::info('没有传入图片：'.$_FILES[$name]['error']);
//			return false;
//		}
//
//
//		if(!$_FILES[$name]['error']==0){
//			//有错误   返回错误信息
//			Log::info('保存图片错误，错误代码：'.$_FILES[$name]['error']);
//			return false;
//		}

        if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]['tmp_name']) && ($_FILES[$name]['error'] == 0)) {
            $img = $_FILES[$name];    //文件
            $imgName = $img['name'];//9.jpg
            $imgType = $img['type'];//image/jpeg
            $imgSize = $img['size'];//上传文件的尺寸 考虑缩放剪切。。。以后加入
            $imgTmpFile = $img['tmp_name'];//暂时缓存地址
            $type = self::getTypeFromImgFile($imgTmpFile);

            $b = realpath("./");  //   F:\phpsoft\Apache24\htdocs\laravel5\public
            $src_img = SaveImage::getImageHander($imgTmpFile);
            if ( !$src_img) {
                //非图片
                clearstatcache();// 函数清除文件状态缓存。
                return false;
            }
            //产生一个时间数
            $timestamp = time() . 'r' . rand(0, 3000);
            $realpath = storage_path($path) . "/$pre{$timestamp}.$type";


            //图片等比例缩放
            if ($zoom) {
                $src_img = self::ImgZoom($src_img, $width, $height);
            }

            imagejpeg($src_img, $realpath);  //将图片保存 F:\phpsoft\Apache24\htdocs\laravel5\public\upimg\1.jpg
            //销毁内存对象
            imagedestroy($src_img);
            clearstatcache();// 函数清除文件状态缓存。
            //返回保存的地址
            return  "/storage/$path/$pre{$timestamp}.$type";
        } else {
            Log::info('没有传入图片或者传入出错');
            //没文件传来
            clearstatcache();// 函数清除文件状态缓存。
            return false;
        }

    }


    //多图
    public static function getSaveMultiImageUrl($path, $name = 'img', $pre = '', $zoom = true, $width = 720, $height = 400)
    {

        $files = $_FILES[$name];
        $count = count($files['name']);
        $files['count'] = $count;
        //return $files;

        if ( !isset($_FILES[$name])) {
            return false;
        }


        $arr = [];
        for ($i = 0; $i < $count; $i++) {
            if (is_uploaded_file($_FILES[$name]['tmp_name'][$i]) && ($_FILES[$name]['error'][$i] == 0)) {
                //$img = $_FILES[$name]['name'][$i];	//文件
                $imgName = $files['name'][$i];//9.jpg
                $imgType = $files['type'][$i];//image/jpeg
                $imgSize = $files['size'][$i];//上传文件的尺寸 考虑缩放剪切。。。以后加入
                $imgTmpFile = $files['tmp_name'][$i];//暂时缓存地址
                //list($n, $d,$type) = preg_split("/./",  $imgName,3); //分割名称 取出类型名 保存使用
//			$type='jpg';
//			$namarr=explode('.',$imgName);
//			$newtype=end($namarr);
//			$imgarr=['jpg', 'jpeg','gif','png', 'pdg'];
//			if(in_array($newtype,$imgarr)){
//				$type=$newtype;
//			}
//			d($newtype,'类型');
//			d($namarr);exit;
                $type = self::getTypeFromImgFile($imgTmpFile);

                $b = realpath("./");  //   F:\phpsoft\Apache24\htdocs\laravel5\public
                $src_img = SaveImage::getImageHander($imgTmpFile);
                if ( !$src_img) {
                    //非图片
                    clearstatcache();// 函数清除文件状态缓存。
                    continue;
                }
                //产生一个时间数
                $timestamp = time() . 'r' . rand(0, 3000);
                $realpath = storage_path($path) . "/$pre{$timestamp}.$type";


                //图片等比例缩放
                if ($zoom) {
                    $src_img = self::ImgZoom($src_img, $width, $height);
                }

                imagejpeg($src_img, $realpath);  //将图片保存 F:\phpsoft\Apache24\htdocs\laravel5\public\upimg\1.jpg
                //销毁内存对象
                imagedestroy($src_img);
                clearstatcache();// 函数清除文件状态缓存。
                //返回保存的地址
                $str =  "/storage/$path/$pre{$timestamp}.$type";
                array_push($arr, $str);
            } else {
                Log::info('没有传入图片或者传入出错');
                //没文件传来
                clearstatcache();// 函数清除文件状态缓存。
                continue;
            }
        }

        return $arr;

    }


    /*
     * 上传图片剪切保存
     * x：剪切起始点X坐标
     * y：剪切起始点Y坐标
     * w：剪切宽度
     * h：剪切高度
     * */
    static public function getCutImageUrl($path, $name = 'img', $pre = '')
    {

        // is_uploaded_file($_FILES['img']['tmp_name']) 确定为post来的文件 防止恶意攻击
        if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]['tmp_name']) && ($_FILES[$name]['error'] == 0)) {
            $img = $_FILES[$name];    //文件
            $imgName = $img['name'];//9.jpg
            $imgType = $img['type'];//image/jpeg
            $imgSize = $img['size'];//上传文件的尺寸 考虑缩放剪切。。。以后加入
            $imgTmpFile = $img['tmp_name'];//暂时缓存地址
            list($n, $d, $type) = preg_split("/./", $imgName, 3); //分割名称 取出类型名 保存使用
            //取出剪切范围
            $x = $_REQUEST["x"];
            $y = $_REQUEST["y"];
            $w = $_REQUEST["w"];
            $h = $_REQUEST["h"];

            /*
             * 路径操作函数
            // $a=$_SERVER['HTTP_HOST'];  //域名 localhost
            //$ar= $_SERVER['PHP_SELF'];    //域名以外的部分 /laravel5/public/index.php

            //$b= $_SERVER['SCRIPT_NAME'];  //页面自己  同上 也是域名以外的部分

            //$b= $_SERVER['SCRIPT_FILENAME']; // 文件的实际路径  F:/phpsoft/Apache24/htdocs/laravel5/public/index.php

            //$b= $_SERVER['REQUEST_URI'];   //uri /laravel5/public/ueditor/save-image 域名以外的url部分

            //$b=dirname($b);        //    目录部分 uri的父目录   /laravel5/public/ueditor

            //$b=basename($b);          //   save-image uri的当前页

            //realpath(string)  //    返回路径的绝对路径
            //$b=realpath("/");   //  F:\
            //$b=realpath("./");   //   F:\phpsoft\Apache24\htdocs\laravel5\public
            //将文件保存到 F:\phpsoft\Apache24\htdocs\laravel5\public\upimg\。。

            */

            $b = realpath("./");  //   F:\phpsoft\Apache24\htdocs\laravel5\public


            //缓存的图片保存到内存中，考虑不同类型的图片 jpg png gif;
            //$src_img= imagecreatefromjpeg ($imgTmpFile);
            $src_img = SaveImage::getImageHander($imgTmpFile);
            if ( !$src_img) {
                //非图片
                clearstatcache();// 函数清除文件状态缓存。
                return false;
            }


            $dec_img = imagecreatetruecolor($w, $h); //创建一张图片
            //剪切图片
            imagecopy($dec_img, $src_img, 0, 0, $x, $y, $w, $h);
            //产生一个时间数
            $timestamp = time() . 'r' . rand(0, 3000);
            $realpath = "$path/$pre{$timestamp}.$type";
            imagejpeg($dec_img, $realpath);  //将图片保存 F:\phpsoft\Apache24\htdocs\laravel5\public\upimg\1.jpg
            //销毁内存对象
            imagedestroy($src_img);
            imagedestroy($dec_img);
            clearstatcache();// 函数清除文件状态缓存。
            //返回保存的地址
            return  "/storage/$realpath";
        }
        //没文件传来
        clearstatcache();// 函数清除文件状态缓存。
        return false;
    }


    //将缓存的文件保存为图片
    static function getImageHander($url)
    {

        $size = getimagesize($url);

        switch ($size['mime']) {

            case 'image/jpeg':
                $im = imagecreatefromjpeg($url);
                break;

            case 'image/gif' :
                $im = imagecreatefromgif($url);
                break;

            case 'image/png' :
                $im = imagecreatefrompng($url);
                break;

            default :
                $im = false;
                break;

        }

        return $im;

    }

    // 根据图片文件 获取图片类型
    public static function getTypeFromImgFile($filename)
    {
        $extensions = [
            IMAGETYPE_GIF     => "gif",
            IMAGETYPE_JPEG    => "jpg",
            IMAGETYPE_PNG     => "png",
            IMAGETYPE_SWF     => "swf",
            IMAGETYPE_PSD     => "psd",
            IMAGETYPE_BMP     => "bmp",
            IMAGETYPE_TIFF_II => "tiff",
            IMAGETYPE_TIFF_MM => "tiff",
            IMAGETYPE_JPC     => "jpc",
            IMAGETYPE_JP2     => "jp2",
            IMAGETYPE_JPX     => "jpx",
            IMAGETYPE_JB2     => "jb2",
            IMAGETYPE_SWC     => "swc",
            IMAGETYPE_IFF     => "iff",
            IMAGETYPE_WBMP    => "wbmp",
            IMAGETYPE_XBM     => "xbm",
            IMAGETYPE_ICO     => "ico"
        ];
        $imgsize = getimagesize($filename);
        return $extensions[$imgsize[2]];


    }


    //图片等比例缩放
    public static function ImgZoom($im, $newwidth, $newheight)
    {
        $pic_width = imagesx($im);
        $pic_height = imagesy($im);

        //修改原图到合适比例
        $o_width = floor($pic_height * $newwidth / $newheight);
        if ($o_width > $pic_width) {
            //超高
            //保留原宽度
            //压缩高度
            $pic_height = floor($pic_width * $newheight / $newwidth);
        } else {
            //不超高
            //高度保留
            //使用新宽度
            $pic_width = $o_width;
        }

        $newim = imagecreatetruecolor($newwidth, $newheight);    //生成一张要生成的黑色背景图 ，比例为计算出来的新图片比例
        imagecopyresampled($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $pic_width, $pic_height);  //复制按比例缩放的原图到 ，新的黑色背景中。
        imagedestroy($im);
        return $newim;
    }


    //删除图片
    public static function deletefile($file)
    {
        if (file_exists($file)) {
            unlink($file);
        } else {
//            echo '文件不存在';
        }
    }
}