@extends('layouts.app-dashboard')

@section('title', 'Access Management')
@section('page-title', 'Akses & Izin')
@section('page-sub', 'Kelola role, izin granular, dan penetapan akses pengguna dalam satu panel terpadu.')

@section('content')

<style>
    /* Global refinement for elegant look */
    .access-card { @apply bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm transition-all duration-300; }
    .rounded-custom { @apply rounded; } /* Small rounding as requested */
    .nav-tab-active { @apply bg-indigo-600 text-white shadow-md shadow-indigo-200 dark:shadow-none; }
    .nav-tab-inactive { @apply bg-white dark:bg-slate-900 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 border-slate-200 dark:border-slate-800; }
</style>

{{-- ─────────────────────────────────────────────────────────────────────────
     NAVIGATION PANEL (TABS)
───────────────────────────────────────────────────────────────────────── --}}
<div class="mb-8">
    @include('authorization::management.partials.navigation')
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     SECTIONS
───────────────────────────────────────────────────────────────────────── --}}
<div class="mt-6">
    {{-- Roles Section --}}
    <div id="section-roles" class="access-section">
        @include('authorization::management.sections.roles')
    </div>

    {{-- Permissions Section (Read-only) --}}
    <div id="section-permissions" class="access-section hidden">
        @include('authorization::management.sections.permissions')
    </div>

    {{-- Role Assignment Section --}}
    <div id="section-assignment" class="access-section hidden">
        @include('authorization::management.sections.assignment')
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     MODALS
───────────────────────────────────────────────────────────────────────── --}}
@include('authorization::management.modals.assign_modal')

{{-- ─────────────────────────────────────────────────────────────────────────
     SCRIPTS
───────────────────────────────────────────────────────────────────────── --}}
@include('authorization::management.partials.scripts')

@endsection
