<?php

namespace App\Http\Controllers;

use App\Folder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'required|uuid|exists:folders,id',
            //'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:128|min:1'
        ]);

        if (isset($validated["errors"])) {
            return response($validated["errors"], 403)->withErrors($validated["errors"]);
        }

        if (Auth::user()->cant("create", [Folder::class, $validated["parent_id"]])) {
            return response(["error" => __("You are not allowed to create folders in this directory")]);
        }

        // todo: fix magic number
        $folder = new Folder([
            "parent_id" => $validated["parent_id"],
            "user_id" => Folder::find($validated["parent_id"])->user_id,
            "name" => $validated["name"],
            "type_id" => 3,
        ]);

        $folder->save();

        return response(["new_id" => $folder->id]);
    }

    /**
     * soft delete the specified resource
     *
     * @return \Illuminate\Http\Response
     */
    public function trash(Request $request, $id)
    {
        $folder = Folder::find($id);
        if ($folder && Auth::user()->can("delete", $folder)) {
            $folder->delete();
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
        $folder = Folder::find($id);
        if ($folder && Auth::user()->can("forceDelete", $folder)) {
            $folder->forceDelete();
            return response("success");
        } else {
            return response("fail");
        }
    }

    public function rename(Request $request)
    {
        $validated = $request->validate([
            'folder_id' => 'required|uuid|exists:folders,id',
            'name' => 'required|string|max:128|min:1'
        ]);

        if (isset($validated["errors"])) {
            return response($validated["errors"], 403)->withErrors($validated["errors"]);
        }

        if (Auth::user()->cant("update", [Folder::class, $validated["folder_id"]])) {
            return response(["error" => __("You are not allowed to edit folders in this directory")]);
        }

        $folder = Folder::find($validated["folder_id"]);
        $folder->name = $validated["name"];
        $folder->save();
        return response("success");
    }

    public function showTrash(Request $request)
    {

    }


    public function show(Request $request, $folder_id) {
        $folder = Folder::find($folder_id);

        if (!$folder && Auth::check()) {
            $folder = Auth::user()->rootFolder();
        }

        if (Auth::user()->can("view", $folder)) {
            $subfolders = Folder::where("parent_id", $folder_id)->get();
            // todo: send only necessary values
            return response()->json([
                "parent_id" => (is_null($folder->parent_id)) ? "" : $folder->parent_id,
                "files" => $folder->files,
                "folders" => $subfolders
            ]);
        } else {
            return response()->json(["error" => "true"]);
        }
    }
}
