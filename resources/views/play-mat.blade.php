<div class="mb-1">
    <h1>Played cards</h1>
    @forelse($cards as $card)
        {!! $card->render() !!}
        @empty
            <div>No cards yet</div>
    @endforelse
</div>
