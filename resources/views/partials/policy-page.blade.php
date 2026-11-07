<main id="main" class="site-main cal-policy-page">
    @php cal_render_page_banner($bannerClass ?? 'cal-policy', $title ?? ($page->post_title ?? '')); @endphp

    <section class="cal-policy-sub-bar">
        <div class="cal-policy-shell">
            @php cal_render_breadcrumbs($breadcrumbClass ?? 'cal-policy-breadcrumbs'); @endphp
        </div>
    </section>

    <section class="cal-policy-content">
        <div class="cal-policy-shell">
            <div class="cal-policy-entry">
                {!! \App\Services\CulinaAcademy::theContent() !!}
            </div>
        </div>
    </section>
</main>