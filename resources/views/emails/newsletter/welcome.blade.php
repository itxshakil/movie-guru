<x-mail::message>
    # Welcome to Movie Guru, {{ $firstName }}! 🎬

    We're thrilled to have you join our community of movie lovers.

    Every week, we'll bring you:

    <x-mail::panel>
        🎥 **Curated movie picks** — hidden gems, top-rated classics, and fresh releases
        ⭐ **Personalized recommendations** based on what's trending
        📰 **Behind-the-scenes insights** and cinematic news
    </x-mail::panel>

    ## What's Next?

    Start exploring thousands of movies and series right now. Find where to watch, read reviews, and discover your next
    favorite film.

    <x-mail::button :url="config('app.url')">
        Browse Movies Now
    </x-mail::button>

    If you ever want to unsubscribe, you can do so at any time from the link below.

    Thanks for joining us,
    **The Movie Guru Team**
</x-mail::message>
