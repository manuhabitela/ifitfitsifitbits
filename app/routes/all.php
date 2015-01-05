<?php

include LIB_PATH.'/GoogleFit.php';
include LIB_PATH.'/Halp.php';

function getFitbit() {
    return new \Fitbit\Api(
        FITBIT_CLIENT,
        FITBIT_SECRET,
        HOST.'/fitbit-login',
        "json",
        new \OAuth\Common\Storage\Session(false)
    );
}

function getGoogleFit() {
    return new \GoogleFit\Api(
        GOOGLE_CLIENT,
        GOOGLE_SECRET,
        HOST.'/google-login',
        new \OAuth\Common\Storage\Session(false)
    );
}

$app->get('/', function() use ($app) {
    $fitbit = getFitbit();
    $googleFit = getGoogleFit();

    if (!$fitbit->isAuthorized())
        $viewVars['fitbit']= true;

    if (!$googleFit->isAuthorized())
        $viewVars['google']= true;

    if (!$googleFit->isAuthorized() || !$fitbit->isAuthorized()) {
        $viewVars['intro']= true;

        $app->render('home', $viewVars);
        return true;
    }

    $success = $app->request->get('success');
    if (!empty($success)) {
        $viewVars['done'] = true;
        $app->render('home', $viewVars);
        return true;
    }

    $viewVars = ['toDo' => []];

    //1. get the estimated steps dataset from last week
    $data = $googleFit->req(sprintf(
        'https://www.googleapis.com/fitness/v1/users/me/dataSources/%s/datasets/%s-%s',
        "derived:com.google.step_count.delta:com.google.android.gms:estimated_steps",
        number_format(Halp::toNanos(strtotime('-7 days')), 0, '', ''),
        number_format(Halp::toNanos(time()), 0, '', '')
    ));
    $data = $data['point'];

    //2. transform data to something better fit for fitbit (get it, get it?)
    $fitBitUserData = $fitbit->getProfile();
    $timezone = !empty($fitBitUserData->user->timezone) ? $fitBitUserData->user->timezone : null;
    if (in_array($timezone, DateTimeZone::listIdentifiers(DateTimeZone::ALL)))
        date_default_timezone_set($fitBitUserData->user->timezone);

    $sets = [];
    foreach ($data as $key => $set) {
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

    //3. merge sets together when detecting close times (+/- 10 minutes)
    //and remove not interesting sets (less than 30 steps or less than 3 minutes, this is a totally random choice by ME)
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
    foreach ($sets as $k => $set) {
        if ($set['steps'] <= 30 || ($set['steps'] <= 30 && $set['duration'] <= 3*1000*60))
            unset($sets[$k]);
    }
    $sets = array_values($sets);

    //4. add the steps in the fitbit account, making sure we didn't already add it
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

            $viewVars['toDo'][]= $set;
        }
    }

    $app->render('home', $viewVars);
});

$app->post('/', function() use ($app) {
    $fitbit = getFitbit();
    $sets = $app->request->post('sets');

    if (empty($sets))
        $app->redirect(HOST);

    foreach ($sets as &$set) {
        $set = json_decode($set, true);
        $date = new DateTime();
        $date->setTimestamp($set['date']);
        $set['date'] = $date;

        $fitbit->logActivity(
            $set['date'],
            17170, //walking activity fitbit id
            $set['duration'],
            null,
            $set['steps'],
            "Steps"
        );
    }

    $app->redirect(HOST.'?success=ofcourseitsucceededcomeonwhatdidyouthink');
});

$app->get('/fitbit-login', function() use ($app) {
    $fitbit = getFitbit();
    $fitbit->initSession();
    if ($fitbit->isAuthorized()) {
        $app->redirect(HOST);
    }
});

$app->get('/google-login', function() use ($app) {
    $googleFit = getGoogleFit();
    $googleFit->initSession();
    if ($googleFit->isAuthorized()) {
        $app->redirect(HOST);
    }
});
