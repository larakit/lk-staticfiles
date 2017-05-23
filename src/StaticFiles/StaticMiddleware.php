<?php
/**
 * Created by Larakit.
 * Link: http://github.com/larakit
 * User: Alexey Berdnikov
 * Date: 12.05.17
 * Time: 9:20
 */

namespace Larakit\StaticFiles;

class StaticMiddleware {
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, \Closure $next) {
        Manager::init();
        return  $next($request);
    }
}