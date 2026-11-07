@extends('admin.layout')

@section('title', 'Course Intake Management')
@section('heading', 'Course Intake Management')

@push('styles')
<style>
    .intake-course-picker { display: flex; gap: 12px; align-items: center; margin-bottom: 24px; flex-wrap: wrap; }
    .intake-course-picker select { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 15px; min-width: 300px; }
    .intake-course-picker .admin-btn { white-space: nowrap; }
    .intake-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; margin-bottom: 16px; transition: box-shadow .2s; }
    .intake-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    .intake-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 8px; }
    .intake-date { font-size: 18px; font-weight: 700; color: #1a1a2e; }
    .intake-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; margin-bottom: 16px; }
    .intake-stat { background: #f9fafb; padding: 12px; border-radius: 8px; text-align: center; }
    .intake-stat .val { font-size: 22px; font-weight: 700; color: #1a1a2e; }
    .intake-stat .lbl { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: .5px; }
    .override-section { background: #f8faff; border: 1px solid #dbeafe; border-radius: 8px; padding: 16px; margin-bottom: 12px; }
    .override-section h4 { margin: 0 0 10px; font-size: 14px; color: #1e40af; }
    .override-options { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .override-btn { padding: 7px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; border: 2px solid transparent; cursor: pointer; transition: all .2s; }
    .override-btn.auto { background: #f3f4f6; color: #374151; border-color: #d1d5db; }
    .override-btn.auto.active { background: #e0e7ff; border-color: #6366f1; color: #4338ca; }
    .override-btn.force-available { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
    .override-btn.force-available.active { background: #dcfce7; border-color: #22c55e; }
    .override-btn.force-full { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
    .override-btn.force-full.active { background: #fee2e2; border-color: #ef4444; }
    .intake-edit-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: end; }
    .intake-edit-grid label { font-size: 13px; font-weight: 600; color: #555; display: block; margin-bottom: 3px; }
    .intake-edit-grid input, .intake-edit-grid select, .intake-edit-grid textarea { width: 100%; padding: 7px 10px; border: 1px solid #d1d5db; border-radius: 5px; font-size: 14px; box-sizing: border-box; }
    .intake-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 12px; }
    .add-intake-card { background: #f8faff; border: 2px dashed #93c5fd; border-radius: 10px; padding: 24px; }
    .add-intake-card h3 { margin: 0 0 16px; color: #1e40af; font-size: 16px; }
    .cap-bar { height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; margin-top: 6px; }
    .cap-bar .fill { height: 100%; border-radius: 4px; transition: width .4s; }
    .cap-bar .fill.green { background: #10b981; }
    .cap-bar .fill.yellow { background: #f59e0b; }
    .cap-bar .fill.red { background: #ef4444; }
    .effective-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
    .effective-badge.available { background: #ecfdf5; color: #065f46; }
    .effective-badge.few { background: #fffbeb; color: #92400e; }
    .effective-badge.full { background: #fef2f2; color: #991b1b; }
    .override-tag { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; margin-left: 6px; }
    .override-tag.overridden { background: #fef3c7; color: #92400e; }
    @media (max-width: 640px) { .intake-stats { grid-template-columns: 1fr 1fr; } }
</style>
@endpush

@section('content')

{{-- Course Picker --}}
<div class="intake-course-picker">
    <form action="{{ route('admin.intakes.index') }}" method="GET" style="display:flex;gap:8px;align-items:center;flex:1;">
        <select name="course_id" onchange="this.form.submit()">
            <option value="">— Select a course —</option>
            @foreach($courses as $course)
            <option value="{{ $course->ID }}" @selected($courseId == $course->ID)>{{ $course->post_title }}</option>
            @endforeach
        </select>
    </form>
</div>

@if($courseId && $selectedCourse)
    <div style="margin-bottom: 20px;">
        <h3 style="margin:0 0 4px;color:#1a1a2e;">{{ $selectedCourse->post_title }}</h3>
        <p class="admin-muted" style="margin:0;">Manage intake dates, capacity, and availability overrides for this course.</p>
    </div>

    {{-- Existing Intake Dates --}}
    @forelse($intakes as $intake)
    @php
        $effectiveStatus = $intake->effectiveStatus();
        $remaining = $intake->remainingSpots();
        $totalBooked = $intake->enrolled_count + \App\Models\Booking::confirmedCount($intake->course_id, $intake->intake_date?->format('Y-m-d'));
        $capPercent = $intake->max_students > 0 ? min(100, round(($totalBooked / $intake->max_students) * 100)) : 0;
        $capColor = $capPercent < 70 ? 'green' : ($capPercent < 100 ? 'yellow' : 'red');
        $isOverridden = $intake->availability_override !== null;
    @endphp
    <div class="intake-card">
        <div class="intake-header">
            <div>
                <span class="intake-date">📅 {{ $intake->intake_date->format('d F Y') }}</span>
                @if($isOverridden)
                    <span class="override-tag overridden">OVERRIDDEN</span>
                @endif
            </div>
            <span class="effective-badge {{ $effectiveStatus }}">
                @if($effectiveStatus === 'available') ✅ Available
                @elseif($effectiveStatus === 'few') ⚠️ Limited Spots
                @else 🚫 Fully Booked
                @endif
            </span>
        </div>

        {{-- Live stats --}}
        <div class="intake-stats">
            <div class="intake-stat">
                <div class="val">{{ $totalBooked }}</div>
                <div class="lbl">Booked</div>
            </div>
            <div class="intake-stat">
                <div class="val">{{ $intake->max_students }}</div>
                <div class="lbl">Capacity</div>
            </div>
            <div class="intake-stat">
                <div class="val">{{ $remaining }}</div>
                <div class="lbl">Remaining</div>
            </div>
            <div class="intake-stat">
                <div class="val">{{ $capPercent }}%</div>
                <div class="lbl">Filled</div>
                <div class="cap-bar"><div class="fill {{ $capColor }}" style="width:{{ $capPercent }}%"></div></div>
            </div>
        </div>

        {{-- Override Controls --}}
        <div class="override-section">
            <h4>🎯 Availability Override</h4>
            <p style="margin:0 0 10px;font-size:13px;color:#666;">Override the automatic availability to move customers to preferred dates.</p>
            <div class="override-options">
                <form action="{{ route('admin.intakes.toggle-override', $intake->id) }}" method="POST" style="display:inline;">
                    @csrf @method('PUT')
                    <input type="hidden" name="availability_override" value="">
                    <button type="submit" class="override-btn auto {{ !$isOverridden ? 'active' : '' }}">⚙️ Automatic</button>
                </form>
                <form action="{{ route('admin.intakes.toggle-override', $intake->id) }}" method="POST" style="display:inline;">
                    @csrf @method('PUT')
                    <input type="hidden" name="availability_override" value="force_available">
                    <button type="submit" class="override-btn force-available {{ $intake->availability_override === 'force_available' ? 'active' : '' }}">✅ Force Available</button>
                </form>
                <form action="{{ route('admin.intakes.toggle-override', $intake->id) }}" method="POST" style="display:inline;">
                    @csrf @method('PUT')
                    <input type="hidden" name="availability_override" value="force_full">
                    <button type="submit" class="override-btn force-full {{ $intake->availability_override === 'force_full' ? 'active' : '' }}">🚫 Force Fully Booked</button>
                </form>
            </div>
        </div>

        {{-- Edit form --}}
        <form action="{{ route('admin.intakes.update', $intake->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="intake-edit-grid">
                <div>
                    <label>Intake Date</label>
                    <input type="date" name="intake_date" value="{{ $intake->intake_date->format('Y-m-d') }}" required>
                </div>
                <div>
                    <label>Max Students</label>
                    <input type="number" name="max_students" value="{{ $intake->max_students }}" min="1" max="999" required>
                </div>
                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="available" @selected($intake->status === 'available')>Available</option>
                        <option value="closed" @selected($intake->status === 'closed')>Closed</option>
                    </select>
                </div>
                <div style="grid-column: 1 / -1;">
                    <label>Admin Notes</label>
                    <input type="text" name="admin_notes" value="{{ $intake->admin_notes }}" placeholder="Internal notes (not shown to students)">
                </div>
                <input type="hidden" name="availability_override" value="{{ $intake->availability_override }}">
            </div>
            <div class="intake-actions">
                <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">Save Changes</button>
                <button type="button" class="admin-btn admin-btn-danger admin-btn-sm"
                    onclick="if(confirm('Delete this intake date?')) document.getElementById('delete-intake-{{ $intake->id }}').submit();">
                    Delete
                </button>
            </div>
        </form>
        <form id="delete-intake-{{ $intake->id }}" action="{{ route('admin.intakes.destroy', $intake->id) }}" method="POST" style="display:none;">
            @csrf @method('DELETE')
        </form>
    </div>
    @empty
        <div class="admin-card" style="text-align:center;padding:40px;">
            <p class="admin-muted">No intake dates configured for this course. Add one below.</p>
        </div>
    @endforelse

    {{-- Add New Intake Date --}}
    <div class="add-intake-card">
        <h3>➕ Add New Intake Date</h3>
        <form action="{{ route('admin.intakes.store') }}" method="POST">
            @csrf
            <input type="hidden" name="course_id" value="{{ $courseId }}">
            <div class="intake-edit-grid">
                <div>
                    <label>Intake Date *</label>
                    <input type="date" name="intake_date" required>
                </div>
                <div>
                    <label>Max Students *</label>
                    <input type="number" name="max_students" value="10" min="1" max="999" required>
                </div>
                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="available">Available</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div>
                    <label>Override</label>
                    <select name="availability_override">
                        <option value="">⚙️ Automatic</option>
                        <option value="force_available">✅ Force Available</option>
                        <option value="force_full">🚫 Force Fully Booked</option>
                    </select>
                </div>
                <div style="grid-column: 1 / -1;">
                    <label>Admin Notes</label>
                    <input type="text" name="admin_notes" placeholder="Optional internal notes">
                </div>
            </div>
            <div class="intake-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Add Intake Date</button>
            </div>
        </form>
    </div>

@else
    <div class="admin-card" style="text-align:center;padding:60px;">
        <p style="font-size:18px;color:#888;">👆 Select a course above to manage its intake dates and availability.</p>
    </div>
@endif

@endsection
