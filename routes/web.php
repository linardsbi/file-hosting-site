<?php

use App\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Folder;
use Illuminate\Support\Facades\DB;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route("root_folder", Auth::user()->rootFolder()->id);
    } else {
        return redirect()->route("login")->withErrors("you need to log in");
    }
});

Route::get('/download/{file_id}', "FileController@download");

Route::get('/test/{user_id}/{item_id}/{access_type}', function ($user_id, $item_id, $access_type) {
    dd(DB::select("select pt.id from access_permissions as ap
    left join permission_types as pt on ap.permission_id = pt.id
    left join users as u on ap.user_id = u.id
    where u.email='guest'
    and (ap.folder_id='{$item_id}' or ap.file_id='{$item_id}')
    and pt.name='{$access_type}'", [1]));
});

Route::get('/folder/{folder_id}', "FolderController@show");

Auth::routes();



Route::get('/{folder_id}', 'HomeController@show')->name('root_folder');

Route::post('/upload', 'FileController@store');

Route::get('/file/preview/{id}', 'FileController@previewThumbnail');
Route::delete('/file/trash/{ids}', 'FileController@trash');
Route::post('/file/rename', 'FileController@rename');

Route::post('/folder/create', 'FolderController@store');
Route::delete('/folder/trash/{ids}', 'FolderController@trash');
Route::post('/folder/rename', 'FolderController@rename');

Route::get('/trash', "FolderController@showTrash");
Route::get('/shared', "FolderController@showShared");

