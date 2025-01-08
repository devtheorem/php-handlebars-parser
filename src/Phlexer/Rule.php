<?php

namespace DevTheorem\Handlebars\Phlexer;

readonly class Rule
{
    /**
     * @var string[]
     */
    public array $startConditions;

    /**
     * @param string[] $startConditions,
     * @param \Closure(): ?string $handler
     */
    public function __construct(
        array $startConditions,
        public string $pattern,
        public \Closure $handler,
    ) {
        $this->startConditions = $startConditions ?: [Phlexer::INITIAL_STATE];
    }

    public function hasStartCondition(string $condition): bool
    {
        return in_array($condition, $this->startConditions, true);
    }
}
