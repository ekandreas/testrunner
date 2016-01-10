<?php
use Deployer\Deployer;

/* default parameters */
set('docker_host_name', 'tests');
set('test_dir', __DIR__);

task('tests:setup_docker', function () {

    $output = "";

    $docker_name = get('docker_host_name');
    writeln('Getting docker env...');

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

    // get the IP!
    $output = runLocally("docker-machine env $docker_name");
    preg_match('/tcp:\/\/(.*?):/', $output, $matches);
    $ip = $matches[1];
    writeln("<comment>Docker running at $ip</comment>");
    set('testrunner_docker_ip', $ip);

    writeln('Setting env parameters');
    runLocally($output);

    writeln('Running: eval "$(docker-machine env ' . $docker_name . ')"');

    runLocally('eval "$(docker-machine env ' . $docker_name . ')"');

})->desc('Create/start docker-machine and sets the environments');

task('tests:rebuild_images', function () {
    $test_dir = get('test_dir');
    writeln('Rebuilding Docker images');
    runLocally("cd $test_dir && docker-compose build --no-cache --force-rm", 999);
})->desc('Rebuilds the Docker container images without cache');

task('tests:run_containers', function () {
    $test_dir = get('test_dir');
    writeln('Starting Docker containers');
    runLocally("cd $test_dir && docker-compose up -d", 999);
    writeln("Waiting for mysql to spin! (3s)");
    sleep(3);
})->desc('Runs the Docker containers');

task('tests:install', function () {
    $test_dir = get('test_dir');
    $ip = env('testrunner_docker_ip');
    writeln('Running install...');
    runLocally("cd $test_dir && docker-compose run web bin/install.sh $ip", 999);
})->desc('Runs the install script within the Docker container instance');

task('tests:run_tests', function () {
    $test_dir = get('test_dir');
    writeln('Running tests...');
    runLocally("cd $test_dir && docker-compose run web bin/run.sh", 999);
})->desc('Runs the tests within the Docker container instance');

task('tests:stop_containers', function () {
    $test_dir = get('test_dir');
    writeln('Stopping containers...');
    runLocally("cd $test_dir && docker-compose stop");
})->desc('Stopping the Docker containers');

task('tests:kill_containers', function () {
    $test_dir = get('test_dir');
    writeln('Killing containers...');
    runLocally("cd $test_dir && docker-compose rm -f");
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
    'tests:rebuild_images',
    'tests:run_containers',
    'tests:install',
])->desc('Setting up docker, rebuilds the images and runs the Docker container instances');

task('tests', [
    'tests:setup_docker',
    'tests:rebuild_images',
    'tests:run_containers',
    'tests:install',
    'tests:run_tests',
    'tests:stop_containers',
])->desc('Runs the whole install and tests, then stopping container instances');

task('tests:run', [
    'tests:run_containers',
    'tests:run_tests',
])->desc('Running tests within already installed images and containers');

task('tests:stop', [
    'tests:stop_containers',
    'tests:kill_containers',
    'tests:stop_machine',
])->desc('Stopping and killing containers and then stops the virtual machine');

task('tests:kill', [
    'tests:kill_machine',
])->desc('Stopping and killing containers and removes the virtual machine');
