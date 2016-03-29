#!/usr/bin/env php
<?php
chdir(__DIR__ . '/../../');
include 'vendor/autoload.php';
die(\PreCommit\Handler::run(basename($_SERVER['argv'][0])));
