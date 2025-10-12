<?php

namespace LMS;

abstract class Content
{
    public function __construct(
        protected string $title,
        protected Course $course
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }
}
