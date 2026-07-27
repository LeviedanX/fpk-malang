@props(['profile', 'fixed' => false])

@php
    $desktopBackgroundUrl = $profile->hero_background_path
        ? \Illuminate\Support\Facades\Storage::url($profile->hero_background_path)
        : null;
    $mobileBackgroundUrl = $profile->hero_mobile_background_path
        ? \Illuminate\Support\Facades\Storage::url($profile->hero_mobile_background_path)
        : $desktopBackgroundUrl;
@endphp

<div
    @class([
        'home-fixed-background' => $fixed,
        'public-synced-background' => ! $fixed,
    ])
    @if ($fixed) data-home-fixed-background @else data-public-synced-background @endif
    aria-hidden="true"
>
    @if ($desktopBackgroundUrl)
        <div
            class="home-fixed-background__image hidden lg:block"
            style="background-image: url('{{ $desktopBackgroundUrl }}')"
            data-hero-background="desktop"
        ></div>
    @else
        <div class="hero-motif absolute inset-0 hidden lg:block" data-hero-default-decoration="desktop"></div>
    @endif

    @if ($mobileBackgroundUrl)
        <div
            class="home-fixed-background__image home-fixed-background__image--mobile lg:hidden"
            style="background-image: url('{{ $mobileBackgroundUrl }}')"
            data-hero-background="mobile"
            data-hero-background-source="{{ $profile->hero_mobile_background_path ? 'mobile' : 'desktop-fallback' }}"
        ></div>
    @else
        <div class="hero-motif absolute inset-0 lg:hidden" data-hero-default-decoration="mobile"></div>
    @endif

    <div class="home-fixed-background__overlay home-fixed-background__overlay--desktop hidden lg:block" data-hero-overlay="desktop"></div>
    <div class="home-fixed-background__overlay home-fixed-background__overlay--mobile lg:hidden" data-hero-overlay="mobile"></div>
</div>
