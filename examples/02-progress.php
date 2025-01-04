<?php

namespace ProcessWire;

$count = 0;
$max = 100;
rocksse()->addStream(
  url: '/examples/progress',
  loop: function (RockSSE $sse) use (&$count, $max) {
    $count += 1;
    if ($count > $max) return false;
    $sse->send($count);

    $ms = 200;
    usleep($ms * 1000);
  },
);
