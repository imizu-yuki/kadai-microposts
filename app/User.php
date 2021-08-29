<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function index()
    {
        $data = [];
        if (\Auth::check()) {
            // 認証済みユーザ（閲覧者）を取得
            $user = \Auth::user();
            // ユーザとフォロー中ユーザの投稿の一覧を作成日時の降順で取得
            $microposts = $user->feed_microposts()->orderBy('created_at', 'desc')->paginate(10);

            $data = [
                'user' => $user,
                'microposts' => $microposts,
            ];
        }

        // Welcomeビューでそれらを表示
        return view('welcome', $data);
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
     public function loadRelationshipCounts()
    {
        $this->loadCount(['microposts', 'followings', 'followers']);
    }
    
    /**
     * $userIdで指定されたユーザをアンフォローする。
     *
     * @param  int  $userId
     * @return bool
     */
    public function unfollow($userId)
    {
        // すでにフォローしているかの確認
        $exist = $this->is_following($userId);
        // 対象が自分自身かどうかの確認
        $its_me = $this->id == $userId;

        if ($exist && !$its_me) {
            // すでにフォローしていればフォローを外す
            $this->followings()->detach($userId);
            return true;
        } else {
            // 未フォローであれば何もしない
            return false;
        }
    }

    /**
     * 指定された $userIdのユーザをこのユーザがフォロー中であるか調べる。フォロー中ならtrueを返す。
     *
     * @param  int  $userId
     * @return bool
     */
    public function is_following($userId)
    {
        // フォロー中ユーザの中に $userIdのものが存在するか
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    
    /**
     * このユーザがフォロー中のユーザ。（ Userモデルとの関係を定義）
     */
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }

    /**
     * このユーザをフォロー中のユーザ。（ Userモデルとの関係を定義）
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    /**
     * このユーザとフォロー中ユーザの投稿に絞り込む。
     */
    public function feed_microposts()
    {
        // このユーザがフォロー中のユーザのidを取得して配列にする
        $userIds = $this->followings()->pluck('users.id')->toArray();
        // このユーザのidもその配列に追加
        $userIds[] = $this->id;
        // それらのユーザが所有する投稿に絞り込む
        return Micropost::whereIn('user_id', $userIds);
    }
    
    /**
* このユーザがお気に入りしてる1以上のmicropost。
*/
public function favoritesMicroposts()
{
return $this->belongsToMany(User::class, 'favorites', 'user_id', 'micropost_id')->withTimestamps();
}

/**
* micropostをお気に入りにしてる1以上のユーザ。
*/
public function favoritesUser()
{
return $this->belongsToMany(User::class, 'favorites', 'micropost_id', 'user_id')->withTimestamps();
}

/**
* $micropost_idで指定されたmicropostをお気に入り登録する。
*
* @param int $userId
* @return bool
*/
public function favorite($userId)
{
    // すでにお気に入りしているかの確認
    $exist = $this->is_favorite($micropostId);
    // 相手が自分自身かどうかの確認
    $its_me = $this->id == $micropostId;
    
    if ($exist || $its_me) {
    // すでにお気に入り登録していればお気に入り登録を外す
    
    return false;
    } else {
    // お気に入り登録していなければお気に入り登録をする
    $this->favorite()->attach($userId);
    return true;
    }

}

/**
* $micropost_idで指定されたmicropostをお気に入り登録を外す。
*
* @param int $userId
* @return bool
*/
public function unfavorite($userId)
{
    // すでにフォローしているかの確認
    $exist = $this->is_favorite($userId);
    // 相手が自分自身かどうかの確認
    $its_me = $this->id == $userId;
    
    if ($exist && !$its_me) {
    // すでにフォローしていればフォローを外す
    $this->favorite()->detach($micropostId);
    return true;
    } else {
    // 未フォローであれば何もしない
    return false;
    }
}

/**
* 指定された $userIdのユーザをこのユーザがフォロー中であるか調べる。フォロー中ならtrueを返す。
*
* @param int $userId
* @return bool
*/
public function is_favorite($userId)
{
    // フォロー中ユーザの中に $userIdのものが存在するか
    return $this->favorite()->where('ｍicrosoft_id', $userId)->exists();
}
}
