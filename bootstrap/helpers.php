<?php

/**
 * json 编码
 */
if ( !function_exists('jsonHelper')) {
    function jsonHelper($code = 0, $msg = 'success', $data = null)
    {
        if ($data) {
            return json_encode([
                'code' => $code,
                'msg'  => $msg,
                'data' => $data
            ]);
        } else {
            return json_encode([
                'code' => $code,
                'msg'  => $msg
            ]);
        }
    }
}