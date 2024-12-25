@php
    $colorClass = !$hidden || $card->knownColor ? "bg-{$card->color->value}-700" : 'bg-white';
    $number = !$hidden || $card->knownNumber ? $card->number->value : 'X';
@endphp

<span class="px-2 mr-2 text-black {{ $colorClass }}">
    {{ $number }}
</span>
