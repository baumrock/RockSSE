<?php

namespace RockSSE;

use ProcessWire\WireData;

class Iterator extends WireData
{
  public int $index = 0;
  public int $num = 1;
  public float $progress = 0;
  public int $total = 0;

  public function __debugInfo()
  {
    return [
      'index' => $this->index,
      'num' => $this->num,
      'progress' => $this->progress,
      'total' => $this->total,
    ];
  }

  public function isDone(): bool
  {
    if ($this->total < 1) return false;
    return $this->num >= $this->total;
  }

  public function next(): void
  {
    $this->index++;
    $this->num++;
    $this->updateProgress();
  }

  public function setTotal(int $total): self
  {
    $this->total = $total;
    $this->updateProgress();
    return $this;
  }

  private function updateProgress(): void
  {
    if (!$this->total) return;
    $this->progress = round($this->num / $this->total, 6);
  }
}
