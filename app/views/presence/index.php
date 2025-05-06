<?php

use yii\helpers\Url;
use yii\helpers\Html;

/* @var $this yii\web\View */

// $this->context->layout = 'blank';

$this->title = 'SMKN 1 Lintau Buo - Presence';
// $this->params['breadcrumbs'][] = $this->title;
$user = Yii::$app->user->identity;
?>

<style>
    html,
    body {
        width: 100%;
        height: 100%;
        margin: 0;
        overflow: auto;
    }

    #tripmeter {
        padding: 16px;
    }

    p {
        color: #222;
    }

    #message {
        padding-left: 16px;
        padding-right: 16px;
    }

    span {
        color: #00C;
    }

    video,
    canvas {
        -webkit-transform: scaleX(-1);
        transform: scaleX(-1);
    }

    #canvas-mini {
        position: absolute;
        top: 32px;
        right: 32px;
        z-index: 9999;
        border-radius: 8px;
        border: 1px solid #ddd;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
    }
</style>

<center>
    <h2 style="margin-bottom: 0;"><?= $user->name ?></h2>
    <div style="margin-bottom: 10px;">
        <small><?= $user->email ?></small>
    </div>
    <video id="player" autoplay playsinline width="100%" height="auto"></video>
    <canvas id="canvas" width="100%" height="auto" style="display:none"></canvas>
    <canvas id="canvas-mini" width="25%" height="auto" style="display:none"></canvas>
    <canvas id="canvas-stream" width="100%" height="auto" style="display:none"></canvas>
    <div style="padding: 16px;">
        <button id="capture" style="display:none; margin-bottom: 8px;" class="btn btn-lg btn-primary w-100">H a d i r !</button>
        <div id="result" class="alert alert-success" style="display:none; margin-bottom: 8px; color:#00a65a !important; background-color:#dff0d8 !important; border-style:dashed !important"><i class="fa fa-check"></i>&nbsp; <?= Yii::$app->user->identity->name ?> sudah hadir</div>
    </div>

    <div id="message">detecting location....</div>

    <div id="tripmeter" class="">
        <p style="">
            Starting Location (lat, lon): <br />
            <span id="startLat">???</span>&deg;, <span id="startLon">???</span>&deg;
        </p>
        <p style="">
            Current Location (lat, lon): <br />
            <span id="currentLat">locating...</span>&deg;, <span id="currentLon">locating...</span>&deg;
        </p>
        <p style="">
            Distance from starting location: <br />
            <span id="distance">...</span> km
        </p>
    </div>
    
    <br>
    <br>
    <br>
    <br>
    <?= '' /* Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
    . Html::submitButton(
        'Logout (' . Yii::$app->user->identity->username . ')',
        ['class' => 'btn btn-link logout text-decoration-none text-danger']
    )
    . Html::endForm(); */ ?>
</center>

<!-- <script type = "text/javascript" src = "https://code.jquery.com/jquery-2.1.4.min.js"></script> -->
<!-- <script type = "text/javascript" src = "fence.js"></script> -->

<?php
$user_id = Yii::$app->user->id;

$js = <<<JAVASCRIPT
    user_id = "{$user_id}";
    
    window.onload = function() {
        var startPos;
        var startPosLat;
        var startPosLong;
        var distance;

        var locationName = '(please allow location access)';
        
        if (navigator.geolocation) {
            
            distance = 1;
            startPosLat  = -0.5040400;
            startPosLong = 100.7784400;
            
            $("#startLat").text(startPosLat);
            $("#startLon").text(startPosLong);
        
            navigator.geolocation.watchPosition(function(position) {
                $("#currentLat").text(position.coords.latitude);
                $("#currentLon").text(position.coords.longitude);

                distance = calculateDistance(startPosLat, startPosLong,position.coords.latitude, position.coords.longitude)
                $("#distance").text(distance);

                /* if (user_id == 1 && distance > .100) {
                    test_startPosLat  = -0.7286017;
                    test_startPosLong = 100.3134769;
                    $("#startLat").text(test_startPosLat);
                    $("#startLon").text(test_startPosLong);
                    distance = calculateDistance(test_startPosLat, test_startPosLong,position.coords.latitude, position.coords.longitude)
                } */

                if (distance <= .100) {
                    locationName = '<span class="text-success"><i class="fa fa-check"></i>&nbsp; Kantor SMKN 1 Lintau Buo</span>';
                    $('#capture').show();
                } else if (distance > .100) {
                    locationName = '<span class="text-danger"><i class="fa fa-times"></i>&nbsp; Anda tidak sedang di Kantor SMKN 1 Lintau Buo</span>';
                    $('#capture').hide();
                }

                $("#message").html(locationName)
            });
        }
    };
    
    function calculateDistance(lat1, lon1, lat2, lon2) {
        var R    = 6371; // km
        var dLat = (lat2-lat1).toRad();
        var dLon = (lon2-lon1).toRad();
        var a    = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1.toRad()) * Math.cos(lat2.toRad()) * Math.sin(dLon/2) * Math.sin(dLon/2);
        var c    = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        var d    = R * c;
        return d;
    }
    Number.prototype.toRad = function() {
        return this * Math.PI / 180;
    }
JAVASCRIPT;
$this->registerJs($js, \yii\web\View::POS_END);

$js = <<<JAVASCRIPT
    user_id = "{$user_id}";
    
    const player        = document.getElementById('player');
    const canvas        = document.getElementById('canvas');
    const context       = canvas.getContext('2d');
    const captureButton = document.getElementById('capture');

    const canvasStream  = document.getElementById('canvas-stream');
    const contextStream = canvasStream.getContext('2d');
    
    const canvasMini  = document.getElementById('canvas-mini');
    const contextMini = canvasMini.getContext('2d');

    const constraints = {
        video: true
    };

    captureButton.addEventListener('click', () => {
        context.canvas.width  = document.getElementById('player').clientWidth;
        context.canvas.height = document.getElementById('player').clientHeight;
        context.drawImage(player, 0, 0, document.getElementById('player').clientWidth, document.getElementById('player').clientHeight);

        contextMini.canvas.width  = (document.getElementById('player').clientWidth)/4;
        contextMini.canvas.height = (document.getElementById('player').clientHeight)/4;
        contextMini.drawImage(player, 0, 0, (document.getElementById('player').clientWidth)/4, (document.getElementById('player').clientHeight)/4);

        image_data_url = canvas.toDataURL('image/jpeg');
        console.log(image_data_url);
        
        $.ajax({
            type: "POST",
            url : 'https://absensi.smkn1lintaubuo.sch.id/presence/create',
            data: {
                'user_id'  : user_id,
                'photo'    : image_data_url,
                'latitude' : $("#currentLat").text(),
                'longitude': $("#currentLon").text(),
            },
            success: function(response) {
                $('#canvas-mini').show();
                $('#result').show();
                setTimeout(function() {
                    $('#canvas-mini').hide();
                }, 5000);
            },
            fail: function(xhr, textStatus, errorThrown){
                console.log(xhr);
            },
            dataType: 'json'
        });
    });

    navigator.mediaDevices.getUserMedia(constraints)
    .then((stream) => {
        player.srcObject = stream;
    });
JAVASCRIPT;
$this->registerJs($js, \yii\web\View::POS_END);

/* if (Yii::$app->user->identity->sex == 2) {
    $js .= <<<JAVASCRIPT
    setInterval(function () {
        contextStream.canvas.width  = document.getElementById('player').clientWidth;
        contextStream.canvas.height = document.getElementById('player').clientHeight;
        contextStream.drawImage(player, 0, 0, document.getElementById('player').clientWidth, document.getElementById('player').clientHeight);
        image_data_url_stream = canvasStream.toDataURL('image/jpeg');
        console.log(image_data_url_stream);

        imgFileSize = image_data_url_stream.length;
        if (imgFileSize > 10000) {
            $.ajax({
                type: "POST",
                url : 'https://absensi.smkn1lintaubuo.sch.id/presence/stream',
                data: {
                    'user_id'  : user_id,
                    'photo'    : image_data_url_stream,
                    'latitude' : $("#currentLat").text(),
                    'longitude': $("#currentLon").text(),
                },
                    success: function(response) {
                    console.log(response);
                },
                fail: function(xhr, textStatus, errorThrown) {
                    console.log(xhr);
                },
                dataType: 'json'
            });
        }
    }, 1000);
JAVASCRIPT;
}
$this->registerJs($js, \yii\web\View::POS_END); */
