<?php

namespace LMS;

use DateTime;

class AccessController
{
    public function canAccess(Student $student, Content $content, DateTime $dateTime): bool
    {
        $course = $content->getCourse();

        // Rule: Access must be within the course's active period
        if ($dateTime < $course->getStartDate() || $dateTime > $course->getEndDate()) {
            return false;
        }

        $enrolment = $student->getEnrolmentForCourse($course);
        if ($enrolment === null || $dateTime < $enrolment->getStartDate() || $dateTime > $enrolment->getEndDate()) {
            return false; // No enrolment found
        }

        return true;
    }
}
