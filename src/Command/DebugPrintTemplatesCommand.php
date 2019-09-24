<?php
namespace App\Command;

use App\Config\AppConfig;
use App\Model\TaskMeta;
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
                    /** @var TaskMeta $meta */
                    $meta = $tasks[$task][$section][$i]->getMeta();
                    $ns .= '[' . $i . ']';
                    $ctx = $this->loader->getSourceContext($ns)->getCode();
                    $ref = sprintf('%s::%s::%s[%d]', $meta->getPlugin(), $meta->getTask(), $meta->getSection(), $meta->getIndex());
                    $table->addRow([$ns, $ref, trim($ctx)]);
                }
            }
        }
        $table->render();
    }
}
