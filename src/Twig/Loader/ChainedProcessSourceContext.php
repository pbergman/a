<?php
declare(strict_types=1);

namespace App\Twig\Loader;

class ChainedProcessSourceContext implements ProcessSourceContextInterface
{
    /** @var ProcessSourceContextInterface[] */
    private $processors = [];

    public function __construct(ProcessSourceContextInterface ...$processors)
    {
        foreach ($processors as $processor) {
            $this->addProcessors($processor);
        }
    }

    public function process(string $context) :string
    {
        $ctx = '';

        foreach ($this->processors as $processor) {
            $ctx .= $processor->process($context);
        }

        return $ctx;
    }

    /**
     * @param ProcessSourceContextInterface $processor
     */
    public function addProcessors(ProcessSourceContextInterface $processor): void
    {
        $this->processors[] = $processor;
    }
}