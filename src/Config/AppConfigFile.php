<?php
declare(strict_types=1);

namespace App\Config;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class AppConfigFile
{
    /** @var InputInterface  */
    private $input;

    const OPTION_LONG = 'config';
    const OPTION_SHORT = 'c';


    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    private function getDefaultConfigFile()
    {
        return getcwd() . '/a.yaml';
    }

    public function getAppConfigFile() :FileResource
    {
        static $resource;

        if (!$resource) {
            $resource = new FileResource($this->input->getParameterOption($this->getRawOption(), self::getDefaultConfigFile(), true));
        }

        return $resource;
    }

    public function getInputOption()
    {
        return new InputOption(self::OPTION_LONG, self::OPTION_SHORT, InputOption::VALUE_REQUIRED, 'The location of the application config file', $this->getDefaultConfigFile());
    }

    private function getRawOption() :array
    {
        return [
            '--' . self::OPTION_LONG,
            '-' . self::OPTION_SHORT,
        ];
    }
}