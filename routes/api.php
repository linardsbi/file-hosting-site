<?php
use App\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/{folder_id}', function ($folder_id) {
    $folder = Folder::find($folder_id);

    if (!$folder && Auth::check()) {
        $folder = Auth::user()->rootFolder();
    }

    $subfolders = Folder::where("parent_id", $folder_id)->get();
    // todo: send only necessary values
    return response()->json([
        "parent_id" => (is_null($folder->parent_id)) ? "" : $folder->parent_id,
        "files" => $folder->files,
        "folders" => $subfolders
    ],
        ($folder) ? 200 : 404);
});
