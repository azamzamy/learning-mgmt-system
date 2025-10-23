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
- **Course** (Aggregate Root) - Manages course schedule and all content items
- **Student** (Aggregate Root) - Manages enrolments and orchestrates access control
- **ContentItem** (Value Object) - Represents lessons, homework, and prep materials with availability rules

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

**11 comprehensive tests, 13 assertions** covering:
1. ✔ Student cannot access content before course starts
2. ✔ Student can access prep material after course starts
3. ✔ Student can access content on exact course start date
4. ✔ Student cannot access content after enrolment ends
5. ✔ Student can access content in ongoing course with no end date
6. ✔ Student cannot access lesson before scheduled time
7. ✔ Student can access lesson after scheduled time
8. ✔ Student can access lesson at exact scheduled time
9. ✔ Student with multiple enrolments can access enrolled course
10. ✔ Student cannot access course they are not enrolled in
11. ✔ Student with renewed enrolment can access during extended period


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

## Development Process

This project was built following strict TDD methodology:

### Red-Green-Refactor Cycle
1. **RED** - Write a failing test
2. **GREEN** - Write minimal code to pass the test
3. **REFACTOR** - Improve code structure without changing behavior

## Architecture:

Decided to refactor the implementation to use **Aggregate Root pattern** from Domain-Driven Design (DDD) as an opportunity to learn more about the approach, reducing complexity by consolidating related entities into aggregates.

#### **Course Aggregate Root**
The `Course` class serves as an aggregate root that owns and manages its content:

**Key principle:** Content cannot exist independently of a Course - it's always accessed through the Course aggregate.

#### **Student Aggregate Root**
The `Student` class manages enrolments and orchestrates access control:

**Key principle:** Business logic for access control lives in the domain objects, not external services.

---

## Consious Design Decisions:

### 1. ContentItem Objects vs Arrays

In the aggregate root refactoring, I chose to represent content as **ContentItem value objects** rather than associative arrays:

```php
// Array approach
$content = [
    'type' => 'lesson',
    'title' => 'Cell Structure',
    'scheduledDateTime' => new DateTime('2025-05-15 10:00:00')
];

// Object approach
$content = new ContentItem(
    type: 'lesson',
    title: 'Cell Structure',
    scheduledDateTime: new DateTime('2025-05-15 10:00:00')
);
```

#### Why Objects Over Arrays?

##### 1. **Type Safety**
```php
// With arrays - typos cause runtime errors
echo $content['titel'];  // null, silent failure

// With objects - typos caught at compile time
echo $content->getTitel();  // Fatal error: method doesn't exist
```

##### 2. **IDE Support**
```php
// With arrays - no autocomplete
$content['???']  // You need to remember the keys

// With objects - full autocomplete
$content->get...  // IDE suggests: getType(), getTitle(), getScheduledDateTime()
```

##### 3. **Encapsulation**
```php
// With arrays - logic scattered across the codebase
if ($content['type'] === 'lesson') {
    return $at >= $content['scheduledDateTime'];
}

// With objects - logic encapsulated in the value object
return $content->isAvailableAt($at);  // Business logic lives where it belongs
```

##### 4. **Refactoring Safety**
```php
// With arrays - changing structure breaks everything
$content['scheduled_time']  // Changed from 'scheduledDateTime'
// All code using this key must be manually found and updated ❌

// With objects - change internal implementation, keep public API
class ContentItem {
    private ?DateTime $scheduledDateTime;  // Can rename this freely
    
    public function getScheduledDateTime(): ?DateTime {  // Public API stays same
        return $this->scheduledDateTime;
    }
}
```

##### 5. **Prevents Invalid State**
```php
// With arrays - can create invalid data
$content = [
    'type' => 'lesson',
    'scheduledDateTime' => null  // Invalid! Lessons need scheduled time ❌
];

// With objects - constructor ensures valid state
class ContentItem {
    public function __construct(
        private string $type,
        private string $title,
        private ?DateTime $scheduledDateTime = null
    ) {
        // Could add validation here
        if ($type === 'lesson' && $scheduledDateTime === null) {
            throw new InvalidArgumentException('Lessons require scheduled time');
        }
    }
}
```

### 2. Separating StudentAAccessControlTests from StudentTests

To maintain clarity and separation of concerns, I created a dedicated test class `StudentAccessControlTests` specifically for access control scenarios, distinct from general `StudentTests`.

---

## AI Usage

This project utilized AI assistance (GitHub Copilot) in the following areas:

- **Test Case Generation**: AI was used to generate comprehensive test cases covering various scenarios; positive, edge cases, and boundary conditions.
- **Documentation**: Some parts of this README with AI support.
