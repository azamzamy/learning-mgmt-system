<?php

namespace LMS;

use DateTime;

class AccessController
{
    public function canAccess(Student $student, Content $content, DateTime $dateTime): bool
    {
        // TODO: Implement access logic
        return true; // Intentionally wrong to make test fail
    }
}
