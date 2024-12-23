<div class="mb-1">
    <h1>Discard</h1>
    @forelse($cards as $card)
        {!! $card->render(false) !!}
    @empty
        <div>No cards yet</div>
    @endforelse
</div>
