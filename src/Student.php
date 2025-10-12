<?php

namespace LMS;

class Student
{
    private array $enrolments = [];

    public function __construct(
        private string $name
    ) {
    }

    public function addEnrolment(Enrolment $enrolment): void
    {
        $this->enrolments[] = $enrolment;
    }

    public function getEnrolments(): array
    {
        return $this->enrolments;
    }

    public function getEnrolmentForCourse(Course $course, ?\DateTime $dateTime = null): ?Enrolment
    {
        foreach ($this->enrolments as $enrolment) {
            if ($enrolment->getCourse() === $course) {
                // If no date provided, return first matching enrolment
                if ($dateTime === null) {
                    return $enrolment;
                }
                
                // Check if this enrolment is active on the given date
                if ($dateTime >= $enrolment->getStartDate() && $dateTime <= $enrolment->getEndDate()) {
                    return $enrolment;
                }
            }
        }
        return null;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
