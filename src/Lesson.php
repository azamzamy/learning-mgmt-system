<?php

namespace LMS;

use DateTime;

class Lesson extends Content
{
    public function __construct(
        string $title,
        Course $course,
        private DateTime $scheduledDateTime
    ) {
        parent::__construct($title, $course);
    }

    public function getScheduledDateTime(): DateTime
    {
        return $this->scheduledDateTime;
    }
}
