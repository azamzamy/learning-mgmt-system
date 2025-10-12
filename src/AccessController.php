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

        return true;
    }
}
