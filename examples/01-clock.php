<?php

namespace ProcessWire;

use RockSSE\Stream;

rocksse()->addStream(
  url: '/examples/clock',
  init: function (Stream $stream) {
    $stream->sleep = 1000;
  },
  loop: function (Stream $stream) {
    $stream->send(date("Y-m-d H:i:s"));
  },
);
