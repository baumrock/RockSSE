# RockSSE

## Usage

```php
rocksse()->add('/clock', function(RockSSE $sse) {
  $sse->send(date("Y-m-d H:i:s"));
  sleep(1);
});
```