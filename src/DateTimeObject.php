<?php

declare(strict_types=1);

namespace FluencePrototype\Bean;

use DateTime;
use DateTimeZone;
use JsonSerializable;
use stdClass;

class DateTimeObject extends DateTime implements JsonSerializable
{

    public function __construct($datetime = 'now', DateTimeZone $timezone = null)
    {
        parent::__construct($datetime, $timezone);
    }

    public function jsonSerialize(): stdClass
    {
        $json = new stdClass();
        $json->timestamp = $this->getTimestamp();

        return $json;
    }

}