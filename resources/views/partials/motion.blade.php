<!-- SyBook Motion & Skeleton Assets -->
<link rel="stylesheet" href="{{ asset('css/motion.css') }}">
<script src="{{ asset('js/motion.js') }}" defer></script>
<!-- SyBook Logout Confirmation Modal Assets -->
<link rel="stylesheet" href="{{ asset('css/logout-modal.css') }}">
<script src="{{ asset('js/logout-confirm.js') }}" defer></script>
<noscript>
    <style>
        .page-motion-enter, .page-motion-leave {
            animation: none !important;
            transform: none !important;
            opacity: 1 !important;
        }
        #motion-progress-bar {
            display: none !important;
        }
        #bookSkeletonGrid, #detailBookSkeleton {
            display: none !important;
        }
        #bookGrid, #detailBookReal {
            display: grid !important;
            opacity: 1 !important;
        }
    </style>
</noscript>
