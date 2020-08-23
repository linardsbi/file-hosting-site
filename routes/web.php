<?php

use App\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

Route::get('/download/{file_id}', function ($id) {
    $file = File::find($id);
    if ($file) {
        //$localfile = file_get_contents(base_path() . "\\storage\\app\\uploads\\$file->name");
        return response()->download(storage_path('app/uploads') ."/". $file->name, $file->real_name);
    }
    else {
        abort(404);
    }
});

Route::post('/upload', 'FileController@store');

Auth::routes();

Route::delete('/files/trash/{id}', 'FileController@trash');
Route::post('/files/rename/{id}', 'FileController@rename');

Route::post('/folders/create', 'FolderController@store');

Route::get('/{folder_id}', 'HomeController@show')->name('root_folder');
