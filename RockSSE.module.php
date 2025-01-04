<?php

namespace ProcessWire;

use RockSSE\Iterator;
use RockSSE\Stream;

function rocksse(): RockSSE
{
  return wire()->modules->get('RockSSE');
}

require_once __DIR__ . '/Stream.php';
require_once __DIR__ . '/Iterator.php';
/**
 * @author Bernhard Baumrock, 03.01.2025
 * @license Licensed under MIT
 * @link https://www.baumrock.com
 */
class RockSSE extends WireData implements Module, ConfigurableModule
{
  private $exampleData = false;
  public $loadExamples = false;
  private $streams = [];

  public function init()
  {
    $this->loadExampleData();
    $this->devTools();
    wire()->addHook('/rocksse/(.*)', $this, 'serveStream');
  }

  public function addStream(
    string $url,
    callable $loop,
    callable $init = null,
  ): void {
    if (array_key_exists($url, $this->streams)) {
      throw new WireException("Stream already exists: $url");
    }

    $stream = new Stream($url);
    $stream->loop = $loop;
    if (is_callable($init)) $stream->init = $init;
    $this->streams[$url] = $stream;
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

  public function __debugInfo()
  {
    return [
      'streams' => $this->streams,
    ];
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

  public function getStream($url): Stream|false
  {
    return $this->streams[$url] ?? false;
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

  public function newIterator(): Iterator
  {
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
    wire()->config->js('rocksse-done', Stream::done);
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

  public function serveStream(HookEvent $event)
  {
    $url = '/' . $event->arguments(1);
    $stream = $this->getStream($url);
    if (!$stream) throw new Wire404Exception("Stream not found: $url");
    $stream->serve();
  }
}
