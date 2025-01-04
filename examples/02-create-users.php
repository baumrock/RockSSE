<?php

namespace ProcessWire;

use RockSSE\Stream;

rocksse()->addStream(
  url: '/examples/create-users',
  loop: function (Stream $stream) {
    $iterator = $stream->iterator;
    $u = new User();
    $u->name = 'rocksse-example-' . uniqid();
    $u->save();
    $stream->send($iterator->num . ": Created User #$u {$u->name}");
  },
);
