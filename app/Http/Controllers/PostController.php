<?php

namespace App\Http\Controllers;

use App\Jobs\VerifyRegisterMail;
use App\Mail\VerifyRegister;
use App\Models\MediaTemporary;
use App\Models\Post;
use App\Repositories\PostRepo;
use App\Utils\RegexUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class PostController extends Controller
{
    //
    public function __construct(protected PostRepo $post_repo) {}

    public function index()
    {
        // Mail::to('5751071044@st.utc2.edu.vn')->send(new VerifyRegister());
        VerifyRegisterMail::dispatch();
        return $this->jsonResponse($this->post_repo->paginate());
    }

    public function show(int $id)
    {
        return $this->jsonResponse($this->post_repo->findOrFail($id));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content'       => ['required', 'string', RegexUtils::REGEX_TAGS],
            'media_ids'     => 'array',
            'media_ids.*'   => 'required|integer'
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        $post = $this->post_repo->create([
            'user_id' => $this->onUserAuth()->id,
            'content' => $request->content,
        ]);
        if ($request->has('media_ids')) {
            foreach ($request->media_ids as $media_id) {
                $this->addMediaToModel($post, $media_id, MediaTemporary::COLLECTION_POST);
            }
        }
        return $this->jsonResponse($post->refresh());
    }

    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'media_ids'     => 'array',
            'media_ids.*'   => 'required|integer'
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        $user_id = $this->onUserAuth()->id;
        $post = Post::where('user_id', $user_id)->findOrFail($id);
        foreach ($request->media_ids as $media_id) {
            $this->addMediaToModel($post, $media_id, MediaTemporary::COLLECTION_POST);
        }
        $post->update($request->only(['content', 'status']));
        return $this->jsonResponse($post->refresh());
    }

    public function destroy(int $id)
    {
        Post::where('user_id', $this->onUserAuth()->id)->findOrFail($id)->delete();
        return $this->jsonResponse([], 202);
    }
}
