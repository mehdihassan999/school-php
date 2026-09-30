<?php
/**
 * Entity definitions for the generic CRUD editor (admin/content.php).
 * Each entity declares its table, fields and validation. Adding a new
 * managed content type is a matter of adding an entry here.
 */

if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

return [
    'programs' => [
        'title' => 'Academic Programs', 'singular' => 'Program', 'table' => 'academic_programs',
        'desc' => 'Early Years, Primary, Middle and Secondary programs shown on the Academics page.',
        'order' => 'sort_order, id', 'has_slug' => true, 'has_publish' => true,
        'fields' => [
            ['title', 'Program Title', 'text', true],
            ['level', 'Level', 'select', true, ['Early Years', 'Primary', 'Middle School', 'Secondary', 'General']],
            ['summary', 'Short Summary', 'textarea', false, null, 'Shown on homepage cards.'],
            ['content', 'Full Description', 'textarea', false, null, 'Blank line = new paragraph.'],
            ['image', 'Image', 'image', false, null, 'JPG, PNG, WEBP or GIF — max 4 MB.'],
            ['sort_order', 'Sort Order', 'number', false, null, 'Lower numbers appear first.'],
        ],
    ],

    'subjects' => [
        'title' => 'Subjects', 'singular' => 'Subject', 'table' => 'subjects',
        'desc' => 'Subjects listed under each level on the Academics page.',
        'order' => 'sort_order, id', 'has_slug' => false, 'has_publish' => true,
        'fields' => [
            ['name', 'Subject Name', 'text', true],
            ['level', 'Level', 'select', true, ['Early Years', 'Primary', 'Middle School', 'Secondary', 'All Levels']],
            ['description', 'Description', 'textarea', false, null, 'Shown as a tooltip on the Academics page.'],
            ['sort_order', 'Sort Order', 'number'],
        ],
    ],

    'faculty' => [
        'title' => 'Faculty & Staff', 'singular' => 'Faculty member', 'table' => 'faculty',
        'desc' => 'Profiles shown on the Faculty page. Use department "Leadership" for about-page leaders.',
        'order' => 'sort_order, id', 'has_slug' => false, 'has_publish' => true,
        'fields' => [
            ['name', 'Full Name', 'text', true],
            ['position', 'Position', 'text', true],
            ['department', 'Department', 'select', false, ['Leadership', 'Science', 'Mathematics', 'Languages', 'Arts', 'Sports', 'Administration', 'General']],
            ['qualification', 'Qualification', 'text', false, null, 'e.g. M.Sc., B.Ed.'],
            ['email', 'Email', 'email', false],
            ['bio', 'Short Biography', 'textarea', false],
            ['photo', 'Photo', 'image', false, null, 'Portrait image, max 4 MB.'],
            ['sort_order', 'Sort Order', 'number'],
        ],
    ],

    'testimonials' => [
        'title' => 'Testimonials', 'singular' => 'Testimonial', 'table' => 'testimonials',
        'desc' => 'Parent and student testimonials shown on the homepage.',
        'order' => 'sort_order, id', 'has_slug' => false, 'has_publish' => true,
        'fields' => [
            ['name', "Person's Name", 'text', true],
            ['designation', 'Designation / Relationship', 'text', false, null, 'e.g. Parent of Grade 4 student'],
            ['quote', 'Quote', 'textarea', true],
            ['photo', 'Photo', 'image'],
            ['sort_order', 'Sort Order', 'number'],
        ],
    ],

    'facilities' => [
        'title' => 'Campus & Facilities', 'singular' => 'Facility', 'table' => 'facilities',
        'desc' => 'Facility cards shown on the Campus page and homepage.',
        'order' => 'sort_order, id', 'has_slug' => false, 'has_publish' => true,
        'fields' => [
            ['title', 'Facility Title', 'text', true],
            ['description', 'Description', 'textarea', false],
            ['image', 'Image', 'image'],
            ['sort_order', 'Sort Order', 'number'],
        ],
    ],

    'activities' => [
        'title' => 'Student Life', 'singular' => 'Activity', 'table' => 'activities',
        'desc' => 'Sports, clubs, competitions, trips, cultural activities and achievements.',
        'order' => 'sort_order, id', 'has_slug' => false, 'has_publish' => true,
        'fields' => [
            ['title', 'Activity Title', 'text', true],
            ['category', 'Category', 'select', true, ['Sports', 'Clubs', 'Competitions', 'Field Trips', 'Cultural', 'Achievements']],
            ['description', 'Description', 'textarea', false],
            ['image', 'Image', 'image'],
            ['sort_order', 'Sort Order', 'number'],
        ],
    ],

    'news' => [
        'title' => 'News', 'singular' => 'News item', 'table' => 'news',
        'desc' => 'School news and stories. The slug is generated automatically.',
        'order' => 'published_at DESC', 'has_slug' => true, 'has_publish' => true,
        'fields' => [
            ['title', 'Title', 'text', true],
            ['category', 'Category', 'select', false, ['News', 'Event', 'Announcement', 'Achievement']],
            ['excerpt', 'Excerpt', 'textarea', false, null, 'Short summary shown in listings.'],
            ['content', 'Full Story', 'textarea', false, null, 'Blank line = new paragraph.'],
            ['published_at', 'Publication Date', 'datetime', false, null, 'Defaults to now.'],
            ['image', 'Featured Image', 'image'],
        ],
    ],

    'events' => [
        'title' => 'Events', 'singular' => 'Event', 'table' => 'events',
        'desc' => 'School events shown on the Events page and academic calendar.',
        'order' => 'event_date DESC', 'has_slug' => true, 'has_publish' => true,
        'fields' => [
            ['title', 'Event Title', 'text', true],
            ['event_date', 'Event Date & Time', 'datetime', true],
            ['location', 'Location', 'text', false, null, 'e.g. Main Auditorium'],
            ['excerpt', 'Excerpt', 'textarea', false],
            ['content', 'Details', 'textarea', false],
            ['image', 'Event Image', 'image'],
        ],
    ],

    'notices' => [
        'title' => 'Notice Board', 'singular' => 'Notice', 'table' => 'notices',
        'desc' => 'Publish holiday, exam, admission and announcement notices.',
        'order' => 'notice_date DESC', 'has_slug' => false, 'has_publish' => true,
        'fields' => [
            ['title', 'Notice Title', 'text', true],
            ['category', 'Category', 'select', false, ['General', 'Holiday', 'Examination', 'Admission', 'Announcement', 'Circular']],
            ['notice_date', 'Notice Date', 'datetime', false, null, 'Defaults to now.'],
            ['description', 'Description', 'textarea', false],
            ['document', 'Attachment', 'document', false, null, 'PDF or document, max 10 MB.'],
        ],
    ],

    'downloads' => [
        'title' => 'Downloads', 'singular' => 'Download', 'table' => 'downloads',
        'desc' => 'Public documents: admission forms, prospectus, calendars and schedules.',
        'order' => 'created_at DESC', 'has_slug' => false, 'has_publish' => true,
        'fields' => [
            ['title', 'Document Title', 'text', true],
            ['category', 'Category', 'select', false, ['Admission Form', 'Prospectus', 'Fee Structure', 'Academic Calendar', 'Exam Schedule', 'Notices', 'Other']],
            ['description', 'Short Description', 'text', false],
            ['file_name', 'Download File Name', 'text', false, null, 'Optional friendly name for visitors.'],
            ['file_path', 'File', 'document', false, null, 'Required for new records. PDF, DOC, XLS, PPT, TXT — max 10 MB.'],
        ],
    ],
];
