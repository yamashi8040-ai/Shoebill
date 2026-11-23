@php($announcements = \App\Models\Announcement::query()->active()->latest()->get())
@if($announcements->isNotEmpty())
    {{-- Dismissed announcements are remembered per browser; editing one brings it back. --}}
    <div class="sb-announcements mb-4 flex flex-col gap-3">
        @foreach($announcements as $announcement)
            <div x-data="{ key: 'sb-announcement-{{ $announcement->id }}-{{ $announcement->updated_at?->timestamp }}', hidden: false }"
                 x-init="try { hidden = localStorage.getItem(key) === '1' } catch (e) {}"
                 x-show="!hidden" x-cloak>
                <x-filament::callout :color="$announcement->type" :icon="\App\Enums\TablerIcon::Speakerphone">
                    <x-slot name="heading">{{ $announcement->title }}</x-slot>
                    @if(filled($announcement->body))
                        <x-slot name="description">{{ str($announcement->body)->markdown()->sanitizeHtml()->toHtmlString() }}</x-slot>
                    @endif
                    <x-slot name="controls">
                        <x-filament::icon-button :icon="\App\Enums\TablerIcon::X" color="gray" :label="trans('admin/announcement.dismiss')"
                            x-on:click="hidden = true; try { localStorage.setItem(key, '1') } catch (e) {}" />
                    </x-slot>
                </x-filament::callout>
            </div>
        @endforeach
    </div>
@endif
