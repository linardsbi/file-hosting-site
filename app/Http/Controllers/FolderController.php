<?php

namespace App\Http\Controllers;

use App\Folder;
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

        $folder = new Folder([
            "parent_id" => $validated["parent_id"],
            "user_id" => Folder::find($validated["parent_id"])->user_id,
            "name" => $validated["name"],
        ]);

        $folder->save();

        return response(["new_id" => $folder->id]);
    }
}
