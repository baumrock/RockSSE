<?php

namespace ProcessWire;

rocksse()->addStream(
  url: '/examples/clock',
  loop: function (RockSSE $sse) {
    $sse->send(date("Y-m-d H:i:s"));
    sleep(1);
  },
);

return [
  'description' => 'This example shows a simple clock that uses the SSE stream to show the current server time every second.',
];
