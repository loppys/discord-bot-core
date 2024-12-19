# install

`composer require vengine/discord-core`

# run

```php
<?php

$_SERVER['base.dir'] = __DIR__; // base.dir required for runtime migrations

$configurator = Configurator::create([
    'globalConfigPath' => __DIR__ . '/config/global.config.php',
    'discordOptions' => [
        'intents' => Intents::getAllIntents(),
        'token' => '****',
        'dnsConfig' => '1.1.1.1',
    ],
    'overrideComponents' => [
        'name' => Component::class,
    ],
    'discordEvents' => [
        'ready' => Event::class,
    ]
]);

Core::create($configurator)->run();

```

# global.config

```php
<?php

return [
    'databaseParams' => [
        'dbType' => 'pdo_mysql',
        'dbHost' => 'localhost',
        'dbName' => 'bot',
        'dbLogin' => 'user',
        'dbPassword' => 'password'
    ],
    'symbolCommand' => '~',
    'useNewCommandSystem' => true,
];

```
