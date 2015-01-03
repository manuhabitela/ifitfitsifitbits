<div class="row">
    <div class="col-xs-12">
        <div class="page-header">
            <h1 class="text-center">
                <img src="/img/google-fit.png" title="Your Google Fit &amp; Android Wear" alt="Google Fit icon">
                <img src="/img/steps.png" title="collected footsteps" alt="Footsteps">
                <span title="in">&rarr;</span>
                <img src="/img/fitbit.png" title="your Fitbit account" alt="Fitbit icon">
                <span title="equals">=</span>
                <img src="/img/party.png" title="PARTAYYY" alt="Yolo">
            </h1>
        </div>
    </div>
</div>

<div class="row text-center">
    <div class="col-xs-12">
        <ul class="list-inline">
            <li>
                <?php if (!empty($fitbit)): ?>
                    <a href="/fitbit-login" class="btn btn-lg btn-primary">Connect to FitBit</a>
                <?php else: ?>
                    <a href="#" class="btn btn-lg btn-success btn-inverted btn-almost-disabled">Connected to Fitbit
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    </a>
                <?php endif ?>
            </li>
            <li>
                <?php if (!empty($google)): ?>
                    <a href="/google-login" class="btn btn-lg btn-danger">Connect to Google</a>
                <?php else: ?>
                    <a href="#" class="btn btn-lg btn-success btn-inverted btn-almost-disabled">Connected to Google Fit
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span></a>
                    </a>
                <?php endif ?>
            </li>
        </ul>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <?php if (isset($toDo)): ?>
        <div class="panel panel-info panel-to-import">
            <div class="panel-heading">
                <h3 class="panel-title">Google Fit data to import in Fitbit:</h3>
            </div>
            <div class="panel-body">
                <?php if (!empty($toDo)): ?>
                <form action="" method="post">
                    <table class="table center-block"> <!-- yeah -->
                        <?php foreach ($toDo as $set): ?>
                            <tr>
                                <td>
                                     <input type="checkbox" checked name="sets[]" value='<?php echo json_encode([
                                        'date' => $set['date']->getTimestamp(),
                                        'duration' => $set['duration'],
                                        'steps' => $set['steps'] ]) ?>'>
                                </td>
                                <td><?php echo $set['date']->format('m/d - h:ia') ?></td>
                                <?php $min = round(ceil($set['duration']/1000/60)); ?>
                                <td class="text-right"><?php echo $min." ".($min > 1 ? "minutes" : "minute") ?></td>
                                <td class="text-right"><?php echo $set['steps'] ?> steps</td>
                            </tr>
                        <?php endforeach ?>
                    </table>
                    <button class="btn btn-lg btn-success">Import selection</button>
                </form>
                <?php else: ?>
                <p>Nothing! It seems everything has been imported already. Maybe. I think. Whatever.</p>
                <p class="x-small">If you think something is missing, it may be a sync issue between your phone and Google account.</p>
                <?php endif ?>
            </div>
        </div>
        <hr>
        <?php endif ?>

        <?php if (!empty($done)): ?>
        <div class="panel panel-success panel-import-results">
            <div class="panel-heading">
                <h3 class="panel-title">Success!</h3>
            </div>
            <div class="panel-body">
                <p><a href="https://www.fitbit.com/activities" target="_blank">Everything</a> has been imported.</p>
                <p class="text-center"><a href="/">Go back</a></p>
            </div>
        </div>
        <hr>
        <?php endif ?>

        <div class="panel panel-warning panel-intro">
            <div class="panel-heading">
                <h3 class="panel-title">What is this madness?</h3>
            </div>
            <div class="panel-body">
                <p>This website lets you import your <em>not-older-than-a-week</em> Android Wear tracked steps in your Fitbit account.</p>
                <ol>
                    <li>Login with both your Google and Fitbit accounts</li>
                    <li>(Un)check (un)desired stuff and click the green thingy</li>
                    <li><em>Voilà!</em></li>
                </ol>
                <p class="x-small">this is quick and dirty stuff, sorry if it doesn't work for you. And I certainly won't help if that's the case 'cause I'm the laziest</p>
            </div>
        </div>
    </div>
</div>
