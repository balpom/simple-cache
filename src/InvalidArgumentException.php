<?php

declare(strict_types=1);

namespace Balpom\SimpleCache;

use Psr\SimpleCache\InvalidArgumentException as InvalidArgument;

class InvalidArgumentException extends \Exception implements InvalidArgument
{

}