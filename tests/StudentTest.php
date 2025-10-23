<?php

namespace LMS\Tests;

use DateTime;
use LMS\Student;
use PHPUnit\Framework\TestCase;

class StudentTest extends TestCase
{
    public function test_student_can_enrol_in_course(): void
    {
        $student = new Student(name: 'Emma');

        $student->enrolInCourse(
            courseId: 'course-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-31')
        );

        $hasEnrolment = $student->hasActiveEnrolment(
            courseId: 'course-123',
            at: new DateTime('2025-05-15')
        );

        $this->assertTrue($hasEnrolment);
    }

    public function test_student_has_active_enrolment_during_period(): void
    {
        $student = new Student(name: 'Emma');
        $student->enrolInCourse(
            courseId: 'course-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-31')
        );

        $this->assertTrue(
            $student->hasActiveEnrolment('course-123', new DateTime('2025-05-01'))
        );

        $this->assertTrue(
            $student->hasActiveEnrolment('course-123', new DateTime('2025-05-15'))
        );

        $this->assertTrue(
            $student->hasActiveEnrolment('course-123', new DateTime('2025-05-31'))
        );
    }

    public function test_student_has_no_active_enrolment_before_start_date(): void
    {
        $student = new Student(name: 'Emma');
        $student->enrolInCourse(
            courseId: 'course-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-31')
        );

        $hasEnrolment = $student->hasActiveEnrolment(
            courseId: 'course-123',
            at: new DateTime('2025-04-30')
        );

        $this->assertFalse($hasEnrolment);
    }

    public function test_student_has_no_active_enrolment_after_end_date(): void
    {
        $student = new Student(name: 'Emma');
        $student->enrolInCourse(
            courseId: 'course-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-31')
        );

        $hasEnrolment = $student->hasActiveEnrolment(
            courseId: 'course-123',
            at: new DateTime('2025-06-01')
        );

        $this->assertFalse($hasEnrolment);
    }

    public function test_student_has_no_enrolment_for_non_enrolled_course(): void
    {
        $student = new Student(name: 'Emma');
        $student->enrolInCourse(
            courseId: 'course-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-31')
        );

        $hasEnrolment = $student->hasActiveEnrolment(
            courseId: 'course-999',
            at: new DateTime('2025-05-15')
        );

        $this->assertFalse($hasEnrolment);
    }

    public function test_student_can_have_multiple_enrolments(): void
    {
        $student = new Student(name: 'Emma');

        $student->enrolInCourse(
            courseId: 'biology-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-31')
        );

        $student->enrolInCourse(
            courseId: 'chemistry-456',
            startDate: new DateTime('2025-05-10'),
            endDate: new DateTime('2025-06-10')
        );

        $this->assertTrue(
            $student->hasActiveEnrolment('biology-123', new DateTime('2025-05-15'))
        );

        $this->assertTrue(
            $student->hasActiveEnrolment('chemistry-456', new DateTime('2025-05-15'))
        );
    }

    public function test_student_can_have_renewed_enrolment_for_same_course(): void
    {
        $student = new Student(name: 'Sarah');

        $student->enrolInCourse(
            courseId: 'math-101',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-31')
        );

        $student->enrolInCourse(
            courseId: 'math-101',
            startDate: new DateTime('2025-07-01'),
            endDate: new DateTime('2025-08-31')
        );

        $this->assertTrue(
            $student->hasActiveEnrolment('math-101', new DateTime('2025-05-15'))
        );

        $this->assertTrue(
            $student->hasActiveEnrolment('math-101', new DateTime('2025-07-15'))
        );

        $this->assertFalse(
            $student->hasActiveEnrolment('math-101', new DateTime('2025-06-26'))
        );
    }

    public function test_student_can_get_name(): void
    {
        $student = new Student(name: 'Emma Watson');

        $this->assertEquals('Emma Watson', $student->getName());
    }
}
