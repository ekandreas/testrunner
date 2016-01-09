<?php

use Deployer\Deployer;

task('phpunit:docker', function () {

    $output = "";

    writeln('Getting docker env');

    $server = \Deployer\Task\Context::get()->getServer();

    $docker_name = $server->getConfiguration()->getName();
    env('testrunner_docker_ip', $docker_name);

    $docker_ip = $server->getConfiguration()->getHost();
    env('testrunner_docker_name',$docker_ip);

    try {
        writeln("Create docker-machine");
        $output = runLocally("docker-machine create -d virtualbox $docker_name");
    }
    catch(Exception $ex) {
        writeln('<comment>' . $ex->getMessage() . '</comment>');
    }

    try {
        writeln("Start docker-machine");
        $output = runLocally("docker-machine start $docker_name");
    }
    catch(Exception $ex) {
        writeln('<comment>' . $ex->getMessage() . '</comment>');
    }

    // get the IP!
    $output = runLocally("docker-machine env $docker_name");
    preg_match('/tcp:\/\/(.*?):/', $output, $matches);
    $ip = $matches[1]; 
    writeln("<comment>Docker running at $ip</comment>");
    set('testrunner_docker_ip',$ip);

    writeln('Setting env parameters');
    runLocally('eval "$(docker-machine env test)"');

    writeln('Starting docker container');
    env('test_dir', __DIR__);
    runLocally("cd {{test_dir}} && docker-compose up -d");

    writeln('Wait for mysql to spin up...');
    sleep(4);

})->desc('Starting docker');


task('phpunit:wp', function () {
    writeln('Checking out WordPress from SVN');
    runLocally('cd {{test_dir}} && svn co https://develop.svn.wordpress.org/trunk/ --non-interactive --trust-server-cert  wordpress-develop', 999);
})->desc('Start testing wp');


task('phpunit:prepp', function () {
    writeln('Config files');
    $ip = get('testrunner_docker_ip');
    runLocally("cd {{test_dir}} && sed 's/docker_ip/$ip/g' wp-tests-config.php > wordpress-develop/wp-tests-config.php");
})->desc('Start testing wp');


task('phpunit:run', function () {
    writeln('Running phpunit');
    $output = runLocally('cd {{test_dir}}/wordpress-develop && ../../../../vendor/bin/phpunit', 999);
    writeln($output);
})->desc('Start testing wp');


task('phpunit:cleanup', function () {
    writeln('Killing containers');
    runLocally('rm -Rf vendor/ekandreas/testrunner/wordpress-develop');
    runLocally('cd {{test_dir}} && docker-compose stop && docker-compose rm -f');
})->desc('Cleanup');


task('phpunit', [
    'phpunit:docker',
    'phpunit:wp',
    'phpunit:prepp',
    'phpunit:run',
    'phpunit:cleanup',
])->desc('Initialize phpunit tests');
