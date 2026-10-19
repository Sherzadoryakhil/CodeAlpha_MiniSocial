@props(['user', 'following', 'small' => false])

{{-- Nobody sees a follow button on their own entry. --}}
@if (auth()->id() !== $user->id)
    @if ($following)
        <form method="POST" action="{{ route('follows.destroy', $user) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-primary {{ $small ? 'btn-sm' : '' }}"
                    aria-label="Unfollow {{ $user->name }}"><i class="bi bi-person-check-fill" aria-hidden="true"></i> Unfollow</button>
        </form>
    @else
        <form method="POST" action="{{ route('follows.store', $user) }}">
            @csrf
            <button type="submit" class="btn btn-primary {{ $small ? 'btn-sm' : '' }}"
                    aria-label="Follow {{ $user->name }}"><i class="bi bi-person-plus-fill" aria-hidden="true"></i> Follow</button>
        </form>
    @endif
@endif
