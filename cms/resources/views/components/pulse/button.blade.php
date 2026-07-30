@props(['variant' => 'primary', 'type' => 'button', 'loading' => false])
<button type="{{ $type }}" {{ $attributes->class(['p-button', "p-button--{$variant}" => $variant !== 'primary'])->merge(['disabled' => $loading ? true : null, 'aria-busy' => $loading ? 'true' : null]) }}>{{ $loading ? 'Please wait…' : $slot }}</button>
