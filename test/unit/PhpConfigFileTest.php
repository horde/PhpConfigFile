<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile\Test\Unit;

use Horde\PhpConfigFile\PhpConfigFile;
use PHPUnit\Framework\TestCase;
use Stringable;

/**
 * @coversNothing
 */
class PhpConfigFileTest extends TestCase
{
    public function testReadEmptyConfigFile(): void
    {
        $file = new PhpConfigFile(
            configFilePath: __DIR__ . '/../fixtures/EmptyConfigFile.php',
            footer: '/* Custom footer */'
        );
        $file->readConfigFile();
        $this->assertEquals('', $file->getContent());
    }

    public function testReadEmptyConfigFileWithComment(): void
    {
        $file = new PhpConfigFile(
            configFilePath: __DIR__ . '/../fixtures/EmptyConfigFileWithComment.php',
        );
        $file->readConfigFile();
        $this->assertEquals('// To be done', $file->getContent());
    }

    public function testReadConfigFileDefaultsAndOverridesWorkAsExpected(): void
    {
        $file = new PhpConfigFile(
            configFilePath: __DIR__ . '/../fixtures/WithPreHeaderAndPostFooterContent.php',
            header: '/* Begin Horde Config File - do not edit */',
            footer: '/* End Horde Config File - do not edit */'
        );
        $file->readConfigFile();
        $allContent = $file->parseContent(area: 'content');
        //$betweenContent = $file->parseContent(area: 'contentBetweenHeaderAndFooter');
        $this->assertArrayHasKey('footer_only', $allContent, 'Variable footer_only not found in content');
        $this->assertEquals('not me', $allContent['me'], 'Default variable before footer not overwritten by content');
        $this->assertEquals('not you the other one', $allContent['you'], 'Variable from content not overwritten by footer');
        $this->assertEquals('overwritten', $allContent['something'], 'Variable something not overwritten by footer in content');

    }

    public function testReadConfigFileIgnoringBeforeHeaderAndAfterFooter():void
    {
        $file = new PhpConfigFile(
            configFilePath: __DIR__ . '/../fixtures/WithPreHeaderAndPostFooterContent.php',
            header: '/* Begin Horde Config File - do not edit */',
            footer: '/* End Horde Config File - do not edit */'
        );
        $file->readConfigFile();
        $betweenString = $file->getContentBetweenHeaderAndFooter();
        $this->assertStringContainsString('// Managed content goes here. Comments in here are lost.', $betweenString, 'Comment content not found');
        $betweenContent = $file->parseContent(area: 'contentBetweenHeaderAndFooter');
        $this->assertArrayHasKey('you', $betweenContent, 'Variable  not found in content');
        $this->assertEquals('not you the other one', $betweenContent['you'], 'Variable you not found in content');
        $this->assertEquals('set', $betweenContent['something'], 'Variable something overwritten by footer in content');
        $this->assertArrayNotHasKey('me', $betweenContent, 'Variable me found in content but only exists before header and after footer');
        
    }

    public function testNestedModernArrayFormat(): void
    {
        $file = new PhpConfigFile(
            configFilePath: __DIR__ . '/../fixtures/NestedModernArrayFormat.php',
        );
        $file->readConfigFile();
        $allContent = $file->parseContent();
        // @phpstan-ignore-next-line
        $this->assertNotEmpty($allContent['config']['key3']['subkey1']);
        // @phpstan-ignore-next-line
        $this->assertEquals('subsubvalue1', $allContent['config']['key3']['subkey2']['subsubkey1']);
    }

    public function testClassicHordeFormat(): void
    {
        $file = new PhpConfigFile(
            configFilePath: __DIR__ . '/../fixtures/ClassicHordeFormat.php',
        );
        $file->readConfigFile();
        $allContent = $file->parseContent();
        // @phpstan-ignore-next-line
        $this->assertNotEmpty($allContent['conf']['sql']['hostspec']);
        // @phpstan-ignore-next-line
        $this->assertTrue($allContent['conf']['readwritesplit']);
    }

    public function testWriteEmptyFileWithHeaderAndFooter(): void
    {
        $file = new PhpConfigFile(
            configFilePath: 'deleteme',
            header: '/* Begin */',
            footer: '/* End */'
        );
        $file->writeConfigFile([]);
        $this->assertFileExists('deleteme');
        $contentString = (string)file_get_contents('deleteme');
        $this->assertStringContainsString('/* Begin */', $contentString, 'Header not found');
        $this->assertStringContainsString('/* End */', $contentString, 'Footer not found');
        $contentValues = $file->parseContent();
        $this->assertEmpty($contentValues, 'Content not empty');
        unlink('deleteme');
    }

    public function testReadFailure(): void
    {
        $this->expectException(\RuntimeException::class);
        $file = new PhpConfigFile(
            configFilePath: 'doesnotexist',
        );
        $file->readConfigFile();
    }
}
