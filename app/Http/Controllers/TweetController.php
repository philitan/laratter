<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use Illuminate\Http\Request;

class TweetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 一覧画面の表示
        $tweets = Tweet::with(['user', 'liked'])->latest()->get();
        return view('tweets.index', compact('tweets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // 作成処理の入力側
        return view('tweets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 作成処理の出力側

        // validateで確認
        $request->validate([
            'tweet' => 'required|max:255',
        ]);

        // beforeにtweetを入れる
        $before = $request->tweet;

        // 変換の処理
        $afterarray = [];

        for($i=0; $i<strlen($before); $i++){
            if($i<strlen($before)-2){
                if(preg_match('/^\[$/', $before[$i])){
                    if(preg_match('/^\]$/', $before[$i+2])){
                        if(preg_match('/^a$/', $before[$i+1])){
                            array_push($afterarray, "🍨");
                            $i+=2;
                            continue;
                        }
                        if(preg_match('/^b$/', $before[$i+1])){
                            array_push($afterarray, "🍌");
                            $i+=2;
                            continue;
                        }
                        if(preg_match('/^c$/', $before[$i+1])){
                            array_push($afterarray, "🍛");
                            $i+=2;
                            continue;
                        }
                    }
                }
            }
            array_push($afterarray, $before[$i]);
        }
        $after = implode($afterarray);

        // tweetsテーブルに挿入
        $tweet = new Tweet();
        $tweet->tweet = $after;
        $tweet->before = $before;
        $tweet->user_id = $request->user()->id;
        $tweet->save();

        return redirect()->route('tweets.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tweet $tweet)
    {
        // 詳細画面の表示
        $tweet->load('comments');
        return view('tweets.show', compact('tweet'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tweet $tweet)
    {
        // 更新処理の入力側
        return view('tweets.edit', compact('tweet'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tweet $tweet)
    {
        // 更新処理の出力側
        $request->validate([
            'tweet' => 'required|max:255',
        ]);

        // beforeにtweetを入れる
        $before = $request->tweet;

        // 変換の処理
        $afterarray = [];

        for($i=0; $i<strlen($before); $i++){
            if($i<strlen($before)-2){
                if(preg_match('/^\[$/', $before[$i])){
                    if(preg_match('/^\]$/', $before[$i+2])){
                        if(preg_match('/^a$/', $before[$i+1])){
                            array_push($afterarray, "🍨");
                            $i+=2;
                            continue;
                        }
                        if(preg_match('/^b$/', $before[$i+1])){
                            array_push($afterarray, "🍌");
                            $i+=2;
                            continue;
                        }
                        if(preg_match('/^c$/', $before[$i+1])){
                            array_push($afterarray, "🍛");
                            $i+=2;
                            continue;
                        }
                    }
                }
            }
            array_push($afterarray, $before[$i]);
        }
        $after = implode($afterarray);

        // tweetsテーブルのアップデート
        $tweet->update([  
            "tweet" => $after,
            "before" => $before,
            "user_id" => $request->user()->id,
        ]);

        return redirect()->route('tweets.show', $tweet);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tweet $tweet)
    {
        // 削除処理
        $tweet->delete();
        return redirect()->route('tweets.index');
    }

    /**
     * Search for tweets containing the keyword.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $query = Tweet::query();

        // キーワードが指定されている場合のみ検索を実行
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where('tweet', 'like', '%' . $keyword . '%');
        }

        // ページネーションを追加（1ページに10件表示）
        $tweets = $query
            ->latest()
            ->paginate(10);

        return view('tweets.search', compact('tweets'));
    }
}
