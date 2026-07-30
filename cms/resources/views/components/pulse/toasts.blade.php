<div class="p-toast-region" data-toast-region aria-label="Notifications" aria-live="polite" aria-relevant="additions">
@foreach(['success'=>'success','status'=>'success','warning'=>'warning','error'=>'error','info'=>'info'] as $key=>$variant)@if(session()->has($key))<div class="p-toast p-toast--{{ $variant }}" data-toast role="{{ $variant === 'error' ? 'alert' : 'status' }}"><div class="p-toast__body">{{ session($key) }}</div><button type="button" class="p-button p-button--subtle p-icon-button" data-toast-dismiss aria-label="Dismiss notification">×</button></div>@endif @endforeach
</div>
