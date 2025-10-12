<?php

namespace LMS;

use DateTime;

class Enrolment
{
    public function __construct(
        private Student $student,
        private Course $course,
        private DateTime $startDate,
        private DateTime $endDate
    ) {
    }

    public function getStudent(): Student
    {
        return $this->student;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }
}
