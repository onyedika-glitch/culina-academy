{{-- GDPR-compliant Cookie Consent Banner --}}
@if(config('services.google.measurement_id'))
<div id="cal-cookie-banner" role="dialog" aria-label="Cookie consent" aria-describedby="cal-cookie-text" style="position:fixed;bottom:0;left:0;right:0;z-index:999999;background:#1a1a2e;color:#fff;padding:16px 24px;display:none;box-shadow:0 -4px 20px rgba(0,0,0,.3);font-family:sans-serif;font-size:14px;line-height:1.5">
    <div style="max-width:1200px;margin:0 auto;display:flex;flex-wrap:wrap;align-items:center;gap:16px">
        <p id="cal-cookie-text" style="flex:1;min-width:260px;margin:0">
            {{ cal_i18n_text('We use cookies (including from Google Analytics) to improve your experience on our site.') }}
            {{ cal_i18n_text('By continuing, you agree to our') }}
            <a href="{{ cal_localized_path('/cookies-policy') }}" style="color:#ffb703;text-decoration:underline">{{ cal_i18n_text('Cookie Policy') }}</a>
            {{ cal_i18n_text('and') }}
            <a href="{{ cal_localized_path('/privacy-policy') }}" style="color:#ffb703;text-decoration:underline">{{ cal_i18n_text('Privacy Policy') }}</a>.
        </p>
        <div style="display:flex;gap:10px;flex-shrink:0">
            <button id="cal-cookie-accept" style="background:#d4a017;color:#fff;border:none;padding:10px 24px;border-radius:4px;cursor:pointer;font-weight:600;min-height:44px;min-width:100px;font-size:14px">{{ cal_i18n_text('Accept') }}</button>
            <button id="cal-cookie-decline" style="background:transparent;color:#ccc;border:1px solid #555;padding:10px 24px;border-radius:4px;cursor:pointer;font-weight:400;min-height:44px;min-width:100px;font-size:14px">{{ cal_i18n_text('Decline') }}</button>
        </div>
    </div>
</div>
<script>
(function(){
    var key = 'cal_cookie_consent';
    var val = localStorage.getItem(key);
    if (val === 'accepted') {
        if (typeof gtag !== 'undefined') {
            gtag('consent', 'update', {'analytics_storage': 'granted'});
        }
        return;
    }
    var banner = document.getElementById('cal-cookie-banner');
    if (!banner) return;
    function showBanner() { banner.style.display = 'block'; }
    if ('requestIdleCallback' in window) {
        requestIdleCallback(showBanner, { timeout: 5000 });
    } else {
        window.addEventListener('load', showBanner, { once: true });
    }
    document.getElementById('cal-cookie-accept').addEventListener('click', function(){
        localStorage.setItem(key, 'accepted');
        banner.style.display = 'none';
        // Enable GA (gtag was loaded but not sending without consent)
        if (typeof gtag !== 'undefined') {
            gtag('consent', 'update', {'analytics_storage': 'granted'});
        }
    });
    document.getElementById('cal-cookie-decline').addEventListener('click', function(){
        localStorage.setItem(key, 'declined');
        banner.style.display = 'none';
        if (typeof gtag !== 'undefined') {
            gtag('consent', 'update', {'analytics_storage': 'denied'});
        }
    });
})();
</script>
@endif
