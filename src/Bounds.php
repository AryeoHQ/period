<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Period;

enum Bounds: string
{
    case INCLUDE_START_EXCLUDE_END = '[)';
    case INCLUDE_ALL = '[]';
    case EXCLUDE_START_INCLUDE_END = '(]';
    case EXCLUDE_ALL = '()';

    public function isStartIncluded(): bool
    {
        return self::INCLUDE_START_EXCLUDE_END === $this || self::INCLUDE_ALL === $this;
    }

    public function isEndIncluded(): bool
    {
        return self::EXCLUDE_START_INCLUDE_END === $this || self::INCLUDE_ALL === $this;
    }

    public function equalsStart(self $other): bool
    {
        return match ($this) {
            self::INCLUDE_ALL, self::INCLUDE_START_EXCLUDE_END => $other->isStartIncluded(),
            default => !$other->isStartIncluded(),
        };
    }

    public function equalsEnd(self $other): bool
    {
        return match ($this) {
            self::INCLUDE_ALL, self::EXCLUDE_START_INCLUDE_END => $other->isEndIncluded(),
            default => !$other->isEndIncluded(),
        };
    }

    public function includeStart(): self
    {
        return match ($this) {
            self::EXCLUDE_ALL => self::INCLUDE_START_EXCLUDE_END,
            self::EXCLUDE_START_INCLUDE_END => self::INCLUDE_ALL,
            default => $this,
        };
    }

    public function includeEnd(): self
    {
        return match ($this) {
            self::INCLUDE_START_EXCLUDE_END => self::INCLUDE_ALL,
            self::EXCLUDE_ALL => self::EXCLUDE_START_INCLUDE_END,
            default => $this,
        };
    }

    public function excludeStart(): self
    {
        return match ($this) {
            self::INCLUDE_ALL => self::EXCLUDE_START_INCLUDE_END,
            self::INCLUDE_START_EXCLUDE_END => self::EXCLUDE_ALL,
            default => $this,
        };
    }

    public function excludeEnd(): self
    {
        return match ($this) {
            self::INCLUDE_ALL => self::INCLUDE_START_EXCLUDE_END,
            self::EXCLUDE_START_INCLUDE_END => self::EXCLUDE_ALL,
            default => $this,
        };
    }

    public function replaceStart(self $other): self
    {
        return match (true) {
            $other->isStartIncluded() => $this->includeStart(),
            default => $this->excludeStart(),
        };
    }

    public function replaceEnd(self $other): self
    {
        return match (true) {
            $other->isEndIncluded() => $this->includeEnd(),
            default => $this->excludeEnd(),
        };
    }
}
