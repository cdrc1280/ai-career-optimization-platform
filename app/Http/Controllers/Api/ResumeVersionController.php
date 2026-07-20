<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResumeVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResumeVersionController extends Controller {
    public function destroy(Request $request, ResumeVersion $resumeVersion): JsonResponse {
        $this->authorize('delete', $resumeVersion->resume);
        if ($resumeVersion->is_master) {
            return response()->json(['message' => 'Cannot delete the master version.'], 422);
        }
        $resumeVersion->delete();
        return response()->json(status: 204);
    }

    public function setMaster(Request $request, ResumeVersion $resumeVersion): JsonResponse {
        $this->authorize('update', $resumeVersion->resume);
        // Unset current master
        ResumeVersion::where('resume_id', $resumeVersion->resume_id)
            ->where('is_master', true)
            ->update(['is_master' => false]);
        $resumeVersion->update(['is_master' => true]);
        return response()->json($resumeVersion);
    }
}
