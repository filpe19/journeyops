{{-- Account links shared by both layouts. Link and button labels are also used by the synthetic traffic generator. --}}
@auth
    @if (auth()->user()->isProducer())
        <a href="{{ route('producer.dashboard') }}" class="btn-ghost min-h-10 px-3 whitespace-nowrap">Organizer</a>
    @endif
    @can('view-ops')
        <a href="{{ route('ops.dashboard') }}" class="btn-ghost min-h-10 px-3 whitespace-nowrap">Ops console</a>
    @endcan
    <a href="{{ route('account') }}" class="btn-ghost hidden min-h-10 max-w-40 truncate px-3 sm:inline-flex">{{ auth()->user()->name }}</a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-ghost min-h-10 px-3 whitespace-nowrap text-ink-muted">Sign out</button>
    </form>
@else
    <a href="{{ route('login') }}" class="btn-ghost min-h-10 px-3">Sign in</a>
@endauth
