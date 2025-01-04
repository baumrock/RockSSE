<?php

namespace ProcessWire;

rocksse()->addStream(
  url: '/examples/create-users',
  loop: function (RockSSE $sse) {
    $u = new User();
    $u->name = 'rocksse-example';
    $u->save();
    $sse->send("Created User #$u {$u->name}");
    sleep(1);
  },
);
