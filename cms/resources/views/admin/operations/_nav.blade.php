<nav aria-label="Operations sections" class="p-card"><div class="p-actions">
<a href="{{ route('admin.operations.overview') }}" @if(request()->routeIs('admin.operations.overview')) aria-current="page" @endif>Overview</a>
<a href="{{ route('admin.operations.health') }}" @if(request()->routeIs('admin.operations.health')) aria-current="page" @endif>Health</a>
<a href="{{ route('admin.operations.queue') }}" @if(request()->routeIs('admin.operations.queue*')) aria-current="page" @endif>Queue</a>
<a href="{{ route('admin.operations.scheduler') }}" @if(request()->routeIs('admin.operations.scheduler')) aria-current="page" @endif>Scheduler</a>
<a href="{{ route('admin.operations.logs') }}" @if(request()->routeIs('admin.operations.logs')) aria-current="page" @endif>Logs</a>
<a href="{{ route('admin.operations.notifications') }}" @if(request()->routeIs('admin.operations.notifications*')) aria-current="page" @endif>Notifications</a>
<a href="{{ route('admin.operations.audit') }}" @if(request()->routeIs('admin.operations.audit')) aria-current="page" @endif>Audit</a>
<a href="{{ route('admin.operations.exports') }}" @if(request()->routeIs('admin.operations.exports*')) aria-current="page" @endif>Exports</a>
</div></nav>
