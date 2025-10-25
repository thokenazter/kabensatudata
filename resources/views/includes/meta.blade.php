@php
    $appName = 'PKM Kaben — Satu Data';
    $defaultDescription = 'PKM Kaben – Satu Data: sistem informasi Puskesmas Kaben untuk pendataan dan monitoring kesehatan. Dashboard IKS, peta interaktif, laporan real-time.';
    $metaTitle = isset($meta_title)
        ? $meta_title
        : (View::hasSection('meta_title') ? trim($__env->yieldContent('meta_title')) : $appName);
    $metaDescription = isset($meta_description)
        ? $meta_description
        : (View::hasSection('meta_description') ? trim($__env->yieldContent('meta_description')) : $defaultDescription);
@endphp

<meta name="description" content="{{ $metaDescription }}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ url()->current() }}">

<!-- Open Graph -->
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ asset('images/iconsatudata.PNG') }}">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
