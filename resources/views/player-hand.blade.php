<div>
    @if($displayName)
        <h1>{{ $name }}</h1>
    @endif
    <div>
        @foreach($cards as $card)
           {!! $card->render($hidden) !!}
        @endforeach
    <div>
</div>
