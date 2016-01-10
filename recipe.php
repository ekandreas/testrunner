<?php
use Deployer\Deployer;

task('testrunner:docker', function () {

    $output = "";

    $docker_name = get('docker_host_name');
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
    runLocally($output);

    writeln('Running: eval "$(docker-machine env ' . $docker_name . ')"');

    runLocally('eval "$(docker-machine env ' . $docker_name . ')"');
    env('test_dir', __DIR__);

    runLocally("cd {{test_dir}} && docker-compose up -d",999);

})->desc('Starting docker');

task('testrunner:rebuild_docker', function () {
    writeln('Rebuilding Docker php image');
    runLocally("cd {{test_dir}} && docker-compose build --no-cache --force-rm",999);
})->desc('Start testing wp');

task('testrunner:wp', function () {
    writeln('Running install...');
    $ip = get('testrunner_docker_ip');
    runLocally("cd {{test_dir}} && docker-compose run php $ip",999);
})->desc('Start testing wp');

task('testrunner:cleanup', function () {
    writeln('Killing containers');
    runLocally("rm -Rf {{test_dir}}/wp");
    runLocally("cd {{test_dir}} && docker-compose stop && docker-compose rm -f");
})->desc('Cleanup');


task('testrunner', [
    'testrunner:docker',
    'testrunner:wp',
    'testrunner:cleanup',
])->desc('Initialize tests');

task('testrunner:rebuild', [
    'testrunner:docker',
    'testrunner:rebuild_docker',
    'testrunner:cleanup',
])->desc('Initialize tests');
