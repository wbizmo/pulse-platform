@props(['variant'=>'info'])<div {{ $attributes->class(['p-alert',"p-alert--{$variant}"])->merge(['role'=>$variant === 'error' ? 'alert' : 'status']) }}>{{ $slot }}</div>
