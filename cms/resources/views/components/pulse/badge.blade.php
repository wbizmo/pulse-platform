@props(['variant'=>'neutral'])<span {{ $attributes->class(['p-badge',"p-badge--{$variant}"=>$variant !== 'neutral']) }}>{{ $slot }}</span>
