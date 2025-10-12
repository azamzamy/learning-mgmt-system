<?php

namespace LMS\Tests;

use DateTime;
use LMS\AccessController;
use LMS\Course;
use LMS\Enrolment;
use LMS\Homework;
use LMS\Lesson;
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
     * Rule: Student can access prep material after course starts
     */
    public function student_can_access_prep_material_after_course_starts(): void
    {
        // Arrange: Set up a course that starts on 13/05/2025
        $courseStartDate = new DateTime('2025-05-13');
        $courseEndDate = new DateTime('2025-06-12');
        $course = new Course(
            name: 'A-Level Biology',
            startDate: $courseStartDate,
            endDate: $courseEndDate
        );

        // Create a student
        $student = new Student(name: 'Emma');

        // Create prep material (available from course start)
        $prepMaterial = new PrepMaterial(
            title: 'Biology Reading Guide',
            course: $course
        );

        // Student enrolled with valid dates
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

        // Act: Try to access content on 13/05/2025 (course start date)
        $attemptDate = new DateTime('2025-05-13');
        $canAccess = $accessController->canAccess(
            student: $student,
            content: $prepMaterial,
            dateTime: $attemptDate
        );

        // Assert: Access should be allowed because course has started
        $this->assertTrue(
            $canAccess,
            'Student should be able to access prep material after course starts'
        );
    }

    /**
     * @test
     * Edge case: Access on exact course start date boundary
     */
    public function student_can_access_content_on_exact_course_start_date(): void
    {
        // Arrange: Set up a course with specific start time
        $courseStartDate = new DateTime('2025-05-13 00:00:00');
        $course = new Course(
            name: 'A-Level Physics',
            startDate: $courseStartDate
        );

        // Create a student
        $student = new Student(name: 'David');

        // Create content
        $prepMaterial = new PrepMaterial(
            title: 'Physics Introduction',
            course: $course
        );

        // Student enrolled with valid dates
        $enrolment = new Enrolment(
            student: $student,
            course: $course,
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-31')
        );

        $student->addEnrolment($enrolment);

        // Access controller
        $accessController = new AccessController();

        // Act: Try to access at the EXACT course start time
        $attemptDate = new DateTime('2025-05-13 00:00:00');
        $canAccess = $accessController->canAccess(
            student: $student,
            content: $prepMaterial,
            dateTime: $attemptDate
        );

        // Assert: Access should be allowed at exact start time (inclusive boundary)
        $this->assertTrue(
            $canAccess,
            'Student should be able to access content at the exact course start time'
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

    /**
     * @test
     * Rule: Lesson is only available from its scheduled datetime
     */
    public function student_cannot_access_lesson_before_scheduled_time(): void
    {
        // Arrange: Set up a course
        $courseStartDate = new DateTime('2025-05-13');
        $courseEndDate = new DateTime('2025-06-12');
        $course = new Course(
            name: 'A-Level Biology',
            startDate: $courseStartDate,
            endDate: $courseEndDate
        );

        // Create a student
        $student = new Student(name: 'Emma');

        // Create lesson scheduled for 15/05/2025 at 10:00
        $lessonScheduledTime = new DateTime('2025-05-15 10:00:00');
        $lesson = new Lesson(
            title: 'Cell Structure',
            course: $course,
            scheduledDateTime: $lessonScheduledTime
        );

        // Student enrolled with valid dates
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

        // Act: Try to access lesson on 15/05/2025 at 09:59 (before scheduled time)
        $attemptDateTime = new DateTime('2025-05-15 09:59:00');
        $canAccess = $accessController->canAccess(
            student: $student,
            content: $lesson,
            dateTime: $attemptDateTime
        );

        // Assert: Access should be denied because lesson hasn't started yet
        $this->assertFalse(
            $canAccess,
            'Student should not be able to access lesson before its scheduled time'
        );
    }

    /**
     * @test
     * Rule: Lesson is available from its scheduled datetime
     */
    public function student_can_access_lesson_after_scheduled_time(): void
    {
        // Arrange: Set up a course
        $courseStartDate = new DateTime('2025-05-13');
        $courseEndDate = new DateTime('2025-06-12');
        $course = new Course(
            name: 'A-Level Biology',
            startDate: $courseStartDate,
            endDate: $courseEndDate
        );

        // Create a student
        $student = new Student(name: 'Emma');

        // Create lesson scheduled for 15/05/2025 at 10:00
        $lessonScheduledTime = new DateTime('2025-05-15 10:00:00');
        $lesson = new Lesson(
            title: 'Cell Structure',
            course: $course,
            scheduledDateTime: $lessonScheduledTime
        );

        // Student enrolled with valid dates
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

        // Act: Try to access lesson on 15/05/2025 at 10:01 (after scheduled time)
        $attemptDateTime = new DateTime('2025-05-15 10:01:00');
        $canAccess = $accessController->canAccess(
            student: $student,
            content: $lesson,
            dateTime: $attemptDateTime
        );

        // Assert: Access should be allowed because lesson has started
        $this->assertTrue(
            $canAccess,
            'Student should be able to access lesson after its scheduled time'
        );
    }

    /**
     * @test
     * Edge case: Access at exact lesson scheduled time boundary
     */
    public function student_can_access_lesson_at_exact_scheduled_time(): void
    {
        // Arrange: Set up a course
        $courseStartDate = new DateTime('2025-05-13');
        $courseEndDate = new DateTime('2025-06-12');
        $course = new Course(
            name: 'A-Level Biology',
            startDate: $courseStartDate,
            endDate: $courseEndDate
        );

        // Create a student
        $student = new Student(name: 'Emma');

        // Create lesson scheduled for exactly 15/05/2025 at 10:00:00
        $lessonScheduledTime = new DateTime('2025-05-15 10:00:00');
        $lesson = new Lesson(
            title: 'Cell Structure',
            course: $course,
            scheduledDateTime: $lessonScheduledTime
        );

        // Student enrolled with valid dates
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

        // Act: Try to access at EXACTLY the scheduled time (10:00:00)
        $attemptDateTime = new DateTime('2025-05-15 10:00:00');
        $canAccess = $accessController->canAccess(
            student: $student,
            content: $lesson,
            dateTime: $attemptDateTime
        );

        // Assert: Access should be allowed at exact scheduled time (>= check)
        $this->assertTrue(
            $canAccess,
            'Student should be able to access lesson at the exact scheduled time'
        );
    }

    /**
     * @test
     * Rule: Student with multiple enrolments can access correct course
     */
    public function student_with_multiple_enrolments_can_access_enrolled_course(): void
    {
        // Arrange: Set up two different courses
        $biologyStartDate = new DateTime('2025-05-13');
        $biologyCourse = new Course(
            name: 'A-Level Biology',
            startDate: $biologyStartDate,
            endDate: new DateTime('2025-06-12')
        );

        $chemistryStartDate = new DateTime('2025-05-15');
        $chemistryCourse = new Course(
            name: 'A-Level Chemistry',
            startDate: $chemistryStartDate,
            endDate: new DateTime('2025-06-15')
        );

        // Create a student
        $student = new Student(name: 'Emma');

        // Create content for both courses
        $biologyLesson = new Lesson(
            title: 'Cell Structure',
            course: $biologyCourse,
            scheduledDateTime: new DateTime('2025-05-15 10:00:00')
        );

        $chemistryPrep = new PrepMaterial(
            title: 'Periodic Table',
            course: $chemistryCourse
        );

        // Student enrolled in both courses with different dates
        $biologyEnrolment = new Enrolment(
            student: $student,
            course: $biologyCourse,
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-30')
        );

        $chemistryEnrolment = new Enrolment(
            student: $student,
            course: $chemistryCourse,
            startDate: new DateTime('2025-05-10'),
            endDate: new DateTime('2025-06-20')
        );

        // Add both enrolments to student
        $student->addEnrolment($biologyEnrolment);
        $student->addEnrolment($chemistryEnrolment);

        // Access controller
        $accessController = new AccessController();

        // Act & Assert: Can access Biology lesson on 15/05/2025 at 10:01
        $attemptDateTime = new DateTime('2025-05-15 10:01:00');
        $canAccessBiology = $accessController->canAccess(
            student: $student,
            content: $biologyLesson,
            dateTime: $attemptDateTime
        );

        $this->assertTrue(
            $canAccessBiology,
            'Student should be able to access Biology lesson when enrolled in Biology'
        );

        // Act & Assert: Can access Chemistry prep on 16/05/2025
        $chemistryAttemptDate = new DateTime('2025-05-16');
        $canAccessChemistry = $accessController->canAccess(
            student: $student,
            content: $chemistryPrep,
            dateTime: $chemistryAttemptDate
        );

        $this->assertTrue(
            $canAccessChemistry,
            'Student should be able to access Chemistry prep when enrolled in Chemistry'
        );
    }

    /**
     * @test
     * Rule: Student cannot access course they are not enrolled in
     */
    public function student_cannot_access_course_they_are_not_enrolled_in(): void
    {
        // Arrange: Set up two courses
        $biologyCourse = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13'),
            endDate: new DateTime('2025-06-12')
        );

        $physicsCourse = new Course(
            name: 'A-Level Physics',
            startDate: new DateTime('2025-05-13'),
            endDate: new DateTime('2025-06-12')
        );

        // Create a student
        $student = new Student(name: 'Emma');

        // Create content for Physics (which student is NOT enrolled in)
        $physicsLesson = new Lesson(
            title: 'Mechanics',
            course: $physicsCourse,
            scheduledDateTime: new DateTime('2025-05-15 10:00:00')
        );

        // Student only enrolled in Biology
        $biologyEnrolment = new Enrolment(
            student: $student,
            course: $biologyCourse,
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-30')
        );

        $student->addEnrolment($biologyEnrolment);

        // Access controller
        $accessController = new AccessController();

        // Act: Try to access Physics lesson
        $attemptDateTime = new DateTime('2025-05-15 10:01:00');
        $canAccess = $accessController->canAccess(
            student: $student,
            content: $physicsLesson,
            dateTime: $attemptDateTime
        );

        // Assert: Access should be denied - not enrolled in Physics
        $this->assertFalse(
            $canAccess,
            'Student should not be able to access content from a course they are not enrolled in'
        );
    }

    /**
     * @test
     * Rule: Student with renewed/extended enrolment can access during new period
     */
    public function student_with_renewed_enrolment_can_access_during_extended_period(): void
    {
        // Arrange: Set up a course
        $course = new Course(
            name: 'A-Level Mathematics',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-08-31')
        );

        // Create a student
        $student = new Student(name: 'Sarah');

        // Create content
        $homework = new Homework(
            title: 'Calculus Problem Set',
            course: $course
        );

        // Student had an initial enrolment that ended on 31/05/2025
        $firstEnrolment = new Enrolment(
            student: $student,
            course: $course,
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-31')
        );

        // Student renewed/extended their enrolment starting 01/06/2025
        $renewedEnrolment = new Enrolment(
            student: $student,
            course: $course,
            startDate: new DateTime('2025-06-01'),
            endDate: new DateTime('2025-08-31')
        );

        // Add both enrolments
        $student->addEnrolment($firstEnrolment);
        $student->addEnrolment($renewedEnrolment);

        // Access controller
        $accessController = new AccessController();

        // Act & Assert: Can access during first enrolment period (15/05/2025)
        $duringFirstPeriod = new DateTime('2025-05-15');
        $canAccessFirst = $accessController->canAccess(
            student: $student,
            content: $homework,
            dateTime: $duringFirstPeriod
        );

        $this->assertTrue(
            $canAccessFirst,
            'Student should be able to access during first enrolment period'
        );

        // Act & Assert: Can access during renewed period (15/06/2025)
        $duringRenewedPeriod = new DateTime('2025-06-15');
        $canAccessRenewed = $accessController->canAccess(
            student: $student,
            content: $homework,
            dateTime: $duringRenewedPeriod
        );

        $this->assertTrue(
            $canAccessRenewed,
            'Student should be able to access during renewed enrolment period'
        );

        // Act & Assert: Cannot access during gap between enrolments (31/05/2025 23:59)
        // Note: This assumes enrolment dates are inclusive
        // If there's a gap, access should be denied
    }
}

