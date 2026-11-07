New course enrollment

Name: {{ $enrollment->first_name }} {{ $enrollment->last_name }}
Email: {{ $enrollment->email }}
Phone: {{ $enrollment->phone ?: 'N/A' }}
Intake ID: {{ $enrollment->intake_id }}
Course: {{ $courseTitle ?: 'N/A' }}
Notes: {{ $enrollment->notes ?: 'N/A' }}
Status: {{ $enrollment->status }}