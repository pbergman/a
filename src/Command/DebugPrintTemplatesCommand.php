<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\TaskMeta;
use App\Plugin\PluginConfig;
use App\Twig\Loader\PluginLoader;
use App\Twig\NodeVisitor\MacroFormatTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DebugPrintTemplatesCommand extends Command
{

    use MacroFormatTrait;

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
            ->addOption('template', 't', InputOption::VALUE_REQUIRED, 'only print this template or templates from this namespace')
            ->addOption('macros', 'm', InputOption::VALUE_NONE, 'also print available macros for template');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tasks = $this->config->getTasks();

        if (null !== $template = $input->getOption('template')) {
            $output->writeln($this->format($template, $input->getOption('macros')));
        } else {
            foreach ($tasks as $task => $info) {
                $this->write($output, $task, $info, $tasks, $input->getOption('macros'));
            }
        }
    }

    private function fmtRef(TaskMeta $meta) :string
    {
        return sprintf('%s::%s::%s[%d]', $meta->getPlugin(), $meta->getTask(), $meta->getSection(), $meta->getIndex());
    }

    private function write(OutputInterface $output, string $task, array $info, array $tasks, bool $macros)
    {
        $output->writeln($this->format($task, $macros));
        foreach (['pre', 'exec', 'post'] as $section) {
            if (empty($info[$section])) {
                continue;
            }
            $output->writeln($this->format($task . '::' . $section, $macros));
            for ($i = 0, $c = count($info[$section]); $i < $c; $i++) {
                $output->writeln($this->format($task . '::' . $section . '[' . $i . ']', $macros, $this->fmtRef($tasks[$task][$section][$i]->getMeta())));
            }
        }
    }

    private function format($task, bool $macros, $ref = '') :string
    {
        $spacing = static function($line) {
            return '              ' . $line;
        };
        $prefix = static function(string $text) use ($spacing) :string {
            return implode("\n", array_map($spacing, explode("\n", $text)));
        };

        $extra = null;
        if ($macros) {
            if ([] !== $macros = $this->config->getMacros($this->getTaskName($task))) {
                $extra = "<comment>macros:</comment>\n";
                foreach ($macros as $name => $macro) {
                    $extra .= "        <comment>$name:</comment>\n";
                    $extra .= $prefix($this->createMacros($name, $macro)) . "\n";
                }
            }

            if ([] !== $macros = $this->config->getMacros()) {
                if (null === $extra) {
                    $extra = "<comment>macros:</comment>\n";
                }
                foreach ($macros as $name => $macro) {
                    $extra .= "        <comment>$name:</comment>\n";
                    $extra .= $prefix($this->createMacros($name, $macro)) . "\n";
                }
            }
        }

        return sprintf('  <comment>template:</comment>   %s
  <comment>reference:</comment>  %s
  <comment>value:</comment>      %s               
  %s',
            $task,
            $ref,
            ltrim($prefix($this->loader->getSourceContext($task)->getCode())),
            $extra
        );
    }
}
