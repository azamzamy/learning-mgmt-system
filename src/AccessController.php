<?php

namespace LMS;

use DateTime;

class AccessController
{
    public function canAccess(Student $student, Content $content, DateTime $dateTime): bool
    {
        $course = $content->getCourse();

        // Rule: Course must have started
        if ($dateTime < $course->getStartDate()) {
            return false;
        }

        // Rule: Course must not have ended (if end date is set)
        if ($course->getEndDate() !== null && $dateTime > $course->getEndDate()) {
            return false;
        }

        // Rule: Student must have a valid enrolment for this course
        $enrolment = $student->getEnrolmentForCourse($course);
        if ($enrolment === null) {
            return false; // No enrolment found
        }

        // Rule: Access must be within enrolment period
        if ($dateTime < $enrolment->getStartDate() || $dateTime > $enrolment->getEndDate()) {
            return false;
        }

        return true;
    }
}
