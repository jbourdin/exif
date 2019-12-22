<?php

namespace Jbourdin\Exif;

require __DIR__ . '/../../../vendor/autoload.php';

use Symfony\Component\Console\Application;

$app = new Application('exif');
$app->add(new ExifRewriteCommand());

$app->run();
