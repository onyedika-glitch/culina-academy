New course application

Name: {{ $application->first_name }} {{ $application->surname }}
Email: {{ $application->email }}
Phone: {{ $application->phone }} ({{ $application->intl_code }})
Country: {{ $application->country }}

Address: {{ $application->address }}, {{ $application->city }}, {{ $application->postcode }}
Passport country: {{ $application->passport_country }}
Visa required: {{ $application->visa }}
Heard about us: {{ $application->heard }}
Preferred start: {{ $application->start_month }} {{ $application->start_year }}
Cookery ability: {{ $application->cookery_ability }}

Courses:
@foreach($application->courses as $course)
- {{ $course }}
@endforeach

Motivation:
{{ $application->motivation }}

Course ID: {{ $application->course_id ?: 'N/A' }}
Intake date: {{ $application->intake_date ?: 'N/A' }}
Language: {{ $application->locale }}
Page: {{ $application->page_url }}