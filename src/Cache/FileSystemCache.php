<?php
declare(strict_types=1);

namespace App\Cache;

use App\Helper\FileHelper;
use Psr\SimpleCache\CacheInterface;

class FileSystemCache implements CacheInterface
{
    /** @var string */
    private $base;

    public function __construct(string $base)
    {
        $this->base = $base;
    }

    private function getKey($key) :array
    {
        $key = sha1($key);
        $pre = substr($key, 0, 2);
        $key = substr($key, 2);
        return [FileHelper::joinPath($this->base, $pre), $key . '.php'];
    }

    /** @inheritDoc */
    public function get($key, $default = null)
    {
        $file = FileHelper::joinPath(...$this->getKey($key));

        if (!is_file($file)) {
            return $default;
        }

        return require_once $file;
    }

    /** @inheritDoc */
    public function set($key, $value, $ttl = null)
    {
        [$base, $file] = $this->getKey($key);

        if (!is_dir($base)) {
            mkdir($base, 0700, true);
        }

        return (bool)file_put_contents(FileHelper::joinPath($base, $file), "<?php\n\nreturn ".var_export($value, true).";\n");
    }

    /** @inheritDoc */
    public function delete($key)
    {
        $file = FileHelper::joinPath(...$this->getKey($key));

        if (!is_file($file)) {
            return false;
        }

        return unlink($file);
    }

    /** @inheritDoc */
    public function clear()
    {
        foreach (glob(FileHelper::joinPath($this->base, '*', '*')) as $file) {
            if (false === unlink($file)) {
                return false;
            }
        }
        foreach (glob(FileHelper::joinPath($this->base, '*'), GLOB_ONLYDIR) as $file) {
            rmdir($file);
        }
        rmdir($this->base);
        return true;
    }

    /** @inheritDoc */
    public function getMultiple($keys, $default = null)
    {
        foreach ($keys as $key) {
            yield $this->get($key, $default);
        }
    }

    /** @inheritDoc */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            if (false === $this->set($key, $value, $ttl)) {
                return false;
            }
        }
        return true;
    }

    /** @inheritDoc */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            if (false === $this->delete($key)) {
                return false;
            }
        }
        return true;
    }

    /** @inheritDoc */
    public function has($key)
    {
        return is_file(FileHelper::joinPath(...$this->getKey($key)));
    }
}
