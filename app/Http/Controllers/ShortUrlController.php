<?php

use App\Models\ShortUrl;

class ShortUrlController extends Controller
{
    public function redirect($code)
    {
        $short = ShortUrl::where('code',$code)->firstOrFail();

        // track clicks
        $short->increment('clicks');

        return redirect($short->url);
    }
}