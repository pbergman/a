<?php
declare(strict_types=1);

namespace App\Tests\Helper;

use App\Exception\FindInPathException;
use App\Helper\FileHelper;
use PHPUnit\Framework\TestCase;

class FileHelperTest extends TestCase
{
    public function testFindInPath()
    {
        $expected = trim(shell_exec('which true'));
        $this->assertEquals($expected, FileHelper::findInPath('true'));
        $this->expectException(FindInPathException::class);
        FileHelper::findInPath('truesss');
    }

    public function testJoinPath()
    {
        $this->assertEquals(FileHelper::joinPath('/foo/', '/bar/'), '/foo/bar/');
    }
}