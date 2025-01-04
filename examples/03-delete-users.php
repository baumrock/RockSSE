<?php

namespace ProcessWire;

use RockSSE\Stream;

rocksse()->addStream(
  url: '/examples/delete-users',
  init: function (Stream $stream) {
    $stream->sleep = 100;
    $iterator = $stream->iterator;
    $userIDs = wire()->users->findIDs('name^=rocksse-example,sort=-id');
    $total = count($userIDs);
    if (!$total) {
      $stream->send('No users to delete');
      $stream->done();
    }
    $iterator->setTotal($total);
    $stream->iterator->userIDs = $userIDs;
  },
  loop: function (Stream $stream) {
    $iterator = $stream->iterator;

    // get user
    $id = $iterator->userIDs[$iterator->index];
    $u = wire()->users->get($id);
    if (!$u->id) return;

    // delete user
    $u->delete();
    $stream->send("Deleted user #$u " . $u->name);

    // send iterator to client
    // it has all the data needed to show a progress bar
    $stream->send($iterator);
  },
);
