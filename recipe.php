<?php
use Deployer\Deployer;

/* default parameters */
set('docker_host_name', 'tests');
set('test_dir', __DIR__);


task('tests:setup_docker', function () {
    $output = "";
    $docker_name = get('docker_host_name');

    try {
        writeln("Create docker-machine");
        $output = runLocally("docker-machine create -d virtualbox $docker_name");
        writeln("Virtual machine created!");
    } catch (Exception $ex) {
        writeln('<comment>' . $ex->getMessage() . '</comment>');
    }

    try {
        writeln("Start docker-machine");
        $output = runLocally("docker-machine start $docker_name");
        writeln("Waiting for virtual machine to start! (10s)");
        sleep(20);
    } catch (Exception $ex) {
        writeln('<comment>' . $ex->getMessage() . '</comment>');
    }

})->desc('Create/start docker-machine');


task('tests:docker_env', function () {
    $docker_name = get('docker_host_name');

    writeln('Setting env parameters');

    // get the IP!
    $output = runLocally("docker-machine env $docker_name");
    preg_match('/tcp:\/\/(.*?):/', $output, $matches);
    $ip = $matches[1];
    env('testrunner_docker_ip',$ip);

    writeln("<comment>Docker running at $ip</comment>");
    set('testrunner_docker_ip', $ip);

    //$rows = explode("\n", $output);
    //foreach ($rows as $key => $row) {
    //    preg_match('/export\s(.*?)\=\"(.*)\"/', $row, $matches);
    //    if( sizeof($matches)>1 ) {
    //        env($matches[1],$matches[2]);
    //    }
    //}

    $dir = get('test_dir');
    env( 'docker', "cd $dir && " . 'eval "$(docker-machine env ' . $docker_name . ')"' );

})->desc('Sets the Docker environment parameters');


task('tests:rebuild_images', function () {
    writeln('Rebuilding Docker images');
    runLocally("{{docker}} && docker-compose build --no-cache --force-rm", 999);
})->desc('Rebuilds the Docker container images without cache');


task('tests:run_containers', function () {
    writeln('Starting Docker containers');
    runLocally("{{docker}} && docker-compose up -d", 999);
    writeln("Waiting for mysql to spin! (3s)");
    sleep(3);
})->desc('Runs the Docker containers');


task('tests:install', function () {
    $ip = env('testrunner_docker_ip');
    writeln('Running install...');
    runLocally("{{docker}} && docker-compose run web bin/install.sh $ip", 999);
})->desc('Runs the install script within the Docker container instance');


task('tests:run_tests', function () {
    writeln('Running tests...');
    if( !file_exists(__DIR__.'/wordpress/wp-content') ) {
        $ip = env('testrunner_docker_ip');
        writeln('Running install...');
        runLocally("{{docker}} && docker-compose run web bin/install.sh $ip", 999);
    }
    runLocally("{{docker}} && docker-compose run web bin/tests.sh", 999);
})->desc('Runs the tests within the Docker container instance');


task('tests:stop_containers', function () {
    writeln('Stopping containers...');
    runLocally("{{docker}} && docker-compose stop");
})->desc('Stopping the Docker containers');


task('tests:kill_containers', function () {
    writeln('Killing containers...');
    runLocally("{{docker}} && docker-compose rm -f");
})->desc('Removes the Docker container instances');


task('tests:stop_machine', function () {
    writeln('Stopping test machine...');
    $docker_host_name = get('docker_host_name');
    runLocally("docker-machine stop $docker_host_name");
})->desc('Stops the tests virtual machine');


task('tests:kill_machine', function () {
    $docker_host_name = get('docker_host_name');
    writeln('Killing test machine...');
    runLocally("docker-machine rm -f $docker_host_name");
})->desc('Removes the Docker virtual machine');


task('tests:up', [
    'tests:setup_docker',
    'tests:docker_env',
    'tests:run_containers',
    'tests:install',
])->desc('Setting up docker, runs the Docker container instances');

task('tests:rebuild', [
    'tests:setup_docker',
    'tests:docker_env',
    'tests:stop_containers',
    'tests:kill_containers',
    'tests:rebuild_images',
])->desc('Rebuild Docker container images with no cache');

task('tests', [
    'tests:setup_docker',
    'tests:docker_env',
    'tests:run_containers',
    'tests:install',
    'tests:run_tests',
    'tests:stop_containers',
])->desc('Runs the whole install and tests, then stopping container instances');

task('tests:run', [
    'tests:docker_env',
    'tests:run_containers',
    'tests:run_tests',
])->desc('Running tests within already installed images and containers');

task('tests:stop', [
    'tests:docker_env',
    'tests:stop_containers',
    'tests:kill_containers',
    'tests:stop_machine',
])->desc('Stopping and killing containers and then stops the virtual machine');

task('tests:kill', [
    'tests:kill_machine',
])->desc('Stopping and killing containers and removes the virtual machine');
