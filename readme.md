# Learning Management System (LMS) - Backend Domain Logic

A simplified backend implementation for a Learning Management System that governs student access to course content based on enrolment and scheduling rules. Built using Test-Driven Development(TDD) in PHP.

## Overview

This project implements the core business logic for determining whether a student can access specific content (lessons, homework, prep materials) based on:
- Course schedule (start/end dates)
- Student enrolment status
- Content-specific availability rules
- Multiple enrolment handling

## Features

### Access Control Rules
- **Course Start Date** - Students cannot access content before the course begins
- **Course End Date** - Properly handles courses with optional end dates (ongoing courses)
- **Enrolment Validity** - Students must have an active enrolment during the access attempt
- **Lesson Scheduling** - Lessons are only available from their scheduled date/time
- **Content Types** - Different rules for Lessons, Homework, and Prep Materials
- **Multiple Enrolments** - Handles students enrolled in multiple courses and renewed enrolments

### Domain Models
- **Student** - Manages multiple enrolments
- **Course** - Contains start/end dates and content
- **Enrolment** - Links students to courses with date ranges
- **Content** (abstract) - Base class for all content types
  - **Lesson** - Available from scheduled datetime
  - **Homework** - Available from course start
  - **PrepMaterial** - Available from course start
- **AccessController** - Validates access based on all rules

## 🛠️ Tech Stack

- **PHP 8.1+** - Modern PHP with constructor property promotion
- **PHPUnit 10.x** - Testing framework
- **Composer** - Dependency management
- **PSR-4** - Autoloading standard

## 🚀 Installation

```bash
# Clone the repository
git clone https://github.com/azamzamy/learning-mgmt-system.git
cd learning-mgmt-system

# Install dependencies
composer install
```

## 🧪 Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run with detailed output
vendor/bin/phpunit --testdox

# Run with colors
vendor/bin/phpunit --colors=always --testdox
```

### Test Coverage

**8 comprehensive tests, 10 assertions** covering:
1. ✔ Student cannot access content before course starts
2. ✔ Student cannot access content after enrolment ends
3. ✔ Student can access content in ongoing course with no end date
4. ✔ Student cannot access lesson before scheduled time
5. ✔ Student can access lesson after scheduled time
6. ✔ Student with multiple enrolments can access enrolled course
7. ✔ Student cannot access course they are not enrolled in
8. ✔ Student with renewed enrolment can access during extended period

## Usage Example

```php
<?php

use LMS\AccessController;
use LMS\Course;
use LMS\Student;
use LMS\Enrolment;
use LMS\Lesson;
use DateTime;

// Create a course
$course = new Course(
    name: 'A-Level Biology',
    startDate: new DateTime('2025-05-13'),
    endDate: new DateTime('2025-06-12')
);

// Create a student
$student = new Student(name: 'Emma');

// Create a lesson
$lesson = new Lesson(
    title: 'Cell Structure',
    course: $course,
    scheduledDateTime: new DateTime('2025-05-15 10:00:00')
);

// Enrol the student
$enrolment = new Enrolment(
    student: $student,
    course: $course,
    startDate: new DateTime('2025-05-01'),
    endDate: new DateTime('2025-05-30')
);
$student->addEnrolment($enrolment);

// Check access
$accessController = new AccessController();
$canAccess = $accessController->canAccess(
    student: $student,
    content: $lesson,
    dateTime: new DateTime('2025-05-15 10:01:00')
);

// Result: true (student can access after lesson starts)
```

## Architecture

### Clean Code Principles
- **Single Responsibility** - Each class has one clear purpose
- **Encapsulation** - Business logic contained within domain models
- **Dependency Inversion** - Abstract Content base class for extensibility
- **Test-Driven Development** - All features developed using Red-Green-Refactor cycle

### AccessController Design
The `AccessController` uses private helper methods for clarity:
- `isCourseActiveOn()` - Validates course date range
- `hasValidEnrolment()` - Checks for active enrolment
- `isContentAvailableOn()` - Handles content-specific rules

## Development Process

This project was built following strict TDD methodology:

### Red-Green-Refactor Cycle
1. **RED** - Write a failing test
2. **GREEN** - Write minimal code to pass the test
3. **REFACTOR** - Improve code structure without changing behavior


**Built with ❤️ using Test-Driven Development**
