<div class="row">
    <div class="col-xs-12">
        <h1>Les pas de ta montre Android dans ton Fitbit</h1>
    </div>
</div>

<div class="row text-center">
    <div class="col-xs-12">
        <ul class="list-inline">
            <li>
                <?php if (!empty($fitbit)): ?>
                    <a href="/fitbit-login" class="btn btn-lg btn-primary">Connexion à FitBit</a>
                <?php else: ?>
                    <a href="/fitbit-login" class="text-success btn-lg btn" disabled>Connecté à Fitbit</a>
                <?php endif ?>
            </li>
            <li>
                <?php if (!empty($google)): ?>
                    <a href="/google-login" class="btn btn-lg btn-primary">Connexion à Google Fit</a>
                <?php else: ?>
                    <a href="/google-login" class="text-success btn-lg btn" disabled>Connecté à Google Fit</a>
                <?php endif ?>
            </li>
        </ul>
    </div>
</div>
<div class="row text-center">
    <div class="col-xs-12">
        <?php if (!empty($intro)): ?>
            <p>Une fois connecté aux deux comptes, le site ajoute directement les pas comptés avec ta montre Android Wear sur ton compte Fitbit sans rien te demander. Rien à foutre.</p>
        <?php endif ?>
        <?php if (!empty($done)): ?>
            <h2>Synchro terminée !</h2>
        <?php endif ?>
    </div>
</div>
