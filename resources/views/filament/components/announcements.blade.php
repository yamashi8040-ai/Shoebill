@php($announcements = \App\Models\Announcement::query()->active()->notDismissedBy(user())->latest()->get())
@if($announcements->isNotEmpty())
    {{-- Closing is saved per user on the server, so it stays closed everywhere until the announcement is edited. --}}
    <div class="sb-announcements">
        @foreach($announcements as $announcement)
            <div class="sb-announcement sb-announcement-{{ $announcement->type }}" x-data="{ open: true }" x-show="open"
                 x-transition:leave="sb-announcement-leave" x-transition:leave-start="sb-announcement-leave-start" x-transition:leave-end="sb-announcement-leave-end"
                 wire:key="announcement-{{ $announcement->id }}" role="status">
                <span class="sb-announcement-icon">
                    <x-filament::icon :icon="\App\Enums\TablerIcon::Speakerphone" class="h-5 w-5" />
                </span>
                <div class="sb-announcement-text">
                    <div class="sb-announcement-title">{{ $announcement->title }}</div>
                    @if(filled($announcement->body))
                        <div class="sb-announcement-body">{{ str($announcement->body)->markdown()->sanitizeHtml()->toHtmlString() }}</div>
                    @endif
                </div>
                <button type="button" class="sb-announcement-close" aria-label="{{ trans('admin/announcement.dismiss') }}" title="{{ trans('admin/announcement.dismiss') }}"
                        x-on:click="open = false; fetch(@js(route('announcements.dismiss', $announcement)), { method: 'POST', headers: { 'X-CSRF-TOKEN': @js(csrf_token()), 'Accept': 'application/json' } })">
                    <x-filament::icon :icon="\App\Enums\TablerIcon::X" class="h-4 w-4" />
                </button>
            </div>
        @endforeach
    </div>
@endif
