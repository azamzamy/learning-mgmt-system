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

    public function getName(): string
    {
        return $this->name;
    }
}
