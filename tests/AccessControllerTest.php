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

    /**
     * @test
     * Rule: Student cannot access content after enrolment ends
     */
    public function student_cannot_access_content_after_enrolment_ends(): void
    {
        // Arrange: Set up a course that runs from 13/05/2025 to 12/06/2025
        $courseStartDate = new DateTime('2025-05-13');
        $courseEndDate = new DateTime('2025-06-12');
        $course = new Course(
            name: 'A-Level Biology',
            startDate: $courseStartDate,
            endDate: $courseEndDate
        );

        // Create a student
        $student = new Student(name: 'Emma');

        // Create homework (available from course start)
        $homework = new PrepMaterial(
            title: 'Label a Plant Cell',
            course: $course
        );

        // Student enrolment shortened to end on 20/05/2025
        $enrolmentStart = new DateTime('2025-05-01');
        $enrolmentEnd = new DateTime('2025-05-20');
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

        // Act: Try to access content on 21/05/2025 (after enrolment ended)
        $attemptDate = new DateTime('2025-05-21');
        $canAccess = $accessController->canAccess(
            student: $student,
            content: $homework,
            dateTime: $attemptDate
        );

        // Assert: Access should be denied because enrolment has ended
        $this->assertFalse(
            $canAccess,
            'Student should not be able to access content after enrolment ends'
        );
    }

    /**
     * @test
     * Rule: Student can access content in ongoing course (no end date)
     */
    public function student_can_access_content_in_ongoing_course_with_no_end_date(): void
    {
        // Arrange: Set up an ongoing course with no end date
        $courseStartDate = new DateTime('2025-05-13');
        $course = new Course(
            name: 'A-Level Physics',
            startDate: $courseStartDate
            // No end date - course runs indefinitely
        );

        // Create a student
        $student = new Student(name: 'John');

        // Create prep material
        $prepMaterial = new PrepMaterial(
            title: 'Physics Fundamentals',
            course: $course
        );

        // Student enrolled with valid dates
        $enrolmentStart = new DateTime('2025-05-10');
        $enrolmentEnd = new DateTime('2025-12-31');
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

        // Act: Try to access content on 15/05/2025 (course started, within enrolment)
        $attemptDate = new DateTime('2025-05-15');
        $canAccess = $accessController->canAccess(
            student: $student,
            content: $prepMaterial,
            dateTime: $attemptDate
        );

        // Assert: Access should be allowed - course has no end date
        $this->assertTrue(
            $canAccess,
            'Student should be able to access content in an ongoing course with no end date'
        );
    }
}
