<div class="panel-heading">
    <h3 class="panel-title">Google Fit data to import in Fitbit:</h3>
</div>
<div class="panel-body">
    <?php if (!empty($toDo)): ?>
    <form action="" method="post">
        <input type="hidden" name="timezones" value="">
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
        <button type="submit" class="btn btn-lg btn-success"></button>
    </form>
    <?php else: ?>
    <p>Nothing! It seems everything has been imported already. Maybe. I think. Whatever.</p>
    <p class="x-small">If you think something is missing, it may be a sync issue between your phone and Google account.</p>
    <?php endif ?>
</div>
