<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\FileType;
use App\Permission;
use ZipArchive;
use Intervention\Image\Facades\Image;

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

    private function isImage($filename) {
        $explodeImage = explode('.', $filename);
        $extension = end($explodeImage);

        return in_array($extension, config("folder.image_extensions"));
    }

    private function makeThumbnail($file, $dbFile) {
        if ($this->isImage($dbFile->real_name)) {
            Image::make($file)
            ->resize(200, 200)
            ->save(public_path('thumbnails') . "/$dbFile->id-sm.png", 50, "png");
        } else if ($dbFile->extension() == "pdf") {
            $inputDir = storage_path("app/uploads/") . $dbFile->name;
            $outputDir = public_path('thumbnails') . "/$dbFile->id-sm.png";
            exec("gs -sDEVICE=png16m -dDownScaleFactor=4 -dPDFFitPage=true -dFirstPage=1 -dLastPage=1 -sPAPERSIZE=a4 -sOutputFile='$outputDir' '$inputDir'");
        }
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

        //thumbnail
        $this->makeThumbnail($file, $fileRow);


        return response()->json(['success'=>'true', 'id' => $fileRow->id, 'type' => 'file']);
    }

    public function previewThumbnail($id)
    {
        $id = explode(".", $id)[0];
        $file = File::find($id);
        return response()->file(public_path('thumbnails') . "/{$file->name}-sm");
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
    public function trash(Request $request, $ids)
    {
        if (!$ids) {
            return response()->json(["error" => "files.trash.error"]);
        }

        $ids = explode(",", $ids);

        foreach($ids as $id) {
            $file = File::find($id);
            if ($file && Auth::user()->can("delete", $file)) {
                $file->delete();
            } else {
                return response()->json(["success" => "files.trash.notallowed"]);
            }
        }

        return response()->json(["success" => "files.trash.success"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $ids)
    {
        if (!$ids) {
            return response()->json(["error" => "files.delete.error"]);
        }

        $ids = explode(",", $ids);

        foreach($ids as $id) {
            $file = File::find($id);
            if ($file && Auth::user()->can("forceDelete", $file)) {
                $file->forceDelete();
                return response("success");
            } else {
                return response()->json(["error" => __("files.access.error")]);
            }
        }
    }

    public function getProperties(Request $request, $id) {
        $file = File::find($id);
        if ($file && Auth::user()->can("view", $file)) {
            return response()->json([
                "uploader" => [
                    "value" => User::find($file->user_id)->name,
                    "text" => __("item.uploader"),
                ],
                "upload_date" => [
                    "value" => $file->created_at,
                    "text" => __("item.upload_date"),
                ],
                "last_changed" => [
                    "value" => $file->updated_at,
                    "text" => __("item.edit_date"),
                ],
                "type" => [
                    "value" => $file->type->name,
                    "text" => __("item.type"),
                ],
                "expiry_date" => [
                    "value" => $file->expires_at,
                    "text" => __("item.expiry_date"),
                ],
            ]);
        } else {
            return response()->json(["error" => __("files.access.error")]);
        }
    }

    private function formatPermissionsForBundle($bundle, $type) {
        $permission_types = config("folder.permission_types");
        $formatted = [];

        /**
         * $formatted = [
         *      "<user_id>" => [
         *
         *          "<permission_type> => true,
         *      ],
         * ];
         */
        foreach ($bundle as $item) {
            $formatted[$item->$type]["permissions"][$permission_types[$item->permission_id - 1]] = true;
            $formatted[$item->$type]["item_name"] = ($type == "user_id") ? User::find($item->user_id)->name : "PLACEHOLDER";
        }

        return $formatted;
    }

    /**
     * response format:
     * "columns" => $permission_types,
     *   "items" => [
     *       "ips" => [
     *           "value" => [
     *               [
     *                   "name" => "87.75.25.1",
     *                   "can" => ["read", "write"],
     *               ],
     *               [
     *                   "name" => "78.54.32.1",
     *                   "can" => ["read"],
     *               ],
     *           ],
     *           "text" => __("item.ips"),
     *       ],
     *       "users" => [
     *           "value" => [
     *               [
     *                   "name" => "Admin",
     *                   "id" => "whatever",
     *                   "can" => ["read", "write"],
     *               ],
     *               [
     *                   "name" => "Joe",
     *                   "id" => "whatever",
     *                   "can" => ["read"],
     *               ],
     *           ],
     *           "text" => __("item.allowed_users"),
     *       ],
     *       "others" => [
     *           "value" => ["can" => ["read", "write"]],
     *           "text" => __("item.others"),
     *       ],
     *   ]
     */
    public function getPermissions(Request $request, $id) {
        $file = File::find($id);
        if ($file && Auth::user()->can("view", $file)) {
            $permission_types = config("folder.permission_types");

            // $ips = Permission::where([["ip", "!=", null],["file_id",$file->id]])->get();
            $users = Permission::where([["user_id", "!=", null],["file_id",$file->id]])->get();
            $others = Permission::where([["user_id", User::where("email", "guest")->first()->id],["file_id",$file->id]])->get();

            return response()->json([
                "columns" => $permission_types,
                "items" => [
                    // "ips" => [
                    //     "value" => $this->formatPermissionsForBundle($ips, "ip"),
                    //     "text" => __("item.ips"),
                    // ],
                    "users" => [
                        "value" => $this->formatPermissionsForBundle($users, "user_id"),
                        "text" => __("item.allowed_users"),
                    ],
                    "others" => [
                        "value" => $this->formatPermissionsForBundle($others, "user_id"),
                        "text" => __("item.others"),
                    ],
                ]
            ]);
        } else {
            return response()->json(["error" => __("files.access.error")]);
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
            return response()->json(["error" => __("files.access.error")]);
        }

        $file->name = $validated["name"];
        $file->save();
        return response("success");
    }

    private function makeZip($ids) {
        $zip = new ZipArchive();
        $date = date("Ymd");
        $username = Auth::user()->name;

        $fileName = "{$date}-{$username}-archive.zip";

        if ($zip->open(storage_path("app/archives/{$fileName}"), ZipArchive::CREATE) === TRUE)
        {
            foreach ($ids as $id) {
                $dbFile = File::find($id);
                if ($dbFile) {
                    $zip->addFile(storage_path("app/uploads/{$dbFile->name}"), $dbFile->real_name);
                }
            }

            $zip->close();
        } else {
            return response()->json(["error" => "files.download.ziperror"]);
        }

        return $fileName;
    }

    public function download(Request $request, $ids) {
        if (!$ids) {
            return response()->json(["error" => __("files.download.id.error")]);
        }

        $ids = explode(",", $ids);

        if (count($ids) > 1) {
            $filename = $this->makeZip($ids);
            return response()->download(storage_path("app/archives/{$filename}"));
        } else {
            $file = File::find($ids[0]);
            $filename = $file->name;
            return response()->download(storage_path("app/uploads/{$filename}"), $file->real_name);
        }
    }
}
