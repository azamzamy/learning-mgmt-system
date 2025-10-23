<?php

namespace LMS;

use DateTime;

class ContentItem
{
    public function __construct(
        private string $type,
        private string $title,
        private ?DateTime $scheduledDateTime = null
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getScheduledDateTime(): ?DateTime
    {
        return $this->scheduledDateTime;
    }

    public function isAvailableAt(DateTime $at): bool
    {
        if ($this->type === 'lesson') {
            return $this->scheduledDateTime !== null && $at >= $this->scheduledDateTime;
        }

        // Homework and prep materials are always available
        return true;
    }
}
