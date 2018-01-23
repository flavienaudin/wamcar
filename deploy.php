<?php

namespace Deployer;

use Symfony\Component\Console\Input\InputArgument;

require 'recipe/symfony3.php';

inventory('hosts.yml');

set('ssh_type', 'native');
set('ssh_multiplexing', true);

// Configuration
set('repository', 'git@gitlab.novaway.net:novaproject/wamcar');
set('branch', 'develop');

add('shared_files', [
    'app/config/config_prod.yml',
    'web/app_dev.php',
    '.env',
]);
add('shared_dirs', [
    'web/uploads',
    'web/media',
]);
add('writable_dirs', [
    'web/uploads',
    'web/media',
]);

before('deploy:vendors', 'deploy:install_composer');
before('deploy:symlink', 'database:migrate');
after('deploy', 'upload:assets');
after('deploy', 'api:documentation');

after('deploy', 'reload:php-fpm');
after('rollback', 'reload:php-fpm');

// Servers
set('default_stage', 'demo');

/**
 * Install PHAR version of composer
 */
task('deploy:install_composer', function () {
    run("cd {{release_path}} && curl -sS https://getcomposer.org/installer | {{bin/php}}");
})->desc('downloading composer');

/**
 * Upload API documentation
 */
task('api:documentation', function () {
    upload('web/openapi.json', '{{release_path}}/web/openapi.json');
})->desc('generating API documentation');

/**
 * Upload assets built on script init
 */
task('upload:assets', function () {
    upload('web/assets/bundle/', '{{release_path}}/web/assets/bundle/');
    upload('web/assets/fonts/', '{{release_path}}/web/assets/fonts/');
    upload('web/assets/images/', '{{release_path}}/web/assets/images/');
})->desc('uploading assets');

/**
 * Populate the elasticsearch index
 */
task('elasticsearch:populate', function () {
    run("cd {{release_path}} && {{bin/php}} -d memory_limit=-1 bin/console wamcar:populate:vehicle_info -e=prod");
});

/**
 * Reload php-fpm to ensure symlink to proper release
 */
task('reload:php-fpm', function () {
    run('sudo /usr/sbin/service php71-php-fpm reload');
})->onHosts('demo', 'review');

task('cleanup', function () {
    $releases = get('releases_list');

    $keep = get('keep_releases');

    while ($keep > 0) {
        array_shift($releases);
        --$keep;
    }

    foreach ($releases as $release) {
        run("rm -rf {{deploy_path}}/releases/$release");
    }

    run("cd {{deploy_path}} && if [ -e release ]; then rm release; fi");
    run("cd {{deploy_path}} && if [ -h release ]; then rm release; fi");

})->desc('Cleaning up old releases')->onStage('staging');
