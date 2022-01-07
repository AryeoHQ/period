---
layout: default
title: Accessing Period object properties
---

# Accessing properties

## Period representations

### String representation

~~~php
public Period::toIso8601(): string
~~~

Returns the string representation of a `Period` object using [ISO8601 time interval representation](http://en.wikipedia.org/wiki/ISO_8601#Time_intervals).

~~~php
date_default_timezone_set('Africa/Nairobi');

$period = Period::fromNotation('Y-m-d H:i:s', '[2014-05-01 00:00:00, 2014-05-08 00:00:00)');
echo $period->toIso8601(); // '2014-04-30T23:00:00.000000Z/2014-05-07T23:00:00.000000Z'
~~~

### JSON representation

~~~php
public Period::jsonSerialize(void): array
~~~

`Period` implements the `JsonSerializable` interface and is directly usable with PHP `json_encode` function as shown below:

~~~php
date_default_timezone_set('Africa/Kinshasa');

$period = Period::fromNotation('Y-m-d H:i:s', '[2014-05-01 00:00:00, 2014-05-08 00:00:00)');

$res = json_decode(json_encode($period), true);
//  $res will be equivalent to:
// [
//      'startDate' => '2014-04-30T23:00:00.000000Z,
//      'endDate' => '2014-05-07T23:00:00.000000Z',
//      'startDateIncluded' => true,
//      'endDateIncluded' => false,
// ]
~~~

<p class="message-info">This format was chosen to enable better compatibility with Javascript <a href="https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/toISOString">Date representation</a>.</p>

### Mathematical representation

~~~php
public Period::toNotation(string $format): string
~~~

You can use the `format` method to represent a `Period` object in its [mathematical representation](https://en.wikipedia.org/wiki/Interval_(mathematics)#Notations_for_intervals) as shown below.

- The `$format` parameter expects a string which follow [date](http://php.net/manual/en/function.date.php) first argument rules.
- The boundary representation depends on the `Period` boundaries property.

~~~php
$period = Period::fromNotation('Y-m-d H:i:s', '[2014-05-01 00:00:00, 2014-05-08 00:00:00)');
echo $period->format('Y-m-d'); // [2014-05-01, 2014-05-08)
~~~

This representation can be used, for instance, in a PostgreSQL query against a [DateRange field](https://www.postgresql.org/docs/9.3/static/rangetypes.html).

## Period properties

Once you have a instantiated `Period` object you can access the object datepoints, durations and bounds using the following getter methods:

~~~php
use League\Period\Bounds;

public Period::startDate(): DateTimeImmutable
public Period::endDate(): DateTimeImmutable
public Period::bounds(): Bounds
public Period::dateInterval(): DateInterval
public Period::timestampInterval(): int
~~~

~~~php
use League\Period\Period;

$period = Period::fromDateString('Y-m-d H:i:s', '2012-04-01 08:30:25', '2013-09-04 12:35:21');
$period->startDate();         // returns DateTimeImmutable('2012-04-01 08:30:25');
$period->endDate();           // returns DateTimeImmutable('2013-09-04 12:35:21');
$period->dateInterval();      // returns a DateInterval object
$period->timestampInterval(); // returns the duration in seconds
$period->bounds();            // returns Bounds::INCLUDE_START_EXCLUDE_END
~~~

<p class="message-notice">More information can be extracted from the <code>Bounds</code> enum, please refer to its documentation page.</p>

## Iteration over a Period

Whenever a duration is expected the following types are supported:

- `DateInterval`
- `Period`
- `Duration`
- a `string` parsable by `DateInterval::createFromDateString`

Unless explicitly restricted, whenever a datepoint is expected the following types are supported:

- `DateTimeInterface`
- `DatePoint`
- a `string` parsable by `DateTimeImmutable::__construct`

### Period::datePeriod

~~~php
public Period::datePeriod(Period|Duration|DateInterval|string $duration, int $option = 0): DatePeriod
~~~

Returns a `DatePeriod` using the `Period` datepoints with the given `$duration`.

<p class="message-notice">When iterating over the resulting <code>DatePeriod</code> object, all the generated datepoints are <code>DateTimeImmutable</code> instances.</p>

#### Parameters

- `$duration` can be a `Period`, a `Duration` or a `DateInterval` object
- `$option` Can be set to **`DatePeriod::EXCLUDE_START_DATE`** to exclude the start date from the set of recurring dates within the period.

#### Examples

~~~php
use League\Period\Duration;
use League\Period\Period;

foreach (Period::fromYear(2012)->datePeriod('1 MONTH') as $datetime) {
    echo $datetime->format('Y-m-d');
}
//will iterate 12 times
////the first date is 2012-01-01
//the last date is 2012-12-01
~~~

Using the `$option` parameter

~~~php
use League\Period\Duration;
use League\Period\Period;

$datePeriod = Period::fromYear('2012-06-05')->datePeriod('1 MONTH', DatePeriod::EXCLUDE_START_DATE);
foreach ($datePeriod as $datetime) {
    echo $datetime->format('Y-m-d');
}
//will iterate 11 times
//the first date is 2012-02-01
//the last date is 2012-12-01
~~~

### Period::getDatePeriodBackwards

~~~php
public Period::datePeriodBackwards(Period|Duration|DateInterval|string $duration, int $option = 0): Generator<DateTimeImmutable>
~~~

Returns a `Generator` to allow iteration over the instance datepoints, recurring at regular intervals, backwards starting from the ending datepoint.

#### Parameters

- `$duration` is a interval
- `$option` Can be set to **`DatePeriod::EXCLUDE_START_DATE`** to exclude the ending datepoint from the set of recurring dates within the interval.

#### Examples

~~~php
foreach (Period::fromYear(2012)->datePeriodBackwards(new DateInterval('P1M')) as $datetime) {
    echo $datetime->format('Y-m-d');
}
//will iterate 12 times
//the first date is 2013-01-01
//the last date is 2012-02-01
~~~

Using the `$option` parameter

~~~php
$interval = Period::fromYear('2012-06-05');
$datePeriod = $interval->datePeriodBackwards(new DateInterval('P1M'), DatePeriod::EXCLUDE_START_DATE);
foreach ($datePeriod as $datetime) {
    echo $datetime->format('Y-m-d');
}
//will iterate 11 times
//the first date is 2012-12-01
//the last date is 2012-02-01
~~~

### Period::split

~~~php
public Period::split(Period|Duration|DateInterval|string $duration): Generator<Period>
~~~

This method splits a given `Period` object in smaller `Period` objects according to the given `$duration` starting from the object starting datepoint to its ending datepoint. The result is returned as a `Generator` object. All returned objects must be contained or abutted to the parent `Period` object.

- The first returned `Period` will always share the same starting datepoint with the parent object.
- The last returned `Period` will always share the same ending datepoint with the parent object.
- The last returned `Period` will have a duration equal or lesser than the submitted interval.
- If `$interval` is greater than the parent `Period` interval, the generator will contain a single `Period` whose datepoints equals those of the parent `Period`.

#### Example

~~~php
foreach (Period::fromYear(2012)->split(new DateInterval('P1M')) as $period) {
    echo $period->toNotation('Y-m-d'); //returns Period object whose interval is 1 MONTH
}
//will iterate 12 times
~~~

### Period::splitBackwards

~~~php
public Period::splitBackwards(Period|Duration|DateInterval|string $duration): Generator<Period>
~~~

This method splits a given `Period` object in smaller `Period` objects according to the given `$duration` starting from the object ending datepoint to its starting datepoint. The result is returned as a `Generator` object. All returned objects must be contained or abutted to the parent `Period` object.

- The first returned `Period` will always share the same ending datepoint with the parent object.
- The last returned `Period` will always share the same starting datepoint with the parent object.
- The last returned `Period` will have a duration equal or lesser than the submitted interval.
- If `$interval` is greater than the parent `Period` interval, the generator will contain a single `Period` whose datepoints equals those of the parent `Period`.

#### Example

~~~php
date_default_timezone_set('Africa/Kinshasa');

$collection = iterator_to_array(Period::fromYear(2012)->splitBackwards(Duration::fromDateString('5 MONTH')), false);
echo $collection[0]->toIso8601(); // 2012-07-31T23:00:00Z/2012-12-31T23:00:00Z (5 months interval)
echo $collection[1]->toIso8601(); // 2012-02-29T23:00:00Z/2012-07-31T23:00:00Z (5 months interval)
echo $collection[2]->toIso8601(); // 2011-12-31T23:00:00Z/2012-02-29T23:00:00Z (2 months interval)
~~~
