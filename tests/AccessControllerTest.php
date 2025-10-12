<?php

namespace LMS\Tests;

use DateTime;
use LMS\AccessController;
use LMS\Course;
use LMS\Enrolment;
use LMS\PrepMaterial;
use LMS\Student;
use PHPUnit\Framework\TestCase;

class AccessControllerTest extends TestCase
{
    /**
     * @test
     * Rule: Student cannot access content before the course start date
     */
    public function student_cannot_access_content_before_course_starts(): void
    {
        // Arrange: Set up a course that starts on 13/05/2025
        $courseStartDate = new DateTime('2025-05-13');
        $course = new Course(
            name: 'A-Level Chemistry',
            startDate: $courseStartDate
        );

        // Create a student
        $student = new Student(name: 'Emma');

        // Create prep material (available from course start)
        $prepMaterial = new PrepMaterial(
            title: 'Periodic Table Overview',
            course: $course
        );

        // Student enrolled from 01/05/2025 (before course starts)
        $enrolmentStart = new DateTime('2025-05-01');
        $enrolmentEnd = new DateTime('2025-05-30');
        $enrolment = new Enrolment(
            student: $student,
            course: $course,
            startDate: $enrolmentStart,
            endDate: $enrolmentEnd
        );

        // Add enrolment to student
        $student->addEnrolment($enrolment);

        // Access controller
        $accessController = new AccessController();

        // Act: Try to access content on 01/05/2025 (before course starts)
        $attemptDate = new DateTime('2025-05-01');
        $canAccess = $accessController->canAccess(
            student: $student,
            content: $prepMaterial,
            dateTime: $attemptDate
        );

        // Assert: Access should be denied because course hasn't started yet
        $this->assertFalse(
            $canAccess,
            'Student should not be able to access content before the course start date'
        );
    }
}
