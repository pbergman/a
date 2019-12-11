<?php
declare(strict_types=1);

namespace App\DependencyInjection\Dumper;

use App\Twig\UndefinedFilterCallback;
use App\Twig\UndefinedFunctionCallback;

class XmlServiceDumper
{

    private $parameters = [
        'A_CACHE_TWIG' => false,
        'A_CACHE' => false,
        'A_DEBUG' => false,
    ];

    private $base;
    private $version;
    private $encoding;
    private $indent;


    private $prototypes = [
        [
            'namespace' => 'App\\',
            'resource' => './src/*',
            'exclude' => './src/{Node,Model,Container,Container.php}',
        ]
    ];

    public function __construct(string $base, $version = '1.0', $encoding = 'utf-8', $indent = true)
    {
        $this->base = $base;
        $this->version = $version;
        $this->encoding = $encoding;
        $this->indent = $indent;
    }

    public function dump(string $file) :void
    {
        $writer = new \XMLWriter();
        $writer->openUri($file);
        $writer->setIndent($this->indent);
        $writer->startDocument($this->version, $this->encoding);
        $this->writeContianer($writer);
        $this->writeParameters($writer);
        $this->writeServices($writer);
        $writer->endElement();
        $writer->endDocument();
        $writer->flush();
    }

    private function writeContianer(\XMLWriter $writer) :void
    {
        $writer->startElement('container');
        $this->writeAttributes($writer, [
            'xmlns' => 'http://symfony.com/schema/dic/services',
            'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            'xmlns:schemaLocation' => 'https://symfony.com/schema/dic/services/services-1.0.xsd',
        ]);
    }

    private function writeParameters(\XMLWriter $writer) :void
    {
        $writer->startElement('parameters');
        foreach ($this->parameters as $key => $value) {
            $writer->startElement('parameter');
            $this->writeAttributes($writer, ['key' => $key]);
            $writer->text($this->toString($value));
            $writer->endElement();
        }
        $writer->endElement();
    }

    private function writeServices(\XMLWriter $writer)
    {
        $writer->startElement('services');

            $writer->startElement('defaults');
            $this->writeAttributes($writer, ['autowire' => true, 'autoconfigure' => true, 'public' => false]);
            $writer->endElement();

            foreach ($this->prototypes as $prototype) {
                $writer->startElement('prototype');
                foreach (['resource', 'exclude'] as $key) {
                    if (null !== $prototype[$key] && 0 === strpos($prototype[$key], './')) {
                        $prototype[$key] = $this->base . substr($prototype[$key], 1);
                    }
                }
                $this->writeAttributes($writer, $prototype, true);
                $writer->endElement();
            }

            $this->autoTagElement($writer, 'App\Twig\Loader\ProcessSourceContextInterface', 'app.twig.process_source_context');
            $this->autoTagElement($writer, 'App\Plugin\PluginInterface', 'app.plugin');

            $this->startServiceElement($writer, 'App\DependencyInjection\TypeFixEnvVarProcessor');
            $writer->startElement('tag');
            $this->writeAttributes($writer, ['name' => 'container.env_var_processor']);
            $writer->endElement();
            $writer->endElement();

            $this->startServiceElement($writer, 'App\CommandLoader\CommandLoader');
            $this->writeAttributes($writer, ['public' => true]);
            $writer->endElement();

            $this->startServiceElement($writer, 'App\Twig\Loader\ProcessSourceContextInterface');
            $this->writeAttributes($writer, ['alias' => 'App\Twig\Loader\ChainedProcessSourceContext']);
            $writer->endElement();

            $this->autoTagElement($writer, 'Symfony\Component\Console\Command\Command', 'app.command', ['public' => true]);
            $this->autoTagElement($writer, 'Twig\Extension\ExtensionInterface', 'twig.extension');

            $this->startServiceElement($writer, 'Twig\Loader\LoaderInterface');
            $this->writeAttributes($writer, ['alias' => 'App\Twig\Loader\PluginLoader']);
            $writer->endElement();

            $this->startServiceElement($writer, 'Twig\Environment');
            $writer->startElement('argument');
            $this->writeAttributes($writer, ['key' => '$options', 'type' => 'collection']);

            $args = [
                'strict_variables' => true,
                'autoescape' => false,
                'auto_reload' => true,
                'cache' => '%env(empty_is_false:A_CACHE_TWIG)%',
                'debug' => '%env(bool:A_DEBUG)%',
            ];

            foreach ($args as $key => $value) {
                $writer->startElement('argument');
                $this->writeAttributes($writer, ['key' => $key]);
                $writer->text($this->toString($value));
                $writer->endElement();
            }

            $writer->endElement();

            $calls = [
                'registerUndefinedFunctionCallback' => UndefinedFunctionCallback::class,
                'registerUndefinedFilterCallback' => UndefinedFilterCallback::class
            ];

            foreach ($calls as $method => $service) {
                $writer->startElement('call');
                $this->writeAttributes($writer, ['method' => $method]);
                $writer->startElement('argument');
                $this->writeAttributes($writer, ['type' => 'service', 'id' => $service]);
                $writer->endElement();
                $writer->endElement();
            }

            $writer->endElement();

        $writer->endElement();
    }

    private function autoTagElement(\XMLWriter $writer, string $id, string $tag, array $attributes = [], array $tagAttributes = [])
    {
        $writer->startElement('instanceof');
        $this->writeAttributes($writer, ['id' => $id]);
        if (!empty($attributes)) {
            $this->writeAttributes($writer, $attributes);
        }
        $writer->startElement('tag');
        $this->writeAttributes($writer, ['name' => $tag]);
        if (!empty($tagAttributes)) {
            $this->writeAttributes($writer, $tagAttributes);
        }
        $writer->endElement();
        $writer->endElement();

    }

    private function startServiceElement(\XMLWriter $writer, string $id)
    {
        $writer->startElement('service');
        $this->writeAttributes($writer, ['id' => $id]);
    }

    private function writeAttributes(\XMLWriter $writer, array $attributes = [], $skipEmpty = false)
    {
        foreach ($attributes as $key => $value) {
            if ($skipEmpty && null === $value) {
                continue;
            }
            $writer->startAttribute($key);
            if (null !== $value) {
                $writer->text($this->toString($value));
            }
            $writer->endAttribute();
        }
    }

    private function toString($v) :string
    {
        if (is_bool($v)) {
            return $v ? 'true' : 'false';
        }

        return (string)$v;
    }

    public function addParameter(string $key, $value): void
    {
        $this->parameters[$key] = $value;
    }

    public function addPrototype(string $namespace, string $resource, string $exclude = null): void
    {
        $this->prototypes[] = [
            'namespace' => $namespace,
            'resource' => $resource,
            'exclude' => $exclude,
        ];
    }
}