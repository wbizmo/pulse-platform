@extends($themeRuntime->view('layout'))
@section('title','Pay for order')
@section('content')
<main class="theme-section" aria-labelledby="payment-title"><h1 id="payment-title">Pay for order {{ $order->public_reference }}</h1><p aria-live="polite">Status: {{ str_replace('_',' ',$order->state->value) }}</p><p><strong>{{ $order->currency }} {{ $order->total_minor }}</strong> in minor units.</p>
@if($errors->any())<div role="alert" tabindex="-1">{{ $errors->first() }}</div>@endif
@if($order->state === \App\Domain\Commerce\OrderState::AwaitingPayment)
 @if(count($gateways))<form method="post" action="{{ route('payments.store',$order->public_reference) }}">@csrf<fieldset><legend>Choose a secure payment provider</legend>@foreach($gateways as $gateway)<label><input type="radio" name="gateway" value="{{ $gateway['slug'] }}" required> {{ $gateway['name'] }}</label>@endforeach</fieldset><button type="submit">Continue securely</button></form>
 @else<p role="status">Online payment is not currently configured for this currency. Your order remains reserved until its displayed expiry.</p>@endif
@else<p>Payment is no longer required for this order.</p>@endif
</main>@endsection
