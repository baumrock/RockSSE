<?php

namespace ProcessWire;

rocksse()->addStream(
  url: '/examples/progress-with-input',
  loop: function (RockSSE $sse, WireData $input) {
    $step = $input->step;
    $sse->send($input->max);
    sleep(1);
  },
);



// todo: Examples
// using the iterator: creating users (without max, output to textarea)
// creating pages (user input max, using a progressbar)
// trashing pages
// empty the trash
