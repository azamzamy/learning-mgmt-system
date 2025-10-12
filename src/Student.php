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

    public function getEnrolmentForCourse(Course $course): ?Enrolment
    {
        foreach ($this->enrolments as $enrolment) {
            if ($enrolment->getCourse() === $course) {
                return $enrolment;
            }
        }
        return null;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
