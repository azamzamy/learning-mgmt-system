<?php

namespace LMS;

use DateTime;

class AccessController
{
    public function canAccess(Student $student, Content $content, DateTime $dateTime): bool
    {
        $course = $content->getCourse();

        if (!$this->isCourseActiveOn($course, $dateTime)) {
            return false;
        }

        if (!$this->hasValidEnrolment($student, $course, $dateTime)) {
            return false;
        }

        if (!$this->isContentAvailableOn($content, $dateTime)) {
            return false;
        }

        return true;
    }

    private function isCourseActiveOn(Course $course, DateTime $dateTime): bool
    {
        // Course must have started
        if ($dateTime < $course->getStartDate()) {
            return false;
        }

        // Course must not have ended (if end date is set)
        if ($course->getEndDate() !== null && $dateTime > $course->getEndDate()) {
            return false;
        }

        return true;
    }

    private function hasValidEnrolment(Student $student, Course $course, DateTime $dateTime): bool
    {
        $enrolment = $student->getEnrolmentForCourse($course, $dateTime);
        
        // Student must have an active enrolment for this course at the given time
        if ($enrolment === null) {
            return false;
        }

        return true;
    }

    private function isContentAvailableOn(Content $content, DateTime $dateTime): bool
    {
        // Lessons are only available from their scheduled datetime
        if ($content instanceof Lesson) {
            return $dateTime >= $content->getScheduledDateTime();
        }

        // Homework and PrepMaterial are available from course start (already checked)
        return true;
    }
}
