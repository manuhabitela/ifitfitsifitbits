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
    $viewVars = ['synced' => []];
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

    //3. transform data to something better fit for fitbit (get it, get it?)
    $sets = [];
    foreach ($filteredData as $key => $set) {
        if ($set['dataTypeName'] === "com.google.step_count.delta") {
            $date = new DateTime();
            $date->setTimestamp(Halp::toSeconds($set['startTimeNanos']));
            $sets[]= [
                'date'      => $date,
                'duration'  => (int) (Halp::toSeconds($set['endTimeNanos']) - Halp::toSeconds($set['startTimeNanos']))*1000,
                'steps'     => $set['value'][0]['intVal'],
                'endTime'   => Halp::toSeconds($set['endTimeNanos']),
                'startTime' => Halp::toSeconds($set['startTimeNanos'])
            ];
        }
    }

    //4. merge sets together when detecting close times (+/- 10 minutes)
    $i = count($sets)-1;
    while ($i) {
        $currentSet = $sets[$i];
        $prevSet = !empty($sets[$i-1]) ? $sets[$i-1] : null;

        if ($prevSet) {
            $prevSetArrivesIn = (int) ( ($currentSet['date']->getTimestamp() - $prevSet['date']->getTimestamp())/60); //minutes
            if ($prevSetArrivesIn < 11) { //merge with prev
                $sets[$i-1]['duration']  = (int) ($currentSet['endTime'] - $prevSet['startTime'])*1000; //milliseconds;
                $sets[$i-1]['endTime']   = $currentSet['endTime'];
                $sets[$i-1]['steps']     = $prevSet['steps'] + $currentSet['steps'];
                unset($sets[$i]);
            }
        }

        --$i;
    }
    $sets = array_values($sets);

    //5. add the steps in the fitbit account, making sure we didn't already add it
    //grouping by day to ease the check with fitbit api if the time hasn't been already set
    $stepsByDay = [];
    foreach ($sets as $set) {
        $day = $set['date']->format('Y-m-d');
        $stepsByDay[ $day ][]= $set;
    }

    $activityDate = new DateTime();
    foreach ($stepsByDay as $day => $sets) {

        $activityDate->setDate(substr($day, 0, 4), substr($day, 5, 2), substr($day, -2));
        $existingActivities = $fitbit->getActivities($activityDate);
        $existingActivities = !empty($existingActivities->activities) ? $existingActivities->activities : [];

        foreach ($sets as $set) {
            $alreadyExisting = false;
            foreach ($existingActivities as $activity) {
                if ($activity->steps == $set['steps'] &&
                    $activity->startTime == $set['date']->format('H:i') &&
                    $activity->duration == $set['duration']) {
                    $alreadyExisting = true;
                    break;
                }
            }

            if ($alreadyExisting)
                continue;

            $fitbit->logActivity(
                $set['date'],
                17170, //walking activity fitbit id
                $set['duration'],
                null,
                $set['steps'],
                "Steps"
            );
            $viewVars['synced'][]= $set;
        }
    }

    $viewVars['done'] = true;
    $app->render('home', $viewVars);
});

$app->get('/fitbit-login', function() use ($app, $fitbit) {
    $fitbit->initSession();
    if ($fitbit->isAuthorized()) {
        $app->redirect(HOST);
    }
});

$app->get('/google-login', function() use ($app, $googleFit) {
    $googleFit->initSession();
    if ($googleFit->isAuthorized()) {
        $app->redirect(HOST);
    }
});
