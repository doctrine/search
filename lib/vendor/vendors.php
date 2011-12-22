#!/usr/bin/env php
<?php

/*
 * This file is part of the Doctrine\search package.
 *
 * Copied from: https://github.com/FriendsOfSymfony/FOSTwitterBundle/blob/master/vendor/vendors.php
 */
set_time_limit(0);


$vendorDir = __DIR__;
$deps = array(
    array('doctrine-mongodb-odm', 'git://github.com/doctrine/mongodb-odm.git', 'origin/master'),
    array('doctrine-mongodb', 'git://github.com/doctrine/mongodb.git', 'origin/master'),
    array('doctrine-common', 'git://github.com/doctrine/common.git', 'origin/master'),
    array('Buzz', 'git://github.com/kriswallsmith/Buzz.git', 'origin/master'),
);

foreach ($deps as $dep) {
    list($name, $url, $rev) = $dep;

    echo "> Installing/Updating $name\n";

    $installDir = $vendorDir.'/'.$name;
    if (!is_dir($installDir)) {
        system(sprintf('git clone -q %s %s', escapeshellarg($url), escapeshellarg($installDir)));
    }

    system(sprintf('cd %s && git fetch -q origin && git reset --hard %s', escapeshellarg($installDir), escapeshellarg($rev)));
}