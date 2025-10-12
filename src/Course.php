<?php

namespace LMS;

use DateTime;

class Course
{
    public function __construct(
        private string $name,
        private DateTime $startDate,
        private ?DateTime $endDate = null
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }
}
