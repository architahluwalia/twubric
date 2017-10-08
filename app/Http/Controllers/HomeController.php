<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Twitter;
use Session;
use DateTime;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $request_token = [
            'token'  => Session::get('oauth_request_token'),
            'secret' => Session::get('oauth_request_token_secret'),
        ];
        Twitter::reconfig($request_token);
        $response = Twitter::getFollowers(['count' => 20, 'format' => 'array']);
        return view('home', ['followers' => $response]);
    }

    /**
     * Function to show the twubric score of a user
     **/
    public function getUserScore($id)
    {
        $userProfile =(array) Twitter::getUsers(['user_id' => $id]);
        $reburic = [];
        $reburic['name'] = $userProfile['name'];
        $reburic['screen name'] = $userProfile['screen_name'];
        $reburic['Followers'] = $userProfile['followers_count'];
        $reburic['Freinds'] = $userProfile['friends_count'];
        $reburic['Statuses'] = $userProfile['statuses_count'];
       // Friend Score is calculated as the ratio of followers and following
        if ($userProfile['friends_count'] == 0) {
            $friendScore = 0;
        } else {
            $friendScore = ($userProfile['followers_count'] / $userProfile['friends_count']) * 100;
        }
        if ($friendScore > 200) {
            $reburic['friends'] = 2;
        } elseif ($friendScore > 100) {
            $reburic['friends'] = 1.5;
        } elseif ($friendScore > 75) {
            $reburic['friends'] = 1;
        } else {
            $reburic['friends'] = 0.5;
        }

       //Chirpines is understood as total number of statuses divided by the number of days active
       $createdAt = DateTime::createFromFormat('D M d H:i:s P Y', $userProfile['created_at']);
        $interval = $createdAt->diff(new DateTime());
        $chirpinessScore = $userProfile['statuses_count'] / $interval->days;
        if ($chirpinessScore > 5) {
            $reburic['chirpiness'] = 4;
        } elseif ($chirpinessScore > 3) {
            $reburic['chirpiness'] = 3;
        } elseif ($chirpinessScore > 1) {
            $reburic['chirpiness'] = 2;
        } else {
            $reburic['chirpiness'] = 1;
        }

       //Influece is calculated taking into account the tweets and their retweets
       $timelineTweets =(array) Twitter::getUserTimeline(['user_id' => $id]);
        $tweets = 0;
        $rt = 0;
        foreach ($timelineTweets as $tweet) {
            $tweets++;
            $rt += $tweet->retweet_count;
        }
        if ($tweets == 0) {
            $influenceRatio = 0;
        } else {
            $influenceRatio = $rt / $tweets;
        }
        if ($influenceRatio > 5) {
            $reburic['influence'] = 4;
        } elseif ($influenceRatio > 3) {
            $reburic['influence'] = 3;
        } elseif ($influenceRatio > 1) {
            $reburic['influence'] = 2;
        } else {
            $reburic['influence'] = 1;
        }

        $reburic['Total score'] = $reburic['influence'] + $reburic['friends'] + $reburic['chirpiness'];
        return view('score', ['reburic' => $reburic]);
    }
}
