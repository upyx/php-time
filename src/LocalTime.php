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
use Upyx\PhpTime\Exception\Exception;
use function abs;
use function explode;
use function sprintf;

/**
 * A time (cyclic o'clock) representation with microsecond. It is immutable.
 * If you would like to extend it, try to include it in your new class first.
 *
 * Magic numbers in code is microseconds:
 * 1000000 - a second
 * 60000000 - a minute
 * 3600000000 - an hour
 * 86400000000 - a day
 * 43200000000 - a half of day
 */
class LocalTime
{
    /**
     * @var int
     */
    private $value;

    /**
     * Constructs LocalTime object from parts.
     *
     * @throws Exception when some parameter is out of bounds
     */
    public function __construct(int $hour, int $minute, int $second = 0, int $microsecond = 0)
    {
        $this->ensureCorrectHour($hour);
        $this->ensureCorrectMinute($minute);
        $this->ensureCorrectSecond($second);
        $this->ensureCorrectMicrosecond($microsecond);

        $this->value = $microsecond;
        $this->value += 1000000 * $second;
        $this->value += 60000000 * $minute;
        $this->value += 3600000000 * $hour;
    }

    /**
     * Converts DateTime or DateTimeImmutable to LocalTime object.
     *
     * @throws Exception when something went wrong
     */
    public static function fromDateTime(DateTimeInterface $dateTime): self
    {
        [$hour, $minute, $second, $microsecond] = explode(' ', $dateTime->format('G i s u'));

        return new static((int)$hour, (int)$minute, (int)$second, (int)$microsecond);
    }

    /**
     * Creates LocalTime object from integer value with microseconds.
     *
     * LocalTime object internally saved as that microseconds.
     *
     * @throws Exception when microseconds parameter is out of bounds
     */
    public static function fromMicroseconds(int $microseconds): self
    {
        if ($microseconds < 0 || $microseconds >= 86400000000) {
            throw new Exception(sprintf('Microseconds %d is out of bounds.', $microseconds));
        }

        $time = new static(0, 0);
        $time->value = $microseconds;

        return $time;
    }

    /**
     * Converts LocalTime object to integer value with microseconds.
     *
     * LocalTime object internally saved as that microseconds.
     */
    public function toMicroseconds(): int
    {
        return $this->value;
    }

    /**
     * Returns an hour part.
     */
    public function getHour(): int
    {
        return (int)($this->value / 3600000000);
    }

    /**
     * Returns a minute part.
     */
    public function getMinute(): int
    {
        return (int)($this->value % 3600000000 / 60000000);
    }

    /**
     * Returns a second part.
     */
    public function getSecond(): int
    {
        return $this->value / 1000000 % 60;
    }

    /**
     * Returns a microsecond part.
     */
    public function getMicrosecond(): int
    {
        return $this->value % 1000000;
    }

    /**
     * Returns a new LocalTime object with the given hour.
     *
     * @throws Exception when hour is out of bounds
     */
    public function withHour(int $hour): self
    {
        return new static(
            $hour,
            $this->getMinute(),
            $this->getSecond(),
            $this->getMicrosecond()
        );
    }

    /**
     * Returns a new LocalTime object with the given minute.
     *
     * @throws Exception when minute is out of bounds
     */
    public function withMinute(int $minute): self
    {
        return new static(
            $this->getHour(),
            $minute,
            $this->getSecond(),
            $this->getMicrosecond()
        );
    }

    /**
     * Returns a new LocalTime object with the given second.
     *
     * @throws Exception when second is out of bounds
     */
    public function withSecond(int $second): self
    {
        return new static(
            $this->getHour(),
            $this->getMinute(),
            $second,
            $this->getMicrosecond()
        );
    }

    /**
     * Returns a new LocalTime object with the given microsecond.
     *
     * @throws Exception when microsecond is out of bounds
     */
    public function withMicrosecond(int $microsecond): self
    {
        return new static(
            $this->getHour(),
            $this->getMinute(),
            $this->getSecond(),
            $microsecond
        );
    }

    /**
     * Converts the LocalTime object to string like it does the date() function.
     *
     * Internally uses DateTimeImmutable::format() method so supports the same format.
     * If date literal is used in the format string, the 1970-01-01 UTC is used to produce them.
     *
     * @see DateTimeImmutable::format()
     * @see https://www.php.net/manual/en/function.date.php
     */
    public function format(string $format): string
    {
        $dateTime = DateTimeImmutable::createFromFormat('!H:i:s.u', sprintf(
            '%02d:%02d:%02d.%06d',
            $this->getHour(),
            $this->getMinute(),
            $this->getSecond(),
            $this->getMicrosecond()
        ));

        return $dateTime->format($format);
    }

    /**
     * Cyclically adds the time. If sum of times more then 24 hours, it starts from midnight.
     */
    public function cyclicAdd(self $time): self
    {
        $microseconds = $this->toMicroseconds() + $time->toMicroseconds();

        /** @noinspection PhpUnhandledExceptionInspection */
        return static::fromMicroseconds($microseconds >= 86400000000 ? $microseconds - 86400000000 : $microseconds);
    }

    /**
     * Cyclically subtracts the time. If difference of times below zero, it subtracts from midnight.
     */
    public function cyclicSubtract(self $time): self
    {
        $microseconds = $this->toMicroseconds() - $time->toMicroseconds();

        /** @noinspection PhpUnhandledExceptionInspection */
        return static::fromMicroseconds($microseconds < 0 ? $microseconds + 86400000000 : $microseconds);
    }

    /**
     * Calculates minimal distance between two times.
     */
    public function calcDistance(self $time): self
    {
        $microseconds = abs($this->toMicroseconds() - $time->toMicroseconds());

        /** @noinspection PhpUnhandledExceptionInspection */
        return static::fromMicroseconds($microseconds > 43200000000 ? 86400000000 - $microseconds : $microseconds);
    }

    /**
     * @throws Exception
     */
    private function ensureCorrectHour(int $hour): void
    {
        if ($hour < 0 || $hour >= 24) {
            throw new Exception(sprintf('Wrong hour value: %d.', $hour));
        }
    }

    /**
     * @throws Exception
     */
    private function ensureCorrectMinute(int $minute): void
    {
        if ($minute < 0 || $minute >= 60) {
            throw new Exception(sprintf('Wrong minute value: %d.', $minute));
        }
    }

    /**
     * @throws Exception
     */
    private function ensureCorrectSecond(int $second): void
    {
        if ($second < 0 || $second >= 60) {
            throw new Exception(sprintf('Wrong second value: %d.', $second));
        }
    }

    /**
     * @throws Exception
     */
    private function ensureCorrectMicrosecond(int $microsecond): void
    {
        if ($microsecond < 0 || $microsecond >= 1000000) {
            throw new Exception(sprintf('Wrong microsecond value: %d.', $microsecond));
        }
    }
}
