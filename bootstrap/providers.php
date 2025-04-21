<?php

use App\Orchid\Providers\TableServiceProvider;
use App\Providers\ClientsProvider;
use App\Providers\ParserFactoryProvider;
use App\Providers\RepositoryProvider;

return [
    App\Providers\AppServiceProvider::class,
    ClientsProvider::class,
    RepositoryProvider::class,
    TableServiceProvider::class,
    ParserFactoryProvider::class,
];
