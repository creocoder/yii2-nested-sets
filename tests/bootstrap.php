<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@tests', __DIR__);

new \yii\console\Application([
    'id' => 'unit',
    'basePath' => __DIR__ . '/..',
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite::memory:',
        ],
    ],
]);

Yii::$app->db->open();
$lines = explode(';', file_get_contents(__DIR__ . '/migrations/sqlite.sql'));

foreach ($lines as $line) {
    if (trim($line) !== '') {
        Yii::$app->db->pdo->exec($line);
    }
}
