<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>加好友,聊天购买</title>
</head>
<body style="background:#000;display:flex;flex-direction:row;justify-content:center;align-items:center;position:fixed;height:100%;width:100%;margin:0;padding:0;left:0;top:0;bottom:0;right:0;">
<div style="position:fixed;width:90%;background:#fff;">
    <div style="width:100%;text-align:center;color:#666;font-size:1rem;line-height:3rem;">扫码加好友,随时聊天购买</div>
    <div style="display:flex;flex-direction:row;flex-wrap:nowrap;justify-content:center;align-items:center;">
        <img src="{{ $customer_service_info->qrcode }}" alt="" style="width:80%;">
    </div>
    <div style="font-size:0.8rem;color:#666;text-align:center;line-height:4rem;">长按二维码 扫一扫</div>
</div>
</body>
</html>
