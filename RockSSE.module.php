<?php

namespace ProcessWire;

use RockSSE\Iterator;

function rocksse(): RockSSE
{
  return wire()->modules->get('RockSSE');
}

/**
 * @author Bernhard Baumrock, 03.01.2025
 * @license Licensed under MIT
 * @link https://www.baumrock.com
 */
class RockSSE extends WireData implements Module, ConfigurableModule
{
  private $exampleData = false;
  public $loadExamples = false;

  public function init()
  {
    $this->loadExampleData();
    $this->devTools();
  }

  public function addStream(
    string $url,
    callable $loop,
    callable $init = null,
  ): void {
    wire()->addHookAfter($url, function () use ($loop, $init) {
      set_time_limit(0);
      header("Cache-Control: no-cache");
      header("Content-Type: text/event-stream");

      // initialize the iterator
      $iterator = $this->newIterator();

      // if we have an init callback we call it now
      if (is_callable($init)) $init($iterator);

      // start endless loop for the stream
      while (true) {
        // stop loop when connection is aborted
        if (connection_aborted()) break;

        // execute the callback and get result
        $result = $loop($this, $iterator);

        // if the callback returned FALSE we break out of the endless loop
        if ($result === false) break;

        $iterator->next();
      }
    });
  }

  public function addExamples(InputfieldWrapper $inputfields): void
  {
    $fs = new InputfieldFieldset();
    $fs->label = 'Examples';
    $fs->icon = 'life-ring';
    $inputfields->add($fs);

    // add checkbox to load url hooks for examples
    $fs->add([
      'type' => 'checkbox',
      'name' => 'loadExamples',
      'label' => 'Load Examples',
      'checked' => $this->loadExamples ? 'checked' : '',
      'notes' => 'This will add hooks for all SSE examples. Be sure to remove the checkbox after playing around with the examples!',
    ]);

    $data = $this->loadExampleData() ?? [];
    foreach ($data as $file => $raw) {
      $data = is_array($raw)
        ? (new WireData())->setArray($raw)
        : (new WireData());
      $fs->add([
        'type' => 'markup',
        'label' => basename($file),
        'value' => $data->value,
        'notes' => $data->notes,
        'description' => $data->description,
      ]);
    }
  }

  private function devTools(): void
  {
    if (!wire()->config->debug) return;
    if (!wire()->user->isSuperuser()) return;
    if (!wire()->modules->isInstalled('RockMigrations')) return;
    $rm = rockmigrations();

    // create minified JS file
    $rm->minify(__DIR__ . '/RockSSE.js', __DIR__ . '/dst/RockSSE.min.js');
  }

  /**
   * Config inputfields
   * @param InputfieldWrapper $inputfields
   */
  public function getModuleConfigInputfields($inputfields)
  {
    $this->addExamples($inputfields);
    return $inputfields;
  }

  public function newIterator(): Iterator
  {
    require_once __DIR__ . '/Iterator.php';
    return new Iterator();
  }

  public function ready(): void
  {
    $p = wire()->page;

    // ----- only regular requests -----
    if (wire()->config->ajax) return;
    if (wire()->config->external) return;

    // ----- only admin requests -----
    if ($p->template != 'admin') return;

    // add RockSSE.js file
    $url = wire()->config->urls->root;
    wire()->config->scripts->add($url . 'site/modules/RockSSE/dst/RockSSE.min.js');

    // show warning when example hooks are attached to not forget disabling them
    if ($this->loadExamples) {
      if (wire()->input->get->name !== 'RockSSE') {
        $url = wire()->config->urls->admin . 'module/edit?name=RockSSE&collapse_info=1';
        $link = "<a href=$url>RockSSE module settings</a>";
        wire()->warning(
          "RockSSE example-hooks are still attached - please disable them in the $link!",
          Notice::allowMarkup
        );
      }
    }
  }

  private function loadExampleData()
  {
    if (!$this->loadExamples) return;
    if ($this->exampleData) return $this->exampleData;
    $arr = [];
    $examples = glob(__DIR__ . '/examples/*.php');
    $buttons = wire()->files->render(__DIR__ . '/examples/_buttons.html');
    foreach ($examples as $file) {
      $data = wire()->files->render($file);
      if (!is_array($data)) $data = [];
      if (!array_key_exists('value', $data)) {
        // load markup from .html file
        $html = substr($file, 0, -4) . '.html';
        if (is_file($html)) {
          $markup = wire()->files->render($html);
          $markup = str_replace('{buttons}', $buttons, $markup);
          $data['value'] = $markup;
        }
      }
      $arr[$file] = $data;
    }
    $this->exampleData = $arr;
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
}
