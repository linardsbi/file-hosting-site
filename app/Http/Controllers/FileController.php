<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\FileType;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // todo: validate if user can add to folder; if folder exists
        $validation = $request->validate([
            'file' => 'required|file|max:1000000',
            'folder_id' => 'required|uuid'
        ]);

        $file      = $validation['file'];
        $extension = $file->getClientOriginalExtension();

        $fileRow = new File();
        $fileRow->user_id = (Auth::id()) ? Auth::id() : User::where("email","guest")->first()->id;
        $fileRow->type_id = FileType::getIdByType($extension);
        $fileRow->folder_id = $validation["folder_id"];
        $fileRow->name = uniqid();
        $fileRow->real_name = $file->getClientOriginalName();
        $fileRow->bytes = $file->getSize();
        $fileRow->expires_at = date("Y-m-d H:m:s", strtotime("+1 month"));
        $file->storeAs('uploads', $fileRow->name);

        $fileRow->save();
        return response()->json(['success'=>'true', 'id' => $fileRow->id, 'type' => 'file']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {

    }

    /**
     * soft delete the specified resource
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function trash(Request $request, $id)
    {
        $file = File::find($id);

        if ($file && Auth::user()->can("delete", $file)) {
            $file->delete();
            return response("success");
        } else {
            return response("fail");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $file = File::find($id);
        if ($file && Auth::user()->can("forceDelete", $file)) {
            $file->forceDelete();
            return response("success");
        } else {
            return response("fail");
        }
    }

    public function rename(Request $request, $id)
    {
        $validated = $request->validate([
            'file_id' => 'required|uuid|exists:files,id',
            'name' => 'required|string|max:128|min:1'
        ]);

        if (isset($validated["errors"])) {
            return response($validated["errors"], 403)->withErrors($validated["errors"]);
        }

        $file = File::find($validated["file_id"]);

        if (Auth::user()->cant("update", $file)) {
            return response(["error" => __("You are not allowed to edit folders in this directory")]);
        }

        $file->name = $validated["name"];
        $file->save();
        return response("success");
    }

    public function download(Request $request, $id) {
        $file = File::find($id);

        if ($file && Auth::user()->can("view", $file)) {
        } else {
            return response(["error" => __("You are not allowed to access this resource")]);
        }
    }
}
