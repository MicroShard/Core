<?php

use MicroShard\Core\Configuration;
use MicroShard\Core\Container;
use MicroShard\Core\Services\DatabaseAdapter;

$configuration = new Configuration([
    'env' => 'ENV',
    'db_host' => 'DB_HOST',
    'db_port' => 'DB_PORT',
    'db_name' => 'DB_NAME',
    'db_user' => 'DB_USER',
    'db_pass' => 'DB_PASS',
    'api_token' => 'API_TOKEN'
]);
$configuration->set('base_dir', __DIR__);

$container = new Container($configuration);
$container->addServiceDefinition('db', function (Container $container){
    return new DatabaseAdapter(
        $container->getConfiguration()->get('db_host'),
        $container->getConfiguration()->get('db_user'),
        $container->getConfiguration()->get('db_pass'),
        $container->getConfiguration()->get('db_name')
    );
});

if ($configuration->get('env') == 'dev') {
    require_once __DIR__ . '/bootstrap.dev.php';
}

return $container;