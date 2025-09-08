<?php
use Cake\Error\Debugger;

$colors = [
    'include' => '#00000052',
    'extends' => '#cd1c1c7d',
    'import' => '#17c35b8f',
    'includeblock' => '#17c35b8f',
    'embed' => '#4f1ccd7d',
    'sandbox' => 'black',
];
?>
<style type="text/css">
.latte-panel-type {
    border-radius: 2px;
    padding: 2px 4px;
    font-size: 80%;
    color: white;
    font-weight: bold;
    display: inline-block;
    margin-right: .5rem;
}

.latte-panel-php {
    background: #4F5B93;
    color: white;
    border-radius: 79px;
    padding: 1px 4px 3px 4px;
    font-size: 75%;
    font-style: italic;
    font-weight: bold;
    vertical-align: text-top;
    margin-left: 2px;
    text-decoration: none;
}
</style>
<table>
    <thead>
        <tr>
            <th>Count</th>
            <th>Template</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($templates as $item) : ?>
        <tr>
            <td width="50"><?= $item['count'] . 'x' ?></td>
            <td>
                <?php if ($item['referenceType']) : ?>
                    <span style="margin-left: <?= $item['depth'] * 4 ?>ex"></span>└
                    <span class="latte-panel-type" style="background: <?= $colors[$item['referenceType']] ?>">
                        <?= $item['referenceType'] ?>
                    </span>
                <?php endif; ?>
                <?= $item['name']; ?>
                <a href="<?= Debugger::editorUrl($item['phpFile'], 0) ?>" class="latte-panel-php">php</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
