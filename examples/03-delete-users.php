<?php

namespace ProcessWire;

use RockSSE\Stream;

rocksse()->addStream(
  url: '/examples/delete-users',
  init: function (Stream $stream) {
    $userIDs = wire()->users->findIDs('name^=rocksse-example,sort=-id,limit=3');
    $iterator = $stream->iterator;
    $iterator->setTotal(count($userIDs));
    $stream->iterator->userIDs = $userIDs;
  },
  loop: function (Stream $stream) {
    $iterator = $stream->iterator;
    $iterator->context = 'foo bar';
    $stream->send($iterator);
  },
);
