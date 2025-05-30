@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>{{ $product->name }}</h2>
    <p class="mb-3">{{ $product->description }}</p>
    <p class="mb-4"><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>

    <form id="payment-form" action="{{ route('products.charge', $product->id) }}" method="POST">
        @csrf
        <div id="card-element" class="form-control mb-3"></div>
        <div id="card-errors" class="text-danger mb-3"></div>
        <input type="email" name="email" class="form-control mb-3" placeholder="Enter your email (optional)">
        <button class="btn btn-success" id="submit-button" type="submit">Pay Now</button>
        <a href="{{ route('products.index') }}" class="btn btn-primary">Back to Products List</a>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe("{{ config('cashier.key') }}");
    const elements = stripe.elements();
    const card = elements.create('card');
    card.mount('#card-element');

    card.on('change', function (event) {
        document.getElementById('card-errors').textContent = event.error ? event.error.message : '';
    });

    const form = document.getElementById('payment-form');
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const { paymentMethod, error } = await stripe.createPaymentMethod({
            type: 'card',
            card: card
        });

        if (error) {
            document.getElementById('card-errors').textContent = error.message;
        } else {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'payment_method';
            hiddenInput.value = paymentMethod.id;
            form.appendChild(hiddenInput);
            form.submit();
        }
    });
</script>
@endpush
