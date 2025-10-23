<?php

namespace LMS\Tests;

use DateTime;
use LMS\Course;
use LMS\Student;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Course::isAccessibleTo() - the main orchestration method
 * that validates student access to course content
 */
class StudentAccessControlTest extends TestCase
{
    public function student_cannot_access_content_before_course_starts(): void
    {
        $course = new Course(
            name: 'A-Level Chemistry',
            startDate: new DateTime('2025-05-13')
        );

        $prepMaterialId = $course->addPrepMaterial(title: 'Periodic Table Overview');

        $student = new Student(name: 'Emma');
        $student->enrolInCourse(
            courseId: 'chem-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-30')
        );

        $canAccess = $course->isAccessibleTo(
            student: $student,
            courseId: 'chem-123',
            contentId: $prepMaterialId,
            at: new DateTime('2025-05-01')
        );

        $this->assertFalse($canAccess, 'Student should not access content before course starts');
    }

    public function student_can_access_prep_material_after_course_starts(): void
    {
        // Arrange
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13'),
            endDate: new DateTime('2025-06-12')
        );

        $prepMaterialId = $course->addPrepMaterial(title: 'Biology Reading Guide');

        $student = new Student(name: 'Emma');
        $student->enrolInCourse(
            courseId: 'bio-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-30')
        );

        $canAccess = $course->isAccessibleTo(
            student: $student,
            courseId: 'bio-123',
            contentId: $prepMaterialId,
            at: new DateTime('2025-05-13')
        );

        // Assert
        $this->assertTrue($canAccess, 'Student should access prep material after course starts');
    }

    public function student_can_access_content_on_exact_course_start_date(): void
    {
        // Arrange
        $course = new Course(
            name: 'A-Level Physics',
            startDate: new DateTime('2025-05-13 00:00:00')
        );

        $prepMaterialId = $course->addPrepMaterial(title: 'Physics Introduction');

        $student = new Student(name: 'David');
        $student->enrolInCourse(
            courseId: 'phys-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-31')
        );

        $canAccess = $course->isAccessibleTo(
            student: $student,
            courseId: 'phys-123',
            contentId: $prepMaterialId,
            at: new DateTime('2025-05-13 00:00:00')
        );

        // Assert
        $this->assertTrue($canAccess, 'Student should access content at exact course start time');
    }

    public function student_cannot_access_content_after_enrolment_ends(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13'),
            endDate: new DateTime('2025-06-12')
        );

        $homeworkId = $course->addHomework(title: 'Label a Plant Cell');

        $student = new Student(name: 'Emma');
        $student->enrolInCourse(
            courseId: 'bio-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-20')
        );

        $canAccess = $course->isAccessibleTo(
            student: $student,
            courseId: 'bio-123',
            contentId: $homeworkId,
            at: new DateTime('2025-05-21')
        );

        // Assert
        $this->assertFalse($canAccess, 'Student should not access content after enrolment ends');
    }

    public function student_can_access_content_in_ongoing_course(): void
    {
        // Arrange: Course with no end date
        $course = new Course(
            name: 'A-Level Physics',
            startDate: new DateTime('2025-05-13')
        );

        $prepMaterialId = $course->addPrepMaterial(title: 'Physics Fundamentals');

        $student = new Student(name: 'John');
        $student->enrolInCourse(
            courseId: 'phys-123',
            startDate: new DateTime('2025-05-10'),
            endDate: new DateTime('2025-12-31')
        );

        $canAccess = $course->isAccessibleTo(
            student: $student,
            courseId: 'phys-123',
            contentId: $prepMaterialId,
            at: new DateTime('2025-05-15')
        );

        $this->assertTrue($canAccess, 'Student should access content in ongoing course');
    }

    public function student_cannot_access_lesson_before_scheduled_time(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13'),
            endDate: new DateTime('2025-06-12')
        );

        $lessonId = $course->addLesson(
            title: 'Cell Structure',
            scheduledDateTime: new DateTime('2025-05-15 10:00:00')
        );

        $student = new Student(name: 'Emma');
        $student->enrolInCourse(
            courseId: 'bio-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-30')
        );

        $canAccess = $course->isAccessibleTo(
            student: $student,
            courseId: 'bio-123',
            contentId: $lessonId,
            at: new DateTime('2025-05-15 09:59:00')
        );

        $this->assertFalse($canAccess, 'Student should not access lesson before scheduled time');
    }

    public function student_can_access_lesson_after_scheduled_time(): void
    {
        // Arrange
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13'),
            endDate: new DateTime('2025-06-12')
        );

        $lessonId = $course->addLesson(
            title: 'Cell Structure',
            scheduledDateTime: new DateTime('2025-05-15 10:00:00')
        );

        $student = new Student(name: 'Emma');
        $student->enrolInCourse(
            courseId: 'bio-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-30')
        );

        $canAccess = $course->isAccessibleTo(
            student: $student,
            courseId: 'bio-123',
            contentId: $lessonId,
            at: new DateTime('2025-05-15 10:01:00')
        );

        $this->assertTrue($canAccess, 'Student should access lesson after scheduled time');
    }

    public function student_can_access_lesson_at_exact_scheduled_time(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13'),
            endDate: new DateTime('2025-06-12')
        );

        $lessonId = $course->addLesson(
            title: 'Cell Structure',
            scheduledDateTime: new DateTime('2025-05-15 10:00:00')
        );

        $student = new Student(name: 'Emma');
        $student->enrolInCourse(
            courseId: 'bio-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-30')
        );

        $canAccess = $course->isAccessibleTo(
            student: $student,
            courseId: 'bio-123',
            contentId: $lessonId,
            at: new DateTime('2025-05-15 10:00:00')
        );

        $this->assertTrue($canAccess, 'Student should access lesson at exact scheduled time');
    }

    public function student_with_multiple_enrolments_can_access_correct_course(): void
    {
        $biologyCourse = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13'),
            endDate: new DateTime('2025-06-12')
        );

        $chemistryCourse = new Course(
            name: 'A-Level Chemistry',
            startDate: new DateTime('2025-05-15'),
            endDate: new DateTime('2025-06-15')
        );

        $bioLessonId = $biologyCourse->addLesson(
            title: 'Cell Structure',
            scheduledDateTime: new DateTime('2025-05-15 10:00:00')
        );

        $chemPrepId = $chemistryCourse->addPrepMaterial(title: 'Periodic Table');

        $student = new Student(name: 'Emma');
        $student->enrolInCourse(
            courseId: 'bio-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-30')
        );
        $student->enrolInCourse(
            courseId: 'chem-456',
            startDate: new DateTime('2025-05-10'),
            endDate: new DateTime('2025-06-20')
        );

        $canAccessBio = $biologyCourse->isAccessibleTo(
            student: $student,
            courseId: 'bio-123',
            contentId: $bioLessonId,
            at: new DateTime('2025-05-15 10:01:00')
        );
        $this->assertTrue($canAccessBio, 'Should access Biology lesson');

        $canAccessChem = $chemistryCourse->isAccessibleTo(
            student: $student,
            courseId: 'chem-456',
            contentId: $chemPrepId,
            at: new DateTime('2025-05-16')
        );
        $this->assertTrue($canAccessChem, 'Should access Chemistry prep');
    }

    public function student_cannot_access_non_enrolled_course(): void
    {

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

        $physicsLessonId = $physicsCourse->addLesson(
            title: 'Mechanics',
            scheduledDateTime: new DateTime('2025-05-15 10:00:00')
        );

        $student = new Student(name: 'Emma');
        $student->enrolInCourse(
            courseId: 'bio-123',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-30')
        );

        $canAccess = $physicsCourse->isAccessibleTo(
            student: $student,
            courseId: 'phys-999',
            contentId: $physicsLessonId,
            at: new DateTime('2025-05-15 10:01:00')
        );

        $this->assertFalse($canAccess, 'Student should not access non-enrolled course');
    }

    public function student_with_renewed_enrolment_can_access_during_both_periods(): void
    {
        // Arrange
        $course = new Course(
            name: 'A-Level Mathematics',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-08-31')
        );

        $homeworkId = $course->addHomework(title: 'Calculus Problem Set');

        $student = new Student(name: 'Sarah');
        $student->enrolInCourse(
            courseId: 'math-101',
            startDate: new DateTime('2025-05-01'),
            endDate: new DateTime('2025-05-31')
        );
        $student->enrolInCourse(
            courseId: 'math-101',
            startDate: new DateTime('2025-06-01'),
            endDate: new DateTime('2025-08-31')
        );

        $canAccessFirst = $course->isAccessibleTo(
            student: $student,
            courseId: 'math-101',
            contentId: $homeworkId,
            at: new DateTime('2025-05-15')
        );
        $this->assertTrue($canAccessFirst, 'Should access during first enrolment period');

        $canAccessRenewed = $course->isAccessibleTo(
            student: $student,
            courseId: 'math-101',
            contentId: $homeworkId,
            at: new DateTime('2025-06-15')
        );
        $this->assertTrue($canAccessRenewed, 'Should access during renewed enrolment period');
    }
}
