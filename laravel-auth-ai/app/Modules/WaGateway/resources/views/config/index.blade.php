@extends('layouts.app-dashboard')

@section('title', 'WhatsApp Gateway')
@section('page-title', 'WhatsApp Gateway')

@section('content')

{{-- ⚠️ Alert: Risiko Fonnte --}}
<div 
    x-data="{ showAlert: true }" 
    x-show="showAlert"
    x-transition
    class="my-6 p-4 rounded-xl border 
           bg-yellow-50 border-yellow-200 text-yellow-800
           dark:bg-yellow-500/10 dark:border-yellow-500/20 dark:text-yellow-300"
>

    <div class="flex items-start gap-3">

        {{-- Icon utama --}}
        <div class="text-lg mt-0.5">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        {{-- Content --}}
        <div class="flex-1 text-sm space-y-2">

            {{-- Title --}}
            <p class="font-semibold">
                Fonnte WA-Gateway (Unofficial WhatsApp API)
            </p>

            {{-- Points --}}
            <ul class="space-y-1">
                <li class="flex items-center gap-2">
                    <i class="fa-solid fa-ban text-xs opacity-80"></i>
                    <span>Berisiko tinggi terkena ban nomor</span>
                </li>

                <li class="flex items-center gap-2">
                    <i class="fa-solid fa-user-secret text-xs opacity-80"></i>
                    <span>Pastikan pakai nomor wa tumbal untuk testing!</span>
                </li>

                <li class="flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-xs opacity-80"></i>
                    <span>Disarankan hanya untuk development/testing</span>
                </li>
            </ul>

        </div>

        {{-- Close --}}
        <button 
            @click="showAlert = false"
            class="text-yellow-700 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-200 transition"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>

    </div>
</div>

<div class="space-y-6" x-data="{ tab: 'gateways' }">
    {{-- Toolbar & Tab Selection --}}
    @include('wa-gateway::config.partials.tabs')

    <div class="mt-6">
        <template x-if="tab === 'gateways'">
            <div class="space-y-6">
                @include('wa-gateway::config.partials.stats')
                @include('wa-gateway::config.partials.connection_hub')
            </div>
        </template>

        <template x-if="tab === 'templates'">
            @include('wa-gateway::config.partials.templates_tab')
        </template>

        <template x-if="tab === 'logs'">
            <div class="space-y-6">
                @include('wa-gateway::config.partials.logs_table')
            </div>
        </template>
    </div>
</div>

{{-- Modals --}}
@include('wa-gateway::config.partials.modals')

@push('scripts')
    {{-- Main Logic --}}
    @include('wa-gateway::config.partials.scripts')
@endpush

@endsection
