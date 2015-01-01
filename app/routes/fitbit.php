<?php

include LIB_PATH.'/googlefit.php';
include LIB_PATH.'/halp.php';

$app->get('/fitbit', function() use ($app) {
    $fitbit = new \Fitbit\Api(
        FITBIT_CLIENT,
        FITBIT_SECRET,
        null,
        "json",
        new \OAuth\Common\Storage\Session(false)
    );

    $fitbit->initSession();
    $json = $fitbit->getProfile();

    var_dump($json);
});

$app->get('/google', function() use($app) {
    $googleFit = new \GoogleFit\Api(
        GOOGLE_CLIENT,
        GOOGLE_SECRET,
        null,
        new \OAuth\Common\Storage\Session(false)
    );

    $googleFit->initSession();

    $data = $googleFit->req(
        'https://www.googleapis.com/fitness/v1/users/me/dataSources/derived:com.google.step_count.delta:com.google.android.gms:estimated_steps/datasets/'.Halp::toNanos(strtotime('-7 days')).'-'.Halp::toNanos(time())
    );

    $stepsByDay = [];
    foreach ($data['point'] as $session) {
        if ($session['dataTypeName'] === "com.google.step_count.delta") {
            $sessionDay = date('Y-m-d', Halp::toSeconds($session['startTimeNanos']));
            $stepsByDay[ $sessionDay ]= !empty($stepsByDay[ $sessionDay ])
                ? $stepsByDay[ $sessionDay ] + $session['value'][0]['intVal']
                : $session['value'][0]['intVal'];
        }
    }

    print_r($stepsByDay);
});
