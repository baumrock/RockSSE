<?php

namespace RockSSE;

use ProcessWire\RockSSE;
use ProcessWire\WireData;

use function ProcessWire\rocksse;

class Stream extends WireData
{
  const done = 'ROCKSSE:STREAM-DONE';

  public $iterator;
  public $init;
  public $loop;

  /**
   * Sleep after each iteration (in ms)
   */
  public float $sleep = 0;

  /**
   * Reference to RockSSE module
   * @var RockSSE
   */
  public $sse;

  public function __construct(
    public string $url,
  ) {
    $this->sse = rocksse();
    $this->iterator = $this->sse->newIterator();
    $this->loop = function () {};
    $this->init = function () {};
  }

  public function __debugInfo()
  {
    return [
      'url' => $this->url,
      'iterator' => $this->iterator,
    ];
  }

  public function done(): void
  {
    $this->send(self::done);
    die();
  }

  /**
   * Send SSE message to client
   */
  public function send(mixed $msg): void
  {
    if (!is_string($msg)) $msg = json_encode($msg);
    echo "data: $msg\n\n";
    echo str_pad('', 8186) . "\n";
    flush();
  }

  public function serve(): void
  {
    set_time_limit(0);
    header("Cache-Control: no-cache");
    header("Content-Type: text/event-stream");

    // if we have an init callback we call it now
    ($this->init)($this);

    // start endless loop for the stream
    $iterator = $this->iterator;
    while (true) {
      // stop loop when connection is aborted
      if (connection_aborted()) break;

      // execute the callback and get result
      $result = ($this->loop)($this);

      // if the callback returned FALSE we break out of the endless loop
      // and die() via done() method
      if ($result === false) $this->done();

      // die() when done
      if ($iterator->isDone()) $this->done();

      // increment iterator
      $iterator->next();

      // sleep?
      if ($this->sleep > 0) usleep($this->sleep * 1000);
    }
  }
}
