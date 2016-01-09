<?php

set('testrunner_docker_machine', 'test');

task('tests:docker', function () {

    $output = "";

    writeln('Getting docker env');
    $docker = get('testrunner_docker_machine');

    try {
        writeln("Create docker-machine");
        $output = runLocally("docker-machine create -d virtualbox $docker");
    }
    catch(Exception $ex) {
        writeln('<comment>' . $ex->getMessage() . '</comment>');
    }

    try {
        writeln("Start docker-machine");
        $output = runLocally("docker-machine start $docker");
    }
    catch(Exception $ex) {
        writeln('<comment>' . $ex->getMessage() . '</comment>');
    }

    // get the IP!
    $output = runLocally("docker-machine env $docker");
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


task('tests:wp', function () {
    writeln('Checking out WordPress from SVN');
    runLocally('cd {{test_dir}} && svn co https://develop.svn.wordpress.org/trunk/ --non-interactive --trust-server-cert  wordpress-develop', 999);
})->desc('Start testing wp');


task('tests:prepp', function () {
    writeln('Config files');
    $ip = get('testrunner_docker_ip');
    runLocally("cd {{test_dir}} && sed 's/docker_ip/$ip/g' wp-tests-config.php > wordpress-develop/wp-tests-config.php");
})->desc('Start testing wp');


task('tests:run', function () {
    writeln('Running phpunit');
    $output = runLocally('cd {{test_dir}}/wordpress-develop && ../../../../vendor/bin/phpunit', 999);
    writeln($output);
})->desc('Start testing wp');


task('tests:cleanup', function () {
    writeln('Killing containers');
    runLocally('rm -Rf vendor/ekandreas/testrunner/wordpress-develop');
    runLocally('cd {{test_dir}} && docker-compose stop && docker-compose rm -f');
})->desc('Cleanup');


task('tests', [
    'tests:docker',
    'tests:wp',
    'tests:prepp',
    'tests:run',
    'tests:cleanup',
])->desc('Initialize tests');
