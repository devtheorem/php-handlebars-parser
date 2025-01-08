<?php

namespace DevTheorem\Handlebars\Phlexer;

readonly class Token
{
    public function __construct(
        public string $name,
        public string $text,
        public int $line,
    ) {}
}
