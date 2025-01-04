<?php

namespace RockSSE;

use ProcessWire\WireData;

class Iterator extends WireData
{
  private int $index;
  private int $num;

  public function __construct()
  {
    $this->index = 0;
    $this->num = 1;
  }

  public function __get($name)
  {
    return match ($name) {
      'index' => $this->index,
      'num' => $this->num,
      default => parent::__get($name),
    };
  }

  public function next(): void
  {
    $this->index++;
    $this->num++;
  }
}
