<?php

namespace LMS\Tests;

use DateTime;
use LMS\Course;
use PHPUnit\Framework\TestCase;

class CourseTest extends TestCase
{
    public function test_course_can_add_lesson_with_scheduled_datetime(): void
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

        $this->assertNotEmpty($lessonId);
        
        $lesson = $course->getContent($lessonId);
        $this->assertNotNull($lesson);
        $this->assertInstanceOf(\LMS\ContentItem::class, $lesson);
        $this->assertEquals('lesson', $lesson->getType());
        $this->assertEquals('Cell Structure', $lesson->getTitle());
        $this->assertEquals('2025-05-15 10:00:00', $lesson->getScheduledDateTime()->format('Y-m-d H:i:s'));
    }

    public function test_course_can_add_homework(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13')
        );

        $homeworkId = $course->addHomework(title: 'Label a Plant Cell');

        $this->assertNotEmpty($homeworkId);
        
        $homework = $course->getContent($homeworkId);
        $this->assertNotNull($homework);
        $this->assertInstanceOf(\LMS\ContentItem::class, $homework);
        $this->assertEquals('homework', $homework->getType());
        $this->assertEquals('Label a Plant Cell', $homework->getTitle());
        $this->assertNull($homework->getScheduledDateTime());
    }

    public function test_course_can_add_prep_material(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13')
        );

        $prepMaterialId = $course->addPrepMaterial(title: 'Reading Guide');

        $this->assertNotEmpty($prepMaterialId);
        
        $prepMaterial = $course->getContent($prepMaterialId);
        $this->assertNotNull($prepMaterial);
        $this->assertInstanceOf(\LMS\ContentItem::class, $prepMaterial);
        $this->assertEquals('prep_material', $prepMaterial->getType());
        $this->assertEquals('Reading Guide', $prepMaterial->getTitle());
        $this->assertNull($prepMaterial->getScheduledDateTime());
    }

    public function test_course_returns_null_for_non_existent_content(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13')
        );

        $content = $course->getContent('non-existent-id');

        $this->assertNull($content);
    }

    public function test_course_is_active_after_start_date(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13'),
            endDate: new DateTime('2025-06-12')
        );

        $isActive = $course->isActive(new DateTime('2025-05-15'));

        $this->assertTrue($isActive);
    }

    public function test_course_is_not_active_before_start_date(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13'),
            endDate: new DateTime('2025-06-12')
        );

        $isActive = $course->isActive(new DateTime('2025-05-01'));

        $this->assertFalse($isActive);
    }

    public function test_course_is_active_at_exact_start_datetime(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13 00:00:00')
        );

        $isActive = $course->isActive(new DateTime('2025-05-13 00:00:00'));

        $this->assertTrue($isActive);
    }

    public function test_course_is_not_active_after_end_date(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13'),
            endDate: new DateTime('2025-06-12')
        );

        $isActive = $course->isActive(new DateTime('2025-06-15'));

        $this->assertFalse($isActive);
    }

    public function test_course_with_no_end_date_is_active_indefinitely(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13')
        );

        $isActive = $course->isActive(new DateTime('2026-12-31'));

        $this->assertTrue($isActive);
    }

    public function test_lesson_is_not_available_before_scheduled_datetime(): void
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

        $isAvailable = $course->isContentAvailable(
            contentId: $lessonId,
            at: new DateTime('2025-05-15 09:59:00')
        );

        $this->assertFalse($isAvailable);
    }

    public function test_lesson_is_available_after_scheduled_datetime(): void
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

        $isAvailable = $course->isContentAvailable(
            contentId: $lessonId,
            at: new DateTime('2025-05-15 10:01:00')
        );

        $this->assertTrue($isAvailable);
    }

    public function test_lesson_is_available_at_exact_scheduled_datetime(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13')
        );

        $lessonId = $course->addLesson(
            title: 'Cell Structure',
            scheduledDateTime: new DateTime('2025-05-15 10:00:00')
        );

        $isAvailable = $course->isContentAvailable(
            contentId: $lessonId,
            at: new DateTime('2025-05-15 10:00:00')
        );

        $this->assertTrue($isAvailable);
    }

    public function test_homework_is_always_available(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13')
        );

        $homeworkId = $course->addHomework(title: 'Plant Cell Assignment');

        $isAvailable = $course->isContentAvailable(
            contentId: $homeworkId,
            at: new DateTime('2025-05-13')
        );

        $this->assertTrue($isAvailable);
    }

    public function test_prep_material_is_always_available(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13')
        );

        $prepId = $course->addPrepMaterial(title: 'Reading Guide');

        $isAvailable = $course->isContentAvailable(
            contentId: $prepId,
            at: new DateTime('2025-05-13')
        );

        $this->assertTrue($isAvailable);
    }

    public function test_non_existent_content_is_not_available(): void
    {
        $course = new Course(
            name: 'A-Level Biology',
            startDate: new DateTime('2025-05-13')
        );

        $isAvailable = $course->isContentAvailable(
            contentId: 'non-existent-id',
            at: new DateTime('2025-05-15')
        );

        $this->assertFalse($isAvailable);
    }
}
