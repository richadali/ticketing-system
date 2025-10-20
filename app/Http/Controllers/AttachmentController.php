<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attachment $attachment)
    {
        // Optional: Add authorization check if needed

        // Delete the file from storage
        if (file_exists(public_path($attachment->attachment_loc))) {
            unlink(public_path($attachment->attachment_loc));
        }

        // Delete the attachment record from the database
        $attachment->delete();

        return response()->json(['success' => true, 'message' => 'Attachment deleted successfully.']);
    }
}
