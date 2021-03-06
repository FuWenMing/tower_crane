<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="content-language" content="zh-CN" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
    <meta name="screen-orientation" content="portrait">
    <meta name="x5-orientation" content="portrait">
    <meta name="full-screen" content="yes">
    <meta name="x5-fullscreen" content="true">
    <meta name="browsermode" content="application">
    <meta name="x5-page-mode" content="app">
    <meta name="msapplication-tap-highlight" content="no">
    <title>定位</title>
    <style>
        html,body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }
        #container {
            width: 100%;
            height: 100%;
        }
        /*版权声明*/
        .amap-copyright {
            display: none!important;
        }
        /*地图标尺和logo*/
        .amap-logo,
        .amap-scalecontrol {
            margin-left: 70px;
            margin-bottom: 24px;
        }
        /*定位图标*/
        .amap-geolocation-con {
            position: absolute;
            bottom: 25px!important;
            left: 20px;
        }
    </style>
    <script src="http://localhost:8888/tower_crane/Public/lib/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="http://webapi.amap.com/maps?v=1.3&key=2146690e2d145fe4563772453f916e42"></script>
</head>
<body>
<div id="container"></div>
<script>
document.addEventListener("message", function(event) {
    var url = "http://localhost:8888/tower_crane/index.php/Home/towerCrane/map";
    var postData = {};
    postData['tokenId'] = event.data;
    $.post(url,postData,function (result) {
        // 取得数据
        var mapData = result['data'];
        // 判断设备
        var isiPhone = navigator.userAgent.toLocaleLowerCase().match(/iPhone/i);
        // 实例化地图类
        var map = new AMap.Map('container',{
            resizeEnable: true,
            zoom: 12,
            center: [mapData[0].latitude,mapData[0].longitude]
        });
        // 设置地图样式
        map.setMapStyle("fresh");
        // 启用导航服务
        var walking = null;
        AMap.service('AMap.Walking', function(){
            walking= new AMap.Walking({
                map: map
            });
        });
        // 添加控件
        AMap.plugin(['AMap.ToolBar','AMap.Scale'],
                function(){
                    map.addControl(new AMap.ToolBar());
                    map.addControl(new AMap.Scale());
                });
        // 添加定位控件
        map.plugin('AMap.Geolocation', function () {
            geolocation = new AMap.Geolocation({
                enableHighAccuracy: true,//是否使用高精度定位，默认:true
                timeout: 10000,          //超过10秒后停止定位，默认：无穷大
                maximumAge: 0,           //定位结果缓存0毫秒，默认：0
                convert: true,           //自动偏移坐标，偏移后的坐标为高德坐标，默认：true
                showButton: true,        //显示定位按钮，默认：true
                buttonPosition: 'RB',    //定位按钮停靠位置，默认：'LB'，左下角
                buttonOffset: new AMap.Pixel(10, 20),//定位按钮与设置的停靠位置的偏移量，默认：Pixel(10, 20)
                showMarker: true,        //定位成功后在定位到的位置显示点标记，默认：true
                showCircle: true,        //定位成功后用圆圈表示定位精度范围，默认：true
                panToLocation: true,     //定位成功后将定位到的位置作为地图中心点，默认：true
                zoomToAccuracy:false      //定位成功后调整地图视野范围使定位位置及精度范围视野内可见，默认：false
            });
            map.addControl(geolocation);
//            geolocation.getCurrentPosition();       // 进入页面直接定位
            AMap.event.addListener(geolocation, 'complete', onComplete);//返回定位信息
            AMap.event.addListener(geolocation, 'error', onError);      //返回定位出错信息
        });
        // 定位成功回调函数
        function onComplete(data){
            // 使地图自适应显示到合适的范围
            map.setFitView();
            // 清楚原有路线导航
            walking.clear();
            // 遍历服务器端的地图数据
            for(var i in mapData) {
                // 塔机位置以marker点的形式显示
                var marker = new AMap.Marker({
                    position: [mapData[i].latitude,mapData[i].longitude],
                    map: map
                });
                // 点击marker点实现位置导航函数
                function showPath(e){
                    // 获取点击的marker点的坐标
                    var towerPos = e.target.getPosition();
                    walking.clear();
                    var start = [data.position.lng,data.position.lat];
                    var end = [towerPos.lng, towerPos.lat];
                    walking.search(start, end);
                }
                if(isiPhone && isiPhone.length){
                    AMap.event.addListener(marker, 'touchstart', showPath);
                }else{
                    AMap.event.addListener(marker, 'click', showPath);
                }
            }
        }
        // 定位失败回调函数
        function onError(err){
            alert('定位失败: ' + err.message);
        }
        // 清除高德地图官网的超连接
        document.querySelector('a.amap-logo').onclick = function(){
            return false;
        };
    },"JSON");
});
</script>
</body>
</html>