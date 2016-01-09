<?php

use Deployer\Deployer;

task('testrunner:docker', function () {

    $output = "";

    $server = \Deployer\Task\Context::get()->getServer();

    $docker_name = $server->getConfiguration()->getName();
    env('testrunner_docker_ip', $docker_name);

    $docker_ip = $server->getConfiguration()->getHost();
    env('testrunner_docker_name',$docker_ip);

    writeln('Getting docker env');

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
    //sleep(1);

})->desc('Starting docker');


task('testrunner:wp', function () {
    writeln('Setting up WordPress');
    env('deploy_path','/var/www/html/wp');
    run('mkdir -p wp && cd wp && ls -l', 999);
    die;
})->desc('Start testing wp');


task('testrunner:prepp', function () {
    writeln('Config files');
    $ip = get('testrunner_docker_ip');
    //runLocally("cd {{test_dir}} && sed 's/docker_ip/$ip/g' wp-tests-config.php > wp/wp-tests-config.php");
})->desc('Start testing wp');


task('testrunner:run', function () {
    writeln('Running phpunit');
    //$output = runLocally('cd {{test_dir}}/wp && ../../../../vendor/bin/phpunit', 999);
    //writeln($output);
})->desc('Start testing wp');


task('testrunner:cleanup', function () {
    writeln('Killing containers');
    runLocally('rm -Rf vendor/ekandreas/testrunner/wp');
    runLocally('cd {{test_dir}} && docker-compose stop && docker-compose rm -f');
})->desc('Cleanup');


task('testrunner', [
    'testrunner:docker',
    'testrunner:wp',
    'testrunner:prepp',
    'testrunner:run',
    'testrunner:cleanup',
])->desc('Initialize tests');
