<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Integration\Maker;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 * @group maker
 * @requires PHP 8.1
 */
final class MakeStoryTest extends MakerTestCase
{
    /**
     * @test
     * @dataProvider storyNameProvider
     */
    public function can_create_story(string $name): void
    {
        $tester = new CommandTester((new Application(self::bootKernel()))->find('make:story'));

        $this->assertFileDoesNotExist(self::tempFile('src/Story/FooBarStory.php'));

        $tester->execute(['name' => $name]);

        $this->assertFileExists(self::tempFile('src/Story/FooBarStory.php'));
        $this->assertSame(<<<EOF
            <?php

            /*
             * This file is part of the zenstruck/foundry package.
             *
             * (c) Kevin Bond <kevinbond@gmail.com>
             *
             * For the full copyright and license information, please view the LICENSE
             * file that was distributed with this source code.
             */

            namespace App\\Story;

            use Zenstruck\\Foundry\\Story;

            final class FooBarStory extends Story
            {
                public function build(): void
                {
                    // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
                }
            }

            EOF
            , \file_get_contents(self::tempFile('src/Story/FooBarStory.php'))
        );
    }

    /**
     * @test
     * @dataProvider storyNameProvider
     */
    public function can_create_story_interactively(string $name): void
    {
        $tester = new CommandTester((new Application(self::bootKernel()))->find('make:story'));

        $this->assertFileDoesNotExist(self::tempFile('src/Story/FooBarStory.php'));

        $tester->setInputs([$name]);
        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertFileExists(self::tempFile('src/Story/FooBarStory.php'));
        $this->assertStringContainsString('Note: pass --test if you want to generate stories in your tests/ directory', $output);
        $this->assertSame(<<<EOF
            <?php

            /*
             * This file is part of the zenstruck/foundry package.
             *
             * (c) Kevin Bond <kevinbond@gmail.com>
             *
             * For the full copyright and license information, please view the LICENSE
             * file that was distributed with this source code.
             */

            namespace App\\Story;

            use Zenstruck\\Foundry\\Story;

            final class FooBarStory extends Story
            {
                public function build(): void
                {
                    // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
                }
            }

            EOF
            , \file_get_contents(self::tempFile('src/Story/FooBarStory.php'))
        );
    }

    /**
     * @test
     * @dataProvider storyNameProvider
     */
    public function can_create_story_in_test_dir(string $name): void
    {
        $tester = new CommandTester((new Application(self::bootKernel()))->find('make:story'));

        $this->assertFileDoesNotExist(self::tempFile('tests/Story/FooBarStory.php'));

        $tester->execute(['name' => $name, '--test' => true]);

        $this->assertFileExists(self::tempFile('tests/Story/FooBarStory.php'));
        $this->assertSame(<<<EOF
            <?php

            /*
             * This file is part of the zenstruck/foundry package.
             *
             * (c) Kevin Bond <kevinbond@gmail.com>
             *
             * For the full copyright and license information, please view the LICENSE
             * file that was distributed with this source code.
             */

            namespace App\\Tests\\Story;

            use Zenstruck\\Foundry\\Story;

            final class FooBarStory extends Story
            {
                public function build(): void
                {
                    // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
                }
            }

            EOF
            , \file_get_contents(self::tempFile('tests/Story/FooBarStory.php'))
        );
    }

    /**
     * @test
     * @dataProvider storyNameProvider
     */
    public function can_create_story_in_test_dir_interactively(string $name): void
    {
        $tester = new CommandTester((new Application(self::bootKernel()))->find('make:story'));

        $this->assertFileDoesNotExist(self::tempFile('tests/Story/FooBarStory.php'));

        $tester->setInputs([$name]);
        $tester->execute(['--test' => true]);

        $output = $tester->getDisplay();

        $this->assertFileExists(self::tempFile('tests/Story/FooBarStory.php'));
        $this->assertStringNotContainsString('Note: pass --test if you want to generate stories in your tests/ directory', $output);
        $this->assertSame(<<<EOF
            <?php

            /*
             * This file is part of the zenstruck/foundry package.
             *
             * (c) Kevin Bond <kevinbond@gmail.com>
             *
             * For the full copyright and license information, please view the LICENSE
             * file that was distributed with this source code.
             */

            namespace App\\Tests\\Story;

            use Zenstruck\\Foundry\\Story;

            final class FooBarStory extends Story
            {
                public function build(): void
                {
                    // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
                }
            }

            EOF
            , \file_get_contents(self::tempFile('tests/Story/FooBarStory.php'))
        );
    }

    /**
     * @return iterable<array{string}>
     */
    public static function storyNameProvider(): iterable
    {
        yield ['FooBar'];
        yield ['FooBarStory'];
    }

    /**
     * @test
     * @dataProvider namespaceProvider
     * @param array<string, mixed> $commandOptions
     */
    public function can_customize_namespace(string $filePath, array $commandOptions, string $expectedFullNamespace): void
    {
        $tester = new CommandTester((new Application(self::bootKernel()))->find('make:story'));

        $this->assertFileDoesNotExist(self::tempFile($filePath));

        $tester->execute(['name' => 'FooBar'] + $commandOptions);

        $this->assertFileExists(self::tempFile($filePath));
        self::assertStringContainsString("namespace {$expectedFullNamespace}", \file_get_contents(self::tempFile($filePath))); // @phpstan-ignore-line
    }

    /**
     * @return iterable<array{string, array<string, mixed>, string}>
     */
    public static function namespaceProvider(): iterable
    {
        yield ['src/Some/Namespace/FooBarStory.php', ['--namespace' => 'Some\\Namespace'], 'App\\Some\\Namespace'];
        yield ['src/Some/Namespace/FooBarStory.php', ['--namespace' => 'App\\Some\\Namespace'], 'App\\Some\\Namespace'];
        yield ['tests/Some/Namespace/FooBarStory.php', ['--namespace' => 'Some\\Namespace', '--test' => true], 'App\\Tests\\Some\\Namespace'];
        yield ['tests/Some/Namespace/FooBarStory.php', ['--namespace' => 'App\\Tests\\Some\\Namespace', '--test' => true], 'App\\Tests\\Some\\Namespace'];
    }
}
