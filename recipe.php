<?php
use Deployer\Deployer;

/* default parameters */
set('docker_host_name', 'default');
set('test_dir', __DIR__);
set('temp_dir', '/tmp/testrunner');
set('wp_branch', '');

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

    $output = runLocally("docker-machine env $docker_name");
    preg_match('/tcp:\/\/(.*?):/', $output, $matches);
    $ip = $matches[1];
    env('testrunner_docker_ip',$ip);

    writeln("<comment>Docker running at $ip</comment>");
    set('testrunner_docker_ip', $ip);

    $dir = get('test_dir');
    env( 'docker', "cd $dir && " . 'eval "$(docker-machine env ' . $docker_name . ')"' );

    $dirv = get('temp_dir');
    env( 'dockerv', "cd $dirv && " . 'eval "$(docker-machine env ' . $docker_name . ')"' );

})->desc('Sets the Docker environment parameters');


task('tests:rebuild_images', function () {
    writeln('Rebuilding Docker images');
    runLocally("{{ docker }} && rm -Rf wordpress-develop");
    runLocally("{{docker}} && docker build -t testrunner_php .", 999);
})->desc('Rebuilds the Docker container images without cache');


task('tests:mysql_up', function () {
    writeln('Starting Docker containers');
    testrunner_restart_mysql();
})->desc('Runs the Docker containers');


task('tests:install_wp', function () {
    $ip = env('testrunner_docker_ip');
    $test_dir = get('test_dir');

    writeln('Running install...');

    runLocally("{{ docker }} && rm -Rf wordpress-develop");

    $branch = get('wp_branch');
    if(empty($branch)) {
        $api_url = 'http://api.wordpress.org/core/version-check/1.7/';
        $version = file_get_contents($api_url);
        preg_match('/\"current\":\"(.*?)\"/', $version, $matches);
        $branch = $matches[1];
    }

    runLocally("{{docker}} && docker run \
        -v $test_dir:/usr/src/testrunner \
        --rm --name testrunner_php \
        testrunner_php bin/install.sh $ip $branch", 999);

})->desc('Runs the install script within the Docker container instance');


task('tests:run_tests', function () {
    $test_dir = get('test_dir');
    writeln('Running tests...');
    if( !file_exists($test_dir.'/wordpress-develop/src/wp-content') ) {
        writeln('<error>Folder wordpress-develop is missing, please run dep tests:install and try again!</error>');
        exit();        
    }

    if( !testrunner_is_port_ok('3306') ) {
        testrunner_restart_mysql();
    }
    
    $plugin_dir = realpath(__DIR__.'/../../../');
    runLocally("{{docker}} && docker run \
        -v $test_dir:/usr/src/testrunner \
        -v $plugin_dir:/usr/src/plugin \
        --rm --name testrunner_php \
        testrunner_php bin/tests.sh", 999);

    $result = file_get_contents($test_dir.'/wordpress-develop/src/wp-content/plugins/theplugin/testresult.txt');
    preg_match('/(OK\s\()/', $result, $matches);
    if( sizeof($matches)>1 ) {
        writeln('<fg=green>'.$result.'</fg=green>');
    } else {
        writeln('<fg=red>'.$result.'</fg=red>');
    }
})->desc('Runs the tests within the Docker container instance');


task('tests:stop_containers', function () {
    writeln('Stopping containers...');
    testrunner_stop_container('testrunner_mysql');
})->desc('Stopping the Docker containers');


task('tests:kill_containers', function () {
    writeln('Killing containers...');
    runLocally("{{ docker }} && rm -Rf wordpress-develop");
    testrunner_kill_container('testrunner_mysql');
    testrunner_kill_container('testrunner_php');
})->desc('Removes the Docker container instances');


task('tests:stop_machine', function () {
    writeln('Stopping test machine...');
    $docker_host_name = get('docker_host_name');
    testrunner_stop_container('testrunner_mysql');
})->desc('Stops the tests virtual machine');


task('tests:kill_machine', function () {
    $docker_host_name = get('docker_host_name');
    runLocally("{{ docker }} && rm -Rf wordpress-develop");
    if( askConfirmation('Are you sure?')) {
        writeln('Killing test machine...');
        runLocally("docker-machine rm -f $docker_host_name");
    }
})->desc('Removes the Docker virtual machine');


task('tests:up', [
    'tests:setup_docker',
    'tests:docker_env',
    'tests:mysql_up',
    'tests:install_wp',
])->desc('Setting up docker, runs the Docker container instances');

task('tests:rebuild', [
    'tests:setup_docker',
    'tests:docker_env',
    'tests:stop_containers',
    'tests:kill_containers',
    'tests:rebuild_images',
])->desc('Rebuild Docker container images with no cache');

task('tests:install', [
    'tests:docker_env',
    'tests:install_wp',
])->desc('Rebuild Docker container images with no cache');

task('tests', [
    'tests:setup_docker',
    'tests:docker_env',
    'tests:mysql_up',
    'tests:install_wp',
    'tests:run_tests',
    'tests:stop_containers',
])->desc('Runs the whole install and tests, then stopping container instances');

task('tests:run', [
    'tests:docker_env',
    'tests:run_tests',
])->desc('Running tests within already installed images and containers');

task('tests:stop', [
    'tests:docker_env',
    'tests:stop_containers',
    'tests:kill_containers',
])->desc('Stopping and killing containers and then stops the virtual machine');

task('tests:kill', [
    'tests:stop_machine',
    'tests:kill_machine',
])->desc('Stopping and killing containers and removes the virtual machine');


function testrunner_kill_container($name) {
    try {
        $output = runLocally("{{docker}} && docker inspect $name");
        runLocally("{{docker}} && docker rm -f $name");
    } catch(Exception $ex) {
    }
}

function testrunner_stop_container($name) {
    try {
        $output = runLocally("{{docker}} && docker inspect $name");
        runLocally("{{docker}} && docker stop $name");
    } catch(Exception $ex) {
    }
}

function testrunner_wait_port($waiting_message, $port) {
    write($waiting_message);
    while( !testrunner_is_port_ok($port)  ) {
        sleep(1);
        write('.');
    }
    writeln('<fg=green>Up!</fg=green>');
}

function testrunner_is_port_ok($port) {
    $ip = env('testrunner_docker_ip');
    $result = false;
    try {
        $result = @fsockopen($ip, $port, $errno, $errstr, 5); 
    } catch(Exception $ex) {
        $result = false;
    }
    return $result;
}

function testrunner_restart_mysql() {
    $ip = env('testrunner_docker_ip');
    testrunner_kill_container('testrunner_mysql');
    runLocally("{{docker}} && \
        docker run \
        -d --env 'MYSQL_ROOT_PASSWORD=root' --env 'MYSQL_DATABASE=wp' \
        --name='testrunner_mysql' -p $ip:3306:3306 \
        mysql:5.6", 999);
    testrunner_wait_port('Waiting for mysql to start','3306');
}
