<div class="mb-1">
    <h1>Played cards</h1>
    @if($isEmpty)
        <div>No cards yet</div>
    @else
        @foreach($cards as $color => $colorCards)
            <div>
                @foreach($colorCards as $card)
                    {!! $card->render() !!}
                @endforeach
            <div>
        @endforeach
    @endif
</div>
