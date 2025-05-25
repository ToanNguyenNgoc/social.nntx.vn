<?php

namespace App\Http\Middleware;

use App\Models\Topic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTopicJoined
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $topic_id = $request->input('topic_id');
        if (!$topic_id) return response()->json(['message' => 'topic_id is required!', 'status' => 400], 400);
        $topic = Topic::find($topic_id);
        if (!$topic) return response()->json(['message' => 'Topic not found!', 'status' => 404], 404);

        $joined = $topic->topicUsers()
            ->where('user_id', auth('sanctum')->user()->id)
            ->whereNotNull('joined_at')
            ->exists();
        if (!$joined) return response()->json(['message' => 'Topic not found!!', 'status' => 404,], 404);
        
        $request->attributes->set('topic', $topic);
        return $next($request);
    }
}
