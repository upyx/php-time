<?php

/*
 * This file is part of the PHP Time library.
 *
 * (c) Sergey Rabochiy <upyx.00@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Upyx\PhpTime;

use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use PHPUnit\Framework\TestCase;
use Upyx\PhpTime\Exception\Exception;
use function get_class;
use function sprintf;

/**
 * @covers \Upyx\PhpTime\LocalTime
 */
class LocalTimeTest extends TestCase
{
    public function providerTimes(): Generator
    {
        yield 'Ones' => [1, 1, 1, 1];
        yield 'Midnight' => [0, 0, 0, 0];
        yield 'Just before midnight' => [23, 59, 59, 999999];
        yield 'Just after midnight' => [0, 0, 0, 1];
        yield 'Some time' => [5, 10, 20, 100];
    }

    /**
     * @dataProvider providerTimes
     */
    public function testTimeParts(int $hour, int $minute, int $second, int $microsecond): void
    {
        $time = new LocalTime($hour, $minute, $second, $microsecond);

        $this->assertSame($hour, $time->getHour());
        $this->assertSame($minute, $time->getMinute());
        $this->assertSame($second, $time->getSecond());
        $this->assertSame($microsecond, $time->getMicrosecond());
    }

    /**
     * @dataProvider providerTimes
     */
    public function testCreationFromDateTime(int $hour, int $minute, int $second, int $microsecond): void
    {
        $formattedTime = sprintf('%02d:%02d:%02d.%06d', $hour, $minute, $second, $microsecond);
        $dateTime = DateTimeImmutable::createFromFormat('H:i:s.u', $formattedTime);
        $time = LocalTime::fromDateTime($dateTime);

        $this->assertSame($hour, $time->getHour());
        $this->assertSame($minute, $time->getMinute());
        $this->assertSame($second, $time->getSecond());
        $this->assertSame($microsecond, $time->getMicrosecond());
    }

    /**
     * @dataProvider providerTimes
     */
    public function testMicroseconds(int $hour, int $minute, int $second, int $microsecond): void
    {
        $time = new LocalTime($hour, $minute, $second, $microsecond);
        $time = LocalTime::fromMicroseconds($time->toMicroseconds());

        $this->assertSame($hour, $time->getHour());
        $this->assertSame($minute, $time->getMinute());
        $this->assertSame($second, $time->getSecond());
        $this->assertSame($microsecond, $time->getMicrosecond());
    }

    public function testWithHour(): void
    {
        $time = new LocalTime(5, 10, 20, 100);
        $time = $time->withHour(7);

        $this->assertSame(7, $time->getHour());
        $this->assertSame(10, $time->getMinute());
        $this->assertSame(20, $time->getSecond());
        $this->assertSame(100, $time->getMicrosecond());
    }

    public function testWithMinute(): void
    {
        $time = new LocalTime(5, 10, 20, 100);
        $time = $time->withMinute(7);

        $this->assertSame(5, $time->getHour());
        $this->assertSame(7, $time->getMinute());
        $this->assertSame(20, $time->getSecond());
        $this->assertSame(100, $time->getMicrosecond());
    }

    public function testWithSecond(): void
    {
        $time = new LocalTime(5, 10, 20, 100);
        $time = $time->withSecond(7);

        $this->assertSame(5, $time->getHour());
        $this->assertSame(10, $time->getMinute());
        $this->assertSame(7, $time->getSecond());
        $this->assertSame(100, $time->getMicrosecond());
    }

    public function testWithMicrosecond(): void
    {
        $time = new LocalTime(5, 10, 20, 100);
        $time = $time->withMicrosecond(7000);

        $this->assertSame(5, $time->getHour());
        $this->assertSame(10, $time->getMinute());
        $this->assertSame(20, $time->getSecond());
        $this->assertSame(7000, $time->getMicrosecond());
    }

    public function testComparison(): void
    {
        $midnight = new LocalTime(0, 0, 0, 0);
        $afterMidnight = new LocalTime(10, 20, 30, 4000);
        $beforeMidnight = new LocalTime(23, 59, 59, 999999);

        $this->assertSame(1, $afterMidnight <=> $midnight);
        $this->assertSame(1, $beforeMidnight <=> $midnight);
        $this->assertSame(1, $beforeMidnight <=> $afterMidnight);
        $this->assertSame(0, $midnight <=> clone $midnight);
        $this->assertSame(0, $beforeMidnight <=> clone $beforeMidnight);
    }

    public function providerWrongTimes(): Generator
    {
        yield 'Overflow hour' => [24, 0, 0, 0];
        yield 'Overflow minute' => [0, 60, 0, 0];
        yield 'Overflow second' => [0, 0, 60, 0];
        yield 'Overflow microsecond' => [0, 0, 0, 1000000];
        yield 'Negative hour' => [-1, 0, 0, 0];
        yield 'Negative minute' => [0, -1, 0, 0];
        yield 'Negative second' => [0, 0, -1, 0];
        yield 'Negative microsecond' => [0, 0, 0, -1];
    }

    /**
     * @dataProvider providerWrongTimes
     */
    public function testCreationExceptions(int $hour, int $minute, int $second, int $microsecond): void
    {
        $this->expectException(Exception::class);

        new LocalTime($hour, $minute, $second, $microsecond);
    }

    /**
     * @dataProvider providerWrongTimes
     */
    public function testCloningExceptions(int $hour, int $minute, int $second, int $microsecond): void
    {
        $this->expectException(Exception::class);

        (new LocalTime(10, 20, 30, 40000))
            ->withHour($hour)
            ->withMinute($minute)
            ->withSecond($second)
            ->withMicrosecond($microsecond);
    }

    public function providerWrongMicroseconds(): Generator
    {
        yield 'Overflow' => [86400000000];
        yield 'Underflow' => [-1];
    }

    /**
     * @dataProvider providerWrongMicroseconds
     */
    public function testCreationFromWrongMicroseconds(int $microseconds): void
    {
        $this->expectException(Exception::class);

        LocalTime::fromMicroseconds($microseconds);
    }

    public function testFormat(): void
    {
        $time = new LocalTime(1, 2, 3, 4);

        $this->assertSame('01:02:03.000004', $time->format('H:i:s.u'));
        $this->assertSame('1970-01-01T01:02:03.000+00:00', $time->format(DateTimeInterface::RFC3339_EXTENDED));
    }

    public function providerCalculation(): Generator
    {
        yield [new LocalTime(0, 0, 0, 0), new LocalTime(0, 0, 0, 0), new LocalTime(0, 0, 0, 0)];
        yield [new LocalTime(0, 0, 0, 0), new LocalTime(0, 0, 0, 1), new LocalTime(0, 0, 0, 1)];
        yield [new LocalTime(1, 2, 3, 4000), new LocalTime(2, 3, 4, 5000), new LocalTime(3, 5, 7, 9000)];
        yield [new LocalTime(23, 59, 59, 999999), new LocalTime(0, 0, 0, 1), new LocalTime(0, 0, 0, 0)];
        yield [new LocalTime(12, 0, 0, 0), new LocalTime(12, 0, 0, 0), new LocalTime(0, 0, 0, 0)];
    }

    /**
     * @dataProvider providerCalculation
     */
    public function testCyclicAdd(LocalTime $a, LocalTime $b, LocalTime $sum): void
    {
        $result = $a->cyclicAdd($b);

        $this->assertEquals($sum, $result);
    }

    /**
     * @dataProvider providerCalculation
     */
    public function testSub(LocalTime $diff, LocalTime $b, LocalTime $a): void
    {
        $result = $a->cyclicSubtract($b);

        $this->assertEquals($diff, $result);
    }

    public function providerDistances(): Generator
    {
        yield [new LocalTime(0, 0, 0, 0), new LocalTime(0, 0, 0, 0), new LocalTime(0, 0, 0, 0)];
        yield [new LocalTime(0, 0, 0, 0), new LocalTime(0, 0, 0, 1), new LocalTime(0, 0, 0, 1)];
        yield [new LocalTime(1, 2, 3, 4000), new LocalTime(2, 3, 4, 5000), new LocalTime(1, 1, 1, 1000)];
        yield [new LocalTime(23, 59, 59, 999999), new LocalTime(0, 0, 0, 1), new LocalTime(0, 0, 0, 2)];
        yield [new LocalTime(12, 0, 0, 0), new LocalTime(12, 0, 0, 0), new LocalTime(0, 0, 0, 0)];
        yield [new LocalTime(6, 0, 0, 0), new LocalTime(18, 0, 0, 0), new LocalTime(12, 0, 0, 0)];
    }

    /**
     * @dataProvider providerDistances
     */
    public function testCalcDistance(LocalTime $a, LocalTime $b, LocalTime $distance): void
    {
        $aToB = $a->calcDistance($b);
        $bToa = $b->calcDistance($a);

        $this->assertEquals($distance, $aToB);
        $this->assertEquals($distance, $bToa);
    }

    public function testInheritanceWithStatic(): void
    {
        $proto = new class(0, 0) extends LocalTime {
        };

        $fromDateTime = $proto::fromDateTime(new DateTimeImmutable('10:20:30'));
        $fromMicroseconds = $proto::fromMicroseconds($fromDateTime->toMicroseconds());
        $withHour = $proto->withHour(10);
        $withMinute = $proto->withMinute(20);
        $withSecond = $proto->withSecond(30);
        $withMicrosecond = $proto->withMicrosecond(40000);

        $this->assertInstanceOf(get_class($proto), $fromDateTime);
        $this->assertInstanceOf(get_class($proto), $fromMicroseconds);
        $this->assertInstanceOf(get_class($proto), $withHour);
        $this->assertInstanceOf(get_class($proto), $withMinute);
        $this->assertInstanceOf(get_class($proto), $withSecond);
        $this->assertInstanceOf(get_class($proto), $withMicrosecond);
        $this->assertSame('10:20:30.000000', $fromDateTime->format('H:i:s.u'));
        $this->assertSame('10:20:30.000000', $fromMicroseconds->format('H:i:s.u'));
        $this->assertSame('10:00:00.000000', $withHour->format('H:i:s.u'));
        $this->assertSame('00:20:00.000000', $withMinute->format('H:i:s.u'));
        $this->assertSame('00:00:30.000000', $withSecond->format('H:i:s.u'));
        $this->assertSame('00:00:00.040000', $withMicrosecond->format('H:i:s.u'));
    }
}
