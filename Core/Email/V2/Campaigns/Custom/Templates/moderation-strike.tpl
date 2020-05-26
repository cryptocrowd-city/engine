<tr>
    <td>
        <p>
            <?= $vars['translator']->translate('Your') ?> <?= $vars['translator']->translate($vars['type']) ?>
            <?= $vars['translator']->translate('has been') ?>
            <b><?= $vars['translator']->translate($vars['action']) ?></b> <?= $vars['translator']->translate('because it was determined to violate our Content Policy.') ?>
            <?= $vars['translator']->translate('To appeal this decision to a jury of your peers, please') ?> <a href="https://www.minds.com/settings/reported-content" target="_blank" <?php echo $emailStyles->getStyles('m-link'); ?>><?= $vars['translator']->translate('log in') ?></a> <?= $vars['translator']->translate('and submit an appeal to a jury of your peers from the Reported Content section of your settings.') ?>
        </p>
    </td>
</tr>
<tr>
    <td>
        <p>
            <?= $vars['translator']->translate('More can be learned about how the Appeals process works') ?> <a href="https://www.minds.com/content-policy" target="_blank" <?php echo $emailStyles->getStyles('m-link'); ?>><?= $vars['translator']->translate('here') ?></a>.
        </p>
    </td>
</tr>
