<?php
namespace App\Command;

use App\Config\AppConfig;
use App\Twig\Loader\PluginLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugPrintTemplatesCommand extends Command
{
    /** @var AppConfig  */
    private $config;
    /** @var PluginLoader  */
    private $loader;

    protected static $defaultName = 'debug:print-templates';

    public function __construct(AppConfig $config, PluginLoader $loader)
    {
        parent::__construct();
        $this->config = $config;
        $this->loader = $loader;
    }


    protected function configure()
    {
        $this->setDescription('Print all the available templates.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setStyle('compact');
        $table->setHeaders(['name', 'reference', 'content']);
        $tasks = $this->config->getTasks();

        foreach ($tasks as $task => $info) {
            $table->addRow([$task, '', $this->loader->getSourceContext($task)->getCode()]);
            foreach (['pre', 'exec', 'post'] as $section) {
                if (empty($info[$section])) {
                    continue;
                }
                $ns = $task . '::' . $section;
                $table->addRow([$ns, '', trim($this->loader->getSourceContext($ns)->getCode())]);
                for ($i = 0, $c = count($info[$section]); $i < $c; $i++) {
                    $ns .= '[' . $i . ']';
                    $ctx = $this->loader->getSourceContext($ns)->getCode();
                    $pos = strpos($ctx, '#}');
                    $meta = json_decode(substr($ctx, 3, $pos-3), true);
                    $ref = sprintf('%s::%s::%s[%d]', $meta['plugin'], $meta['task'], $meta['section'], $meta['index']);
                    $ctx = substr($ctx, $pos+2);
                    $table->addRow([$ns, $ref, trim($ctx)]);
                }

            }

        }
        $table->render();
    }
}
