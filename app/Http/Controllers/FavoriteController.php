<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;

class FavoriteController extends Controller
{
    public function show($id)
    {
        // idの値でユーザを検索して取得
        $user = User::findOrFail($id);

        // 関係するモデルの件数をロード
        $user->loadRelationshipCounts();

        // ユーザのお気に入り一覧を取得
       $favorites = $user->favoritesMicroposts()->paginate(10);
       
        // 投稿一覧ビューでそれらを表示
        return view('users.show', [
            'user' => $user,
            'favorites' => $favorites,
        ]);
    }
    
    /**
    * 投稿をお気に入り登録するアクション。
    *
    * @param $id 相手ユーザのid
    * @return \Illuminate\Http\Response
    */
    public function store($id)
    {
    // 認証済みユーザ（閲覧者）が、 投稿をお気に入り登録する
    \Auth::user()->favorite($microsoftId);
    // 前のURLへリダイレクトさせる
    return back();
    }

    /**
    * 投稿のお気に入り登録を外すアクション。
    *
    * @param $id 相手ユーザのid
    * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
    // 認証済みユーザ（閲覧者）が、 idのユーザをアンフォローする
    \Auth::user()->unfollow($id);
    // 前のURLへリダイレクトさせる
    return back();
    }
}