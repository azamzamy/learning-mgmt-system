<?php

namespace LMS;

use DateTime;

/**
 * Student Aggregate Root
 * 
 * Manages student enrolments and access control logic.
 * Enrolments are stored as value objects within the aggregate.
 */
class Student
{
    private array $courseEnrolments = [];

    public function __construct(
        private string $name
    ) {
    }

    public function enrolInCourse(string $courseId, DateTime $startDate, DateTime $endDate): void
    {
        $this->courseEnrolments[] = [
            'courseId' => $courseId,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    public function hasActiveEnrolment(string $courseId, DateTime $at): bool
    {
        foreach ($this->courseEnrolments as $enrolment) {
            if ($enrolment['courseId'] === $courseId) {
                if ($at >= $enrolment['startDate'] && $at <= $enrolment['endDate']) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

