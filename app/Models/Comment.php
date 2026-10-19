<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Comment extends Model
{
    use HasFactory;

    // user_id and post_id are set through relationships, never from user input.
    protected $fillable = [
        'body',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // Loads the newest $limit comments of every given post using only two queries
    // (instead of one query per post) and stores them on each post as "latestComments",
    // oldest first, ready to be shown under the post card in the feed.
    public static function attachLatestTo(iterable $posts, int $limit = 2): void
    {
        $posts = collect($posts);

        if ($posts->isEmpty()) {
            return;
        }

        $ranked = DB::table('comments')
            ->select('id', DB::raw('ROW_NUMBER() OVER (PARTITION BY post_id ORDER BY created_at DESC, id DESC) AS row_num'))
            ->whereIn('post_id', $posts->pluck('id')->all());

        $ids = DB::query()
            ->fromSub($ranked, 'ranked')
            ->where('row_num', '<=', $limit)
            ->pluck('id');

        $byPost = static::with('user')
            ->whereIn('id', $ids)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy('post_id');

        foreach ($posts as $post) {
            $post->setRelation('latestComments', $byPost->get($post->id, collect()));
        }
    }
}
