<div class="mb-1">
    <h1>Discard</h1>
    <div>
        @forelse($cards as $card)
            {!! $card->render(false) !!}
        @empty
            No cards yet
        @endforelse
    </div>
</div>
