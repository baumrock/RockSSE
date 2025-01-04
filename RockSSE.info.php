<?php

namespace ProcessWire;

$info = [
  'title' => 'RockSSE',
  'version' => json_decode(file_get_contents(__DIR__ . "/package.json"))->version,
  'summary' => 'RockSSE brings the power of Server Sent Events to ProcessWire',
  'autoload' => 99999,
  'singular' => true,
  'icon' => 'refresh',
  'requires' => [
    'PHP>=8.1',
  ],
];
