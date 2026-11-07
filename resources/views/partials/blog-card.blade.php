@php
    $thumbId = $post->getMeta('_thumbnail_id');
    $thumbUrl = $thumbId ? \App\Services\AcfFieldService::attachmentUrl((int) $thumbId) : '';
    if (!$thumbUrl) {
        $imageIntro = $post->getMeta('image_intro');
        if ($imageIntro) {
            $thumbUrl = \App\Services\AcfFieldService::migrateUrl($imageIntro);
        }
    }
    if (!$thumbUrl && !empty($post->post_content)) {
        if (preg_match('/<img[^>]+src=(["\'])(.*?)\1/i', $post->post_content, $m)) {
            $thumbUrl = \App\Services\AcfFieldService::migrateUrl($m[2]);
        }
    }
    $content = strip_tags($post->post_content ?? '');
    $excerpt = \Illuminate\Support\Str::words($content, 60, '…');
    $date = $post->post_date ? date('F j, Y', strtotime($post->post_date)) : '';
@endphp

<article id="post-{{ $post->ID }}" class="cal-blog-card">
    @if($thumbUrl)
    <a class="cal-blog-card-thumb" href="{{ cal_localized_path('/blog/' . $post->post_name) }}">
        <img src="{{ $thumbUrl }}" alt="{{ $post->post_title }}" loading="lazy">
    </a>
    @endif
    <div class="cal-blog-card-body">
        <h3 class="cal-blog-card-title">
            <a href="{{ cal_localized_path('/blog/' . $post->post_name) }}">{{ $post->post_title }}</a>
        </h3>
        <div class="cal-blog-card-excerpt">
            <p>{{ $excerpt }}</p>
            <a class="cal-blog-read-more" href="{{ cal_localized_path('/blog/' . $post->post_name) }}">{{ cal_i18n_text('Continue') }}</a>
        </div>
        @if($date)
        <div class="cal-blog-card-meta">
            <span class="cal-blog-card-date">{{ $date }}</span>
        </div>
        @endif
    </div>
</article>