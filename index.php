<?php // @codingStandardsIgnoreFile
/**
 * This file is part of Pico. It's copyrighted by the contributors recorded
 * in the version control history of the file, available from the following
 * original location:
 *
 * <https://github.com/picocms/pico-composer/blob/master/index.php>
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

// load dependencies
// pico-composer MUST be installed as root package
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once(__DIR__ . '/vendor/autoload.php');
} else {
    die("Cannot find 'vendor/autoload.php'. Run `composer install`.");
}

$config['theme'] = 'clean-blog'; 
$config['pages_order_by'] = 'date';
$config['pages_order'] = 'desc';

$config['author'] = 'Your Name';
$config['facebook'] = 'https://www.facebook.com/YourPage';
$config['twitter'] = '@username';
$config['pagination_limit'] = 8;


// instance Pico
$pico = new Pico(
    __DIR__,    // root dir
    'config/',  // config dir
    'plugins/', // plugins dir
    'themes/'   // themes dir
);

// override configuration?
$pico->setConfig($config);

// run application
echo $pico->run();
