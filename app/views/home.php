<div class="row">
    <div class="col-xs-12">
        <div class="page-header">
            <h1 class="text-center">Les pas de ta montre Android dans ton Fitbit</h1>
        </div>
    </div>
</div>

<div class="row text-center">
    <div class="col-xs-12">
        <ul class="list-inline">
            <li>
                <?php if (!empty($fitbit)): ?>
                    <a href="/fitbit-login" class="btn btn-lg btn-primary">Connexion à FitBit</a>
                <?php else: ?>
                    <a href="#" class="btn btn-lg btn-success btn-inverted btn-almost-disabled">Connecté à Fitbit
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    </a>
                <?php endif ?>
            </li>
            <li>
                <?php if (!empty($google)): ?>
                    <a href="/google-login" class="btn btn-lg btn-primary">Connexion à Google Fit</a>
                <?php else: ?>
                    <a href="#" class="btn btn-lg btn-success btn-inverted btn-almost-disabled">Connecté à Google Fit
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span></a>
                    </a>
                <?php endif ?>
            </li>
        </ul>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <p>Une fois connecté aux deux comptes, le site ajoute directement les pas comptés avec ta montre Android Wear sur ton compte Fitbit sans rien te demander. Rien à foutre. Par contre c'est que les pas comptés dans les sept derniers jours. On va pas tout vous faire non plus :/</p>

        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title">Résultat de la synchro</h3>
            </div>
            <div class="panel-body">
                <?php if (!empty($done) && !empty($synced)): ?>
                    <dl class="dl-horizontal">
                    <?php foreach ($synced as $set): ?>
                        <dt>Le <?php echo $set['date']->format('d/m à H:i') ?></dt>
                        <dd><?php echo $set['steps'] ?> pas.</dd>
                    <?php endforeach ?>
                    </dl>
                <?php endif ?>
                <?php if (!empty($done) && empty($synced)): ?>
                    <p>Tout a déjà été importé dans Fitbit.</p>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>
