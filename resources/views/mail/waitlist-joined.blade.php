New waitlist signup

Name: {{ $entry->first_name }} {{ $entry->last_name }}
Email: {{ $entry->email }}
Phone: {{ $entry->phone ?: 'N/A' }}
Course: {{ $courseTitle ?: ('ID ' . $entry->course_id) }}
Intake date: {{ $entry->intake_date ?: 'N/A' }}
Notes: {{ $entry->notes ?: 'N/A' }}
Language: {{ $entry->locale }}
Page: {{ $entry->page_url }}