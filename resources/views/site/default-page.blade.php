@extends('layouts.app')
@section('content')
<?php cal_render_page_banner('cal-default', get_the_title()); ?>
<section class="cal-section">
    <div class="cal-shell">
        {!! \App\Services\CulinaAcademy::theContent() !!}
    </div>
</section>
@endsection