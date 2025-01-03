<?php

namespace ProcessWire;

$count = 0;
$max = 100;
rocksse()->addStream(
  url: '/examples/progress',
  loop: function (RockSSE $sse) use (&$count, $max) {
    $count += 10;
    if ($count > $max) return false;
    $sse->send($count);
    sleep(1);
  },
);
