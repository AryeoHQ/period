<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Period\Chart;

use PHPUnit\Framework\TestCase;
use TypeError;
use function chr;
use function fopen;
use function rewind;
use function stream_get_contents;

/**
 * @coversDefaultClass \League\Period\Chart\StreamOutput
 */
final class StreamOutputTest extends TestCase
{
    /**
     * @return resource
     */
    private function setStream()
    {
        /** @var resource $stream */
        $stream = fopen('php://memory', 'r+');

        return $stream;
    }

    public function testCreateStreamWithInvalidParameter(): void
    {
        $this->expectException(TypeError::class);
        new StreamOutput(__DIR__.'/data/foo.csv', Terminal::POSIX);
    }

    /**
     * @dataProvider providesWritelnTextsPosix
     */
    public function testWriteln(string $message, string $expected): void
    {
        $stream = $this->setStream();
        $output = new StreamOutput($stream, Terminal::POSIX);
        $output->writeln($message, Color::BLUE);
        $output->writeln($message);
        rewind($stream);
        /** @var string $data */
        $data = stream_get_contents($stream);

        self::assertStringContainsString($expected, $data);
    }

    /**
     * @return iterable<string, array{message:string, expected:string}>
     */
    public function providesWritelnTextsPosix(): iterable
    {
        return [
            'empty message' => [
                'message' => '',
                'expected' => '',
            ],
            'simple message' => [
                'message' => "I'm the king of the world",
                'expected' => chr(27).'[34m'."I'm the king of the world".chr(27).'[0m'.PHP_EOL,
            ],
        ];
    }

    /**
     * @dataProvider providesWritelnTextsUnknown
     */
    public function testWritelnUnknown(string $message, string $expected): void
    {
        $stream = $this->setStream();
        $output = new StreamOutput($stream, Terminal::COLORLESS);
        $output->writeln($message, Color::BLUE);
        $output->writeln($message);
        rewind($stream);
        /** @var string $data */
        $data = stream_get_contents($stream);

        self::assertStringContainsString($expected, $data);
    }

    /**
     * @return iterable<string, array{message:string, expected:string}>
     */
    public function providesWritelnTextsUnknown(): iterable
    {
        return [
            'empty message' => [
                'message' => '',
                'expected' => '',
            ],
            'simple message' => [
                'message' => "I'm the king of the world",
                'expected' => "I'm the king of the world".PHP_EOL,
            ],
        ];
    }
}
