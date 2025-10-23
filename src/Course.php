<?php

namespace LMS;

use DateTime;

class Course
{
    private array $contents = [];

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

    public function addLesson(string $title, DateTime $scheduledDateTime): string
    {
        $id = $this->generateContentId();
        
        $this->contents[$id] = new ContentItem(
            type: 'lesson',
            title: $title,
            scheduledDateTime: $scheduledDateTime
        );
        
        return $id;
    }

    public function addHomework(string $title): string
    {
        $id = $this->generateContentId();
        
        $this->contents[$id] = new ContentItem(
            type: 'homework',
            title: $title,
            scheduledDateTime: null
        );
        
        return $id;
    }

    public function addPrepMaterial(string $title): string
    {
        $id = $this->generateContentId();
        
        $this->contents[$id] = new ContentItem(
            type: 'prep_material',
            title: $title,
            scheduledDateTime: null
        );
        
        return $id;
    }

    public function getContent(string $contentId): ?ContentItem
    {
        return $this->contents[$contentId] ?? null;
    }

    public function isActive(DateTime $at): bool
    {
        if ($at < $this->startDate) {
            return false;
        }

        if ($this->endDate !== null && $at > $this->endDate) {
            return false;
        }

        return true;
    }

    public function isContentAvailable(string $contentId, DateTime $at): bool
    {
        $content = $this->getContent($contentId);

        if ($content === null) {
            return false;
        }

        return $content->isAvailableAt($at);
    }

    public function isAccessibleTo(Student $student, string $courseId, string $contentId, DateTime $at): bool
    {
        if (!$this->isActive($at)) {
            return false;
        }

        if (!$student->hasActiveEnrolment($courseId, $at)) {
            return false;
        }

        if (!$this->isContentAvailable($contentId, $at)) {
            return false;
        }

        return true;
    }

    private function generateContentId(): string
    {
        return uniqid('content_', true);
    }
}
