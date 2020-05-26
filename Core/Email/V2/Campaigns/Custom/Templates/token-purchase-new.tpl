<tr>
    <td>
        <p>
            <?= $vars['translator']->translate('Your purchase of Minds Tokens is currently being processed. We will notify you once the transaction is complete.') ?>
        </p>
    </td>
</tr>
<tr>
    <td>
        <p>
            <div><?= $vars['translator']->translate('Date') ?>: <?php echo $vars['date']; ?></div>
            <div><?= $vars['translator']->translate('Token Amount') ?>: <?php echo $vars['amount']; ?></div>
        </p>
    </td>
</tr>
<tr>
    <td>
        <p>
            <?= $vars['translator']->translate('For any issues, please contact us at') ?>
            <a href="mailto:info@minds.com" <?php echo $emailStyles->getStyles('m-link'); ?>>
                info@minds.com</a>.
        </p>
    </td>
</tr>
