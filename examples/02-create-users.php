<?php

namespace ProcessWire;

rocksse()->addStream(
  url: '/examples/create-users',
  loop: function (RockSSE $sse) {
    $sse->send('TBD');
    sleep(1);
  },
);
