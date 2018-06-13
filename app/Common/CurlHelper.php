<?php
namespace App\Common;
class CurlHelper{


	/*
	 * 返回get的数据
	 * */
	public function getCurl($url,$flag=true){
		//需要通过curl获取
		// 1. 初始化
		$ch = curl_init();
		// 2. 设置选项，包括URL
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_HEADER, 0);
		// 3. 执行并获取HTML文档内容
		$output = curl_exec($ch);
		// 4. 释放curl句柄
		curl_close($ch);
		//返回值
        if($flag){
            return json_decode($output,true);
        }else{
            return $output;
        }


	}


    //curl获取远程文件
    public function getUrlFile($url = "", $filename = "")
    {
        //去除URL连接上面可能的引号
        //$url = preg_replace( '/(?:^['"]+|['"/]+$)/', '', $url );
        $hander = curl_init();
        $fp = fopen($filename,'wb');
        curl_setopt($hander,CURLOPT_URL,$url);
        curl_setopt($hander,CURLOPT_FILE,$fp);
        curl_setopt($hander,CURLOPT_HEADER,0);
        curl_setopt($hander,CURLOPT_FOLLOWLOCATION,1);
        //curl_setopt($hander,CURLOPT_RETURNTRANSFER,false);//以数据流的方式返回数据,当为false是直接显示出来
        curl_setopt($hander,CURLOPT_TIMEOUT,60);
        curl_exec($hander);
        curl_close($hander);
        fclose($fp);
        Return true;
    }




    /*
     * 返回post数据
     * */
    public function postCurl($url,$data,$flag=true){
	    //需要通过curl获取
	    // 1. 初始化
	    $ch = curl_init();
	    // 2. 设置选项，包括URL
	    //设置要访问的url
	    curl_setopt($ch, CURLOPT_URL, $url);
	    //设置返回的类型为文件流而不是直接输出
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    //设置请求为post
	    curl_setopt($ch, CURLOPT_POST, 1);
	    //传递请求的数据
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	    //curl_setopt($ch, CURLOPT_HEADER, 0);
	    // 3. 执行并获取HTML文档内容
	    $output = curl_exec($ch);
	    // 4. 释放curl句柄
	    curl_close($ch);
        //返回值
        if($flag){
            return json_decode($output,true);
        }else{
            return $output;
        }
    }


    /*
     * 返回post数据
     * */
    public function postCurlFile($url,$data,$filename,$flag=true){
        //需要通过curl获取
        // 1. 初始化
        $hander = curl_init();
        $fp = fopen($filename,'wb');
        curl_setopt($hander,CURLOPT_URL,$url);
        curl_setopt($hander,CURLOPT_FILE,$fp);
        curl_setopt($hander,CURLOPT_HEADER,0);
        curl_setopt($hander,CURLOPT_FOLLOWLOCATION,1);
        //curl_setopt($hander,CURLOPT_RETURNTRANSFER,false);//以数据流的方式返回数据,当为false是直接显示出来
        curl_setopt($hander,CURLOPT_TIMEOUT,60);
        
        //设置请求为post
        curl_setopt($hander, CURLOPT_POST, 1);
        //传递请求的数据
        curl_setopt($hander, CURLOPT_POSTFIELDS, $data);

        curl_exec($hander);
        curl_close($hander);
        fclose($fp);

    }




    //上传文件 图片
    public function upload_file($url,$path,$key,$flag=true){
//        $data = array(
//            'media'=>'@'.realpath($path).";type=".$type.";filename=".$filename
//        );

        $data = [
            $key=>new \CURLFile(realpath($path))
        ];

        //Log::info('file:'.json_encode($data));

        $ch = curl_init();
        //设置帐号和帐号名
        //curl_setopt($ch, CURLOPT_USERPWD, 'joe:secret' );

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_getinfo($ch);
        $return= curl_exec($ch);
        curl_close($ch);
        //返回值
        if($flag){
            return json_decode($return,true);
        }else{
            return $return;
        }
    }





}


?>