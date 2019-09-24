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
        foreach ($this->processors as $processor) {
            $context .= $processor->process($context);
        }

        return $context;
    }

    /**
     * @param ProcessSourceContextInterface $processor
     */
    public function addProcessors(ProcessSourceContextInterface $processor): void
    {
        $this->processors[] = $processor;
    }
}