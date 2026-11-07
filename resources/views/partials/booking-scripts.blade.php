<script>
window.caData = {
    ajaxUrl: '{{ cal_localized_path('/booking/ajax') }}',
    nonce: '{{ csrf_token() }}',
    language: document.documentElement.lang || '{{ \App\Services\PolylangService::htmlLang() }}',
    coursesUrl: '{{ cal_localized_path('/cooking-courses') }}',
    enrollPage: '{{ cal_localized_path('/course-enrollment') }}',
    i18n: {
        networkError: 'Network error. Please try again.',
        networkErrorShort: 'Network error.',
        loading: 'Loading...',
        couldNotLoad: 'Could not load. Please try again.',
        adding: 'Adding...',
        joinWaitlist: 'Join Waitlist',
        waitlistSuccess: 'You have been added to the waiting list. We will be in contact shortly.',
        errorPrefix: 'Error:',
        tryAgain: 'Please try again.',
        submitting: 'Submitting...',
        applicationSuccess: 'Our team will be in contact with you shortly to discuss your application and schedule an informal telephone interview.',
        successLabel: 'Success',
        thankYou: 'Thank you for your application!',
        submitApplication: 'Submit Application',
        close: 'Close'
    }
};
</script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/intlTelInput.min.js" defer></script>
<script src="/js/ca-booking.js?v=20260707" defer></script>