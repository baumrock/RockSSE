<?php

namespace ProcessWire;

use RockSSE\Stream;

rocksse()->addStream(
  url: '/examples/clock',

  // init callback (when stream is initialised)
  init: function (Stream $stream) {
    // sleep for 1000ms after each loop
    $stream->sleep = 1000;
  },

  // loop callback (called on every iteration)
  loop: function (Stream $stream) {
    $stream->send(date("Y-m-d H:i:s"));
  },
);
