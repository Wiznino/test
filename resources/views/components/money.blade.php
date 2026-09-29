@props(['amount', 'currency' => 'GHS'])
{{ $currency }} {{ number_format((float) $amount, 2) }}
