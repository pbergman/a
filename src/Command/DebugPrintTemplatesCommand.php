<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\TaskMeta;
use App\Plugin\PluginConfig;
use App\Twig\Loader\PluginLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DebugPrintTemplatesCommand extends Command
{
    /** @var PluginConfig  */
    private $config;
    /** @var PluginLoader  */
    private $loader;

    protected static $defaultName = 'debug:print-templates';

    public function __construct(PluginConfig $config, PluginLoader $loader)
    {
        parent::__construct();
        $this->config = $config;
        $this->loader = $loader;
    }


    protected function configure()
    {
        $this
            ->setDescription('Print templates.')
            ->setHelp(<<<EOH
This will print all available template names, references and template values (or only the matching when the 
template option is provided). 

The output will be something like:
              
  template:   env:ssh-copy-id::exec[0]
  reference:  env::env.ssh-copy-id::exec[0]
  value:      ssh-copy-id {{ _self.flags(input) }} {{ env_get('ssh') }}
  
Where template is the name which can be used to include etc., reference can help to determine where it was 
originated and value is the raw template context.

The reference name is build based on the following logic:
   
   PLUGIN_NAME::TASK::GROUP[INDEX]
      
When reference value is empty it will mean that it is an virtual template and is not an task on its own but
just an reference to other templates. If the PLUGIN_NAME is 0 it will mean that the template originated from
the a.yaml of current working dir or the one provided with the config option.
EOH
)
            ->addOption('template', 't', InputOption::VALUE_REQUIRED, 'only print this template or templates from this namespace');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tasks = $this->config->getTasks();

        if (null !== $template = $input->getOption('template')) {
            $this->writeTemplate($output, $template, $tasks);
        } else {
            foreach ($tasks as $task => $info) {
                $this->write($output, $task, $info, $tasks);
            }
        }
    }

    private function fmtRef(TaskMeta $meta) :string
    {
        return sprintf('%s::%s::%s[%d]', $meta->getPlugin(), $meta->getTask(), $meta->getSection(), $meta->getIndex());
    }

    private function writeTemplate(OutputInterface $output, string $template, array $tasks) :void
    {
        $name = $this->loader->getSourceContext($template)->getName();
        foreach ($tasks as $task => $info) {
            if ($task === $name) {
                $this->write($output, $task, $info, $tasks);
                return ;
            }
            foreach (['pre', 'exec', 'post'] as $section) {
                if (empty($info[$section])) {
                    continue;
                }
                $sub = $task . '::' . $section;
                if ($sub === $name) {
                    $this->write($output, $task, $info, $tasks);
                    return;
                }
                for ($i = 0, $c = count($info[$section]); $i < $c; $i++) {
                    $sub .= '[' . $i . ']';
                    if ($sub === $name) {
                        $output->writeln($this->format($sub, $this->fmtRef($tasks[$task][$section][$i]->getMeta())));
                        return;
                    }
                }
            }
        }
    }

    private function write(OutputInterface $output, string $task, array $info, array $tasks)
    {
        $output->writeln($this->format($task));
        foreach (['pre', 'exec', 'post'] as $section) {
            if (empty($info[$section])) {
                continue;
            }
            $output->writeln($this->format($task . '::' . $section));
            for ($i = 0, $c = count($info[$section]); $i < $c; $i++) {
                $output->writeln($this->format($task . '::' . $section . '[' . $i . ']', $this->fmtRef($tasks[$task][$section][$i]->getMeta())));
            }
        }
    }

    private function format($task, $ref = '') :string
    {
        $prefix = static function($line) {
            return '              ' . $line;
        };
        $code = implode("\n", array_map($prefix, explode("\n", $this->loader->getSourceContext($task)->getCode())));

        return sprintf('  <comment>template:</comment>   %s
  <comment>reference:</comment>  %s
  <comment>value:</comment>      %s               
            ',
            $task,
            $ref,
            ltrim($code)
        );
    }
}
