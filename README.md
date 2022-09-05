# Apenas um Hello World

## Como usar?
```php
<?php

require __DIR__.'/vendor/autoload.php';

use Basico\Teste;

echo Teste::world();
echo (new Teste())->hello();
```