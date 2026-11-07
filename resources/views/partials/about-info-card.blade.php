@php
    $icon = cal_image_url(cal_about_field($iconKey));
    $heading = trim((string) cal_about_field($headingKey));
    $text = trim((string) cal_about_field($textKey));

    // Guard against mis-mapped content: long "headings" must not render as bold h3 body copy.
    // If heading looks like a paragraph, demote it to body text.
    if ($heading !== '' && mb_strlen($heading) > 80) {
        if ($text === '' || $text === $heading) {
            $text = $heading;
            $heading = '';
        } else {
            // Keep short first sentence as title when possible.
            if (preg_match('/^(.{8,60}?)([.!?。！？]|\s[-–—]\s)/u', $heading, $m)) {
                $heading = trim($m[1]);
            } else {
                $heading = mb_substr($heading, 0, 48) . '…';
            }
        }
    }
@endphp
@if($icon || $heading || $text)
<article class="cal-about-info-card">
    @if($icon)
    <div class="cal-about-info-icon">
        <img src="{{ $icon }}" alt="" loading="lazy" decoding="async">
    </div>
    @endif
    @if($heading)<h3>{{ $heading }}</h3>@endif
    @if($text)<p>{{ $text }}</p>@endif
</article>
@endif