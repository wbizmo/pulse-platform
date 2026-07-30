@extends('admin.layouts.app', [
    'title' => $plugin->name . ' Settings',
    'heading' => $plugin->name . ' Settings',
    'subheading' => 'Configure payment keys, provider mode, wallet addresses, and checkout behavior.'
])

@section('content')

    <form method="POST" action="{{ route('admin.plugins.settings.update', $plugin) }}" class="p-module-settings-form">
        @csrf

        <div class="p-module-settings-grid">
            <section class="p-card">
                <div class="p-card-head">
                    <h3>Payment Credentials</h3>
                    <p>Store public keys, secret keys, webhook secrets, and provider mode for payment plugins.</p>
                </div>

                <div class="p-module-form-grid">
                    <label>
                        <span>Public / Publishable Key</span>
                        <input type="text" name="public_key" value="{{ $settings['public_key'] ?? '' }}">
                    </label>

                    <label>
                        <span>Secret Key</span>
                        <input type="password" name="secret_key" value="{{ $settings['secret_key'] ?? '' }}">
                    </label>

                    <label>
                        <span>Webhook Secret</span>
                        <input type="password" name="webhook_secret" value="{{ $settings['webhook_secret'] ?? '' }}">
                    </label>

                    <label>
                        <span>Currency</span>
                        <input type="text" name="currency" value="{{ $settings['currency'] ?? 'USD' }}">
                    </label>
                </div>

                <div class="p-module-toggle-list p-module-settings-spacer">
                    <label class="p-module-toggle-row">
                        <span>Enable test/sandbox mode</span>
                        <span class="p-module-switch">
                            <input type="checkbox" name="test_mode" value="1" @checked(($settings['test_mode'] ?? '1') == '1')>
                            <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                        </span>
                    </label>

                    <label class="p-module-toggle-row">
                        <span>Show this payment method on frontend checkout</span>
                        <span class="p-module-switch">
                            <input type="checkbox" name="show_on_frontend" value="1" @checked(($settings['show_on_frontend'] ?? '1') == '1')>
                            <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                        </span>
                    </label>
                </div>
            </section>

            <section class="p-card">
                <div class="p-card-head">
                    <h3>Crypto Wallets</h3>
                    <p>Used by the crypto payments plugin when active.</p>
                </div>

                <div class="p-module-form-grid p-module-form-grid-single">
                    <label>
                        <span>BTC wallet</span>
                        <input type="text" name="btc_wallet" value="{{ $settings['btc_wallet'] ?? '' }}">
                    </label>

                    <label>
                        <span>ETH wallet</span>
                        <input type="text" name="eth_wallet" value="{{ $settings['eth_wallet'] ?? '' }}">
                    </label>

                    <label>
                        <span>USDT TRC20 wallet</span>
                        <input type="text" name="usdt_trc20_wallet" value="{{ $settings['usdt_trc20_wallet'] ?? '' }}">
                    </label>

                    <label>
                        <span>USDT ERC20 wallet</span>
                        <input type="text" name="usdt_erc20_wallet" value="{{ $settings['usdt_erc20_wallet'] ?? '' }}">
                    </label>

                    <label>
                        <span>Confirmations required</span>
                        <input type="number" name="confirmations_required" value="{{ $settings['confirmations_required'] ?? '2' }}">
                    </label>
                </div>
            </section>
        </div>

        <div class="p-module-save-bar">
            <div>
                <strong>{{ $plugin->name }}</strong>
                <span>Save payment provider keys, webhook secrets, and wallet addresses.</span>
            </div>

            <button type="submit" class="p-button">
                <span>Save settings</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>
@endsection
