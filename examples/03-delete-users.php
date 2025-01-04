<?php

namespace ProcessWire;

use RockSSE\Iterator;

rocksse()->addStream(
  url: '/examples/delete-users',
  init: function ($iterator) {
    $userIDs = wire()->users->findIDs('name^=rocksse-example,sort=-id');
    $iterator->userIDs = $userIDs;
  },
  loop: function (RockSSE $sse, Iterator $iterator) {
    // get user to delete
    // $u = wire()->users->get($userID);
    $count = count($iterator->userIDs);
    $sse->send($iterator->num . ': ' . $count);

    sleep(1);
  },
);
