<?php

include LIB_PATH.'/GoogleFit.php';
include LIB_PATH.'/Halp.php';

$fitbit = new \Fitbit\Api(
    FITBIT_CLIENT,
    FITBIT_SECRET,
    HOST.'/fitbit-login',
    "json",
    new \OAuth\Common\Storage\Session(false)
);

$googleFit = new \GoogleFit\Api(
    GOOGLE_CLIENT,
    GOOGLE_SECRET,
    HOST.'/google-login',
    new \OAuth\Common\Storage\Session(false)
);

$app->get('/', function() use ($app, $fitbit, $googleFit) {
    $viewVars = [];
    if (!$fitbit->isAuthorized())
        $viewVars['fitbit']= true;

    if (!$googleFit->isAuthorized())
        $viewVars['google']= true;

    if (!$googleFit->isAuthorized() || !$fitbit->isAuthorized()) {
        $viewVars['intro']= true;

        $app->render('home', $viewVars);
        return true;
    }

    //1. get the list of OK sources we want data from, here it is the android watches
    $sources = $googleFit->req('https://www.googleapis.com/fitness/v1/users/me/dataSources');
    $filteredSources = [];
    foreach ($sources["dataSource"] as $source) {
        if (!empty($source['device']) && $source['device']['type'] === 'watch')
            $filteredSources[]= $source['dataStreamId'];
    }

    //2. get the estimated steps dataset from last week
    //and filter out all data which isnt from a watch
    $data = $googleFit->req(sprintf(
        'https://www.googleapis.com/fitness/v1/users/me/dataSources/%s/datasets/%s-%s',
        str_replace(" ", "%20", "derived:com.google.step_count.delta:com.google.android.gms:estimated_steps"),
        Halp::toNanos(strtotime('-7 days')),
        Halp::toNanos(time())
    ));
    $filteredData = array_filter($data['point'], function($set) use ($filteredSources) {
        return isset($set['originDataSourceId']) && !!in_array($set['originDataSourceId'], $filteredSources);
    });

    //3. sort the data and group it by day and hour, meaning if we have one dataset at 12:30 and
    //another one at 13:00, they'll be combined in one group corresponding to noon 12:00
    $stepsByDay = [];
    foreach ($filteredData as $set) {
        if ($set['dataTypeName'] === "com.google.step_count.delta") {
            $startingTime = Halp::toSeconds($set['startTimeNanos']);
            $setDay = date('Y-m-d', $startingTime);
            $setHour = date('H', $startingTime);
            $hourZone = Halp::roundedHour($setHour);

            $stepsByDay[ $setDay ][ $hourZone ]= !empty($stepsByDay[ $setDay ][ $hourZone ])
                ? $stepsByDay[ $setDay ][ $hourZone ] + $set['value'][0]['intVal']
                : $set['value'][0]['intVal'];
        }
    }

    //4. we add the steps in the fitbit account
    //making sure we didn't already add it
    $moderateWalkActivity = 17170;
    $activityDate = new DateTime();
    foreach ($stepsByDay as $day => $hourGroups) {
        $activityDate->setDate(substr($day, 0, 4), substr($day, 5, 2), substr($day, -2));

        $existingActivities = $fitbit->getActivities($activityDate);
        $existingActivities = !empty($existingActivities->activities) ? $existingActivities->activities : [];

        foreach ($hourGroups as $hour => $steps) {

            $alreadyExisting = false;
            foreach ($existingActivities as $activity) {
                if ($activity->steps == $steps && $activity->startTime == "$hour:13") {
                    $alreadyExisting = true;
                    break;
                }
            }
            if ($alreadyExisting)
                continue;

            $activityDate->setTime($hour, 13, 37);
            //dirty trick to keep the walk miles per hour average:
            //we calculate duration so that the 2.5mph fits
            //we take an totally average stride length of 80cm to calculate distance of steps
            // $distance = ($steps*80)/100000*0.621371192; //steps to cm to km to miles, yo
            // $duration = round( ($distance / 2.5)*60*60*1000 ); //hours to minutes to seconds to milliseconds, ya
            //or, well, you know, NOT
            $duration = round( 30*60*1000 ); //30 minutes to seconds to milliseconds, ya
            $fitbit->logActivity(
                $activityDate,
                $moderateWalkActivity,
                $duration,
                null,
                $steps,
                "Steps"
            );
        }
    }

    $viewVars['done'] = true;
    $app->render('home', $viewVars);
});

$app->get('/fitbit-login', function() use ($app, $fitbit) {
    $fitbit->initSession();
});

$app->get('/google-login', function() use ($app, $googleFit) {
    $googleFit->initSession();
    if ($googleFit->isAuthorized()) {
        $app->redirect(HOST);
    }
});
