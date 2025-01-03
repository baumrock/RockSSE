<?php

namespace ProcessWire;

rocksse()->addStream(
  url: '/examples/clock',
  loop: function (RockSSE $sse) {
    $sse->send(date("Y-m-d H:i:s"));
    sleep(1);
  },
);
